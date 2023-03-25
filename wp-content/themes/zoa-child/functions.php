<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );

if ( !function_exists( 'chld_thm_cfg_parent_css' ) ):
    function chld_thm_cfg_parent_css() {
        wp_enqueue_style( 'chld_thm_cfg_parent', trailingslashit( get_template_directory_uri() ) . 'style.css', array(  ) );
		wp_enqueue_style( 'chld_thm_cfg_parent1', '/wp-content/themes/zoa-child/style1.css', array(  ) );
		
		wp_enqueue_script('brn_custom',get_template_directory_uri()."-child/js/custom.js",array('jquery'));
		
		
    }
endif;
add_action( 'wp_enqueue_scripts', 'chld_thm_cfg_parent_css', 10 );

// END ENQUEUE PARENT ACTION

//Include custom function files
$widgets = glob( get_template_directory() . '-child/brn/*.php' );

foreach ( $widgets as $key ) {
    if ( file_exists( $key ) ) {
        require_once $key;
    }
}

/* Tagcloud, change the font size */
/*
function custom_tag_cloud_widget($args) {
    $args['largest'] = 18; //largest tag
    $args['smallest'] = 18; //smallest tag
    $args['unit'] = 'px'; //tag font unit
    return $args;
}
*/

function custom_tag_cloud_widget($args) {
    $args = array(
        'largest' => 18, //largest tag
        'smallest' => 18, //smallest tag
        'unit' => 'px', //tag font unit
    );
    return $args;
}


/* Trim zeros in price decimals */
add_filter( 'woocommerce_price_trim_zeros', '__return_true' );

/* Shop product subtitle */
add_action('woocommerce_shop_loop_item_title',function() {
    ?>
    <span class="subtitle"><?php echo get_field('subtitle'); ?></span>
    <?php
}, 11);

/* only on sale query */
add_action( 'elementor/query/only_on_sale', function( $q ) {
	$product_ids_on_sale = wc_get_product_ids_on_sale();
    $q->set( 'post__in', $product_ids_on_sale );
} );

/* only recipes query */
add_action( 'elementor/query/only_recipes', function( $q ) {
    $q->set( 'post_type', 'post' );
} );
