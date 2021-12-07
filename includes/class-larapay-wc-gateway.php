<?php
if ( !defined( 'ABSPATH' ) ) exit;

class Larapay_WC_Gateway extends WC_Payment_Gateway {

    private $larapay;

    private $auth_token;
    private $secret_key;
    private $sandbox;
    private $debug;

    public function __construct() {

        $this->id                 = 'larapay';
        $this->has_fields         = true;
        $this->method_title       = __( 'LaraPay', 'larapay-wc' );
        $this->method_description = __( 'Enable LaraPay payment gateway for your site.', 'larapay-wc' );
        $this->order_button_text  = __( 'Pay with LaraPay', 'larapay-wc' );
        $this->supports           = array( 'products' );

        $this->init_form_fields();
        $this->init_settings();

        $this->title              = $this->get_option( 'title' );
        $this->description        = $this->get_option( 'description' );
        $this->icon               = LARAPAY_WC_URL . 'assets/images/larapay.png';

        $this->auth_token         = $this->get_option( 'auth_token' );
        $this->secret_key         = $this->get_option( 'secret_key' );
        $this->sandbox            = $this->get_option( 'sandbox' ) === 'yes' ? true : false;
        $this->debug              = $this->get_option( 'debug' ) === 'yes' ? true : false;

        $this->register_hooks();

        // Check if the payment gateway is ready to use
        if ( !$this->validate_required_settings() ) {
            $this->enabled = 'no';
        }

        $this->init_api();

    }

    // Register WooCommerce payment gateway hooks
    private function register_hooks() {

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_api_' . $this->id . '_wc_gateway', array( $this, 'handle_ipn' ) );

    }

    // Check if all required settings is filled
    private function validate_required_settings() {
        return $this->auth_token && $this->secret_key;
    }

    // Override the normal options so we can print the webhook and callback URL to the admin
    public function admin_options() {

        parent::admin_options();
        include( LARAPAY_WC_PATH . 'admin/views/settings/callback-url.php' );

    }

    // Form fields
    public function init_form_fields() {
        $this->form_fields = larapay_wc_settings_form_fields();
    }

    // Initialize API
    private function init_api() {
        $this->larapay = new Larapay_WC_API( $this->auth_token, $this->secret_key, $this->sandbox, $this->debug );
    }

    // Process the payment
    public function process_payment( $order_id ) {

        if ( !$this->validate_required_settings() ) {
            return false;
        }

        if ( !$order = wc_get_order( $order_id ) ) {
            return false;
        }

        larapay_wc_logger( 'Creating payment for order #' . $order_id );

        list( $code, $response ) = $this->larapay->create_payment( array(
            'externalId' => (string) $order_id,
            'amount'     => (float) $order->get_total(),
        ) );

        larapay_wc_logger( 'Payment created for order #' . $order_id );

        // Payment error
        if ( $code !== 201 ) {

            if ( isset( $response['message'] ) ) {
                $error_messages = implode( '<br>', (array) $response['message'] );
                wc_add_notice( __( 'Payment error: ', 'larapay-wc' ) . $error_messages, 'error' );
            } else {
                wc_add_notice( esc_html__( 'Payment error!', 'larapay-wc' ), 'error' );
            }

            return;
        }

        // Redirect to payment page
        return array(
            'result'   => 'success',
            'redirect' => $response['paymentUrl'], // LaraPay payment URL
        );

    }

    // Handle IPN
    public function handle_ipn() {

        $response = $this->larapay->get_ipn_response();

        if ( $_SERVER['REQUEST_METHOD'] === 'GET' ) {
            return $this->handle_ipn_callback( $response );
        } else {
            return $this->handle_ipn_webhook( $response );
        }

    }

    // Handle payment webhook
    private function handle_ipn_webhook( $response ) {

        if ( !$response ) {
            larapay_wc_logger( 'IPN webhook failed' );
            wp_die( 'LaraPay IPN webhook failed', 'LaraPay IPN', array( 'response' => 200 ) );
        }

        larapay_wc_logger( 'IPN webhook response: ' . wp_json_encode( $response ) );

        $order_id = absint( $response['externalId'] );
        $order = wc_get_order( $order_id );

        if ( !$order ) {
            larapay_wc_logger( 'Order #' . $order_id . ' not found' );
            return false;
        }

        // Check if the payment already marked as paid
        if ( get_post_meta( $order_id, $response['paymentId'], true ) === 'paid' ) {
            // return false;
        }

        try {
            larapay_wc_logger( 'Verifying hook digest for order #' . $order->get_id() );
            $this->larapay->validate_ipn_response( $response );
        } catch ( Exception $e ) {
            larapay_wc_logger( $e->getMessage() );
            wp_die( $e->getMessage(), 'LaraPay IPN', array( 'response' => 200 ) );
        } finally {
            larapay_wc_logger( 'Verified hook digest for order #' . $order->get_id() );
        }

        if ( $response['status'] === 'paid' ) {
            $this->handle_success_payment( $order, $response );
        }

        larapay_wc_logger( 'IPN webhook success' );
        wp_die( 'LaraPay IPN webhook success', 'LaraPay IPN', array( 'response' => 200 ) );

    }

    // Handle payment callback
    private function handle_ipn_callback( $response ) {

        if ( !$response ) {
            larapay_wc_logger( 'IPN callback failed' );
            wp_die( 'LaraPay IPN callback failed', 'LaraPay IPN', array( 'response' => 500 ) );
        }

        larapay_wc_logger( 'IPN callback response: ' . wp_json_encode( $response ) );

        if ( $response['status'] === 'fail' ) {
            wp_redirect( wc_get_checkout_url() );
            exit;
        }

        $orders = wc_get_orders( array(
            'limit'      => 1,
            'meta_key'   => '_transaction_id',
            'meta_value' => $response['paymentId'],
        ) );

        $order = isset( $orders[0] ) ? $orders[0] : null;

        if ( !$order ) {
            larapay_wc_logger( 'Order for payment #' . $response['paymentId'] . ' not found' );
            wp_die( 'An error occured. Please refresh this page or contact admin for further assistance.', 'LaraPay IPN', array( 'response' => 200 ) );
        }

        wp_redirect( $order->get_checkout_order_received_url() );
        exit;

    }

    // Handle success payment
    private function handle_success_payment( WC_Order $order, $response ) {

        update_post_meta( $order->get_id(), '_transaction_id', $response['paymentId'] );
        update_post_meta( $order->get_id(), $response['paymentId'], 'paid' );

        $order->payment_complete();

        $reference = '<br>.<br>Payment ID: ' . $response['paymentId'];
        $reference .= '<br>.<br>Collection ID: ' . $response['collectionId'];
        $reference .= '<br>Gateway ID: ' . $response['gatewayId'];
        $reference .= '<br>.<br>Sandbox: ' . ( larapay_wc_get_setting( 'sandbox' ) ? __( 'Yes', 'larapay-wc' ) : __( 'No', 'larapay-wc' ) );

        $order->add_order_note( esc_html__( 'Payment success!', 'larapay-wc' ) . $reference );

        larapay_wc_logger( 'Order #' . $order->get_id() . ' has been marked as Paid' );

    }

}
