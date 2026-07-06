<?php
/**
 * This file contains the definition of the Version_Diff_Activator class, which
 * is used during plugin activation.
 *
 * @package       Version_Diff
 * @subpackage    Version_Diff/includes
 * @author        Sajjad Hossain Sagor <sagorh672@gmail.com>
 */

namespace Sajjad67\VersionDiff;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin activation.
 *
 * @since    1.0.0
 */
class Version_Diff_Activator {
	/**
	 * Activation hook.
	 *
	 * This function is called when the plugin is activated. It can be used to
	 * perform tasks such as creating database tables, setting up default options,
	 * or scheduling cron jobs.
	 *
	 * @since     1.0.0
	 * @static
	 * @access    public
	 */
	public static function on_activate() {}
}
