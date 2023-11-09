<?php defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Plugin Name:       WooCommerce ECPay auto invoice for line pay
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Auto issue ECPay invoice after LinePay payment confirmed
 * Version:           1.0.2
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Novize
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wc-ecpay-auto-invoice
 * Domain Path:       /languages
 */

// 管理介面
if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
	// 載入設定頁面
	add_filter(
		'woocommerce_get_settings_pages',
		function( $settings ) {
			$settings[] = require_once 'class-wc-ecpay-autoinvoice-settings.php';
			return $settings;
		}
	);
}

require_once 'class-wc-ecpay-auto-invoice.php';
$wc_ecpay_auto_invoice = WC_ECPay_Auto_Invoice::get_instance();
