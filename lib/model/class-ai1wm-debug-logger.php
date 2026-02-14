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

class Ai1wm_Debug_Logger {

	/**
	 * Initialize hooks for AI1WM events
	 */
	public static function init() {
		if ( ! ai1wm_debug_is_ai1wm_active() ) {
			return;
		}

		if ( ! get_option( AI1WM_DEBUG_LOGGER_ENABLED_OPTION ) ) {
			return;
		}

		// AI1WM export hooks
		add_filter( 'ai1wm_export', array( 'Ai1wm_Debug_Logger', 'log_export' ), 1 );
		add_filter( 'ai1wm_import', array( 'Ai1wm_Debug_Logger', 'log_import' ), 1 );

		// AI1WM status hooks
		add_action( 'ai1wm_status_export', array( 'Ai1wm_Debug_Logger', 'log_status' ), 10, 1 );
		add_action( 'ai1wm_status_import', array( 'Ai1wm_Debug_Logger', 'log_status' ), 10, 1 );

		// Error hooks
		add_action( 'ai1wm_status_error', array( 'Ai1wm_Debug_Logger', 'log_error' ), 10, 1 );
	}

	/**
	 * Log export event
	 *
	 * @param  array $params
	 * @return array
	 */
	public static function log_export( $params ) {
		self::write( 'EXPORT', 'Export operation started', AI1WM_DEBUG_VERBOSITY_INFO );
		if ( isset( $params['options'] ) && is_array( $params['options'] ) ) {
			self::write( 'EXPORT', 'Options: ' . implode( ', ', $params['options'] ), AI1WM_DEBUG_VERBOSITY_DEBUG );
		}
		return $params;
	}

	/**
	 * Log import event
	 *
	 * @param  array $params
	 * @return array
	 */
	public static function log_import( $params ) {
		self::write( 'IMPORT', 'Import operation started', AI1WM_DEBUG_VERBOSITY_INFO );
		if ( isset( $params['storage'] ) ) {
			self::write( 'IMPORT', 'Storage: ' . $params['storage'], AI1WM_DEBUG_VERBOSITY_DEBUG );
		}
		return $params;
	}

	/**
	 * Log status update
	 *
	 * @param  array $params
	 */
	public static function log_status( $params ) {
		$message = 'Status update';
		if ( is_array( $params ) ) {
			if ( isset( $params['message'] ) ) {
				$message = $params['message'];
			} elseif ( isset( $params['type'] ) ) {
				$message = $params['type'];
			}
		} elseif ( is_string( $params ) ) {
			$message = $params;
		}
		self::write( 'STATUS', $message, AI1WM_DEBUG_VERBOSITY_INFO );
	}

	/**
	 * Log error
	 *
	 * @param  array $params
	 */
	public static function log_error( $params ) {
		$message = 'Error occurred';
		if ( is_array( $params ) && isset( $params['message'] ) ) {
			$message = $params['message'];
		} elseif ( is_string( $params ) ) {
			$message = $params;
		}
		self::write( 'ERROR', $message, AI1WM_DEBUG_VERBOSITY_ERROR );
	}

	/**
	 * Write an entry to the debug log
	 *
	 * @param string $category
	 * @param string $message
	 * @param int    $level
	 */
	public static function write( $category, $message, $level = AI1WM_DEBUG_VERBOSITY_INFO ) {
		$verbosity = get_option( AI1WM_DEBUG_LOGGER_VERBOSITY_OPTION, AI1WM_DEBUG_VERBOSITY_INFO );

		if ( $level > $verbosity ) {
			return;
		}

		// Rotate if needed
		self::maybe_rotate();

		$timestamp = date( 'Y-m-d H:i:s' );
		$line      = sprintf( "[%s] [%s] %s\n", $timestamp, $category, $message );

		@file_put_contents( AI1WM_DEBUG_LOG_FILE, $line, FILE_APPEND | LOCK_EX );
	}

	/**
	 * Read new log entries from a file position
	 *
	 * @param  int   $last_pos File byte position
	 * @return array
	 */
	public static function read_new_entries( $last_pos = 0 ) {
		if ( ! file_exists( AI1WM_DEBUG_LOG_FILE ) ) {
			return array(
				'entries'  => array(),
				'last_pos' => 0,
			);
		}

		$file_size = filesize( AI1WM_DEBUG_LOG_FILE );

		// File was rotated, reset position
		if ( $last_pos > $file_size ) {
			$last_pos = 0;
		}

		$entries = array();

		if ( $last_pos < $file_size ) {
			$handle = fopen( AI1WM_DEBUG_LOG_FILE, 'r' );
			if ( $handle ) {
				fseek( $handle, $last_pos );
				$content  = fread( $handle, $file_size - $last_pos );
				$last_pos = ftell( $handle );
				fclose( $handle );

				if ( $content ) {
					$lines = explode( "\n", trim( $content ) );
					foreach ( $lines as $line ) {
						$line = trim( $line );
						if ( ! empty( $line ) ) {
							$entries[] = $line;
						}
					}
				}
			}
		}

		return array(
			'entries'  => $entries,
			'last_pos' => $last_pos,
		);
	}

	/**
	 * Check if the logger is enabled
	 *
	 * @return boolean
	 */
	public static function is_enabled() {
		return (bool) get_option( AI1WM_DEBUG_LOGGER_ENABLED_OPTION );
	}

	/**
	 * Get current verbosity level
	 *
	 * @return int
	 */
	public static function get_verbosity() {
		return (int) get_option( AI1WM_DEBUG_LOGGER_VERBOSITY_OPTION, AI1WM_DEBUG_VERBOSITY_INFO );
	}

	/**
	 * Rotate log file if it exceeds max size
	 */
	private static function maybe_rotate() {
		if ( ! file_exists( AI1WM_DEBUG_LOG_FILE ) ) {
			return;
		}

		if ( filesize( AI1WM_DEBUG_LOG_FILE ) > AI1WM_DEBUG_LOG_MAX_SIZE ) {
			$backup = AI1WM_DEBUG_LOG_FILE . '.1';
			if ( file_exists( $backup ) ) {
				@unlink( $backup );
			}
			@rename( AI1WM_DEBUG_LOG_FILE, $backup );
		}
	}
}
