<?php
/**
 * This file contains the definition of the Version_Diff_Deactivator class, which
 * is used during plugin deactivation.
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
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin deactivation.
 *
 * @since    1.0.0
 */
class Version_Diff_Deactivator {
	/**
	 * Deactivation hook.
	 *
	 * This function is called when the plugin is deactivated. It can be used to
	 * perform tasks such as cleaning up temporary data, unscheduling cron jobs,
	 * or removing options.
	 *
	 * @since     1.0.0
	 * @static
	 * @access    public
	 */
	public static function on_deactivate() {}
}
