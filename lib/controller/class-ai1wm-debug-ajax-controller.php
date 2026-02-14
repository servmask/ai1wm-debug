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
		$filename = isset( $_POST['filename'] ) ? sanitize_file_name( $_POST['filename'] ) : null;

		$result = Ai1wm_Debug_Logger::read_new_entries( $last_pos, $filename );

		// Include current run filename so JS can detect run changes
		$current = Ai1wm_Debug_Logger::get_current_log_file();
		$result['current_file'] = $current ? basename( $current ) : '';

		echo json_encode( $result );
		exit;
	}

	/**
	 * Get list of run logs (AJAX)
	 */
	public static function get_run_logs() {
		self::verify_request();

		echo json_encode( Ai1wm_Debug_Logger::get_run_logs() );
		exit;
	}

	/**
	 * Download a run log file
	 */
	public static function download_realtime_log() {
		self::verify_request();

		$filename = isset( $_GET['filename'] ) ? sanitize_file_name( $_GET['filename'] ) : '';

		// Default to current run
		if ( empty( $filename ) ) {
			$current = Ai1wm_Debug_Logger::get_current_log_file();
			if ( ! $current ) {
				echo 'No log file available.';
				exit;
			}
			$filename = basename( $current );
		}

		$filepath = AI1WM_DEBUG_LOGS_PATH . DIRECTORY_SEPARATOR . $filename;
		if ( ! file_exists( $filepath ) ) {
			echo 'Log file not found.';
			exit;
		}

		// Read content and strip PHP guard
		$content = @file_get_contents( $filepath );
		$content = preg_replace( '/^<\?php\s+exit;\s*\?>\s*/', '', $content );

		$download_name = str_replace( '.php', '.log', $filename );

		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename=' . $download_name );
		header( 'Content-Length: ' . strlen( $content ) );

		echo $content;
		exit;
	}

	/**
	 * Delete a run log file (AJAX)
	 */
	public static function delete_run_log() {
		self::verify_request();

		$filename = isset( $_POST['filename'] ) ? sanitize_file_name( $_POST['filename'] ) : '';

		Ai1wm_Debug_Logger::delete_run_log( $filename );

		echo json_encode( array( 'success' => true ) );
		exit;
	}

	/**
	 * Clear all run log files (AJAX)
	 */
	public static function clear_realtime_log() {
		self::verify_request();

		$logs = Ai1wm_Debug_Logger::get_run_logs();
		foreach ( $logs as $log ) {
			Ai1wm_Debug_Logger::delete_run_log( $log['filename'] );
		}

		echo json_encode( array( 'success' => true ) );
		exit;
	}

	/**
	 * Save filter overrides (AJAX)
	 */
	public static function save_filter_overrides() {
		self::verify_request();

		if ( ! current_user_can( 'manage_options' ) ) {
			echo json_encode( array( 'error' => 'Unauthorized' ) );
			exit;
		}

		$presets    = isset( $_POST['presets'] ) ? $_POST['presets'] : array();
		$exclusions = isset( $_POST['exclusions'] ) ? $_POST['exclusions'] : array();
		$custom     = isset( $_POST['custom'] ) ? $_POST['custom'] : array();

		// Sanitize presets
		$valid_presets = Ai1wm_Debug_Filters::get_preset_definitions();
		$saved_presets = array();
		foreach ( $valid_presets as $key => $def ) {
			if ( isset( $presets[ $key ] ) ) {
				$saved_presets[ $key ] = array(
					'enabled' => ! empty( $presets[ $key ]['enabled'] ),
					'value'   => $presets[ $key ]['value'],
					'steps'   => isset( $presets[ $key ]['steps'] ) ? sanitize_text_field( $presets[ $key ]['steps'] ) : '',
				);
			}
		}

		// Sanitize exclusions
		$saved_exclusions = array();
		$valid_exclusion_keys = array( 'content', 'media', 'plugins', 'themes' );
		foreach ( $valid_exclusion_keys as $key ) {
			$saved_exclusions[ $key ] = isset( $exclusions[ $key ] ) ? sanitize_textarea_field( $exclusions[ $key ] ) : '';
		}

		// Sanitize custom filters
		$saved_custom = array();
		if ( is_array( $custom ) ) {
			foreach ( $custom as $item ) {
				if ( empty( $item['filter'] ) ) {
					continue;
				}
				// Only allow ai1wm_ prefixed filters
				$filter_name = sanitize_text_field( $item['filter'] );
				if ( strpos( $filter_name, 'ai1wm' ) !== 0 ) {
					continue;
				}
				// PHP type needs raw code (not sanitize_text_field which strips tags)
				$item_type  = in_array( $item['type'], array( 'int', 'bool', 'string', 'php' ) ) ? $item['type'] : 'string';
				$item_value = $item_type === 'php' ? wp_unslash( $item['value'] ) : sanitize_text_field( $item['value'] );

				$saved_custom[] = array(
					'filter'  => $filter_name,
					'value'   => $item_value,
					'type'    => $item_type,
					'enabled' => ! empty( $item['enabled'] ),
					'steps'   => isset( $item['steps'] ) ? sanitize_text_field( $item['steps'] ) : '',
				);
			}
		}

		Ai1wm_Debug_Config::set( AI1WM_DEBUG_FILTER_OVERRIDES_OPTION, array(
			'presets'    => $saved_presets,
			'exclusions' => $saved_exclusions,
			'custom'     => $saved_custom,
		) );

		echo json_encode( array( 'success' => true ) );
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

		$enabled  = isset( $_POST['enabled'] ) ? intval( $_POST['enabled'] ) : 0;
		$channels = isset( $_POST['channels'] ) ? $_POST['channels'] : array();

		// Sanitize channels
		$valid_channels = array( 'pipeline', 'params', 'status', 'exclusions', 'http', 'errors' );
		$saved_channels = array();
		foreach ( $valid_channels as $channel ) {
			$saved_channels[ $channel ] = ! empty( $channels[ $channel ] );
		}
		$saved_channels['errors'] = true; // Always on

		Ai1wm_Debug_Config::set( AI1WM_DEBUG_LOGGER_ENABLED_OPTION, $enabled );
		Ai1wm_Debug_Config::set( AI1WM_DEBUG_LOGGER_CHANNELS_OPTION, $saved_channels );

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
