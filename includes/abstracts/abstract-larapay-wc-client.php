<?php
if ( !defined( 'ABSPATH' ) ) exit;

abstract class Larapay_WC_Client {

    const PRODUCTION_URL = 'https://api.larapay.asia/api/';
    const SANDBOX_URL    = 'https://api-stg.larapay.asia/api/';

    protected $auth_token;
    protected $secret_key;
    protected $sandbox = true;
    protected $debug = false;

    // HTTP request URL
    private function get_url( $route = null ) {

        if ( $this->sandbox ) {
            return self::SANDBOX_URL . $route;
        } else {
            return self::PRODUCTION_URL . $route;
        }

    }

    // HTTP request headers
    private function get_headers() {

        return array(
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
            'x-auth-token' => $this->auth_token,
        );

    }

    // HTTP GET request
    protected function get( $route, $params = array() ) {
        return $this->request( $route, $params, 'GET' );
    }

    // HTTP POST request
    protected function post( $route, $params = array() ) {
        return $this->request( $route, $params );
    }

    // HTTP request
    protected function request( $route, $params = array(), $method = 'POST' ) {

        if ( !( $this->auth_token ) ) {
            throw new Exception( 'Missing API authentication token.' );
        }

        $url = $this->get_url( $route );

        $args['headers'] = $this->get_headers();

        larapay_wc_logger( 'URL: ' . $url );
        larapay_wc_logger( 'Headers: ' . wp_json_encode( $args['headers'] ) );

        if ( $params ) {
            $args['body'] = $method !== 'POST' ? $params : wp_json_encode( $params );
            larapay_wc_logger( 'Body: ' . wp_json_encode( $params ) );
        }

        // Set request timeout to 30 seconds
        $args['timeout'] = 30;

        switch ( $method ) {
            case 'GET':
                $response = wp_remote_get( $url, $args );
                break;

            case 'POST':
                $response = wp_remote_post( $url, $args );
                break;

            default:
                $args['method'] = $method;
                $response = wp_remote_request( $url, $args );
        }

        if ( is_wp_error( $response ) ) {
            larapay_wc_logger( 'Response Error: ' . $response->get_error_message() );
            throw new Exception( $response->get_error_message() );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        larapay_wc_logger( 'Response: ' . wp_json_encode( $body ) );

        return array( $code, $body );

    }

    // Get IPN response data
    public function get_ipn_response() {

        if ( !in_array( $_SERVER['REQUEST_METHOD'], array( 'GET', 'POST' ) ) ) {
            return false;
        }

        $data = array_map( 'sanitize_text_field', $_REQUEST );

        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            $data = file_get_contents( 'php://input' );
            $data = json_decode( $data, true );
        }

        if ( empty( $data ) ) {
            return false;
        }

        if ( !$formatted_data = $this->get_valid_ipn_response( $data ) ) {
            return false;
        }

        return $formatted_data;

    }

    // Format IPN response data to only get accepted parameters
    private function get_valid_ipn_response( array $data ) {

        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            $params = $this->get_callback_params();
        } else {
            $params = $this->get_redirect_params();
        }

        $allowed_params = array();

        foreach ( $params as $param ) {
            // Return false if required parameters is not passed to the URL
            if ( !isset( $data[ $param ] ) ) {
                return false;
            }

            $allowed_params[ $param ] = $data[ $param ];
        }

        return $allowed_params;

    }

    // Get list of parameters that will be passed in callback URL (refer transaction object)
    private function get_callback_params() {

        return array(
            'paymentId',
            'collectionId',
            'gatewayId',
            'status',
            'externalId',
            'amount',
            'currency',
            'description',
            'paymentEmail',
            'createdAt',
            'paidAt',
            'hookDigest',
            'kind',
        );

    }

    // Get list of parameters that will be passed in redirect URL
    private function get_redirect_params() {

        return array(
            'status',
            'amount',
            'currency',
            'collectionName',
            'paymentId',
            'txnId',
            'bank',
            'time',
        );

    }

    // Validate IPN response data
    public function validate_ipn_response( $response ) {

        if ( !$this->verify_hook_digest( $response ) ) {
            throw new Exception( 'Hook digest mismatch.' );
        }

        return true;

    }

    // Verify hook digest parameter value received from IPN response data
    private function verify_hook_digest( $response ) {

        if ( !( $this->secret_key ) ) {
            throw new Exception( 'Missing collection secret key.' );
        }

        if ( !isset( $response['hookDigest'] ) || empty( $response['hookDigest'] ) ) {
            return false;
        }

        $hook_digest = $response['hookDigest'];

        unset( $response['hookDigest'] );

        $encoded_hook_digest = implode( '|', array_values( $response ) );
        $generated_hook_digest = hash_hmac( 'sha1', $encoded_hook_digest, $this->secret_key );

        return $hook_digest == $generated_hook_digest;

    }

}
