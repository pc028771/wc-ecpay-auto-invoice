<?php defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Plugin Name:       WooCommerce ECPay Auto Invoice
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Auto issue ECPay invoice after LinePay payment complete
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Novize
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wc-ecpay-auto-invoice
 * Domain Path:       /languages
 */

require_once './class-wc-ecpay-auto-invoice.php';

$wc_ecpay_auto_invoice = WC_ECPay_Auto_Invoice::get_instance();
