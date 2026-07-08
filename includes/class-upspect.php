<?php
/**
 * This file contains the definition of the UpSpect class, which
 * is used to begin the plugin's functionality.
 *
 * @package       UpSpect
 * @subpackage    UpSpect/includes
 * @author        Sajjad Hossain Sagor <sagorh672@gmail.com>
 */

namespace Sajjad67\UpSpect;

use Sajjad67\UpSpect\UpSpect_Loader;
use Sajjad67\UpSpect\UpSpect_Admin;
use Sajjad67\UpSpect\UpSpect_Public;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * The core plugin class.
 *
 * This is used to define admin-specific hooks and public-facing hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since    1.0.0
 */
class UpSpect {
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since     1.0.0
	 * @access    protected
	 * @var       UpSpect_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since     1.0.0
	 * @access    protected
	 * @var       string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since     1.0.0
	 * @access    protected
	 * @var       string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since     1.0.0
	 * @access    public
	 */
	public function __construct() {
		$this->version     = defined( 'VRSNDFF_UPSPECT_PLUGIN_VERSION' ) ? VRSNDFF_UPSPECT_PLUGIN_VERSION : '1.0.0';
		$this->plugin_name = 'upspect';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - UpSpect_Loader. Orchestrates the hooks of the plugin.
	 * - UpSpect_Admin.  Defines all hooks for the admin area.
	 * - UpSpect_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since     1.0.0
	 * @access    private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once VRSNDFF_UPSPECT_PLUGIN_PATH . 'includes/class-upspect-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once VRSNDFF_UPSPECT_PLUGIN_PATH . 'admin/class-upspect-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once VRSNDFF_UPSPECT_PLUGIN_PATH . 'public/class-upspect-public.php';

		$this->loader = new UpSpect_Loader();
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since     1.0.0
	 * @access    private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new UpSpect_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'plugin_row_meta', $plugin_admin, 'plugin_row_meta', 99, 2 );

		$this->loader->add_action( 'admin_footer', $plugin_admin, 'admin_footer' );

		$this->loader->add_action( 'wp_ajax_vrsndff_get_plugin_diff', $plugin_admin, 'handle_diff_ajax' );
		$this->loader->add_action( 'wp_ajax_vrsndff_get_file_diff', $plugin_admin, 'handle_file_diff_ajax' );
		$this->loader->add_action( 'wp_ajax_vrsndff_delete_temporary_files', $plugin_admin, 'handle_folder_delete_ajax' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since     1.0.0
	 * @access    private
	 */
	private function define_public_hooks() {
		$plugin_public = new UpSpect_Public( $this->get_plugin_name(), $this->get_version() );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since     1.0.0
	 * @access    public
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of WordPress.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @return    string The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @return    UpSpect_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @return    string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
