<?php if ( !defined( 'ABSPATH' ) ) exit; ?>

<h3 class="wc-settings-sub-title " id="woocommerce_larapay_callback_url"><?php esc_html_e( 'Webhook &amp; Callback', 'larapay-wc' ); ?></h3>
<table class="form-table">
	<tr valign="top">
		<th scope="row" class="titledesc">
			<label><?php esc_html_e( 'Webhook &amp; Callback URL', 'larapay-wc' ); ?></label>
		</th>
		<td class="forminp">
			<fieldset>
				<legend class="screen-reader-text"><span><?php esc_html_e( 'Webhook &amp; Callback URL', 'larapay-wc' ); ?></span></legend>
				<input class="input-text regular-input" type="text" value="<?php echo esc_attr( WC()->api_request_url( get_class( $this ) ) ); ?>" readonly>
				<p class="description"><?php esc_html_e( 'Copy and paste this URL in your collection settings.', 'larapay-wc' ); ?></p>
			</fieldset>
		</td>
	</tr>
</table>
