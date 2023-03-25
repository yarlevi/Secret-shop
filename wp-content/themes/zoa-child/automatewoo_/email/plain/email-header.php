<?php
// phpcs:ignoreFile
/**
 * Plain email header
 *
 * Override this template by copying it to yourtheme/automatewoo/email/plain/email-header.php
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<html><body><?php // important to set a body tag for preheader usage  ?>

<img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" width="150" class="woocommerce-email-gallery__image" style="border:0;display:block;outline:none;text-decoration:none;width:100%;height:auto;" />
