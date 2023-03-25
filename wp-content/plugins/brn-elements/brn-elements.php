<?php
/**
 * BRN Elements WordPress Plugin
 *
 * @package BrnElements
 *
 * Plugin Name: BRN Elements
 * Description: Plugins for elementor
 * Plugin URI:  https://www.brn.co.il
 * Version:     1.0.0
 * Author:      BRN
 * Author URI:  https://www.brn.co.il
 * Text Domain: brn-elements
 */

define( 'BRN_ELEMENTS', __FILE__ );

define('BRN_ELEMENTS_URL',"https://".$_SERVER['HTTP_HOST']."/wp-content/plugins/brn-elements/");

/**
 * Include the Elementor_Awesomesauce class.
 */
require plugin_dir_path( BRN_ELEMENTS ) . 'class-brn-elements.php';