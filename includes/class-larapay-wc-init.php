<?php
if ( !defined( 'ABSPATH' ) ) exit;

class Larapay_WC_Init {

    private $gateway_class = 'Larapay_WC_Gateway';

    // Register hooks
    public function __construct() {

        add_action( 'woocommerce_payment_gateways', array( $this, 'register_gateway' ) );
        add_action( 'init', array( $this, 'load_dependencies' ) );

    }

    // Register LaraPay as WooCommerce payment method
    public function register_gateway( $methods ) {
        $methods[] = $this->gateway_class;
        return $methods;
    }

    // Load required files
    public function load_dependencies() {

        if ( !class_exists( 'WC_Payment_Gateway' ) ) {
            return;
        }

        require_once( LARAPAY_WC_PATH . 'admin/settings.php' );
        require_once( LARAPAY_WC_PATH . 'includes/class-larapay-wc-gateway.php' );

    }

}
new Larapay_WC_Init();
