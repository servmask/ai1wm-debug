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

class Ai1wm_Debug_Access {

	/**
	 * Initialize token login handler
	 */
	public static function init() {
		add_action( 'init', array( 'Ai1wm_Debug_Access', 'handle_login' ) );
	}

	/**
	 * Create a support access token and temporary user
	 *
	 * @param  string $level 'full' or 'debug_only'
	 * @return array
	 */
	public static function create_access( $level ) {
		$token  = Ai1wm_Debug_Security::generate_random_hex( 32 );
		$suffix = substr( Ai1wm_Debug_Security::generate_random_hex( 10 ), 0, 5 );

		$username = 'servmask_support_' . $suffix;
		$password = Ai1wm_Debug_Security::generate_random_hex( 32 );
		$email    = $username . '@servmask.local';

		// Set role based on access level
		$role = ( $level === 'full' ) ? 'administrator' : 'subscriber';

		$user_id = wp_insert_user( array(
			'user_login' => $username,
			'user_pass'  => $password,
			'user_email' => $email,
			'role'       => $role,
			'display_name' => 'ServMask Support',
		) );

		if ( is_wp_error( $user_id ) ) {
			return array( 'error' => $user_id->get_error_message() );
		}

		// Mark as managed support user
		update_user_meta( $user_id, '_ai1wm_debug_support_user', '1' );

		// Grant debug view capability for debug_only access
		if ( $level === 'debug_only' ) {
			$user = new WP_User( $user_id );
			$user->add_cap( AI1WM_DEBUG_VIEW_CAPABILITY );
		}

		// Store token data
		$tokens = get_option( AI1WM_DEBUG_ACCESS_TOKENS_OPTION, array() );
		$tokens[$token] = array(
			'token'      => $token,
			'user_id'    => $user_id,
			'username'   => $username,
			'level'      => $level,
			'created_at' => date( 'Y-m-d H:i:s' ),
			'created_by' => get_current_user_id(),
			'ip'         => isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '',
			'active'     => true,
		);
		update_option( AI1WM_DEBUG_ACCESS_TOKENS_OPTION, $tokens );

		// Build the login URL
		$url = add_query_arg( 'ai1wm_debug_token', $token, site_url( '/' ) );

		return array(
			'success'  => true,
			'token'    => $token,
			'url'      => $url,
			'username' => $username,
			'level'    => $level,
		);
	}

	/**
	 * Validate a token
	 *
	 * @param  string    $token
	 * @return int|false User ID or false
	 */
	public static function validate_token( $token ) {
		$tokens = get_option( AI1WM_DEBUG_ACCESS_TOKENS_OPTION, array() );

		if ( ! isset( $tokens[$token] ) ) {
			return false;
		}

		$data = $tokens[$token];

		if ( empty( $data['active'] ) ) {
			return false;
		}

		// Verify the user still exists
		$user = get_user_by( 'id', $data['user_id'] );
		if ( ! $user ) {
			return false;
		}

		return $data['user_id'];
	}

	/**
	 * Revoke a support access token
	 *
	 * @param string $token
	 */
	public static function revoke_access( $token ) {
		$tokens = get_option( AI1WM_DEBUG_ACCESS_TOKENS_OPTION, array() );

		if ( ! isset( $tokens[$token] ) ) {
			return;
		}

		$data = $tokens[$token];

		// Delete the temporary user
		if ( ! empty( $data['user_id'] ) ) {
			self::delete_support_user( $data['user_id'] );
		}

		// Mark as inactive
		$tokens[$token]['active'] = false;
		update_option( AI1WM_DEBUG_ACCESS_TOKENS_OPTION, $tokens );
	}

	/**
	 * Revoke all active tokens
	 */
	public static function revoke_all() {
		$tokens = get_option( AI1WM_DEBUG_ACCESS_TOKENS_OPTION, array() );

		foreach ( $tokens as $token => $data ) {
			if ( ! empty( $data['active'] ) && ! empty( $data['user_id'] ) ) {
				self::delete_support_user( $data['user_id'] );
				$tokens[$token]['active'] = false;
			}
		}

		update_option( AI1WM_DEBUG_ACCESS_TOKENS_OPTION, $tokens );
	}

	/**
	 * Get all active support sessions
	 *
	 * @return array
	 */
	public static function get_active_sessions() {
		$tokens  = get_option( AI1WM_DEBUG_ACCESS_TOKENS_OPTION, array() );
		$active  = array();

		foreach ( $tokens as $token => $data ) {
			if ( ! empty( $data['active'] ) ) {
				// Mask the token for display
				$masked = substr( $token, 0, 8 ) . str_repeat( '*', 16 ) . substr( $token, -8 );
				$data['masked_token'] = $masked;

				// Get the creator's display name
				$creator = get_user_by( 'id', $data['created_by'] );
				$data['created_by_name'] = $creator ? $creator->display_name : 'Unknown';

				$active[] = $data;
			}
		}

		return $active;
	}

	/**
	 * Handle token-based login from URL
	 */
	public static function handle_login() {
		if ( ! isset( $_GET['ai1wm_debug_token'] ) ) {
			return;
		}

		$token   = sanitize_text_field( $_GET['ai1wm_debug_token'] );
		$user_id = self::validate_token( $token );

		if ( ! $user_id ) {
			wp_die( 'Invalid or expired support access token.', 'Access Denied', array( 'response' => 403 ) );
			return;
		}

		// Log the user in
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, false );

		// Set support session flag in user meta
		update_user_meta( $user_id, '_ai1wm_debug_support_session', '1' );

		// Log the login
		Ai1wm_Debug_Audit::log_action( $token, 'login', 'Support user logged in via token' );

		// Determine redirect based on access level
		$tokens = get_option( AI1WM_DEBUG_ACCESS_TOKENS_OPTION, array() );
		$level  = isset( $tokens[$token]['level'] ) ? $tokens[$token]['level'] : 'debug_only';

		if ( $level === 'debug_only' ) {
			wp_redirect( admin_url( 'admin.php?page=servmask-debug' ) );
		} else {
			wp_redirect( admin_url() );
		}
		exit;
	}

	/**
	 * Delete a support user and destroy their sessions
	 *
	 * @param int $user_id
	 */
	private static function delete_support_user( $user_id ) {
		// Verify it's a support user
		$is_support = get_user_meta( $user_id, '_ai1wm_debug_support_user', true );
		if ( ! $is_support ) {
			return;
		}

		// Destroy sessions
		$sessions = WP_Session_Tokens::get_instance( $user_id );
		$sessions->destroy_all();

		// Delete the user
		if ( ! function_exists( 'wp_delete_user' ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}
		wp_delete_user( $user_id );
	}
}
