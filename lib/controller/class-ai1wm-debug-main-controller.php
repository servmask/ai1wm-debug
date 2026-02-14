<?php
/**
 * Copyright (C) 2014-2026 ServMask Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ███████╗███████╗██████╗ ██╗   ██╗███╗   ███╗ █████╗ ███████╗██╗  ██╗
 * ██╔════╝██╔════╝██╔══██╗██║   ██║████╗ ████║██╔══██╗██╔════╝██║ ██╔╝
 * ███████╗█████╗  ██████╔╝██║   ██║██╔████╔██║███████║███████╗█████╔╝
 * ╚════██║██╔══╝  ██╔══██╗╚██╗ ██╔╝██║╚██╔╝██║██╔══██║╚════██║██╔═██╗
 * ███████║███████╗██║  ██║ ╚████╔╝ ██║ ╚═╝ ██║██║  ██║███████║██║  ██╗
 * ╚══════╝╚══════╝╚═╝  ╚═╝  ╚═══╝  ╚═╝     ╚═╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝
 */

class Ai1wm_Debug_Main_Controller {

	public function __construct() {
		$this->activate_actions();
	}

	/**
	 * Register hooks
	 */
	private function activate_actions() {
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts_and_styles' ), 25 );
		add_action( 'admin_init', array( $this, 'setup_storage' ) );
		add_action( 'admin_init', array( $this, 'router' ) );

		if ( is_multisite() ) {
			add_action( 'network_admin_menu', array( $this, 'admin_menu' ), 20 );
		} else {
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 20 );
		}
	}

	/**
	 * Create storage directory if needed
	 */
	public function setup_storage() {
		if ( ! is_dir( AI1WM_DEBUG_STORAGE_PATH ) ) {
			mkdir( AI1WM_DEBUG_STORAGE_PATH, 0755, true );
		}

		$htaccess = AI1WM_DEBUG_STORAGE_PATH . DIRECTORY_SEPARATOR . '.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			file_put_contents( $htaccess, 'deny from all' );
		}

		$index = AI1WM_DEBUG_STORAGE_PATH . DIRECTORY_SEPARATOR . 'index.php';
		if ( ! file_exists( $index ) ) {
			file_put_contents( $index, '<?php // Silence is golden' );
		}
	}

	/**
	 * Register plugin menu
	 */
	public function admin_menu() {
		add_menu_page(
			'ServMask Debug',
			'ServMask Debug',
			'manage_options',
			'servmask-debug',
			'Ai1wm_Debug_Main_Controller::index',
			'dashicons-sos',
			'77.296'
		);

		// Allow support users with debug-only access to see the menu
		add_action( 'user_has_cap', array( $this, 'filter_caps' ), 10, 3 );
	}

	/**
	 * Grant menu access to users with ai1wm_debug_view capability
	 *
	 * @param  array $allcaps All capabilities
	 * @param  array $caps    Required capabilities
	 * @param  array $args    Arguments
	 * @return array
	 */
	public function filter_caps( $allcaps, $caps, $args ) {
		if ( ! empty( $allcaps[AI1WM_DEBUG_VIEW_CAPABILITY] ) ) {
			if ( isset( $args[0] ) && $args[0] === 'manage_options' ) {
				// Only grant on our own page
				if ( isset( $_GET['page'] ) && $_GET['page'] === 'servmask-debug' ) {
					$allcaps['manage_options'] = true;
				}
			}
		}
		return $allcaps;
	}

	/**
	 * Register scripts and styles
	 */
	public function register_scripts_and_styles( $hook ) {
		if ( stripos( $hook, 'servmask-debug' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'servmask-debug',
			AI1WM_DEBUG_URL . '/lib/view/assets/css/servmask-debug.css',
			array(),
			AI1WM_DEBUG_VERSION
		);

		wp_enqueue_script(
			'servmask-debug',
			AI1WM_DEBUG_URL . '/lib/view/assets/js/servmask-debug.js',
			array( 'jquery' ),
			AI1WM_DEBUG_VERSION,
			true
		);

		wp_localize_script(
			'servmask-debug',
			'ai1wm_debug',
			array(
				'ajax' => array(
					'url' => wp_make_link_relative( admin_url( 'admin-ajax.php' ) ),
				),
				'nonce' => wp_create_nonce( AI1WM_DEBUG_NONCE ),
			)
		);
	}

	/**
	 * Display the main page
	 */
	public static function index() {
		if ( ! ai1wm_debug_current_user_can() ) {
			wp_die( 'Unauthorized access.' );
		}

		$ai1wm_active = ai1wm_debug_is_ai1wm_active();

		// Determine active tab
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'environment';

		// Define available tabs
		$tabs = array(
			'environment' => 'Environment',
			'filesystem'  => 'Filesystem',
			'database'    => 'Database',
			'plugins'     => 'Plugins',
			'logs'        => 'Logs',
		);

		// AI1WM-dependent tabs
		if ( $ai1wm_active ) {
			$tabs['realtime']   = 'Real-time Log';
			$tabs['operations'] = 'Operations';
		}

		// Support tabs always available
		$tabs['support'] = 'Support';
		$tabs['audit']   = 'Audit Log';

		// Validate current tab
		if ( ! isset( $tabs[$current_tab] ) ) {
			$current_tab = 'environment';
		}

		ai1wm_debug_render( 'main/index', array(
			'tabs'        => $tabs,
			'current_tab' => $current_tab,
			'ai1wm_active' => $ai1wm_active,
		) );
	}

	/**
	 * Register AJAX routes
	 */
	public function router() {
		add_action( 'wp_ajax_ai1wm_debug_get_database_tables', 'Ai1wm_Debug_Ajax_Controller::get_database_tables' );
		add_action( 'wp_ajax_ai1wm_debug_get_log_files', 'Ai1wm_Debug_Ajax_Controller::get_log_files' );
		add_action( 'wp_ajax_ai1wm_debug_read_log', 'Ai1wm_Debug_Ajax_Controller::read_log' );
		add_action( 'wp_ajax_ai1wm_debug_download_report', 'Ai1wm_Debug_Ajax_Controller::download_report' );
		add_action( 'wp_ajax_ai1wm_debug_poll_realtime_log', 'Ai1wm_Debug_Ajax_Controller::poll_realtime_log' );
		add_action( 'wp_ajax_ai1wm_debug_toggle_logger', 'Ai1wm_Debug_Ajax_Controller::toggle_logger' );
		add_action( 'wp_ajax_ai1wm_debug_generate_access', 'Ai1wm_Debug_Ajax_Controller::generate_access' );
		add_action( 'wp_ajax_ai1wm_debug_revoke_access', 'Ai1wm_Debug_Ajax_Controller::revoke_access' );
		add_action( 'wp_ajax_ai1wm_debug_revoke_all_access', 'Ai1wm_Debug_Ajax_Controller::revoke_all_access' );
		add_action( 'wp_ajax_ai1wm_debug_get_audit_entries', 'Ai1wm_Debug_Ajax_Controller::get_audit_entries' );
	}
}
