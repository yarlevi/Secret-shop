<?php


require_once dirname( __FILE__ ) .'/helprs.php';
require_once dirname( __FILE__ ) .'/sync-orders.php';


// woocommerce_payment_complete
function ps_woocommerce_payment_complete( $order_id ) {

    $order = new WC_Order($order_id);
    $order_total = $order->get_total();

    $is_invoice = get_option('prio_sync_order_type') == 'invoice' ;
    $is_invoice_order =  get_option('prio_sync_order_type') == 'invoice_order';
    $is_cash = $order->get_payment_method() == 'cod';
    
    if(!($is_invoice || $is_invoice_order)){
        $msg = 'Cannot sync invoice. if option `Send new orders as` not set to `invoice` or `invoice_order`';
        echo $msg ;
        return $msg;
    }

    if($is_invoice_order && $is_cash){
        $msg = 'Cannot sync invoice. if sync options is `invoice_order` and payment method is `Cash on delivery`';
        echo $msg ;
        return $msg;
    }
    
    if($order->get_status() == 'pending' && !$is_cash){
        $msg =  'Cannot sync invoice. if order status is `pending` and payment method is not `Cash on delivery`';
        echo $msg ;
        return $msg;
    }

    if(0 == $order_total){
        return ps_create_order($order_id);
    }

    $data = array(
        'cust'          =>  $order->get_billing_email(),
        'name'          =>  $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
        'date'          =>  $order->get_date_created()->date('F j, Y, g:i a'),
        'order_id'      =>  $order_id,
        'items'         =>  ps_get_items_from_order($order),
		'ship_items'    =>  ps_get_ship_items_from_order($order),
        'total'         =>  $order->get_total(),
        'ship_addr'     =>  ps_get_ship_address_from_order($order),
        'cust_notes'    =>  $order->get_customer_note(),
        'internal_notes'=>  ps_extract_text_from_note($order_id),
        'payments'      =>  ps_get_payments_from_order($order)
    );

    $data['sync_result'] = ps_call_priority_gateway_api('POST', 'Invoice/Add',$data);

    if(get_option('prio_sync_log_active') == "1"){
        ps_email_log_data('liel@qama.co.il','Update Invoice' , $data);
    }

    return $data;

}

add_action( 'woocommerce_order_status_pending', 'ps_woocommerce_payment_complete');
add_action( 'woocommerce_order_status_processing', 'ps_woocommerce_payment_complete');


?>