<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @package           Sajjad67\UpSpect
 * @author            Sajjad Hossain Sagor <sagorh672@gmail.com>
 *
 * Plugin Name:       UpSpect – Plugin Update Inspector
 * Plugin URI:        https://wordpress.org/plugins/upspect/
 * Description:       Preview and inspect plugin updates before installing them. Compare files, review code changes, and see exactly what has changed between versions.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Sajjad Hossain Sagor
 * Author URI:        https://sajjadhsagor.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       upspect
 * Domain Path:       /languages
 */

namespace Sajjad67\UpSpect;

use Sajjad67\UpSpect\UpSpect;
use Sajjad67\UpSpect\UpSpect_Activator;
use Sajjad67\UpSpect\UpSpect_Deactivator;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'VRSNDFF_UPSPECT_PLUGIN_VERSION', '1.0.0' );

/**
 * Define Plugin Folders Path
 */
define( 'VRSNDFF_UPSPECT_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

define( 'VRSNDFF_UPSPECT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

define( 'VRSNDFF_UPSPECT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-upspect-activator.php
 *
 * @since    1.0.0
 */
function on_activate_upspect() {
	require_once VRSNDFF_UPSPECT_PLUGIN_PATH . 'includes/class-upspect-activator.php';

	UpSpect_Activator::on_activate();
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\\on_activate_upspect' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-upspect-deactivator.php
 *
 * @since    1.0.0
 */
function on_deactivate_upspect() {
	require_once VRSNDFF_UPSPECT_PLUGIN_PATH . 'includes/class-upspect-deactivator.php';

	UpSpect_Deactivator::on_deactivate();
}

register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\on_deactivate_upspect' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 *
 * @since    1.0.0
 */
require VRSNDFF_UPSPECT_PLUGIN_PATH . 'includes/class-upspect.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_upspect() {
	$plugin = new UpSpect();

	$plugin->run();
}

run_upspect();
