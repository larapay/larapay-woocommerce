<?php
if ( !defined( 'ABSPATH' ) ) exit;

class Larapay_WC_API extends Larapay_WC_Client {

    // Initialize API
    public function __construct( $auth_token = null, $secret_key = null, $sandbox = false, $debug = false ) {

        $this->auth_token = $auth_token ?: larapay_wc_get_setting( 'auth_token' );
        $this->secret_key = $secret_key ?: larapay_wc_get_setting( 'secret_key' );
        $this->sandbox    = ( $sandbox || larapay_wc_get_setting( 'sandbox' ) === 'yes' ) ? true : false;
        $this->debug      = ( $debug || larapay_wc_get_setting( 'debug' ) === 'yes' ) ? true : false;

    }

    // Request a QR code
    public function create_payment( array $params ) {
        return $this->post( 'payments/create-payment-link', $params );
    }

}
