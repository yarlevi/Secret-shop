<?php


//////////// ---=== PRODUCT SYNC ===---  ////////////


require_once dirname( __FILE__ ) .'/sync-category.php';
require_once dirname( __FILE__ ) .'/helprs.php';


// 1.Trigers
$syncSetting =  get_option('prio_sync_rt_products');
$realTimeSync =  $syncSetting == "1";
if($realTimeSync){
	add_action( 'save_post', 'ps_save_product_change', 10,3 );

	function ps_save_product_change( $post_id, $post, $update ) {
		if ( 'product' === $post->post_type ) {
			ps_sync_product($post_id);
		}
	}
}

function ps_sync_product($product_id){
    try {
        $product = wc_get_product( $product_id );
        $sku = $product->get_sku();

        $data = ps_product_get_vm($sku);
        $data['sync_result'] = ps_call_priority_gateway_api('POST', 'Product/WordpressWebhook',$data);
        // ps_email_log_data('liel@tzurtech.co.il','Update Product' , $data);

    } catch (Exception $e) {
        return ps_build_error_response($e,'Product');
    }
}

// 2.EndPoints
add_action( 'rest_api_init', function () {
  register_rest_route( 'priority-sync/v1', '/products', array(
    'methods' => 'GET',
    'callback' => 'ps_product_get_handler',
    'permission_callback' => '__return_true'
  ) );
  register_rest_route( 'priority-sync/v1', '/products', array(
    'methods' => 'PUT',
    'callback' => 'ps_product_put_handler',
    'permission_callback' => '__return_true'
  ) );
    register_rest_route( 'priority-sync/v1', '/products', array(
    'methods' => 'POST',
    'callback' => 'ps_product_post_handler',
    'permission_callback' => '__return_true'
  ) );
  
} );


function ps_product_get_handler(WP_REST_Request $request){
    try {
        ps_valid_apikey($request);

        $sku = $request->get_param('id');
        if(empty($sku)){
            return  ps_product_get_all();
        }
        return ps_product_get_vm( $sku );
    } 
    catch (Exception $e) {
        return ps_build_error_response($e,'Product');
    }
}

function ps_product_put_handler(WP_REST_Request $request){
    try {
        ps_valid_apikey($request);

        $data = $request->get_params();
        return ps_product_set_vm($data);

    } catch (Exception $e) {
        return ps_build_error_response($e,'Product');
    }
}

function ps_product_post_handler(WP_REST_Request $request){
    try {
        ps_valid_apikey($request);

        $data = $request->get_params();

        $sku = $data["id"];
        $product_id = wc_get_product_id_by_sku($sku);
        if($product_id > 0){
            throw new Exception("Product exist");
        }

        $status_type = 'publish';
        if(get_option('prio_sync_product_status') != null){
            $status_type = get_option('prio_sync_product_status');
        }

        $post = array(
            'post_author' => ps_get_user_admin_id(),
            'post_content' => '',
            'post_status' => $status_type,
            'post_title' => $data["name"],
            'post_parent' => '',
            'post_type' => "product",
        );

        //Create post
        $post_id = wp_insert_post( $post, $wp_error );
        update_post_meta( $post_id, '_sku', $sku);
        update_post_meta( $post_id, '_stock_status', 'instock');
        update_post_meta( $post_id, '_manage_stock', 'yes' );
        update_post_meta( $post_id, '_regular_price', $data['price'] );
        wc_update_product_stock($post_id, $data['quantity'], 'set');

        if($data['image'] != ''){
            Generate_Featured_Image($data['image'],$post_id,'');
        }


        return ps_product_set_vm( $data );

    } catch (Exception $e) {
        return ps_build_error_response($e,'Product');
    }
}



// Helprs

function ps_get_user_admin_id(){
       $args = array(
            'role'    => 'administrator',
            'orderby' => 'user_nicename',
            'order'   => 'ASC'
        );
        $users = get_users( $args );

        return $users[0]->ID;
}

function ps_product_get_vm( $sku ) {

    $product_id = wc_get_product_id_by_sku($sku);
    if($product_id == 0){
        throw new Exception("Product not exist");
    }
    $product = wc_get_product( $product_id );

    $parent_id =  wp_get_post_parent_id($product_id);
    $is_variation =  $parent_id > 0;

    $main_product_id = $is_variation ? $parent_id : $product_id;

    $data = array(
        'id'            => $product->get_sku(),
        'name'          => $product->get_name(),
        'price'         => $product->get_regular_price(),
        'quantity'      => $product->get_stock_quantity(),
        'image'			=> wp_get_attachment_image_url($product->get_image_id()),
        'category'    =>    ps_get_category_by_id($product->get_id()),
    );
    
    return $data;
} 

function ps_product_get_all( ) {

     $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
		 'post_status' => array('publish', 'pending', 'draft', 'auto-draft')

    );
    $loop = new WP_Query( $args );

    $data = array();

    if ( $loop->have_posts() ): while ( $loop->have_posts() ): $loop->the_post();

        global $product;
		
	
        $cat = ps_get_category_by_id($product->get_id());

        if ( $product->is_type( 'variable' ) ) {

			
            $variations = $product->get_available_variations();
            foreach($variations as $variation){
                $variation_id = $variation['variation_id'];
                $variation_obj = new WC_Product_variation($variation_id);

                $vm = array(
                    'id'            => $variation_obj->get_sku(),
                    'name'          => $variation_obj->get_name(),
                    'price'         => $variation_obj->get_regular_price(),
                    'quantity'      => $variation_obj->get_stock_quantity(),
                    'image'			=> wp_get_attachment_image_url($variation_obj->get_image_id()),
                    'category'    => $cat,
                );
                array_push($data, $vm);
            }
        }
        else{
            $vm = array(
                'id'            => $product->get_sku(),
                'name'          => $product->get_name(),
                'price'         => $product->get_regular_price(),
                'quantity'      => $product->get_stock_quantity(),
                'image'			=> wp_get_attachment_image_url($product->get_image_id()),
                'category'    =>  ps_get_category_by_id($product->get_id()),
            );

            array_push($data,$vm);
        }


    endwhile; endif; wp_reset_postdata();


    
    return $data;
} 

function ps_get_category_by_id($id){

        $categories = get_the_terms( $id, 'product_cat' );

        foreach($categories as $cat){
            return $cat->slug;
        }

        return null;
}


function ps_product_set_vm( $model ) {

    $sku = $model["id"];
	

    $product_id = wc_get_product_id_by_sku($sku);
    $product = wc_get_product( $product_id );
	
	

    $parent_id =  wp_get_post_parent_id($product_id);
    $is_variation =  $parent_id > 0;

    $main_product_id = $is_variation ? $parent_id : $product_id;

    //id - cat change
    //name
    if(isset($model['name'])){
        if($is_variation){
            
        }
        else{ //regular product
            $product->set_name($model['name']);
        }
    }

    if(isset($model['price'])){
        //price
        $product->set_regular_price($model['price']);
    }

    if(isset($model['quantity'])){
        //quantity
        wc_update_product_stock($product,$model['quantity']);
    }

    //image
    if(isset($model['image'])){
        try {
            if( $model['image'] != ''){
                Generate_Featured_Image($model['image'],$main_product_id,'');
            }
        } catch (Exception $e) {
            ps_email_log_data('liel@tzurtech.co.il','image sync',$e->getMessage());
        }
    }


    if(isset($model['category'])){
        //categories
        if(isset($model['category'])){
            $collect_new_cats = array();
            $category = get_term_by( 'slug', $model['category'], 'product_cat' );
            array_push($collect_new_cats,$category->term_id);
            wp_set_object_terms($main_product_id, $collect_new_cats, 'product_cat');
        }
    }
	
	$product->save();



    $data = array(
        'id'            => $product->get_sku(),
        'name'          => $product->get_name(),
        'price'         => $product->get_regular_price(),
        'quantity'      => $product->get_stock_quantity(),
        'category'    => ps_get_category_by_id($product->get_id()),
    );
    
    return $data;
}


function Generate_Featured_Image( $image_url, $post_id  ){
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_url);
    $filename = basename($image_url);
    if(wp_mkdir_p($upload_dir['path']))
      $file = $upload_dir['path'] . '/' . $filename;
    else
      $file = $upload_dir['basedir'] . '/' . $filename;
    file_put_contents($file, $image_data);

    $wp_filetype = wp_check_filetype($filename, null );
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );
    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    $res1= wp_update_attachment_metadata( $attach_id, $attach_data );
    $res2= set_post_thumbnail( $post_id, $attach_id );
}

?>