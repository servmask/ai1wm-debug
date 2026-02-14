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

class Ai1wm_Debug_Ajax_Controller {

	/**
	 * Verify AJAX request
	 *
	 * @return boolean
	 */
	private static function verify_request() {
		if ( ! ai1wm_debug_current_user_can() ) {
			echo json_encode( array( 'error' => 'Unauthorized' ) );
			exit;
		}

		if ( ! check_ajax_referer( AI1WM_DEBUG_NONCE, 'nonce', false ) ) {
			echo json_encode( array( 'error' => 'Invalid nonce' ) );
			exit;
		}

		return true;
	}

	/**
	 * Get database tables (AJAX)
	 */
	public static function get_database_tables() {
		self::verify_request();
		echo json_encode( Ai1wm_Debug_Database::get_tables() );
		exit;
	}

	/**
	 * Get log file list (AJAX)
	 */
	public static function get_log_files() {
		self::verify_request();
		echo json_encode( Ai1wm_Debug_Logs::get_log_files() );
		exit;
	}

	/**
	 * Read a log file (AJAX)
	 */
	public static function read_log() {
		self::verify_request();

		$file   = isset( $_POST['file'] ) ? sanitize_text_field( $_POST['file'] ) : '';
		$offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;
		$lines  = isset( $_POST['lines'] ) ? intval( $_POST['lines'] ) : 100;

		echo json_encode( Ai1wm_Debug_Logs::read_log( $file, $offset, $lines ) );
		exit;
	}

	/**
	 * Download full report (AJAX)
	 */
	public static function download_report() {
		self::verify_request();

		$format = isset( $_GET['format'] ) ? sanitize_key( $_GET['format'] ) : 'text';

		if ( $format === 'json' ) {
			header( 'Content-Type: application/json' );
			header( 'Content-Disposition: attachment; filename=servmask-debug-report.json' );
			echo json_encode( Ai1wm_Debug_Report::generate(), JSON_PRETTY_PRINT );
		} else {
			header( 'Content-Type: text/plain' );
			header( 'Content-Disposition: attachment; filename=servmask-debug-report.txt' );
			echo Ai1wm_Debug_Report::generate_text();
		}
		exit;
	}

	/**
	 * Poll real-time log entries (AJAX)
	 */
	public static function poll_realtime_log() {
		self::verify_request();

		$last_pos = isset( $_POST['last_pos'] ) ? intval( $_POST['last_pos'] ) : 0;

		echo json_encode( Ai1wm_Debug_Logger::read_new_entries( $last_pos ) );
		exit;
	}

	/**
	 * Toggle real-time logger (AJAX)
	 */
	public static function toggle_logger() {
		self::verify_request();

		if ( ! current_user_can( 'manage_options' ) ) {
			echo json_encode( array( 'error' => 'Unauthorized' ) );
			exit;
		}

		$enabled   = isset( $_POST['enabled'] ) ? intval( $_POST['enabled'] ) : 0;
		$verbosity = isset( $_POST['verbosity'] ) ? intval( $_POST['verbosity'] ) : AI1WM_DEBUG_VERBOSITY_INFO;

		update_option( AI1WM_DEBUG_LOGGER_ENABLED_OPTION, $enabled );
		update_option( AI1WM_DEBUG_LOGGER_VERBOSITY_OPTION, $verbosity );

		echo json_encode( array( 'success' => true ) );
		exit;
	}

	/**
	 * Generate support access token (AJAX)
	 */
	public static function generate_access() {
		self::verify_request();

		if ( ! current_user_can( 'manage_options' ) ) {
			echo json_encode( array( 'error' => 'Unauthorized' ) );
			exit;
		}

		$level = isset( $_POST['level'] ) ? sanitize_key( $_POST['level'] ) : 'debug_only';

		$result = Ai1wm_Debug_Access::create_access( $level );

		echo json_encode( $result );
		exit;
	}

	/**
	 * Revoke a support access token (AJAX)
	 */
	public static function revoke_access() {
		self::verify_request();

		if ( ! current_user_can( 'manage_options' ) ) {
			echo json_encode( array( 'error' => 'Unauthorized' ) );
			exit;
		}

		$token = isset( $_POST['token'] ) ? sanitize_text_field( $_POST['token'] ) : '';

		Ai1wm_Debug_Access::revoke_access( $token );

		echo json_encode( array( 'success' => true ) );
		exit;
	}

	/**
	 * Revoke all support access tokens (AJAX)
	 */
	public static function revoke_all_access() {
		self::verify_request();

		if ( ! current_user_can( 'manage_options' ) ) {
			echo json_encode( array( 'error' => 'Unauthorized' ) );
			exit;
		}

		Ai1wm_Debug_Access::revoke_all();

		echo json_encode( array( 'success' => true ) );
		exit;
	}

	/**
	 * Get audit log entries (AJAX)
	 */
	public static function get_audit_entries() {
		self::verify_request();

		$token  = isset( $_POST['token'] ) ? sanitize_text_field( $_POST['token'] ) : '';
		$offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;
		$limit  = isset( $_POST['limit'] ) ? intval( $_POST['limit'] ) : 100;

		echo json_encode( Ai1wm_Debug_Audit::get_entries( $token, $offset, $limit ) );
		exit;
	}
}
