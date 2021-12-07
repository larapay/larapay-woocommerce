<?php
if ( !defined( 'ABSPATH' ) ) exit;

class Larapay_WC_Admin {

    // Register hooks
    public function __construct() {

        add_action( 'plugin_action_links_' . LARAPAY_WC_BASENAME, array( $this, 'register_settings_link' ) );
        add_action( 'admin_notices', array( $this, 'woocommerce_notice' ) );
        add_action( 'admin_notices', array( $this, 'currency_not_supported_notice' ) );

    }

    // Register plugin settings link
    public function register_settings_link( $links ) {

        $url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=larapay' );
        $label = esc_html__( 'Settings', 'larapay-wc' );

        $settings_link = sprintf( '<a href="%s">%s</a>', $url, $label );
        array_unshift( $links, $settings_link );

        return $links;

    }

    // Check if WooCommerce is installed and activated
    private function is_woocommerce_activated() {
        return in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
    }

    // Show notice if WooCommerce not installed
    public function woocommerce_notice() {

        if ( !$this->is_woocommerce_activated() ) {
            larapay_wc_notice( __( 'WooCommerce needs to be installed and activated.', 'larapay-wc' ), 'error' );
        }

    }

    // Show notice if currency selected is not supported by LaraPay
    public function currency_not_supported_notice() {

        if ( !function_exists( 'get_woocommerce_currency' ) ) {
            return false;
        }

        if ( get_woocommerce_currency() !== 'MYR' ) {
            larapay_wc_notice( sprintf( __( 'Currency not supported by Larapay. <a href="%s">Change currency</a>', 'larapay-wc' ), admin_url( 'admin.php?page=wc-settings&tab=general#woocommerce_currency' ) ), 'error' );
        }

    }

}
new Larapay_WC_Admin();
