<?


namespace secretElements\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

// Security Note: Blocks direct access to the plugin PHP files.
defined( 'ABSPATH' ) || die();

/**
 * Zoa blog posts widget.
 *
 * Zoa widget that displays blog posts.
 *
 * @since 1.0.0
 */
class secret_brands_archive extends Widget_Base {
	/**
	 * Get widget name.
	 *
	 * Retrieve blog posts widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'secret-brands-archive';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve blog posts widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Secret Brands Archive', 'general' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve blog posts widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'fa fa-pencil';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the icon widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'wordpress' ];
	}
        
	/**
	 * Register blog posts widget controls.
	 *
	 * Add different input fields to allow the user to change and customize the widget settings
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _register_controls() {
		parent::_register_controls();

	}

	protected function render() {
		
		wp_enqueue_style("secret_brands_style",BRN_ELEMENTS_URL."widgets/css/secret_brands_archive.css");
				
		$rand_id = time()."_".rand(111,999);
			
		$settings = $this->get_settings_for_display();
		
		$limit = 10;
		$page = ( get_query_var('paged') ) ? get_query_var( 'paged' ) : 1;
		
		$brands = get_terms( array(
			'taxonomy' => 'product_brand',
			'hide_empty' => false,
			'number' => $limit,
			'offset' => (($page-1)*$limit)
			//'order' => strtoupper($settings['order']),
		));
				
		?>
        
        
		<div class="brands_archive">
        
        	<? foreach ($brands as $brand): ?>
            <div class="brand_con">
            	<?
					$brand->fields = get_fields("product_brand_".$brand->term_id);
					$img = wp_get_attachment_url( get_term_meta($brand->term_id, 'thumbnail_id', true) );          
				?>
                
                <div class="brand_top">
                	<img class="brnad_img" src="<?=$img?>" />
                    <div class="brand_content">
                        <h4><?=$brand->name?></h4>
                        <h5><?=$brand->description?></h5>
                        <?php if ($brand->specialities) { ?>
                        	<h6>התמחות: <?=$brand->specialities?></h6>
                        <? } ?>
                    </div>
                </div>
                <div class="brand_bottom">
                	<a href="/brand/<?=$brand->slug?>" class="to_brand_products">לדף המותג</a>
                    
                    <?
					
					$query = new \WP_Query(array(
						'posts_per_page' => 5,
						'post_status' => 'publish',
						'post_type' => 'product',
						'tax_query' => array(
							array(
								'taxonomy' => 'product_brand',
								'field' => 'term_id',
								'terms' => $brand->term_id
							)
						)
					));
					
					// Check that we have query results.
					if ( $query->have_posts() ) {
					 
						// Start looping over the query results.
						while ( $query->have_posts() ) {
					 
							$query->the_post();
														
							echo do_shortcode('[elementor-template id="1601"]');
					 
							// Contents of the queried post results go here.
					 
						}
					 
					}
					 
					// Restore original post data.
					wp_reset_postdata();
					
					?>
                    
                </div>
                
            </div>
            <? endforeach; ?>
            
        </div>
        
        <?
			$total_terms = wp_count_terms( 'product_brand' );
			$pages = ceil($total_terms/$limit);
		
			// if there's more than one page
			if( $pages > 1 ):
				echo '<ul class="pagination">';
		
				if ($page != 1) {
					echo '<li class="pagination_item prev"><a href="'.get_permalink().'page/'.($page-1).'">העמוד הקודם</a></li>';
				}
		
				for ($pagecount=1; $pagecount <= $pages; $pagecount++):
					echo '<li class="pagination_item '.($pagecount == $page ? "active" : "").'"><a href="'.get_permalink().'page/'.$pagecount.'/">'.$pagecount.'</a></li>';
				endfor;
				
				if ($page < $pages) {
					echo '<li class="pagination_item next"><a href="'.get_permalink().'page/'.($page+1).'">העמוד הבא</a></li>';
				}
				
				echo '</ul>';
		
			endif;

		?>
        
		<?php	
                
	}
}


\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new secret_brands_archive() );





