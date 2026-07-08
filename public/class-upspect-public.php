<?php
/**
 * This file contains the definition of the Version_Diff_Public class, which
 * is used to load the plugin's public-facing functionality.
 *
 * @package       Sajjad67\UpSpect
 * @subpackage    Sajjad67\UpSpect/public
 * @author        Sajjad Hossain Sagor <sagorh672@gmail.com>
 */

namespace Sajjad67\UpSpect;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version and other methods.
 *
 * @since    1.0.0
 */
class UpSpect_Public {
	/**
	 * The ID of this plugin.
	 *
	 * @since     1.0.0
	 * @access    private
	 * @var       string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since     1.0.0
	 * @access    private
	 * @var       string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @param     string $plugin_name The name of the plugin.
	 * @param     string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}
}
