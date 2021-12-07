<?php
/**
 * Plugin Name: LaraPay for WooCommerce
 * Description: LaraPay payment integration for WooCommerce.
 * Version:     1.0.0
 * Author:      Laragate Sdn Bhd
 * Author URI:  https://larapay.asia/
 */

if ( !defined( 'ABSPATH' ) ) exit;

define( 'LARAPAY_WC_FILE', __FILE__ );
define( 'LARAPAY_WC_URL', plugin_dir_url( LARAPAY_WC_FILE ) );
define( 'LARAPAY_WC_PATH', plugin_dir_path( LARAPAY_WC_FILE ) );
define( 'LARAPAY_WC_BASENAME', plugin_basename( LARAPAY_WC_FILE ) );
define( 'LARAPAY_WC_VERSION', '1.0.0' );

// Plugin core class
require( LARAPAY_WC_PATH . 'includes/class-larapay-wc.php' );
