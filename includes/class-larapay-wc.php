<?php
if ( !defined( 'ABSPATH' ) ) exit;

class Larapay_WC {

    // Load dependencies
    public function __construct() {

        // Functions
        require_once( LARAPAY_WC_PATH . 'includes/functions.php' );

        // API
        require_once( LARAPAY_WC_PATH . 'includes/abstracts/abstract-larapay-wc-client.php' );
        require_once( LARAPAY_WC_PATH . 'includes/class-larapay-wc-api.php' );

        // Admin
        require_once( LARAPAY_WC_PATH . 'admin/class-larapay-wc-admin.php' );

        // Initialize payment gateway
        require_once( LARAPAY_WC_PATH . 'includes/class-larapay-wc-init.php' );

    }

}
new Larapay_WC();
