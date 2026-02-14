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

class Ai1wm_Debug_Logs {

	/**
	 * Discover available log files
	 *
	 * @return array
	 */
	public static function get_log_files() {
		$files = array();

		// WordPress debug.log
		$debug_log = WP_CONTENT_DIR . '/debug.log';
		if ( file_exists( $debug_log ) ) {
			$files[] = array(
				'key'   => 'wp_debug',
				'label' => 'WordPress Debug Log',
				'path'  => $debug_log,
				'size'  => ai1wm_debug_size_format( filesize( $debug_log ), 2 ),
			);
		}

		// PHP error_log in ABSPATH
		$php_error = ABSPATH . 'error_log';
		if ( file_exists( $php_error ) ) {
			$files[] = array(
				'key'   => 'php_error_abspath',
				'label' => 'PHP Error Log (ABSPATH)',
				'path'  => $php_error,
				'size'  => ai1wm_debug_size_format( filesize( $php_error ), 2 ),
			);
		}

		// PHP error_log from ini
		$ini_error_log = ini_get( 'error_log' );
		if ( $ini_error_log && file_exists( $ini_error_log ) && $ini_error_log !== $php_error ) {
			$files[] = array(
				'key'   => 'php_error_ini',
				'label' => 'PHP Error Log (php.ini)',
				'path'  => $ini_error_log,
				'size'  => ai1wm_debug_size_format( filesize( $ini_error_log ), 2 ),
			);
		}

		// AI1WM error log
		if ( defined( 'AI1WM_STORAGE_PATH' ) ) {
			$ai1wm_error = AI1WM_STORAGE_PATH . '/error.log';
			if ( file_exists( $ai1wm_error ) ) {
				$files[] = array(
					'key'   => 'ai1wm_error',
					'label' => 'AI1WM Error Log',
					'path'  => $ai1wm_error,
					'size'  => ai1wm_debug_size_format( filesize( $ai1wm_error ), 2 ),
				);
			}
		}

		// Plugin's run logs
		$run_logs = Ai1wm_Debug_Logger::get_run_logs();
		foreach ( $run_logs as $run_log ) {
			$files[] = array(
				'key'   => 'run_' . $run_log['filename'],
				'label' => ucfirst( $run_log['type'] ) . ' ' . $run_log['date'],
				'path'  => $run_log['path'],
				'size'  => ai1wm_debug_size_format( $run_log['size'], 2 ),
			);
		}

		return $files;
	}

	/**
	 * Read a log file by key
	 *
	 * @param  string $file_key The log file key
	 * @param  int    $offset   Line offset
	 * @param  int    $lines    Number of lines to read
	 * @return array
	 */
	public static function read_log( $file_key, $offset = 0, $lines = 100 ) {
		$files = self::get_log_files();
		$path  = '';

		foreach ( $files as $file ) {
			if ( $file['key'] === $file_key ) {
				$path = $file['path'];
				break;
			}
		}

		if ( empty( $path ) || ! is_readable( $path ) ) {
			return array(
				'content'     => '',
				'total_lines' => 0,
				'offset'      => 0,
			);
		}

		// Read file into lines
		$all_lines   = file( $path, FILE_IGNORE_NEW_LINES );
		$total_lines = count( $all_lines );

		// Clamp offset
		if ( $offset < 0 ) {
			$offset = max( 0, $total_lines + $offset );
		}
		if ( $offset > $total_lines ) {
			$offset = $total_lines;
		}

		$slice   = array_slice( $all_lines, $offset, $lines );
		$content = implode( "\n", $slice );

		return array(
			'content'     => $content,
			'total_lines' => $total_lines,
			'offset'      => $offset,
		);
	}
}
