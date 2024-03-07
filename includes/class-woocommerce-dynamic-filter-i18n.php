<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://muhammadkarrar.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Dynamic_Filter
 * @subpackage Woocommerce_Dynamic_Filter/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Woocommerce_Dynamic_Filter
 * @subpackage Woocommerce_Dynamic_Filter/includes
 * @author     Muhammad Karrar <mohammad.karrar1995@hotmail.com>
 */
class Woocommerce_Dynamic_Filter_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'woocommerce-dynamic-filter',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
