<?php

require_once dirname( __FILE__ ) .'/helprs.php';


// 2.EndPoints
add_action( 'rest_api_init', function () {

  register_rest_route( 'priority-sync/v1', '/orders/getStatuses', array(
    'methods' => 'GET',
    'callback' => 'ps_get_woocommerce_statuses',
    'permission_callback' => '__return_true'
  ) );
	
	register_rest_route( 'priority-sync/v1', '/orders/updateStatuses', array(
    'methods' => 'PUT',
    'callback' => 'ps_order_put_handler',
    'permission_callback' => '__return_true'
  ) );
  
} );

function ps_get_woocommerce_statuses(){
    return wc_get_order_statuses();
}

function ps_order_put_handler(WP_REST_Request $request){
    try {
        ps_valid_apikey($request);

        $data = $request->get_params();
        foreach($data as $order_item){
          $order = wc_get_order($order_item['wordpress_order']);
          $order->update_status($order_item['status'] );
        }
        return true;

    } catch (Exception $e) {
        return ps_build_error_response($e,'Product');
    }
}


// woocommerce_payment_complete
function ps_woocommerce_new_order( $order_id ) {

    $order = new WC_Order($order_id);

    $is_invoice_order =  get_option('prio_sync_order_type') == 'invoice_order';
    $is_cash = $order->get_payment_method() == 'cod';

    if(!(get_option('prio_sync_order_type') == 'order' || get_option('prio_sync_order_type') == 'receipt' || get_option('prio_sync_order_type') == 'invoice_order')){
        return '';
    }

    if($is_invoice_order && !$is_cash){
        $msg =  'Cannot sync order if sync options is `invoice_order` and payment method is not `Cash on delivery`';
        echo $msg ;
        return $msg;
    }

    return ps_create_order( $order_id );

}



add_action( 'woocommerce_order_status_pending', 'ps_woocommerce_new_order' ); 
add_action( 'woocommerce_order_status_processing', 'ps_woocommerce_new_order' ); 


function ps_create_order( $order_id ){

    $order = new WC_Order($order_id);

    if($order->get_status() == 'pending' && $order->get_payment_method_title() != 'Cash on delivery'){
        return 'Cannot sync order. if order status is `pending` and payment method is `Cash on delivery`';
    }

    $data = array(
        'cust'          =>  $order->get_billing_email(),
        'name'          =>  $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
        'date'          =>  $order->get_date_created()->date('F j, Y, g:i a'),
        'due_date'      =>  $order->get_date_created()->date('F j, Y, g:i a'),
        'order_id'      =>  $order_id,
        'items'         =>  ps_get_items_from_order($order),
        'ship_items'    =>  ps_get_ship_items_from_order($order),
        'total'         =>  $order->get_total(),
        'ship_addr'     =>  ps_get_ship_address_from_order($order),
        'cust_notes'    =>  $order->get_customer_order_notes(),
        'internal_notes'=>  ps_extract_text_from_note($order_id),
        'payments'      =>  ps_get_payments_from_order($order)
    );

    $data['sync_result'] = ps_call_priority_gateway_api('POST', 'Order/Add',$data);
    
    try {
        $priority_order_prop_name = get_option('prio_sync_priority_order_name');
        if(!ps_is_null_or_empty_string($priority_order_prop_name)){ // has order filed to fill
            $priority_order_id = ps_call_priority_gateway_api('GET', 'Order/GetOrderID?eOrderID=' . $order_id, null);
            if(!ps_is_null_or_empty_string($priority_order_id)){
                update_post_meta( $order_id, $priority_order_prop_name, $priority_order_id );
            }
        }
    } catch (Exception $e) {} 
    

    if(get_option('prio_sync_log_active') == "1"){
        ps_email_log_data('liel@qama.co.il','Update Order' , $data);
    }

    return $data;
}


function ps_status_woocommerce_cancelled_order( $order_get_id ) { 
	$data = array( 'order' => $order_get_id	);
	$data['sync_result'] = ps_call_priority_gateway_api('POST', 'Order/Cancel',$data);
}; 
function ps_status_woocommerce_complite_order( $order_get_id ) { 
	$data = array( 'order' => $order_get_id	);
	$data['sync_result'] = ps_call_priority_gateway_api('POST', 'Order/Close',$data);
}; 

if(get_option('prio_sync_rt_cancel')  == "1"){
    add_action( 'woocommerce_order_status_cancelled', 'ps_status_woocommerce_cancelled_order', 10, 1 ); 
}
if(get_option('prio_sync_rt_close') == "1"){
    add_action( 'woocommerce_order_status_completed', 'ps_status_woocommerce_complite_order' ); 
}


?>