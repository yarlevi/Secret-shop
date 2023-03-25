<?php

//////////// ---=== CATEGORY SYNC ===---  ////////////

require_once dirname( __FILE__ ) .'/helprs.php';

//Entity set:
// id = term slug
// name = term name
// exeption : general category
// Priority side : { id : "0", name : "משפחת מוצר כללית"  }
// Wordpress side : { id : , name : "כללי"  }

// 1.Trigers
$syncSetting =  get_option('prio_sync_rt_categories');
$realTimeSync =  $syncSetting == "1";
if($realTimeSync){
    add_action( 'create_product_cat', 'ps_sync_category', 10, 1 ); 
    add_action( 'edited_product_cat', 'ps_sync_category', 10, 1 ); 
}


// add the action 
function ps_sync_category($id){
    try {

        $data = ps_category_get_vm($id);
        $data['sync_result'] = ps_call_priority_gateway_api('POST', 'category/WordpressWebhook',$data);
        // ps_email_log_data('liel@tzurtech.co.il','Update Category' , $data);

        return $data;
    } catch (Exception $e) {
        return ps_build_error_response($e,'Category');
    }
}


// 2.EndPoints
add_action( 'rest_api_init', function () {
  register_rest_route( 'priority-sync/v1', '/categories', array(
    'methods' => 'GET',
    'callback' => 'ps_category_get_handler',
        'permission_callback' => '__return_true'

  ) );
    register_rest_route( 'priority-sync/v1', '/categories', array(
    'methods' => 'POST',
    'callback' => 'ps_category_add_handler',
        'permission_callback' => '__return_true'

  ) );
  register_rest_route( 'priority-sync/v1', '/categories', array(
    'methods' => 'PUT',
    'callback' => 'ps_category_update_handler',
        'permission_callback' => '__return_true'

  ) );
} );



function ps_category_get_handler(WP_REST_Request $request){
    try {
        ps_valid_apikey($request);

        $id = $request->get_param('id');
        $data = ps_category_get_vm($id);
        return $data;

    } catch (Exception $e) {
        return ps_build_error_response($e,'Category');
    }
}

function ps_category_add_handler(WP_REST_Request $request){
    try {
        ps_valid_apikey($request);

        // $slug = ;
        $curr_cat = get_term_by('slug',$slug,'product_cat');
        $id = $curr_cat->term_id;

        $slug = $request->get_param('id');
        $name = $request->get_param('name');

        $result =  wp_insert_term(  $name , 'product_cat' ,array('slug' => $slug) );

        if ( is_wp_error($result) ) {
            throw new Exception($result->get_error_message());
        }

        return  ps_category_get_vm( $result['term_id']);
    } catch (Exception $e) {
        return ps_build_error_response($e,'Category');
    }
}

function ps_category_update_handler(WP_REST_Request $request){
    try {
        ps_valid_apikey($request);

        $slug = $request->get_param('id');
        $curr_cat = get_term_by('slug',$slug,'product_cat');
        $id = $curr_cat->term_id;

        $result = wp_update_term( $id , 'product_cat', array( 'name' => $request->get_param('name') ) );

        if ( is_wp_error($result) ) {
            throw new Exception($result->get_error_message());
        }

        return  ps_category_get_vm($id);
    } catch (Exception $e) {
        return ps_build_error_response($e,'Category');
    }
}






// // Helprs

function ps_category_get_vm($id){

    if(empty($id)){
        return ps_get_all_categories();
    }
    else{
        if(is_numeric($id)){
            $curr_cat = get_term_by('id',$id,'product_cat');
        }
        else{
            $curr_cat = get_term_by('slug',$id,'product_cat');
        }

        if($curr_cat == ""){
            throw new Exception("Category not exist");
        }
        $data = array(
            'id' => $curr_cat->slug,
            'name' => $curr_cat->name,
        );

        return $data; 
    }

}

function ps_get_all_categories(){

    $data = array();

    $args = array(
            'taxonomy'     => 'product_cat',
            'orderby'      => 'name',
            'show_count'   => 0,
            'pad_counts'   => 0,
            'hierarchical' => 1,
            'title_li'     => '',
            'hide_empty'   => 0
    );
    $all_categories = get_categories( $args );
    foreach ($all_categories as $cat) {
        if($cat->category_parent == 0) {
            $category_id = $cat->term_id;
            
            $parant = array(
                'id'  => $cat->slug,
                'name' => $cat->name,
            );

            array_push($data,$parant);
        } 
            
    }
  return $data;
}

function ps_category_set_vm($id,$data){

    $name = isset($data['name']) ? $data['name'] : 'testttt';

    if(term_exists( $id, 'product_cat')){
    }
    else{
    }

}



?>