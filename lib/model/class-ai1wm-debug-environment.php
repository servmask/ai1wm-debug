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

class Ai1wm_Debug_Environment {

	/**
	 * Get all environment data
	 *
	 * @return array
	 */
	public static function get_data() {
		return array(
			'php'    => self::get_php_info(),
			'wp'     => self::get_wp_info(),
			'server' => self::get_server_info(),
		);
	}

	/**
	 * Get PHP environment info
	 *
	 * @return array
	 */
	public static function get_php_info() {
		$memory_limit       = ini_get( 'memory_limit' );
		$memory_limit_bytes = ai1wm_debug_to_bytes( $memory_limit );
		$max_execution_time = ini_get( 'max_execution_time' );

		$data = array(
			array(
				'label'  => 'PHP Version',
				'value'  => phpversion(),
				'status' => version_compare( phpversion(), '5.3', '>=' ),
			),
			array(
				'label'  => 'PHP SAPI',
				'value'  => php_sapi_name(),
				'status' => true,
			),
			array(
				'label'  => 'Memory Limit',
				'value'  => $memory_limit,
				'status' => $memory_limit_bytes >= 256 * 1048576,
			),
			array(
				'label'  => 'Max Execution Time',
				'value'  => $max_execution_time . 's',
				'status' => $max_execution_time >= 30 || $max_execution_time == 0,
			),
			array(
				'label'  => 'Max Input Vars',
				'value'  => ini_get( 'max_input_vars' ),
				'status' => true,
			),
			array(
				'label'  => 'Post Max Size',
				'value'  => ini_get( 'post_max_size' ),
				'status' => true,
			),
			array(
				'label'  => 'Upload Max Filesize',
				'value'  => ini_get( 'upload_max_filesize' ),
				'status' => true,
			),
			array(
				'label'  => '64-bit Support',
				'value'  => ai1wm_debug_bool_label( PHP_INT_SIZE > 4 ),
				'status' => PHP_INT_SIZE > 4,
			),
		);

		// PHP extensions
		$extensions = array(
			'curl', 'openssl', 'ftp', 'bcmath', 'libxml',
			'simplexml', 'xml', 'mbstring', 'json', 'zip', 'zlib',
		);

		foreach ( $extensions as $ext ) {
			$loaded = extension_loaded( $ext );
			$data[] = array(
				'label'  => $ext . ' Extension',
				'value'  => ai1wm_debug_bool_label( $loaded ),
				'status' => $loaded,
			);
		}

		// cURL version
		if ( extension_loaded( 'curl' ) && function_exists( 'curl_version' ) ) {
			$curl_version = curl_version();
			if ( $curl_version ) {
				$data[] = array(
					'label'  => 'cURL Version',
					'value'  => $curl_version['version'],
					'status' => true,
				);
				$data[] = array(
					'label'  => 'OpenSSL Version (cURL)',
					'value'  => isset( $curl_version['ssl_version'] ) ? $curl_version['ssl_version'] : 'N/A',
					'status' => true,
				);
			}
		}

		// MySQL connect timeout
		$mysql_timeout = ini_get( 'mysql.connect_timeout' );
		$data[] = array(
			'label'  => 'MySQL Connect Timeout',
			'value'  => $mysql_timeout ? $mysql_timeout : '0',
			'status' => true,
		);

		return $data;
	}

	/**
	 * Get WordPress environment info
	 *
	 * @return array
	 */
	public static function get_wp_info() {
		$data = array(
			array(
				'label'  => 'WordPress Version',
				'value'  => get_bloginfo( 'version' ),
				'status' => true,
			),
			array(
				'label'  => 'Site URL',
				'value'  => get_site_url(),
				'status' => true,
			),
			array(
				'label'  => 'Home URL',
				'value'  => get_home_url(),
				'status' => true,
			),
			array(
				'label'  => 'Multisite',
				'value'  => ai1wm_debug_bool_label( is_multisite() ),
				'status' => true,
			),
			array(
				'label'  => 'WP_DEBUG',
				'value'  => ai1wm_debug_bool_label( defined( 'WP_DEBUG' ) && WP_DEBUG ),
				'status' => true,
			),
			array(
				'label'  => 'WP_DEBUG_LOG',
				'value'  => ai1wm_debug_bool_label( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ),
				'status' => true,
			),
			array(
				'label'  => 'WP_DEBUG_DISPLAY',
				'value'  => ai1wm_debug_bool_label( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ),
				'status' => true,
			),
			array(
				'label'  => 'SCRIPT_DEBUG',
				'value'  => ai1wm_debug_bool_label( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ),
				'status' => true,
			),
			array(
				'label'  => 'WP_CACHE',
				'value'  => ai1wm_debug_bool_label( defined( 'WP_CACHE' ) && WP_CACHE ),
				'status' => true,
			),
			array(
				'label'  => 'WP Memory Limit',
				'value'  => defined( 'WP_MEMORY_LIMIT' ) ? WP_MEMORY_LIMIT : 'Not set',
				'status' => true,
			),
			array(
				'label'  => 'WP Max Memory Limit',
				'value'  => defined( 'WP_MAX_MEMORY_LIMIT' ) ? WP_MAX_MEMORY_LIMIT : 'Not set',
				'status' => true,
			),
			array(
				'label'  => 'DB Charset',
				'value'  => defined( 'DB_CHARSET' ) ? DB_CHARSET : 'Not set',
				'status' => true,
			),
			array(
				'label'  => 'DB Collate',
				'value'  => defined( 'DB_COLLATE' ) ? DB_COLLATE : 'Not set',
				'status' => true,
			),
			array(
				'label'  => 'Table Prefix',
				'value'  => self::get_table_prefix(),
				'status' => true,
			),
		);

		// Cron
		$data[] = array(
			'label'  => 'DISABLE_WP_CRON',
			'value'  => ai1wm_debug_bool_label( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ),
			'status' => true,
		);
		$data[] = array(
			'label'  => 'ALTERNATE_WP_CRON',
			'value'  => ai1wm_debug_bool_label( defined( 'ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON ),
			'status' => true,
		);

		// URL rewrite
		$data[] = array(
			'label'  => 'Permalink Structure',
			'value'  => get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default (Plain)',
			'status' => true,
		);

		return $data;
	}

	/**
	 * Get server environment info
	 *
	 * @return array
	 */
	public static function get_server_info() {
		$data = array(
			array(
				'label'  => 'Operating System',
				'value'  => PHP_OS,
				'status' => true,
			),
			array(
				'label'  => 'Architecture',
				'value'  => php_uname( 'm' ),
				'status' => true,
			),
			array(
				'label'  => 'Server Software',
				'value'  => isset( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown',
				'status' => true,
			),
			array(
				'label'  => 'Server Name',
				'value'  => isset( $_SERVER['SERVER_NAME'] ) ? $_SERVER['SERVER_NAME'] : 'Unknown',
				'status' => true,
			),
		);

		// POSIX info
		if ( function_exists( 'posix_getpwuid' ) && function_exists( 'posix_geteuid' ) ) {
			$user_info = posix_getpwuid( posix_geteuid() );
			$data[] = array(
				'label'  => 'Process User',
				'value'  => $user_info ? $user_info['name'] : 'Unknown',
				'status' => true,
			);
		}
		if ( function_exists( 'posix_getgrgid' ) && function_exists( 'posix_getegid' ) ) {
			$group_info = posix_getgrgid( posix_getegid() );
			$data[] = array(
				'label'  => 'Process Group',
				'value'  => $group_info ? $group_info['name'] : 'Unknown',
				'status' => true,
			);
		}

		return $data;
	}

	/**
	 * Get the table prefix
	 *
	 * @return string
	 */
	private static function get_table_prefix() {
		global $wpdb;
		return $wpdb->prefix;
	}
}
