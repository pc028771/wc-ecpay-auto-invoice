<?php defined( 'ABSPATH' ) || die( "Can't access directly" );

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
		add_action( 'updated_postmeta', 'updated_postmeta', 10, 4 );
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'novize/v1',
					'/debug/(?P<id>\d+)',
					array(
						'methods'             => 'GET',
						'callback'            => array( $this, 'debug' ),
						'args'                => array(
							'id' => array(
								'validate_callback' => 'is_numeric',
							),
						),
						'permission_callback' => function () {
							return current_user_can( 'administrator' );
						},
					)
				);
			}
		);
	}

	public function debug( WP_REST_Request $request ) {
	}

	public function updated_postmeta( $meta_id, $object_id, $meta_key, $meta_value ) {
		global $ecpi;

		if (
			'enable' === WC_Admin_Settings::get_option( 'wc_linepay_issue_ecpay_invoice', 'disable' )
			&& '_linepay_payment_status' === $meta_key
			&& WC_Gateway_LINEPay_Const::PAYMENT_STATUS_CONFIRMED === $meta_value
		) {
			$order = new WC_Order( $object_id );
			if ( ! $order->get_id() ) {
				return;
			}

			$ecpi->gen_invoice( $object_id, 'auto' );
		}
	}

	/** @deprecated */
	public function woocommerce_payment_complete( $id ) {
		global $ecpi;

		$linepay_payment_status = get_post_meta( $id, '_linepay_payment_status', true );
		if ( empty( $linepay_payment_status ) ) {
			return;
		}

		$order = new WC_Order( $id );
		if ( ! $order->get_id() ) {
			return;
		}

		$ecpi->gen_invoice( $id, 'auto' );
	}

	/**
	 * 啟用前檢查綠界付款及發票外掛是否已安裝
	 */
	public function ecpay_linepay_dependency_check() {
		$can_activate_plugin = is_admin() && current_user_can( 'activate_plugins' );
		if ( ! $can_activate_plugin ) {
			return false;
		}

		$ecpay_invoice = glob( WP_PLUGIN_DIR . '/*/woocommerce-ecpayinvoice.php' );
		if ( empty( $ecpay_invoice ) || ! is_plugin_active( str_replace( WP_PLUGIN_DIR . '/', '', $ecpay_invoice[0] ) ) ) {
			return $this->deactivate_self_and_warning( '請先安裝及啟用 ECPay Invoice for WooCommerce 外掛.' );
		}

		$linepay = glob( WP_PLUGIN_DIR . '/*/gateway-linepay.php' );
		if ( empty( $linepay ) || ! is_plugin_active( str_replace( WP_PLUGIN_DIR . '/', '', $linepay[0] ) ) ) {
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
