<?php


require_once dirname( __FILE__ ) .'/helprs.php';
require_once dirname( __FILE__ ) .'/sync-orders.php';


// woocommerce_payment_complete
function ps_woocommerce_payment_complete_receipt( $order_id ) {

    $order = new WC_Order($order_id);

    if(get_option('prio_sync_order_type') != 'receipt'){
        echo 'Cannot sync receipt. if option `Send new orders as` not set to `receipt`';
        return;
    }
    if($order->get_status() == 'pending'){
        echo 'לא ניתן לסנכרן קבלה בסטטוס ממתין לתשלום';
		return;
    }
    if($order->get_payment_method_title() == 'Cash on delivery'){
        echo 'לא ניתן לסנכרן קבלה עם תקבול מזומן';
		return;
    }
    if($order->get_total() == $order_total){
        echo 'לא ניתן לסנכרן קבלה עם סך תקבולים 0';
		return;
    }

    $ship_items = array();
    foreach ($order->get_items('shipping') as $item_id => $item_data) {

        $ship_vm = array(
            'part'      =>  $item_data->get_name(),
			'price'     =>  $order->get_total_shipping()
        );

        array_push($ship_items,$ship_vm);
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
        'cust_notes'    =>  $order->get_customer_order_notes(),
        'internal_notes'=> ps_extract_text_from_note($order_id),
        'payments'      => ps_get_payments_from_order($order)
    );

    $data['sync_result'] = ps_call_priority_gateway_api('POST', 'receipt/Add',$data);
    
    if(get_option('prio_sync_log_active') == "1"){
        ps_email_log_data('liel@qama.co.il','Update Recipt' , $data);
    }

    return $data;

}

// add_action( 'woocommerce_order_status_completed', 'ps_woocommerce_payment_complete_receipt' ); 
add_action( 'woocommerce_order_status_pending', 'ps_woocommerce_payment_complete_receipt');
add_action( 'woocommerce_order_status_processing', 'ps_woocommerce_payment_complete_receipt');

?>