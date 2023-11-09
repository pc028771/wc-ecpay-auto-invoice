<?php defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Settings
 *
 * Adds UX for adding/modifying model
 *
 * @since 2.0.0
 */
class WC_ECPay_AutoInvoice_Settings extends WC_Settings_Page {


	/**
	 * Add various admin hooks/filters
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->id    = 'wc-ecpay-auto-invoice';
		$this->label = __( 'LinePay電子發票', 'wc-ecpay-auto-invoice' );

		parent::__construct();

		$this->model = get_option( 'wc_ecpay_autoinvoice_active_model', array() );
	}

	/**
	 * Get sections
	 *
	 * @return array
	 */
	public function get_sections() {
		return array(
			'config' => __( 'LinePay電子發票', 'wc-ecpay-auto-invoice' ),
		);
	}

	/**
	 * Render the settings for the current section
	 *
	 * @since 2.0.0
	 */
	public function output() {
		$settings = $this->get_settings();

		// inject the actual setting value before outputting the fields
		// ::output_fields() uses get_option() but model are stored
		// in a single option so this dynamically returns the correct value
		foreach ( $this->model as $filter => $value ) {

			add_filter( "pre_option_{$filter}", array( $this, 'get_customization' ) );
		}

		WC_Admin_Settings::output_fields( $settings );
	}


	/**
	 * Return the customization value for the given filter
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_customization() {
		$filter = str_replace( 'pre_option_', '', current_filter() );
		return isset( $this->model[ $filter ] ) ? $this->model[ $filter ] : '';
	}


	/**
	 * Save the model
	 *
	 * @since 2.0.0
	 */
	public function save() {
		foreach ( $this->get_settings() as $field ) {
			// skip titles, etc
			if ( ! isset( $field['id'] ) ) {
				continue;
			}

			$field_id = $field['id'];
			if ( ! empty( $_POST[ $field_id ] ) ) {
				$this->model[ $field_id ] = wp_kses_post( stripslashes( $_POST[ $field_id ] ) );
				continue;
			}

			unset( $this->model[ $field_id ] );
		}

		update_option( 'wc_ecpay_autoinvoice_active_model', $this->model );
	}


	/**
	 * Return admin fields in proper format for outputting / saving
	 *
	 * @since 1.1
	 * @return array
	 */
	public function get_settings() {
		$settings = array(

			'config' =>

				array(

					array(
						'title' => __( '介接參數設定', 'wc-ecpay-auto-invoice' ),
						'type'  => 'title',
					),

					array(
						'id'       => 'wc_linepay_issue_ecpay_invoice',
						'title'    => __( '自動開立發票' ),
						'desc_tip' => __( '當使用LinePay金流付款完成後，將使用ECPay自動開立電子發票', 'wc-ecpay-auto-invoice' ),
						'type'     => 'select',
						'options'  => array(
							'enable'  => __( '啟用', 'wc-ecpay-auto-invoice' ),
							'disable' => __( '停用', 'wc-ecpay-auto-invoice' ),
						),
						'default'  => 'disable',
					),

					array( 'type' => 'sectionend' ),

				),
		);

		$current_section = isset( $GLOBALS['current_section'] ) ? $GLOBALS['current_section'] : 'config';

		return isset( $settings[ $current_section ] ) ? $settings[ $current_section ] : $settings['config'];
	}


}

// setup settings
new WC_ECPay_AutoInvoice_Settings();
