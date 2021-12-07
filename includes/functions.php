<?php
if ( !defined( 'ABSPATH' ) ) exit;

// Get plugin setting by key
function larapay_wc_get_setting( $key, $default = null ) {
    $settings = get_option( 'woocommerce_larapay_settings' );
    return !empty( $settings[ $key ] ) ? $settings[ $key ] : $default;
}

// Display notice
function larapay_wc_notice( $message, $type = 'success' ) {
    printf( '<div class="notice notice-%1$s"><p><strong>%2$s:</strong> %3$s</p></div>', $type, esc_html__( 'LaraPay for WooCommerce', 'larapay-wc' ), $message );
}

// Log error message in WooCommerce logs
function larapay_wc_logger( $message ) {

    if ( !function_exists( 'wc_get_logger' ) ) {
        return false;
    }

    return wc_get_logger()->add( 'larapay-wc', $message );

}
