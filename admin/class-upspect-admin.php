<?php
/**
 * This file contains the definition of the UpSpect_Admin class, which
 * is used to load the plugin's admin-specific functionality.
 *
 * @package       UpSpect
 * @subpackage    UpSpect/admin
 * @author        Sajjad Hossain Sagor <sagorh672@gmail.com>
 */

namespace Sajjad67\UpSpect;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version and other methods.
 *
 * @since    1.0.0
 */
class UpSpect_Admin {
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
	 * @param     string $plugin_name The name of this plugin.
	 * @param     string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @param     string $hook_suffix The current admin page.
	 */
	public function enqueue_styles( $hook_suffix ) {
		if ( 'plugins.php' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style( 'wp-html-css' );

		wp_enqueue_style( 'colors' );

		wp_enqueue_style( $this->plugin_name, VRSNDFF_UPSPECT_PLUGIN_URL . 'admin/css/admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @param     string $hook_suffix The current admin page.
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( 'plugins.php' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_script( 'wp-diff-checker-js' );

		wp_enqueue_script( $this->plugin_name, VRSNDFF_UPSPECT_PLUGIN_URL . 'admin/js/admin.js', array( 'jquery' ), $this->version, false );

		wp_localize_script(
			$this->plugin_name,
			'Version_Diff',
			array(
				'ajaxurl'                 => admin_url( 'admin-ajax.php' ),
				'nonce'                   => wp_create_nonce( 'vrsndff_ajax_nonce' ),
				'scanningCodebaseTxti18n' => __( 'Scanning codebase...', 'upspect' ),
				'fileSelectionTxti18n'    => __( 'Please select a file from the sidebar tree to inspect changes.', 'upspect' ),
				'failedToIndexTxti18n'    => __( 'Failed to index.', 'upspect' ),
				'noCodeChangedTxti18n'    => __( 'No code changes inside this file layout.', 'upspect' ),
				'loadingDiffTxti18n'      => __( 'Loading diff...', 'upspect' ),
			)
		);
	}

	/**
	 * Add "Check Diff" link in the plugin meta row if an update is available.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @param     array  $plugin_meta An array of the plugin's metadata.
	 * @param     string $plugin_file Path to the plugin file relative to the plugins directory.
	 * @return    array               Modified array of plugin metadata with the added diff link.
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$current_update = get_site_transient( 'update_plugins' );

		if ( isset( $current_update->response[ $plugin_file ] ) ) {
			$update_data = $current_update->response[ $plugin_file ];

			if ( isset( $update_data->slug ) ) {
				$slug            = $update_data->slug;
				$new_version     = $update_data->new_version;
				$plugin_path     = trailingslashit( WP_PLUGIN_DIR ) . $update_data->plugin;
				$current_version = '0.0.0';

				if ( file_exists( $plugin_path ) ) {
					$plugin_data     = get_plugin_data( $plugin_path );
					$current_version = $plugin_data['Version'];
				}

				$plugin_meta[] = sprintf(
					'<a href="#" class="vrsndff-diff-check-btn" data-slug="%s" data-file="%s" data-version="%s">Full Diff (%s vs %s)</a>',
					esc_attr( $slug ),
					esc_attr( $plugin_file ),
					esc_attr( $new_version ),
					esc_html( $current_version ),
					esc_html( $new_version ),
				);
			}
		}

		return $plugin_meta;
	}

	/**
	 * Render the Split IDE-Style Viewport Modal.
	 *
	 * @since     1.0.0
	 * @access    public
	 */
	public function admin_footer() {
		if ( 'plugins' !== get_current_screen()->id ) {
			return;
		}
		?>
		<div id="vrsndff-diff-modal">
			<div class="vrsndff-diff-modal-header">
				<button type="button" class="vrsndff-close-diff-modal-btn"><?php esc_html_e( 'Close Window', 'upspect' ); ?></button>
			</div>
			
			<div class="vrsndff-diff-modal-content">
				<div id="vrsndff-diff-sidebar-tree"></div>
				<div id="vrsndff-diff-viewscreen"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Ajax: Download package and generate diff meta data.
	 */
	public function handle_diff_ajax() {
		check_ajax_referer( 'vrsndff_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'upspect' ) );
		}

		if ( empty( $_POST['slug'] ) || empty( $_POST['file'] ) || empty( $_POST['version'] ) ) {
			wp_send_json_error( __( 'Missing required params.', 'upspect' ) );
		}

		$slug        = sanitize_key( $_POST['slug'] );
		$plugin_file = sanitize_text_field( wp_unslash( $_POST['file'] ) );
		$new_version = sanitize_text_field( wp_unslash( $_POST['version'] ) );

		$current_update = get_site_transient( 'update_plugins' );

		if ( ! isset( $current_update->response[ $plugin_file ]->package ) ) {
			wp_send_json_error( __( 'No valid update transient package mapping.', 'upspect' ) );
		}

		$package_url = $current_update->response[ $plugin_file ]->package;

		if ( empty( $package_url ) ) {
			wp_send_json_error( __( 'Public package URL not provided.', 'upspect' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		$tmp_zip = download_url( $package_url );
		if ( is_wp_error( $tmp_zip ) ) {
			wp_send_json_error( $tmp_zip->get_error_message() );
		}

		$unzip_dir = get_temp_dir() . 'diff-cache-' . $slug . '-' . time();
		$unzipped  = unzip_file( $tmp_zip, $unzip_dir );

		wp_delete_file( $tmp_zip );

		if ( is_wp_error( $unzipped ) ) {
			wp_send_json_error( __( 'Failed extracting target archive.', 'upspect' ) );
		}

		$plugin_dir_name = dirname( $plugin_file );
		if ( '.' === $plugin_dir_name || empty( $plugin_dir_name ) ) {
			$local_plugin_dir  = WP_PLUGIN_DIR;
			$remote_plugin_dir = $unzip_dir . '/' . $slug;
			$root_folder_label = $slug;
		} else {
			$local_plugin_dir  = WP_PLUGIN_DIR . '/' . $plugin_dir_name;
			$remote_plugin_dir = $unzip_dir . '/' . $slug;
			$root_folder_label = $plugin_dir_name;
		}

		$local_files  = $this->list_files_recursive( $local_plugin_dir );
		$remote_files = $this->list_files_recursive( $remote_plugin_dir );

		$all_relative_paths = array_unique( array_merge( array_keys( $local_files ), array_keys( $remote_files ) ) );
		sort( $all_relative_paths );

		if ( ! class_exists( 'WP_Text_Diff_Renderer_Table' ) ) {
			require_once ABSPATH . WPINC . '/wp-diff.php';
		}

		$tree_nodes = array();

		foreach ( $all_relative_paths as $rel_path ) {
			$clean_key = trim( $rel_path, '/' );

			if ( preg_match( '/\.(png|jpg|jpeg|gif|ico|zip|woff2?|ttf|eot|mp4|webm)$/i', $clean_key ) ) {
				continue;
			}

			$local_file_path  = $local_plugin_dir . '/' . $clean_key;
			$remote_file_path = $remote_plugin_dir . '/' . $clean_key;
			$local_exists     = isset( $local_files[ $clean_key ] ) && file_exists( $local_file_path );
			$remote_exists    = isset( $remote_files[ $clean_key ] ) && file_exists( $remote_file_path );
			$meta             = array(
				'adds' => 0,
				'dels' => 0,
			);

			if ( $local_exists && ! $remote_exists ) {
				$status = 'deleted';
			} elseif ( ! $local_exists && $remote_exists ) {
				$status = 'added';
			} elseif ( $local_exists && $remote_exists && md5_file( $local_file_path ) === md5_file( $remote_file_path ) ) {
				continue;
			} else {
				$status = 'modified';

				$local_code  = $this->preserve_empty_lines( file_get_contents( $local_file_path ) ); // phpcs:ignore
				$remote_code = $this->preserve_empty_lines( file_get_contents( $remote_file_path ) ); // phpcs:ignore

				// Parse additions/deletions out of core internal diff engine metrics.
				$text_diff = new \Text_Diff( explode( "\n", $local_code ), explode( "\n", $remote_code ) );
				foreach ( $text_diff->getDiff() as $op ) {
					if ( $op instanceof \Text_Diff_Op_add ) {
						$meta['adds'] += count( $op->final );
					} elseif ( $op instanceof \Text_Diff_Op_delete ) {
						$meta['dels'] += count( $op->orig );
					} elseif ( $op instanceof \Text_Diff_Op_change ) {
						$meta['adds'] += count( $op->final );
						$meta['dels'] += count( $op->orig );
					}
				}
			}

			$tree_nodes[ $clean_key ] = array(
				'status'           => $status,
				'meta'             => $meta,
				'local_file_path'  => $local_file_path,
				'remote_file_path' => $remote_file_path,
			);
		}

		if ( empty( $tree_nodes ) ) {
			wp_send_json_success(
				array(
					'tree_html' => '<p class="vrsndff-file-nochanges">✅ Code structures match 100%! No file adjustments detected.</p>',
				)
			);
		}

		$tree_html = $this->build_html_tree( $tree_nodes, $root_folder_label );
		$cache_key = 'vrsndff_' . md5( $slug . time() . wp_rand() );

		set_transient(
			$cache_key,
			array(
				'tree_nodes' => $tree_nodes,
				'unzip_dir'  => $unzip_dir,
			),
			HOUR_IN_SECONDS
		);

		wp_send_json_success(
			array(
				'tree_html' => $tree_html,
				'cache_key' => $cache_key,
			)
		);
	}

	/**
	 * Ajax: Generate diff for a single file.
	 *
	 * @since     1.0.0
	 * @access    public
	 */
	public function handle_file_diff_ajax() {
		check_ajax_referer( 'vrsndff_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'upspect' ) );
		}

		if ( empty( $_POST['cache_key'] ) || empty( $_POST['filepath'] ) ) {
			wp_send_json_error( __( 'Missing required params.', 'upspect' ) );
		}

		$cache_key = sanitize_key( wp_unslash( $_POST['cache_key'] ) );
		$file_path = sanitize_text_field( wp_unslash( $_POST['filepath'] ) );

		if ( ! preg_match( '/^vrsndff_[a-f0-9]{32}$/i', $cache_key ) ) {
			wp_send_json_error( __( 'Invalid cache key.', 'upspect' ) );
		}

		$cache = get_transient( $cache_key );

		if ( ! $cache || empty( $cache['tree_nodes'][ $file_path ] ) ) {
			wp_send_json_error( __( 'Cache expired or file not found.', 'upspect' ) );
		}

		$file   = $cache['tree_nodes'][ $file_path ];
		$status = $file['status'];

		if ( 'deleted' === $status ) {
			wp_send_json_success(
				'<div class="vrsndff-file-deleted">
					🗑️ This file was removed in the update version.
				</div>'
			);
		}

		$local_file_path  = wp_normalize_path( $file['local_file_path'] );
		$remote_file_path = wp_normalize_path( $file['remote_file_path'] );

		if ( 'added' === $status ) {
			$remote_code = file_get_contents( $remote_file_path ); // phpcs:ignore
			$diff        = wp_text_diff(
				'',
				$remote_code,
				array(
					'title_left'      => __( 'Non-Existent File', 'upspect' ),
					'title_right'     => __( 'New File Version', 'upspect' ),
					'show_split_view' => true,
				)
			);

			wp_send_json_success(
				'<div class="vrsndff-file-new">➕ New File Added</div><table class="diff">' . $diff . '</table>'
			);
		}

		$local_code  = $this->preserve_empty_lines(
			file_get_contents( $local_file_path ) // phpcs:ignore
		);
		$remote_code = $this->preserve_empty_lines(
			file_get_contents( $remote_file_path ) // phpcs:ignore
		);

		$diff = wp_text_diff(
			$local_code,
			$remote_code,
			array(
				'title_left'      => __( 'Current Version', 'upspect' ),
				'title_right'     => __( 'Update Version', 'upspect' ),
				'show_split_view' => true,
			)
		);

		wp_send_json_success( $diff );
	}

	/**
	 * Ajax: Delete temporary plugin folders in upgrade directory.
	 *
	 * @since     1.0.0
	 * @access    public
	 */
	public function handle_folder_delete_ajax() {
		check_ajax_referer( 'vrsndff_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'update_plugins' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'upspect' ) );
		}

		if ( empty( $_POST['cache_key'] ) ) {
			wp_send_json_error( __( 'Missing required params.', 'upspect' ) );
		}

		$cache_key = sanitize_key( wp_unslash( $_POST['cache_key'] ) );

		if ( ! preg_match( '/^vrsndff_[a-f0-9]{32}$/i', $cache_key ) ) {
			wp_send_json_error( __( 'Invalid cache key.', 'upspect' ) );
		}

		$cache = get_transient( $cache_key );

		if ( ! $cache || empty( $cache['unzip_dir'] ) ) {
			wp_send_json_error( __( 'Cache expired.', 'upspect' ) );
		}

		$unzip_dir = sanitize_text_field( $cache['unzip_dir'] );

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		$wp_filesystem->delete( $unzip_dir, true );

		wp_send_json_success( __( 'Success', 'upspect' ) );
	}

	/**
	 * Preserve empty lines.
	 *
	 * @since     1.0.0
	 * @access    private
	 * @param     string $text File content.
	 */
	private function preserve_empty_lines( $text ) {
		$lines = preg_split( '/\R/', $text );

		foreach ( $lines as &$line ) {
			if ( '' === $line ) {
				$line = "\u{200B}"; // Zero-width space.
			}
		}

		return implode( "\n", $lines );
	}

	/**
	 * Map a linear array of system paths into a nested list architecture.
	 *
	 * @since     1.0.0
	 * @access    private
	 * @param     array $file_statuses File statues.
	 * @param     array $root_label    Root label.
	 */
	private function build_html_tree( $file_statuses, $root_label ) {
		$tree = array();

		foreach ( $file_statuses as $path => $data ) {
			$parts   = explode( '/', $path );
			$current = &$tree;

			foreach ( $parts as $i => $part ) {
				if ( count( $parts ) - 1 === $i ) {
					$current[ $part ] = array(
						'type'   => 'file',
						'status' => $data['status'],
						'meta'   => $data['meta'],
						'path'   => $path,
					);
				} else {
					if ( ! isset( $current[ $part ] ) ) {
						$current[ $part ] = array(
							'type'     => 'dir',
							'children' => array(),
						);
					}
					$current = &$current[ $part ]['children'];
				}
			}
		}

		// Render wrapping the entire tree node inside the primary plugin destination root folder.
		$root_wrapper = array(
			$root_label => array(
				'type'     => 'dir',
				'children' => $tree,
			),
		);

		return $this->render_tree_nodes_html( $root_wrapper );
	}

	/**
	 * Recursive layout generator converting multi-dimensional paths to styled list objects.
	 *
	 * @since     1.0.0
	 * @access    private
	 * @param     string $nodes Nodes.
	 */
	private function render_tree_nodes_html( $nodes ) {
		uksort(
			$nodes,
			function ( $a, $b ) use ( $nodes ) {
				$a_type = $nodes[ $a ]['type'];
				$b_type = $nodes[ $b ]['type'];

				if ( $a_type !== $b_type ) {
					return ( 'dir' === $a_type ) ? -1 : 1;
				}
				return strcasecmp( $a, $b );
			}
		);

		$html = '<ul class="vrsndff-diff-tree-list">';
		foreach ( $nodes as $name => $node ) {
			if ( 'dir' === $node['type'] ) {
				$html .= '<li class="vrsndff-diff-tree-dir-wrapper">';
				$html .= sprintf( '<div class="vrsndff-diff-tree-dir">📁 %s</div>', esc_html( $name ) );
				$html .= $this->render_tree_nodes_html( $node['children'] );
				$html .= '</li>';
			} else {
				$html      .= '<li class="vrsndff-diff-tree-item">';
				$badge_html = '';

				if ( 'modified' === $node['status'] ) {
					$badge_html = sprintf( '<span class="vrsndff-git-badge vrsndff-git-mod">%s: +%d/-%d</span>', __( 'mod', 'upspect' ), $node['meta']['adds'], $node['meta']['dels'] );
				} elseif ( 'added' === $node['status'] ) {
					$badge_html = sprintf( '<span class="vrsndff-git-badge vrsndff-git-add">%s</span>', __( 'new', 'upspect' ) );
				} elseif ( 'deleted' === $node['status'] ) {
					$badge_html = sprintf( '<span class="vrsndff-git-badge vrsndff-git-del">%s</span>', __( 'del', 'upspect' ) );
				}

				$html .= sprintf(
					'<a href="#" class="vrsndff-diff-tree-file status-%s" data-filepath="%s"><span>📄 %s</span>%s</a>',
					esc_attr( $node['status'] ),
					esc_attr( $node['path'] ),
					esc_html( $name ),
					$badge_html
				);

				$html .= '</li>';
			}
		}

		$html .= '</ul>';

		return $html;
	}

	/**
	 * List files recuresively.
	 *
	 * @since     1.0.0
	 * @access    private
	 * @param     string $dir The directory to list files of.
	 */
	private function list_files_recursive( $dir ) {
		$file_list = array();
		if ( ! is_dir( $dir ) ) {
			return $file_list;
		}

		$iterator = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ) );
		foreach ( $iterator as $file ) {
			if ( $file->isFile() ) {
				$real_path                   = $file->getPathname();
				$relative_path               = ltrim( str_replace( $dir, '', $real_path ), '/' );
				$file_list[ $relative_path ] = $real_path;
			}
		}
		return $file_list;
	}
}
