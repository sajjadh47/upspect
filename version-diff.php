<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @package           Sajjad67\VersionDiff
 * @author            Sajjad Hossain Sagor <sagorh672@gmail.com>
 *
 * Plugin Name:       Version Diff
 * Plugin URI:        https://wordpress.org/plugins/version-diff/
 * Description:       Preview and inspect plugin updates before installing them. Compare files, review code changes, and see exactly what has changed between versions.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Sajjad Hossain Sagor
 * Author URI:        https://sajjadhsagor.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       version-diff
 * Domain Path:       /languages
 */

namespace Sajjad67\VersionDiff;

use Sajjad67\VersionDiff\Version_Diff;
use Sajjad67\VersionDiff\Version_Diff_Activator;
use Sajjad67\VersionDiff\Version_Diff_Deactivator;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'VRSNDFF_VERSION_DIFF_PLUGIN_VERSION', '1.0.0' );

/**
 * Define Plugin Folders Path
 */
define( 'VRSNDFF_VERSION_DIFF_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

define( 'VRSNDFF_VERSION_DIFF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

define( 'VRSNDFF_VERSION_DIFF_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-version-diff-activator.php
 *
 * @since    1.0.0
 */
function on_activate_version_diff() {
	require_once VRSNDFF_VERSION_DIFF_PLUGIN_PATH . 'includes/class-version-diff-activator.php';

	Version_Diff_Activator::on_activate();
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\\on_activate_version_diff' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-version-diff-deactivator.php
 *
 * @since    1.0.0
 */
function on_deactivate_version_diff() {
	require_once VRSNDFF_VERSION_DIFF_PLUGIN_PATH . 'includes/class-version-diff-deactivator.php';

	Version_Diff_Deactivator::on_deactivate();
}

register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\on_deactivate_version_diff' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 *
 * @since    1.0.0
 */
require VRSNDFF_VERSION_DIFF_PLUGIN_PATH . 'includes/class-version-diff.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_version_diff() {
	$plugin = new Version_Diff();

	$plugin->run();
}

run_version_diff();
