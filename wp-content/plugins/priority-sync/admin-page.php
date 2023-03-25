<?php

function my_admin_menu() {

    $page_title = 'Priority Sync';
    $menu_title = 'Priority Sync';
    $capability = 'manage_options';
    $menu_slug  = 'qsa_admin_page';

    add_menu_page(
        $page_title,
        $menu_title,
        'manage_options',
        $menu_slug,
        'admin_page_contents',
        'dashicons-schedule',
        3 
    );
}
add_action( 'admin_menu', 'my_admin_menu' );
    
function admin_page_contents() {
    echo get_template_part( '../../plugins/priority-sync/views/admin-page' );
}


// register priority api connection settigns
if( !function_exists("admin_set_priority_settings") ) { 
    function admin_set_priority_settings() {   
        register_setting( 'priority-sync-settings', 'prio_sync_url' ); 
        register_setting( 'priority-sync-settings', 'prio_sync_apikey' ); 
        register_setting( 'priority-sync-settings', 'prio_sync_wp_apikey' ); 
        
        register_setting( 'priority-sync-settings', 'prio_sync_rt_categories' ); 
        register_setting( 'priority-sync-settings', 'prio_sync_rt_customers' ); 
        register_setting( 'priority-sync-settings', 'prio_sync_rt_products' ); 
        
        register_setting( 'priority-sync-settings', 'prio_sync_order_type' ); 

        register_setting( 'priority-sync-settings', 'prio_sync_rt_cancel' ); 
        register_setting( 'priority-sync-settings', 'prio_sync_rt_close' ); 

        register_setting( 'priority-sync-settings', 'prio_sync_get_token' ); 
        register_setting( 'priority-sync-settings', 'prio_sync_product_status' ); 

        register_setting( 'priority-sync-settings', 'prio_sync_priority_order_name' ); 

        register_setting( 'priority-sync-settings', 'prio_sync_log_active' ); 

    } 
} 
add_action('admin_init', 'admin_set_priority_settings');


function priority_sync_test(){
    try{
        $result = call_priority_api('GET','',null);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    catch (Exception $e){
        echo $e;
    }
    wp_die(); 
}

add_action( 'wp_ajax_priority_registration_form', 'registration_form_heandler' );
add_action( 'wp_ajax_nopriv_priority_registration_form', 'registration_form_heandler' );



add_filter( 'woocommerce_admin_order_actions', 'ps_add_prio_sync_button', 100, 2 );

function ps_add_prio_sync_button( $actions, $order ) {

    // The key slug defined for your action button
    $action_slug = 'Priority_Sync';
    $order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
    // Set the action button
    $actions[$action_slug] = array(
        'url'       => wp_nonce_url(admin_url('admin-ajax.php?action=priority_order_sync&order_id=' . $order_id), 'Priority_Sync'),
        'name'      => __( 'סנכרן לפריורירטי', 'woocommerce' ),
        'action'    => $action_slug,
    );

    return $actions;
}


add_action( 'admin_head', 'add_custom_order_status_actions_button_css' );
function add_custom_order_status_actions_button_css() {
    $action_slug = "Priority_Sync"; // The key slug defined for your action button

    echo '<style>.wc-action-button-'.$action_slug.'::after { font-family: woocommerce !important; content: "\e029" !important; }</style>';
}


function priority_order_sync(){
    try{

		
        $order_id = $_REQUEST["order_id"];
		
        $result = "";
        switch (get_option('prio_sync_order_type')) {
            case 'invoice':
                $result = ps_woocommerce_payment_complete($order_id)['sync_result'];
                break;
            case 'order':
                $result = ps_woocommerce_new_order($order_id)['sync_result'];
                break;
            case 'receipt':
                $result .= ps_woocommerce_new_order($order_id)['sync_result'];
                $result .= '. ';
                $result = ps_woocommerce_payment_complete_receipt($order_id)['sync_result'];
                break;
            case 'invoice_order':
                $result .=  ps_woocommerce_new_order($order_id)['sync_result'];
                $result .= '. ';
                $result =  ps_woocommerce_payment_complete($order_id)['sync_result'];
                break;
            default:
                echo 'לא נבחרה פעולת סנכרון';
                break;
        }
         
        echo var_dump($result);
		
		wp_die();
    }
    catch (Exception $e){
        echo  $e->getMessage();
    }
    wp_die(); 
}

add_action( 'wp_ajax_priority_order_sync', 'priority_order_sync' );

?>