<?php

/////////////////////////////////// Helpers ////////////////////////////////

function ps_call_priority_gateway_api($method, $action, $data){

    $url = get_option( 'prio_sync_url' ) . '/api/' . $action;
    $url = str_replace(' ', '%20', $url);


    $curl = curl_init();

    switch ($method){
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE) );			 					
            break;
        case "PATCH":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE) );			 					
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    // OPTIONS:
    $apikey = get_option( 'prio_sync_apikey' );

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'authorization: Token ' . $apikey,
        'Content-Type: application/json',
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);


    // EXECUTE:
    $result = curl_exec($curl);
    if(!$result){return null;}
    curl_close($curl);

    return json_decode($result,true);
}

function ps_is_null_or_empty_string($str){
    return (!isset($str) || trim($str) === '');
}

function ps_valid_apikey(WP_REST_Request $request){
    $auth_header = $request->get_header('auth');
    if($auth_header != null){
        $wp_apikey =  get_option( 'prio_sync_wp_apikey' );
      
        if($auth_header == $wp_apikey){
            return true;
        }
    }else{
        ps_email_log_data('liel@tzurtech.co.il','test',$request);

    }

    throw new Exception('403');
}

function ps_build_error_response(Exception $e, $entity_name){
        $ex_data = $e->getMessage();

        if($ex_data == "403"){
            $response = new WP_REST_Response('Authorization fail', 403); // data => array of returned data
        }
        else if($ex_data == "404"){
            $response = new WP_REST_Response( $entity_name . ' not exist', 404); // data => array of returned data
        }
        else{
            $result = array(
                "error" => $ex_data
            );
            $response = new WP_REST_Response($result, 500); 
        }

        return $response;
}


function ps_email_log_data($to = 'liel@tzurtech.co.il',$subject = 'Prioriy sync hook', $data = array() ){
 
    wp_mail( $to, $subject, json_encode($data, JSON_UNESCAPED_UNICODE) );
}

function ps_extract_text_from_note($order_id)
{
    $items = wc_get_order_notes(array('order_id' => $order_id));
    $result = '';

    try {
        if(is_array($items)){
            foreach ($items as $item_id => $item_data) {
                $text = strip_tags($item_data->content);
                $result .= $text . '\n';
            } 
        }
    }
    catch (exception $e) {
        $result = "שגיאה בהשגת הערות להזמנה";
    }

    return $result;
}

function ps_get_ship_address_from_order($order)
{
    $ship_addr =  array(
        'name'  => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
        'city'  => $order->get_billing_city(),
        'address'  => $order->get_billing_address_1(),
        'address2'  =>  $order->get_billing_address_2(),
        'zip_code'  => $order->get_billing_postcode(),
        'phone'  => $order->get_billing_phone()
    );
    if($order->has_shipping_address() == true){
       $ship_addr['name'] = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
       $ship_addr['city'] = $order->get_shipping_city();
       $ship_addr['address'] = $order->get_shipping_address_1();
       $ship_addr['address2'] = $order->get_shipping_address_2();
       $ship_addr['zip_code'] = $order->get_shipping_postcode();

       $bill_phone = $order->get_shipping_phone();
       if(!empty($bill_phone)){
            $ship_addr['phone'] = $bill_phone;
       }
    }

    return $ship_addr;
}

function ps_get_ship_items_from_order($order)
{
    $ship_items = array();
    foreach ($order->get_items('shipping') as $item_id => $item_data) {

        $ship_vm = array(
            'part'      =>  $item_data->get_name(),
			'price'     =>  $order->get_total_shipping()
        );

        array_push($ship_items,$ship_vm);
    } 

    return $ship_items;
}

function ps_get_items_from_order($order)
{
    $items = array();
    foreach ($order->get_items() as $item_id => $item_data) {

        // Get an instance of corresponding the WC_Product object
        $product = $item_data->get_product();

        $product_vm = array(
            'part'      =>  $product->get_sku(),
            'quantity'  =>  $item_data->get_quantity(),
            'price'     =>  $item_data->get_total() +  $item_data->get_total_tax()
        );

        array_push($items,$product_vm);
    } 

    return $items;
}


function ps_get_payments_from_order($order)
{
    $credit_card_last_digits = "";
    $credit_card_token = "";
    $credit_card_date = "";
	$credit_card_refrence= "";
	$credit_card_payments = "1";

    if(get_option('prio_sync_get_token')  == "1" &&  $order->get_payment_method_title() != 'Cash on delivery'){

        $has_pelecard = class_exists('Pelecard\\Order');
        $pelecatdTrans = array();
        
        try{
            $pelecatdTrans = \Pelecard\Order::instance()->get_transactions($order);
        }
        catch (Exception $e) {} 

	    if(count($pelecatdTrans) > 0){
			$trans = $pelecatdTrans[0];
			$credit_card_last_digits = $trans->get_last4();
			$credit_card_token = 'tk578' . $trans->get_token();
			$credit_card_date = $trans->get_card_expiry();
			$credit_card_refrence = $trans->get_meta('DebitApproveNumber');
			$credit_card_payments = $trans->get_meta('TotalPayments');
		}
        else{
            $token_trys = 0;
            while($token_trys < 10) {
                $payment_tokens = $order->get_payment_tokens();
                if (isset($payment_tokens[0])) {
                    $token = WC_Payment_Tokens::get($payment_tokens[0]);
                    $credit_card_last_digits = $token->get_last4();
                    $credit_card_token = $token->get_token();
                    $credit_card_refrence = $token->get_meta('reference');
                    $credit_card_payments = $token->get_meta('payments');

                    $credit_card_date = $token->get_expiry_month();
                    $year = $token->get_expiry_year();
                    if(strlen($year)>2){
                        $credit_card_date .= substr($year, -2);
                    }

                    $token_trys = 10; //stop search for tokens
                }

                $token_trys += 1;
                sleep(6); //wait 6 sec;
            }
        }
    }

    $payments = array(array(
            'type'          => $order->get_payment_method_title(),
            'total_price'   => $order->get_total(),
            'cc_token'         => $credit_card_token,
            'cc_last_digits'   => $credit_card_last_digits,
            'cc_expiry'          => $credit_card_date,
			'cc_refrence'		=> $credit_card_refrence,
            'cc_payments'		=> $credit_card_payments,
    ));

    return $payments;
}
?>