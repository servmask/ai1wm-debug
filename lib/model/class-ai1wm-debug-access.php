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
		$token  = Ai1wm_Debug_Security::generate_random_hex( 64 );
		$suffix = substr( Ai1wm_Debug_Security::generate_random_hex( 10 ), 0, 5 );

		$username = 'servmask_support_' . $suffix;
		$password = Ai1wm_Debug_Security::generate_random_hex( 32 );
		$email    = $username . '@servmask.local';

		// Set role based on access level
		$role = ( $level === 'full' ) ? 'administrator' : 'subscriber';

		$user_id = wp_insert_user(
			array(
				'user_login'   => $username,
				'user_pass'    => $password,
				'user_email'   => $email,
				'role'         => $role,
				'display_name' => 'ServMask Support',
			)
		);

		if ( is_wp_error( $user_id ) ) {
			return array( 'error' => $user_id->get_error_message() );
		}

		// Mark as managed support user
		update_user_meta( $user_id, '_ai1wm_debug_support_user', '1' );

		// Grant capabilities for debug_only access
		if ( $level === 'debug_only' ) {
			$user = new WP_User( $user_id );
			$user->add_cap( AI1WM_DEBUG_VIEW_CAPABILITY );
			$user->add_cap( 'export' );
			$user->add_cap( 'import' );
			$user->add_cap( 'upload_files' );
		}

		// Store token data keyed by hash — plaintext token is never stored
		$token_hash   = hash( 'sha256', $token );
		$token_prefix = substr( $token, 0, 8 );
		$ttl          = defined( 'AI1WM_DEBUG_TOKEN_TTL' ) ? AI1WM_DEBUG_TOKEN_TTL : 259200; // 72 hours

		$tokens                = Ai1wm_Debug_Config::get( AI1WM_DEBUG_ACCESS_TOKENS_OPTION, array() );
		$tokens[ $token_hash ] = array(
			'token_prefix' => $token_prefix,
			'user_id'      => $user_id,
			'username'     => $username,
			'level'        => $level,
			'created_at'   => date( 'Y-m-d H:i:s' ),
			'expires_at'   => time() + $ttl,
			'created_by'   => get_current_user_id(),
			'ip'           => isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '',
			'active'       => true,
		);
		Ai1wm_Debug_Config::set( AI1WM_DEBUG_ACCESS_TOKENS_OPTION, $tokens );

		// Log token creation
		Ai1wm_Debug_Audit::log_action( $token_prefix, 'token_created', 'Access level: ' . $level . ', user: ' . $username );

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
		$token_hash = hash( 'sha256', $token );
		$tokens     = Ai1wm_Debug_Config::get( AI1WM_DEBUG_ACCESS_TOKENS_OPTION, array() );

		if ( ! isset( $tokens[ $token_hash ] ) ) {
			return false;
		}

		$data = $tokens[ $token_hash ];

		if ( empty( $data['active'] ) ) {
			return false;
		}

		// Check token expiration
		if ( ! empty( $data['expires_at'] ) && time() > $data['expires_at'] ) {
			self::revoke_access_by_hash( $token_hash );
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
	 * Revoke a support access token by plaintext token
	 *
	 * @param string $token
	 */
	public static function revoke_access( $token ) {
		$token_hash = hash( 'sha256', $token );
		self::revoke_access_by_hash( $token_hash );
	}

	/**
	 * Revoke a support access token by hash
	 *
	 * @param string $token_hash
	 */
	public static function revoke_access_by_hash( $token_hash ) {
		$tokens = Ai1wm_Debug_Config::get( AI1WM_DEBUG_ACCESS_TOKENS_OPTION, array() );

		if ( ! isset( $tokens[ $token_hash ] ) ) {
			return;
		}

		$data   = $tokens[ $token_hash ];
		$prefix = isset( $data['token_prefix'] ) ? $data['token_prefix'] : substr( $token_hash, 0, 8 );

		// Log revocation
		Ai1wm_Debug_Audit::log_action( $prefix, 'token_revoked', 'User: ' . $data['username'] );

		// Delete the temporary user
		if ( ! empty( $data['user_id'] ) ) {
			self::delete_support_user( $data['user_id'] );
		}

		// Remove the token
		unset( $tokens[ $token_hash ] );
		Ai1wm_Debug_Config::set( AI1WM_DEBUG_ACCESS_TOKENS_OPTION, $tokens );
	}

	/**
	 * Revoke all active tokens
	 */
	public static function revoke_all() {
		$tokens = Ai1wm_Debug_Config::get( AI1WM_DEBUG_ACCESS_TOKENS_OPTION, array() );

		foreach ( $tokens as $token_hash => $data ) {
			if ( ! empty( $data['active'] ) ) {
				$prefix = isset( $data['token_prefix'] ) ? $data['token_prefix'] : substr( $token_hash, 0, 8 );
				Ai1wm_Debug_Audit::log_action( $prefix, 'token_revoked', 'User: ' . $data['username'] );
				if ( ! empty( $data['user_id'] ) ) {
					self::delete_support_user( $data['user_id'] );
				}
			}
		}

		Ai1wm_Debug_Config::set( AI1WM_DEBUG_ACCESS_TOKENS_OPTION, array() );
	}

	/**
	 * Get all active support sessions
	 *
	 * @return array
	 */
	public static function get_active_sessions() {
		$tokens = Ai1wm_Debug_Config::get( AI1WM_DEBUG_ACCESS_TOKENS_OPTION, array() );
		$active = array();

		foreach ( $tokens as $token_hash => $data ) {
			// Auto-revoke expired tokens
			if ( ! empty( $data['active'] ) && ! empty( $data['expires_at'] ) && time() > $data['expires_at'] ) {
				self::revoke_access_by_hash( $token_hash );
				continue;
			}

			if ( ! empty( $data['active'] ) ) {
				// Mask the token hash for display — show prefix only
				$prefix               = isset( $data['token_prefix'] ) ? $data['token_prefix'] : substr( $token_hash, 0, 8 );
				$data['masked_token'] = $prefix . str_repeat( '*', 24 );

				// Store hash for revocation via AJAX
				$data['token_hash'] = $token_hash;

				// Get the creator's display name
				$creator                 = get_user_by( 'id', $data['created_by'] );
				$data['created_by_name'] = $creator ? $creator->display_name : 'Unknown';

				// Add human-readable expiry
				if ( ! empty( $data['expires_at'] ) ) {
					$remaining = $data['expires_at'] - time();
					if ( $remaining > 3600 ) {
						$data['expires_in'] = round( $remaining / 3600 ) . 'h';
					} else {
						$data['expires_in'] = round( $remaining / 60 ) . 'm';
					}
				}

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

		$token = sanitize_text_field( $_GET['ai1wm_debug_token'] );

		// Rate limit token login attempts by IP
		$ip            = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
		$transient_key = 'ai1wm_debug_login_' . md5( $ip );
		$attempts      = (int) get_transient( $transient_key );

		if ( $attempts >= 5 ) {
			wp_die( 'Too many login attempts. Please try again later.', 'Rate Limited', array( 'response' => 429 ) );
			return;
		}

		$user_id = self::validate_token( $token );

		if ( ! $user_id ) {
			set_transient( $transient_key, $attempts + 1, 15 * MINUTE_IN_SECONDS );
			wp_die( 'Invalid or expired support access token.', 'Access Denied', array( 'response' => 403 ) );
			return;
		}

		// Clear rate limit on success
		delete_transient( $transient_key );

		// Log the user in
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, false );

		// Set support session flag in user meta
		update_user_meta( $user_id, '_ai1wm_debug_support_session', '1' );

		$token_prefix = substr( $token, 0, 8 );
		$token_hash   = hash( 'sha256', $token );
		$tokens       = Ai1wm_Debug_Config::get( AI1WM_DEBUG_ACCESS_TOKENS_OPTION, array() );
		$level        = isset( $tokens[ $token_hash ]['level'] ) ? $tokens[ $token_hash ]['level'] : 'debug_only';

		Ai1wm_Debug_Audit::log_action( $token_prefix, 'login', 'Support user logged in via token' );
		self::notify_login( $token_hash, $ip );

		// Prevent token leaking via Referer header
		header( 'Referrer-Policy: no-referrer' );

		// Redirect to strip token from URL
		if ( $level === 'debug_only' ) {
			wp_redirect( admin_url( 'admin.php?page=servmask-debug' ) );
		} else {
			wp_redirect( admin_url() );
		}
		exit;
	}

	/**
	 * Email the token creator (or site admin) that a support session has started
	 *
	 * @param string $token_hash
	 * @param string $ip
	 */
	private static function notify_login( $token_hash, $ip ) {
		$tokens = Ai1wm_Debug_Config::get( AI1WM_DEBUG_ACCESS_TOKENS_OPTION, array() );
		if ( ! isset( $tokens[ $token_hash ] ) ) {
			return;
		}

		$data = $tokens[ $token_hash ];

		$recipient = '';
		if ( ! empty( $data['created_by'] ) ) {
			$creator = get_user_by( 'id', $data['created_by'] );
			if ( $creator && ! empty( $creator->user_email ) ) {
				$recipient = $creator->user_email;
			}
		}

		if ( empty( $recipient ) ) {
			$recipient = get_option( 'admin_email' );
		}

		if ( empty( $recipient ) ) {
			return;
		}

		$site_name = get_bloginfo( 'name' );
		$level     = isset( $data['level'] ) ? $data['level'] : 'debug_only';
		$username  = isset( $data['username'] ) ? $data['username'] : '';
		$ua        = isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '';
		$time      = current_time( 'mysql' );
		$page_url  = admin_url( 'admin.php?page=servmask-debug' );

		$subject = sprintf( '[%s] ServMask Debug support session started', $site_name );

		$message  = "A ServMask Debug support session just started on your site.\n\n";
		$message .= 'Site:         ' . site_url() . "\n";
		$message .= 'Time:         ' . $time . "\n";
		$message .= 'Access level: ' . $level . "\n";
		$message .= 'Support user: ' . $username . "\n";
		$message .= 'IP address:   ' . $ip . "\n";
		$message .= 'User agent:   ' . $ua . "\n\n";
		$message .= "If you did not expect this session, revoke the token now:\n";
		$message .= $page_url . "\n";

		wp_mail( $recipient, $subject, $message );
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
