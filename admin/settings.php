<?php
if ( !defined( 'ABSPATH' ) ) exit;

// Settings form fields
function larapay_wc_settings_form_fields() {

    return array(
        'enabled' => array(
            'title'       => __( 'Enable/Disable', 'larapay-wc' ),
            'type'        => 'checkbox',
            'label'       => __( 'Enable LaraPay', 'larapay-wc' ),
            'default'     => 'no',
        ),
        'title' => array(
            'title'       => __( 'Title', 'larapay-wc' ),
            'type'        => 'text',
            'description' => __( 'This controls the title which the user sees during checkout.', 'larapay-wc' ),
            'placeholder' => __( 'LaraPay', 'larapay-wc' ),
            'default'     => __( 'LaraPay', 'larapay-wc' ),
            'desc_tip'    => true,
        ),
        'description' => array(
            'title'       => __( 'Description', 'larapay-wc' ),
            'type'        => 'textarea',
            'description' => __( 'This controls the description which the user sees during checkout.', 'larapay-wc' ),
            'desc_tip'    => true,
            'placeholder' => __( 'Pay with Online Banking', 'larapay-wc' ),
            'default'     => __( 'Pay with Online Banking', 'larapay-wc' ),
        ),
        'collection' => array(
            'title'       => __( 'Collection', 'larapay-wc' ),
            'type'        => 'title',
            'description' => __( 'Collection information can be obtained from LaraPay dashboard > Gateway > Collection > Settings page.', 'larapay-wc' ),
        ),
        'secret_key' => array(
            'title'       => __( 'Secret Key', 'larapay-wc' ),
            'type'        => 'text',
        ),
        'auth_token' => array(
            'title'       => __( 'Access Token', 'larapay-wc' ),
            'type'        => 'text',
        ),
        'debugging' => array(
            'title'       => __( 'Debugging', 'larapay-wc' ),
            'type'        => 'title',
        ),
        'sandbox' => array(
            'title'       => __( 'Sandbox', 'larapay-wc' ),
            'type'        => 'checkbox',
            'label'       => sprintf( __('Use sandbox. <a href="%s" target="_blank">Register LaraPay account</a>.', 'larapay-wc' ), 'https://larapay.asia/' ),
            'description' => __( 'If checked, it will send request to LaraPay on sandbox mode.', 'larapay-wc' ),
            'desc_tip'    => true,
            'default'     => 'no',
        ),
        'debug' => array(
            'title'       => __( 'Debug Log', 'larapay-wc' ),
            'type'        => 'checkbox',
            'label'       => __( 'Enable debug log', 'larapay-wc' ),
            'description' => __( 'Log LaraPay events, eg: IPN requests. Logs can be viewed on WooCommerce > Status > Logs.', 'larapay-wc' ),
            'desc_tip'    => true,
            'default'     => 'no',
        ),
    );

}
