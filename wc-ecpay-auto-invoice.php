<?php defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Plugin Name:       WooCommerce ECPay Auto Invoice
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Auto issue invoice after woocommerce order is completed
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Howard Yang
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wc-ecpay-auto-invoice
 * Domain Path:       /languages
 */

class WC_ECPay_Auto_Invoice {
	static $instance = false;

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function __construct() {
		add_action( 'admin_init', array( $this, 'ecpay_linepay_dependency_check' ) );
		add_action( 'woocommerce_payment_complete', array( $this, 'woocommerce_payment_complete' ) );
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'novize/v1',
					'/debug/(?P<id>\d+)',
					array(
						'methods'  => 'GET',
						'callback' => array( $this, 'debug' ),
					)
				);
			}
		);
	}

	public function debug( WP_REST_Request $request ) {
		global $ecpi;

		$id = $request->get_param( 'id' );
		if ( ! is_numeric( $id ) ) {
			return WP_Error( 'invalid_id', 'Invalid id', array( 'status' => 400 ) );
		}

		$order = new WC_Order( $id );

		// $ecpi->ecpay_auto_invoice( 1, true );
		return $order->get_data();
	}

	public function woocommerce_payment_complete( $order_id ) {
		global $ecpi;

		$linepay_payment_status = get_post_meta( $order_id, '_linepay_payment_status', true );
		if ( WC_Gateway_LINEPay_Const::PAYMENT_STATUS_CONFIRMED !== $linepay_payment_status ) {
			return;
		}

		$ecpi->gen_invoice( $order_id, 'auto' );
	}

	/**
	 * 啟用前檢查綠界付款及發票外掛是否已安裝
	 */
	public function ecpay_linepay_dependency_check() {
		$can_activate_plugin = is_admin() && current_user_can( 'activate_plugins' );
		if ( ! $can_activate_plugin ) {
			return false;
		}

		// if ( ! is_plugin_active( 'WooCommerce_Payment/integration_plugin.php' ) ) {
		// 	return $this->deactivate_self_and_warning( '請先安裝及啟用 ECPay Payment for WooCommerce 外掛.' );
		// }

		if ( ! is_plugin_active( 'WooCommerce_Invoice/woocommerce-ecpayinvoice.php' ) ) {
			return $this->deactivate_self_and_warning( '請先安裝及啟用 ECPay Invoice for WooCommerce 外掛.' );
		}

		if ( ! is_plugin_active( 'WooCommerce_LinePay-0.8.1/gateway-linepay.php' ) ) {
			return $this->deactivate_self_and_warning( '請先安裝及啟用 WooCommerce LINEPay Gateway 外掛.' );
		}
	}

	/**
	 * 在後台顯示錯誤訊息
	 */
	private function deactivate_self_and_warning( $warning ) {
		$warning_message = "<div class=\"error\"><p>$warning</p></div>";
		add_action(
			'admin_notices',
			function() use ( $warning_message ) {
				printf( $warning_message );
			}
		);
		deactivate_plugins( plugin_basename( __FILE__ ) );
		unset( $_GET['activate'] );
	}
}

$wc_ecpay_auto_invoice = WC_ECPay_Auto_Invoice::get_instance();
