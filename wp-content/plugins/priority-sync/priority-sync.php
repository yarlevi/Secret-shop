<?php
/**
 * @package Priority_Sync
 * @version 5.4.3
 */
/*
Plugin Name: Qama Priority Sync
Plugin URI: https://qama.co.il/
Description: Sync items and orders to and from Priority
Author: Liel Tzur
Version: 5.4.3
Author URI: https://www.facebook.com/Tzurtech/
*/


require_once dirname( __FILE__ ) .'/helprs.php';
require_once dirname( __FILE__ ) .'/sync-customers.php';
require_once dirname( __FILE__ ) .'/sync-category.php';
require_once dirname( __FILE__ ) .'/sync-product.php';
require_once dirname( __FILE__ ) .'/sync-orders.php';
require_once dirname( __FILE__ ) .'/sync-invoice.php';
require_once dirname( __FILE__ ) .'/sync-receipt.php';
require_once dirname( __FILE__ ) .'/sync-stock.php';
require_once dirname( __FILE__ ) .'/admin-page.php';

?>