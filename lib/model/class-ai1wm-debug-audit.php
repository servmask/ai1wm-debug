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

class Ai1wm_Debug_Audit {

	/**
	 * Initialize audit hooks (called when a support session is detected)
	 */
	public static function init() {
		if ( ! self::is_support_session() ) {
			return;
		}

		$token = self::get_current_token();

		add_action( 'admin_init', array( 'Ai1wm_Debug_Audit', 'log_page_visit' ) );
		add_action( 'activated_plugin', array( 'Ai1wm_Debug_Audit', 'log_plugin_activated' ), 10, 1 );
		add_action( 'deactivated_plugin', array( 'Ai1wm_Debug_Audit', 'log_plugin_deactivated' ), 10, 1 );
		add_action( 'switch_theme', array( 'Ai1wm_Debug_Audit', 'log_theme_switch' ), 10, 1 );
		add_action( 'wp_insert_post', array( 'Ai1wm_Debug_Audit', 'log_post_change' ), 10, 2 );
		add_action( 'delete_post', array( 'Ai1wm_Debug_Audit', 'log_post_delete' ), 10, 1 );
		add_action( 'user_register', array( 'Ai1wm_Debug_Audit', 'log_user_register' ), 10, 1 );
		add_action( 'delete_user', array( 'Ai1wm_Debug_Audit', 'log_user_delete' ), 10, 1 );
		add_action( 'wp_logout', array( 'Ai1wm_Debug_Audit', 'log_logout' ) );
		add_action( 'updated_option', array( 'Ai1wm_Debug_Audit', 'log_option_update' ), 10, 3 );
	}

	/**
	 * Check if the current user is in a support session
	 *
	 * @return boolean
	 */
	public static function is_support_session() {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		return (bool) get_user_meta( get_current_user_id(), '_ai1wm_debug_support_session', true );
	}

	/**
	 * Get the token for the current support user
	 *
	 * @return string
	 */
	public static function get_current_token() {
		$user_id = get_current_user_id();
		$tokens  = Ai1wm_Debug_Config::get( AI1WM_DEBUG_ACCESS_TOKENS_OPTION, array() );

		foreach ( $tokens as $token => $data ) {
			if ( ! empty( $data['active'] ) && $data['user_id'] == $user_id ) {
				return $token;
			}
		}

		return '';
	}

	/**
	 * Log an action to the audit file
	 *
	 * @param string $token
	 * @param string $action
	 * @param string $details
	 */
	public static function log_action( $token, $action, $details = '' ) {
		$user = wp_get_current_user();
		$username = $user && $user->ID ? $user->user_login : 'unknown';
		$masked   = substr( $token, 0, 8 ) . '...';

		$line = sprintf(
			"[%s] [%s] [%s] [%s] %s\n",
			date( 'Y-m-d H:i:s' ),
			$masked,
			$username,
			$action,
			$details
		);

		// Add PHP guard on first write
		if ( ! file_exists( AI1WM_DEBUG_AUDIT_FILE ) ) {
			@file_put_contents( AI1WM_DEBUG_AUDIT_FILE, "<?php exit; ?>\n", LOCK_EX );
		}

		@file_put_contents( AI1WM_DEBUG_AUDIT_FILE, $line, FILE_APPEND | LOCK_EX );
	}

	// Hook callbacks

	public static function log_page_visit() {
		$token = self::get_current_token();
		if ( $token ) {
			$page = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
			self::log_action( $token, 'page_visit', $page );
		}
	}

	public static function log_plugin_activated( $plugin ) {
		$token = self::get_current_token();
		if ( $token ) {
			self::log_action( $token, 'plugin_activated', $plugin );
		}
	}

	public static function log_plugin_deactivated( $plugin ) {
		$token = self::get_current_token();
		if ( $token ) {
			self::log_action( $token, 'plugin_deactivated', $plugin );
		}
	}

	public static function log_theme_switch( $new_theme ) {
		$token = self::get_current_token();
		if ( $token ) {
			self::log_action( $token, 'theme_switch', $new_theme );
		}
	}

	public static function log_post_change( $post_id, $post ) {
		$token = self::get_current_token();
		if ( $token && ! wp_is_post_revision( $post_id ) ) {
			self::log_action( $token, 'post_change', $post->post_type . ' #' . $post_id . ': ' . $post->post_title );
		}
	}

	public static function log_post_delete( $post_id ) {
		$token = self::get_current_token();
		if ( $token ) {
			self::log_action( $token, 'post_delete', 'Post #' . $post_id );
		}
	}

	public static function log_user_register( $user_id ) {
		$token = self::get_current_token();
		if ( $token ) {
			$user = get_user_by( 'id', $user_id );
			self::log_action( $token, 'user_register', $user ? $user->user_login : 'User #' . $user_id );
		}
	}

	public static function log_user_delete( $user_id ) {
		$token = self::get_current_token();
		if ( $token ) {
			self::log_action( $token, 'user_delete', 'User #' . $user_id );
		}
	}

	public static function log_logout() {
		$token = self::get_current_token();
		if ( $token ) {
			self::log_action( $token, 'logout', 'Support user logged out' );
		}
	}

	public static function log_option_update( $option, $old_value, $value ) {
		$token = self::get_current_token();
		if ( ! $token ) {
			return;
		}

		// Only log important options, not transients or internal ones
		$tracked = array(
			'blogname', 'blogdescription', 'siteurl', 'home',
			'admin_email', 'users_can_register', 'default_role',
			'permalink_structure', 'active_plugins', 'template', 'stylesheet',
		);

		if ( in_array( $option, $tracked ) ) {
			self::log_action( $token, 'option_update', $option );
		}
	}

	/**
	 * Get audit entries, optionally filtered by token
	 *
	 * @param  string $token  Filter by token prefix (optional)
	 * @param  int    $offset
	 * @param  int    $limit
	 * @return array
	 */
	public static function get_entries( $token = '', $offset = 0, $limit = 100 ) {
		if ( ! file_exists( AI1WM_DEBUG_AUDIT_FILE ) ) {
			return array( 'entries' => array(), 'total' => 0 );
		}

		$lines = file( AI1WM_DEBUG_AUDIT_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		if ( ! $lines ) {
			return array( 'entries' => array(), 'total' => 0 );
		}

		// Strip PHP guard line
		if ( ! empty( $lines[0] ) && strpos( $lines[0], '<?php' ) === 0 ) {
			array_shift( $lines );
		}

		// Reverse so newest first
		$lines = array_reverse( $lines );

		// Filter by token if provided
		if ( ! empty( $token ) ) {
			$token_prefix = substr( $token, 0, 8 );
			$filtered     = array();
			foreach ( $lines as $line ) {
				if ( strpos( $line, '[' . $token_prefix ) !== false ) {
					$filtered[] = $line;
				}
			}
			$lines = $filtered;
		}

		$total   = count( $lines );
		$entries = array_slice( $lines, $offset, $limit );

		return array(
			'entries' => $entries,
			'total'   => $total,
		);
	}

	/**
	 * Get unique tokens from audit log
	 *
	 * @return array
	 */
	public static function get_session_tokens() {
		if ( ! file_exists( AI1WM_DEBUG_AUDIT_FILE ) ) {
			return array();
		}

		$lines  = file( AI1WM_DEBUG_AUDIT_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		$tokens = array();

		if ( $lines ) {
			// Strip PHP guard line
			if ( ! empty( $lines[0] ) && strpos( $lines[0], '<?php' ) === 0 ) {
				array_shift( $lines );
			}

			foreach ( $lines as $line ) {
				if ( preg_match( '/\[(\w{8})\.\.\.\]/', $line, $matches ) ) {
					$tokens[$matches[1]] = true;
				}
			}
		}

		return array_keys( $tokens );
	}
}
