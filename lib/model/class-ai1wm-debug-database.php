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

class Ai1wm_Debug_Database {

	/**
	 * Get database overview data
	 *
	 * @return array
	 */
	public static function get_data() {
		global $wpdb;

		return array(
			'version'          => self::get_version(),
			'name'             => $wpdb->dbname,
			'host'             => $wpdb->dbhost,
			'charset'          => $wpdb->charset,
			'collate'          => $wpdb->collate,
			'prefix'           => $wpdb->prefix,
			'total_size'       => self::get_total_size(),
			'autoloaded_size'  => self::get_autoloaded_size(),
		);
	}

	/**
	 * Get database version
	 *
	 * @return string
	 */
	public static function get_version() {
		global $wpdb;
		return $wpdb->get_var( 'SELECT VERSION()' );
	}

	/**
	 * Get total database size
	 *
	 * @return string
	 */
	public static function get_total_size() {
		global $wpdb;

		$result = $wpdb->get_var( $wpdb->prepare(
			"SELECT SUM(data_length + index_length)
			 FROM information_schema.TABLES
			 WHERE table_schema = %s",
			$wpdb->dbname
		) );

		return $result ? ai1wm_debug_size_format( $result, 2 ) : 'Unknown';
	}

	/**
	 * Get autoloaded options size
	 *
	 * @return string
	 */
	public static function get_autoloaded_size() {
		global $wpdb;

		$result = $wpdb->get_var(
			"SELECT SUM(LENGTH(option_value))
			 FROM {$wpdb->options}
			 WHERE autoload = 'yes'"
		);

		return $result ? ai1wm_debug_size_format( $result, 2 ) : 'Unknown';
	}

	/**
	 * Get table listing (called via AJAX for performance)
	 *
	 * @return array
	 */
	public static function get_tables() {
		global $wpdb;

		$prefixed     = array();
		$non_prefixed = array();

		$results = $wpdb->get_results( $wpdb->prepare(
			"SELECT
				table_name AS 'name',
				engine AS 'engine',
				table_rows AS 'rows',
				data_length AS 'data_size',
				index_length AS 'index_size',
				(data_length + index_length) AS 'total_size'
			 FROM information_schema.TABLES
			 WHERE table_schema = %s
			 ORDER BY (data_length + index_length) DESC",
			$wpdb->dbname
		), ARRAY_A );

		if ( $results ) {
			foreach ( $results as $row ) {
				$table = array(
					'name'       => $row['name'],
					'engine'     => $row['engine'],
					'rows'       => intval( $row['rows'] ),
					'data_size'  => ai1wm_debug_size_format( $row['data_size'], 2 ),
					'index_size' => ai1wm_debug_size_format( $row['index_size'], 2 ),
					'total_size' => ai1wm_debug_size_format( $row['total_size'], 2 ),
				);

				if ( strpos( $row['name'], $wpdb->prefix ) === 0 ) {
					$prefixed[] = $table;
				} else {
					$non_prefixed[] = $table;
				}
			}
		}

		return array(
			'prefixed'     => $prefixed,
			'non_prefixed' => $non_prefixed,
		);
	}
}
