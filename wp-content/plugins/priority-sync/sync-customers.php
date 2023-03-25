<?php


require_once dirname( __FILE__ ) .'/helprs.php';


//////////// ---=== CUSTOMERS SYNC ===---  ////////////

// 1.Trigers
$syncSetting =  get_option('prio_sync_rt_customers');
$realTimeSync =  $syncSetting == "1";
if($realTimeSync){
    add_action( 'user_register', 'ps_update_cust', 10, 1 );
    add_action( 'profile_update', 'ps_update_cust', 10, 1 );
}


// add the action 
function ps_update_cust($user_id){
    try {
        $user = get_user_by('id',$user_id);
      	$user_meta = get_userdata($user_id);
        $user_roles = $user_meta->roles;

        if ( in_array( 'customer', $user_roles, true ) ) {
            $email = $user->user_email;
            $data = ps_customer_get_vm($email);
            $data['sync_result'] = ps_call_priority_gateway_api('POST', 'Customer/WordpressWebhook',$data);
            // ps_email_log_data('liel@tzurtech.co.il','Update Customers' , $data);
        }
    } catch (Exception $e) {
        return ps_build_error_response($e,'Customer');
    }
}

// 2.EndPoints
add_action( 'rest_api_init', function () {
  register_rest_route( 'priority-sync/v1', '/customers', array(
    'methods' => 'GET',
    'callback' => 'ps_customers_get_handler',
        'permission_callback' => '__return_true'

  ) );
register_rest_route( 'priority-sync/v1', '/customers', array(
    'methods' => 'POST',
    'callback' => 'ps_customers_add_handler',
    'permission_callback' => '__return_true'
  ) );
  register_rest_route( 'priority-sync/v1', '/customers', array(
    'methods' => 'PUT',
    'callback' => 'ps_customers_update_handler',
    'permission_callback' => '__return_true'
  ) );
  
} );

function ps_customers_get_handler(WP_REST_Request $request){
    try {
        ps_valid_apikey($request);

        $user_id = $request->get_param('id');
        if(empty($user_id)){
            $data = ps_customer_get_all();
        }
        else{
            $data = ps_customer_get_vm($user_id);
        }
        return $data;

    } catch (Exception $e) {
        return ps_build_error_response($e,'Customer');
    }
}

function ps_customers_update_handler(WP_REST_Request $request){
    try {
        ps_valid_apikey($request);

        $data = $request->get_params();

        return ps_customer_set_vm($data);
   

    } catch (Exception $e) {
        return ps_build_error_response($e,'Customer');
    }
}

function ps_customers_add_handler(WP_REST_Request $request){
    try {
        ps_valid_apikey($request);

        $data = $request->get_params();

        $email = $data["id"];
        $pass = wp_generate_password();

        $result = wp_create_user($email, $pass, $email);
        if(is_wp_error($result)){
            throw  new Exception($result->get_error_message());
        }
        else{
            $user = new WP_User( $result ); 
            $user->remove_role( 'subscriber' );
            $user->add_role( 'customer' );
        }

        return ps_customer_set_vm($data);
   

    } catch (Exception $e) {
        return ps_build_error_response($e,'Customer');
    }
}

// Helprs

function ps_customer_get_all(){
    
    $result = array();

    $args = array(
        'role'    => 'customer',
        'orderby' => 'user_nicename',
        'order'   => 'ASC'
    );

    $users = get_users( $args);

    foreach( $users as $user )
    {
        $meta = get_user_meta($user->ID);
        $data = ps_get_meta_vm($meta);
        $data["id"]= $user->user_email;
        array_push($result,$data);
    }

    return $result;
}

function ps_customer_get_vm($user_id){

    $user = get_user_by( 'email', $user_id );
    if(!$user ){
        throw new Exception('404');
    }
    $curr_user_meta = get_user_meta ( $user->ID);
    if(!$curr_user_meta){
        throw new Exception('404');
    }

    $data = ps_get_meta_vm($curr_user_meta);
    $data["id"]= $user->user_email;

    return $data;

}

function ps_get_meta_vm($curr_user_meta){
    $data = array(
        'name' =>       extact_user_data($curr_user_meta,"billing_first_name") . ' ' . extact_user_data($curr_user_meta,"billing_last_name") ,
        'city' =>       extact_user_data($curr_user_meta,"billing_city"),
        'address' =>    extact_user_data($curr_user_meta,"billing_address_1"),
        'zip_code' =>   extact_user_data($curr_user_meta,"billing_postcode"),
        'phone' =>      extact_user_data($curr_user_meta,"billing_phone"),

    );

    return $data;
}

function extact_user_data($data_array, $prop ){

    if(isset($data_array)){
        if(isset($data_array[$prop])){
            if(count($data_array[$prop]) > 0){
                return $data_array[$prop][0];
            }
        }
    }

    return '';

}


function ps_customer_set_vm($data){

    $user_mail = $data['id'];

    $user = get_user_by_email( $user_mail );

    $user_id = $user->ID;


    $fname = '';
    $lname = '';

    $name_pieces = explode(" ",  $data['name']);

    for($i = 0; $i < count($name_pieces); ++$i) {
        if($i == 0){
            $fname = $name_pieces[$i];
        }
        else{
            $lname = $lname . $name_pieces[$i];
        }
    }

    wp_update_user([
        'ID' => $user_id, // this is the ID of the user you want to update.
        'first_name' => $fname,
        'last_name' => $lname,
    ]);


    $reverse_vm = array(
        'billing_first_name'  => $fname,
        'billing_last_name'  => $lname,
        'billing_company'  => $data['company'],
        'billing_city'  => $data['city'],
        'billing_address_1'  => $data['address'],
        'billing_postcode'  => $data['zip_code'],
        'billing_phone'  => $data['phone'],
    );


    foreach ($reverse_vm as $key => $value) {
        update_user_meta( $user_id, $key, $value);
    }

    return ps_customer_get_vm($user_mail);
}







?>