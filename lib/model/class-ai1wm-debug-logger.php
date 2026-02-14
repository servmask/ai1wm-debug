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

	const PHP_GUARD = "<?php exit; ?>\n";

	/**
	 * Known export pipeline stages: priority => stage name
	 *
	 * @var array
	 */
	private static $export_stages = array(
		5   => 'Init',
		10  => 'Compatibility',
		30  => 'Archive',
		50  => 'Config',
		60  => 'Config File',
		100 => 'Enumerate Content',
		110 => 'Enumerate Media',
		120 => 'Enumerate Plugins',
		130 => 'Enumerate Themes',
		140 => 'Enumerate Tables',
		150 => 'Export Content',
		160 => 'Export Media',
		170 => 'Export Plugins',
		180 => 'Export Themes',
		200 => 'Export Database',
		220 => 'Export Database File',
		250 => 'Download',
		300 => 'Clean',
	);

	/**
	 * Known import pipeline stages: priority => stage name
	 *
	 * @var array
	 */
	private static $import_stages = array(
		5   => 'Upload',
		10  => 'Compatibility',
		50  => 'Validate',
		70  => 'Check Compression',
		75  => 'Check Encryption',
		100 => 'Confirm',
		150 => 'Blogs',
		170 => 'Permalinks',
		200 => 'Enumerate',
		250 => 'Import Content',
		270 => 'Import MU Plugins',
		295 => 'Import Database File',
		300 => 'Import Database',
		310 => 'Import Users',
		330 => 'Import Options',
		350 => 'Done',
		400 => 'Clean',
	);

	/**
	 * Default channels (all enabled)
	 *
	 * @var array
	 */
	private static $default_channels = array(
		'pipeline'   => true,
		'params'     => false,
		'status'     => true,
		'exclusions' => true,
		'http'       => true,
		'errors'     => true,
	);

	/**
	 * Last logged status message (to avoid duplicates)
	 *
	 * @var string
	 */
	private static $last_status_message = '';

	/**
	 * Last logged pipeline priority (to log stage entry once)
	 *
	 * @var int
	 */
	private static $last_logged_priority = -1;

	/**
	 * Cached current log file path
	 *
	 * @var string|null
	 */
	private static $current_log_file = null;

	/**
	 * Get the current pipeline priority from AI1WM's global params
	 *
	 * @return int
	 */
	public static function get_current_priority() {
		if ( isset( $GLOBALS['ai1wm_params']['priority'] ) ) {
			return intval( $GLOBALS['ai1wm_params']['priority'] );
		}
		return 0;
	}

	/**
	 * Resolve a priority number to a human-readable stage name
	 *
	 * @param  int $priority
	 * @return string
	 */
	public static function get_stage_name( $priority ) {
		if ( isset( self::$export_stages[ $priority ] ) ) {
			return self::$export_stages[ $priority ];
		}
		if ( isset( self::$import_stages[ $priority ] ) ) {
			return self::$import_stages[ $priority ];
		}
		return 'Unknown';
	}

	// -------------------------------------------------------------------------
	// Per-run log file management
	// -------------------------------------------------------------------------

	/**
	 * Get the path to the current-run tracker file
	 *
	 * @return string
	 */
	private static function tracker_path() {
		return AI1WM_DEBUG_LOGS_PATH . DIRECTORY_SEPARATOR . 'current-run';
	}

	/**
	 * Start a new log file for this run
	 *
	 * @param  string $type 'export' or 'import'
	 * @return string Log file path
	 */
	public static function start_new_run( $type ) {
		self::setup_logs_dir();

		$filename = $type . '-' . date( 'Ymd-His' ) . '.php';
		$filepath = AI1WM_DEBUG_LOGS_PATH . DIRECTORY_SEPARATOR . $filename;

		// Write the PHP guard as first line
		@file_put_contents( $filepath, self::PHP_GUARD, LOCK_EX );

		// Store as current run
		@file_put_contents( self::tracker_path(), $filename, LOCK_EX );
		self::$current_log_file = $filepath;

		return $filepath;
	}

	/**
	 * Get the current run's log file path
	 *
	 * @return string|null
	 */
	public static function get_current_log_file() {
		if ( self::$current_log_file !== null ) {
			return self::$current_log_file;
		}

		$tracker = self::tracker_path();
		if ( file_exists( $tracker ) ) {
			$filename = trim( @file_get_contents( $tracker ) );
			if ( $filename ) {
				$filepath = AI1WM_DEBUG_LOGS_PATH . DIRECTORY_SEPARATOR . $filename;
				if ( file_exists( $filepath ) ) {
					self::$current_log_file = $filepath;
					return $filepath;
				}
			}
		}

		return null;
	}

	/**
	 * List all run log files (newest first)
	 *
	 * @return array
	 */
	public static function get_run_logs() {
		$logs = array();

		if ( ! is_dir( AI1WM_DEBUG_LOGS_PATH ) ) {
			return $logs;
		}

		$files = scandir( AI1WM_DEBUG_LOGS_PATH );
		if ( ! $files ) {
			return $logs;
		}

		$current = self::get_current_log_file();

		foreach ( $files as $file ) {
			if ( ! preg_match( '/^(export|import)-(\d{8}-\d{6})\.php$/', $file, $matches ) ) {
				continue;
			}

			$filepath = AI1WM_DEBUG_LOGS_PATH . DIRECTORY_SEPARATOR . $file;
			$logs[]   = array(
				'filename' => $file,
				'path'     => $filepath,
				'type'     => $matches[1],
				'date'     => substr( $matches[2], 0, 4 ) . '-' . substr( $matches[2], 4, 2 ) . '-' . substr( $matches[2], 6, 2 )
					. ' ' . substr( $matches[2], 9, 2 ) . ':' . substr( $matches[2], 11, 2 ) . ':' . substr( $matches[2], 13, 2 ),
				'size'     => filesize( $filepath ),
				'current'  => ( $current === $filepath ),
			);
		}

		// Newest first
		usort( $logs, array( 'Ai1wm_Debug_Logger', 'sort_logs_desc' ) );

		return $logs;
	}

	/**
	 * Sort callback for run logs (newest first)
	 *
	 * @param  array $a
	 * @param  array $b
	 * @return int
	 */
	public static function sort_logs_desc( $a, $b ) {
		return strcmp( $b['filename'], $a['filename'] );
	}

	/**
	 * Delete a run log file
	 *
	 * @param string $filename
	 */
	public static function delete_run_log( $filename ) {
		// Only allow deleting run log files
		if ( ! preg_match( '/^(export|import)-\d{8}-\d{6}\.php$/', $filename ) ) {
			return;
		}

		$filepath = AI1WM_DEBUG_LOGS_PATH . DIRECTORY_SEPARATOR . $filename;
		if ( file_exists( $filepath ) ) {
			@unlink( $filepath );
		}

		// If this was the current run, clear the tracker
		$tracker = self::tracker_path();
		if ( file_exists( $tracker ) ) {
			$current = trim( @file_get_contents( $tracker ) );
			if ( $current === $filename ) {
				@unlink( $tracker );
				self::$current_log_file = null;
			}
		}
	}

	/**
	 * Create logs directory if needed
	 */
	private static function setup_logs_dir() {
		if ( ! is_dir( AI1WM_DEBUG_LOGS_PATH ) ) {
			@mkdir( AI1WM_DEBUG_LOGS_PATH, 0755, true );
		}
	}

	// -------------------------------------------------------------------------
	// Initialization
	// -------------------------------------------------------------------------

	/**
	 * Initialize hooks for AI1WM events
	 */
	public static function init() {
		if ( ! ai1wm_debug_is_ai1wm_active() ) {
			return;
		}

		if ( ! Ai1wm_Debug_Config::get( AI1WM_DEBUG_LOGGER_ENABLED_OPTION ) ) {
			return;
		}

		$channels = self::get_channels();

		// Lifecycle hooks (errors are always logged)
		add_action( 'ai1wm_status_export_init', array( 'Ai1wm_Debug_Logger', 'log_export_init' ), 10, 1 );
		add_action( 'ai1wm_status_export_start', array( 'Ai1wm_Debug_Logger', 'log_lifecycle' ), 10, 1 );
		add_action( 'ai1wm_status_export_done', array( 'Ai1wm_Debug_Logger', 'log_lifecycle' ), 10, 1 );
		add_action( 'ai1wm_status_export_canceled', array( 'Ai1wm_Debug_Logger', 'log_lifecycle' ), 10, 1 );
		add_action( 'ai1wm_status_export_error', array( 'Ai1wm_Debug_Logger', 'log_error' ), 10, 2 );

		add_action( 'ai1wm_status_import_start', array( 'Ai1wm_Debug_Logger', 'log_import_start' ), 10, 1 );
		add_action( 'ai1wm_status_import_done', array( 'Ai1wm_Debug_Logger', 'log_lifecycle' ), 10, 1 );
		add_action( 'ai1wm_status_import_canceled', array( 'Ai1wm_Debug_Logger', 'log_lifecycle' ), 10, 1 );
		add_action( 'ai1wm_status_import_error', array( 'Ai1wm_Debug_Logger', 'log_error' ), 10, 2 );
		add_action( 'ai1wm_status_upload_error', array( 'Ai1wm_Debug_Logger', 'log_error' ), 10, 2 );

		// Status message monitoring (also logs pipeline stage entry on first status per stage)
		if ( $channels['status'] || $channels['pipeline'] ) {
			add_filter( 'pre_update_option_ai1wm_status', array( 'Ai1wm_Debug_Logger', 'log_status_change' ), 10, 2 );
		}

		// File exclusion logging
		if ( $channels['exclusions'] ) {
			add_filter( 'ai1wm_exclude_content_from_export', array( 'Ai1wm_Debug_Logger', 'log_exclusion_content' ), 999, 1 );
			add_filter( 'ai1wm_exclude_media_from_export', array( 'Ai1wm_Debug_Logger', 'log_exclusion_media' ), 999, 1 );
			add_filter( 'ai1wm_exclude_plugins_from_export', array( 'Ai1wm_Debug_Logger', 'log_exclusion_plugins' ), 999, 1 );
			add_filter( 'ai1wm_exclude_themes_from_export', array( 'Ai1wm_Debug_Logger', 'log_exclusion_themes' ), 999, 1 );
		}

		// HTTP loopback request logging (WP_CLI / cron)
		if ( $channels['http'] ) {
			add_filter( 'ai1wm_http_export_url', array( 'Ai1wm_Debug_Logger', 'log_http_export_url' ), 999, 1 );
			add_filter( 'ai1wm_http_export_timeout', array( 'Ai1wm_Debug_Logger', 'log_http_export_timeout' ), 999, 1 );
			add_filter( 'ai1wm_http_import_url', array( 'Ai1wm_Debug_Logger', 'log_http_import_url' ), 999, 1 );
			add_filter( 'ai1wm_http_import_timeout', array( 'Ai1wm_Debug_Logger', 'log_http_import_timeout' ), 999, 1 );
		}
	}

	// -------------------------------------------------------------------------
	// Lifecycle hooks
	// -------------------------------------------------------------------------

	/**
	 * Start a new export run and log it
	 *
	 * @param array $params
	 */
	public static function log_export_init( $params ) {
		self::start_new_run( 'export' );
		self::write( 'LIFECYCLE', 'EXPORT INIT (' . self::get_environment() . ')' );
	}

	/**
	 * Start a new import run and log it
	 *
	 * @param array $params
	 */
	public static function log_import_start( $params ) {
		self::start_new_run( 'import' );
		self::write( 'LIFECYCLE', 'IMPORT START (' . self::get_environment() . ')' );
	}

	/**
	 * Log lifecycle event from do_action hook name
	 *
	 * @param  array $params
	 */
	public static function log_lifecycle( $params ) {
		$action = current_filter();
		$label  = str_replace( 'ai1wm_status_', '', $action );
		$label  = strtoupper( str_replace( '_', ' ', $label ) );
		self::write( 'LIFECYCLE', $label . ' (' . self::get_environment() . ')' );
	}

	/**
	 * Detect the execution environment
	 *
	 * @return string
	 */
	public static function get_environment() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return 'WP-CLI';
		}

		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return 'Scheduled';
		}

		return 'HTTP';
	}

	/**
	 * Log error from export or import
	 *
	 * @param  array     $params
	 * @param  Exception $exception
	 */
	public static function log_error( $params, $exception = null ) {
		$action  = current_filter();
		$context = strpos( $action, 'export' ) !== false ? 'EXPORT' : 'IMPORT';
		$message = 'Error occurred';

		if ( $exception && method_exists( $exception, 'getMessage' ) ) {
			$message = $exception->getMessage();
			if ( method_exists( $exception, 'getCode' ) && $exception->getCode() ) {
				$message .= ' (code: ' . $exception->getCode() . ')';
			}
		}

		self::write( 'ERROR', $context . ': ' . $message );

		// Always log params on error
		if ( is_array( $params ) ) {
			self::write( 'ERROR', 'Params at error: ' . print_r( $params, true ) );
		}
	}

	// -------------------------------------------------------------------------
	// Status message monitoring
	// -------------------------------------------------------------------------

	/**
	 * Intercept ai1wm_status option writes
	 *
	 * @param  mixed $new_value
	 * @param  mixed $old_value
	 * @return mixed
	 */
	public static function log_status_change( $new_value, $old_value ) {
		if ( is_array( $new_value ) ) {
			$type    = isset( $new_value['type'] ) ? $new_value['type'] : '';
			$message = isset( $new_value['message'] ) ? $new_value['message'] : '';
			$title   = isset( $new_value['title'] ) ? $new_value['title'] : '';

			// Skip progress percentage messages entirely
			if ( preg_match( '/\d+%\s*complete/', $message ) ) {
				return $new_value;
			}

			// Deduplicate repeated messages
			$dedup_key = $type . '|' . $message;
			if ( $dedup_key === self::$last_status_message ) {
				return $new_value;
			}
			self::$last_status_message = $dedup_key;

			$priority   = self::get_current_priority();
			$stage_name = self::get_stage_name( $priority );
			$channels   = self::get_channels();

			// Log stage entry once per priority (pipeline channel)
			if ( $channels['pipeline'] && $priority > 0 && $priority !== self::$last_logged_priority ) {
				self::$last_logged_priority = $priority;
				self::write( 'STAGE', $stage_name . ' [priority ' . $priority . ']' );
			}

			// Log status message
			if ( $channels['status'] ) {
				$log_msg = '[' . $stage_name . ' ' . $priority . '] [' . $type . ']';
				if ( $title ) {
					$log_msg .= ' ' . $title;
				}
				if ( $message ) {
					$log_msg .= ': ' . wp_strip_all_tags( $message );
				}

				self::write( 'STATUS', $log_msg );
			}
		}

		return $new_value;
	}

	// -------------------------------------------------------------------------
	// Exclusion logging
	// -------------------------------------------------------------------------

	public static function log_exclusion_content( $exclude ) {
		if ( ! empty( $exclude ) ) {
			self::write( 'EXCLUDE', 'Content exclusions: ' . print_r( $exclude, true ) );
		}
		return $exclude;
	}

	public static function log_exclusion_media( $exclude ) {
		if ( ! empty( $exclude ) ) {
			self::write( 'EXCLUDE', 'Media exclusions: ' . print_r( $exclude, true ) );
		}
		return $exclude;
	}

	public static function log_exclusion_plugins( $exclude ) {
		if ( ! empty( $exclude ) ) {
			self::write( 'EXCLUDE', 'Plugin exclusions: ' . print_r( $exclude, true ) );
		}
		return $exclude;
	}

	public static function log_exclusion_themes( $exclude ) {
		if ( ! empty( $exclude ) ) {
			self::write( 'EXCLUDE', 'Theme exclusions: ' . print_r( $exclude, true ) );
		}
		return $exclude;
	}

	// -------------------------------------------------------------------------
	// HTTP loopback logging (WP_CLI / cron)
	// -------------------------------------------------------------------------

	public static function log_http_export_url( $url ) {
		self::write( 'HTTP', 'Export loopback URL: ' . $url );
		return $url;
	}

	public static function log_http_export_timeout( $timeout ) {
		self::write( 'HTTP', 'Export loopback timeout: ' . $timeout . 's' );
		return $timeout;
	}

	public static function log_http_import_url( $url ) {
		self::write( 'HTTP', 'Import loopback URL: ' . $url );
		return $url;
	}

	public static function log_http_import_timeout( $timeout ) {
		self::write( 'HTTP', 'Import loopback timeout: ' . $timeout . 's' );
		return $timeout;
	}

	// -------------------------------------------------------------------------
	// Core write / read / utility
	// -------------------------------------------------------------------------

	/**
	 * Write an entry to the current run's log file
	 *
	 * @param string $category
	 * @param string $message
	 */
	public static function write( $category, $message ) {
		$log_file = self::get_current_log_file();
		if ( ! $log_file ) {
			return;
		}

		$timestamp = date( 'Y-m-d H:i:s' );
		$line      = sprintf( "[%s] [%s] %s\n", $timestamp, $category, $message );

		@file_put_contents( $log_file, $line, FILE_APPEND | LOCK_EX );
	}

	/**
	 * Read new log entries from a file position
	 *
	 * @param  int    $last_pos File byte position
	 * @param  string $filename Specific log file (null = current run)
	 * @return array
	 */
	public static function read_new_entries( $last_pos = 0, $filename = null ) {
		if ( $filename ) {
			$log_file = AI1WM_DEBUG_LOGS_PATH . DIRECTORY_SEPARATOR . $filename;
		} else {
			$log_file = self::get_current_log_file();
		}

		if ( ! $log_file || ! file_exists( $log_file ) ) {
			return array(
				'entries'  => array(),
				'last_pos' => 0,
			);
		}

		$file_size = filesize( $log_file );

		// File changed (new run), reset position
		if ( $last_pos > $file_size ) {
			$last_pos = 0;
		}

		$entries = array();

		if ( $last_pos < $file_size ) {
			$handle = fopen( $log_file, 'r' );
			if ( $handle ) {
				fseek( $handle, $last_pos );
				$content  = fread( $handle, $file_size - $last_pos );
				$last_pos = ftell( $handle );
				fclose( $handle );

				if ( $content ) {
					// Strip PHP guard if reading from start
					if ( $last_pos <= strlen( self::PHP_GUARD ) + 1 ) {
						$content = preg_replace( '/^<\?php\s+exit;\s*\?>\s*/', '', $content );
					}

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
		return (bool) Ai1wm_Debug_Config::get( AI1WM_DEBUG_LOGGER_ENABLED_OPTION );
	}

	/**
	 * Get current verbosity level
	 *
	 * @return int
	 */
	public static function get_verbosity() {
		return (int) Ai1wm_Debug_Config::get( AI1WM_DEBUG_LOGGER_VERBOSITY_OPTION, AI1WM_DEBUG_VERBOSITY_INFO );
	}

	/**
	 * Get logging channels configuration
	 *
	 * @return array
	 */
	public static function get_channels() {
		$saved = Ai1wm_Debug_Config::get( AI1WM_DEBUG_LOGGER_CHANNELS_OPTION, array() );
		return array_merge( self::$default_channels, $saved );
	}
}
