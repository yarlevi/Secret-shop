<?php


//////////// ---=== STOCK SYNC ===---  ////////////


require_once dirname( __FILE__ ) .'/sync-category.php';
require_once dirname( __FILE__ ) .'/helprs.php';

// 2.EndPoints
add_action( 'rest_api_init', function () {

  register_rest_route( 'priority-sync/v1', '/stock', array(
    'methods' => 'PUT',
    'callback' => 'ps_stock_put_handler',
    'permission_callback' => '__return_true'
  ) );
  
} );



function ps_stock_put_handler(WP_REST_Request $request){
    try {
        ps_valid_apikey($request);

        $data = $request->get_params();
        foreach($data as $part){

          $product_id = wc_get_product_id_by_sku($part['id']);
          if($product_id > 0){
              wc_update_product_stock($product_id,$part['quantity']);
          }
            
        }
        return true;

    } catch (Exception $e) {
        return ps_build_error_response($e,'Product');
    }
}


?>