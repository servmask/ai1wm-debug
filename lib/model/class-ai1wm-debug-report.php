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

class Ai1wm_Debug_Report {

	/**
	 * Generate full report as structured array
	 *
	 * @return array
	 */
	public static function generate() {
		$report = array(
			'generated_at' => date( 'Y-m-d H:i:s T' ),
			'plugin'       => AI1WM_DEBUG_VERSION,
			'environment'  => Ai1wm_Debug_Environment::get_data(),
			'filesystem'   => Ai1wm_Debug_Filesystem::get_data(),
			'database'     => Ai1wm_Debug_Database::get_data(),
			'plugins'      => Ai1wm_Debug_Plugins::get_data(),
		);

		if ( ai1wm_debug_is_ai1wm_active() ) {
			$report['operations'] = Ai1wm_Debug_Operations::get_data();
		}

		return $report;
	}

	/**
	 * Generate report as formatted text
	 *
	 * @return string
	 */
	public static function generate_text() {
		$report = self::generate();
		$output = '';

		$output .= self::section_title( 'ServMask Debug Report' );
		$output .= self::table( array(
			array( 'Generated', $report['generated_at'] ),
			array( 'Plugin Version', $report['plugin'] ),
		) );

		// PHP
		$output .= self::section_title( 'PHP' );
		$output .= self::table( self::status_rows( $report['environment']['php'] ) );

		// WordPress
		$output .= self::section_title( 'WordPress' );
		$output .= self::table( self::status_rows( $report['environment']['wp'] ) );

		// Server
		$output .= self::section_title( 'Server' );
		$output .= self::table( self::status_rows( $report['environment']['server'] ) );

		// Filesystem - Directories
		$output .= self::section_title( 'Filesystem' );
		$fs_rows = array();
		foreach ( $report['filesystem']['directories'] as $dir ) {
			if ( ! $dir['exists'] ) {
				$fs_rows[] = array( $dir['label'], 'MISSING' );
			} else {
				$props = array();
				$props[] = $dir['writable'] ? 'Writable' : 'NOT WRITABLE';
				$props[] = $dir['perms'];
				if ( ! empty( $dir['owner'] ) && $dir['owner'] !== 'N/A' ) {
					$props[] = $dir['owner'] . ':' . $dir['group'];
				}
				$fs_rows[] = array( $dir['label'], implode( '  ', $props ) );
				$fs_rows[] = array( '', $dir['path'] );
			}
		}

		$fs_rows[] = array( '', '' );
		$fs_rows[] = array( 'Disk Free', $report['filesystem']['disk']['free'] );
		$fs_rows[] = array( 'Disk Total', $report['filesystem']['disk']['total'] );
		$fs_rows[] = array( 'Temp Dir', $report['filesystem']['temp']['path'] . ' (' . ( $report['filesystem']['temp']['writable'] ? 'Writable' : 'NOT WRITABLE' ) . ')' );
		$output .= self::table( $fs_rows );

		// Database
		$output .= self::section_title( 'Database' );
		$db = $report['database'];
		$output .= self::table( array(
			array( 'Version', $db['version'] ),
			array( 'Name', $db['name'] ),
			array( 'Host', $db['host'] ),
			array( 'Charset', $db['charset'] ),
			array( 'Table Prefix', $db['prefix'] ),
			array( 'Total Size', $db['total_size'] ),
			array( 'Autoloaded', $db['autoloaded_size'] ),
		) );

		// AI1WM Ecosystem
		$plugins = $report['plugins'];
		$output .= self::section_title( 'AI1WM Ecosystem' );
		$eco_rows = array();
		foreach ( $plugins['ai1wm_ecosystem'] as $ext ) {
			if ( $ext['installed'] ) {
				$version_str = 'v' . $ext['version'];
				if ( ! empty( $ext['latest'] ) ) {
					if ( $ext['up_to_date'] ) {
						$version_str .= ' (latest)';
					} else {
						$version_str .= ' -> v' . $ext['latest'] . ' available';
					}
				}
				$eco_rows[] = array( '[x] ' . $ext['name'], $version_str );
			} else {
				$eco_rows[] = array( '[ ] ' . $ext['name'], '' );
			}
		}
		$output .= self::table( $eco_rows );

		// Known Conflicts
		if ( ! empty( $plugins['known_conflicts'] ) ) {
			$output .= self::section_title( 'Known Conflicts' );
			$conflict_rows = array();
			foreach ( $plugins['known_conflicts'] as $conflict ) {
				$conflict_rows[] = array( '[' . strtoupper( $conflict['severity'] ) . '] ' . $conflict['name'], $conflict['reason'] );
			}
			$output .= self::table( $conflict_rows );
		}

		// Active Plugins
		$output .= self::section_title( 'Active Plugins (' . count( $plugins['active_plugins'] ) . ')' );
		$ap_rows = array();
		foreach ( $plugins['active_plugins'] as $plugin ) {
			$ap_rows[] = array( $plugin['name'], 'v' . $plugin['version'] );
		}
		$output .= self::table( $ap_rows );

		// Inactive Plugins
		$output .= self::section_title( 'Inactive Plugins (' . count( $plugins['inactive_plugins'] ) . ')' );
		$ip_rows = array();
		foreach ( $plugins['inactive_plugins'] as $plugin ) {
			$ip_rows[] = array( $plugin['name'], 'v' . $plugin['version'] );
		}
		$output .= self::table( $ip_rows );

		// Active Theme
		$output .= self::section_title( 'Active Theme' );
		$theme = $plugins['active_theme'];
		$theme_rows = array(
			array( 'Name', $theme['name'] ),
			array( 'Version', $theme['version'] ),
		);
		if ( $theme['is_child'] ) {
			$theme_rows[] = array( 'Parent Theme', $theme['parent'] );
		}
		$output .= self::table( $theme_rows );

		// Inactive Themes
		if ( ! empty( $plugins['inactive_themes'] ) ) {
			$output .= self::section_title( 'Inactive Themes (' . count( $plugins['inactive_themes'] ) . ')' );
			$it_rows = array();
			foreach ( $plugins['inactive_themes'] as $t ) {
				$it_rows[] = array( $t['name'], 'v' . $t['version'] );
			}
			$output .= self::table( $it_rows );
		}

		// Operations (AI1WM-dependent)
		if ( ! empty( $report['operations'] ) ) {
			$ops = $report['operations'];

			$output .= self::section_title( 'Operations' );
			$ops_rows = array();
			$ops_rows[] = array( 'Active Operation', $ops['current']['active'] ? 'Yes (' . $ops['current']['type'] . ')' : 'No' );

			if ( ! empty( $ops['issues'] ) ) {
				foreach ( $ops['issues'] as $issue ) {
					$ops_rows[] = array( '[' . strtoupper( $issue['severity'] ) . '] Issue', $issue['message'] );
				}
			}
			$output .= self::table( $ops_rows );

			if ( ! empty( $ops['backups'] ) ) {
				$output .= self::section_title( 'Backups' );
				$bk_rows = array();
				foreach ( $ops['backups'] as $backup ) {
					$bk_rows[] = array( $backup['name'], $backup['size'] );
				}
				$output .= self::table( $bk_rows );
			}
		}

		return $output;
	}

	/**
	 * Format a section title
	 *
	 * @param  string $title
	 * @return string
	 */
	private static function section_title( $title ) {
		return "\n " . $title . "\n" . str_repeat( '=', 70 ) . "\n";
	}

	/**
	 * Convert status row arrays (label/value/status) to simple two-column rows
	 *
	 * @param  array $items Array of arrays with 'label', 'value', and optional 'status' keys
	 * @return array
	 */
	private static function status_rows( $items ) {
		$rows = array();
		foreach ( $items as $item ) {
			$value = $item['value'];
			if ( isset( $item['status'] ) ) {
				$value .= $item['status'] ? '  [OK]' : '  [!!]';
			}
			$rows[] = array( $item['label'], $value );
		}
		return $rows;
	}

	/**
	 * Render an ASCII table with borders
	 *
	 * @param  array $rows Array of arrays, each with two elements (label, value)
	 * @return string
	 */
	private static function table( $rows ) {
		if ( empty( $rows ) ) {
			return '';
		}

		// Calculate max column widths
		$max_left  = 0;
		$max_right = 0;
		foreach ( $rows as $row ) {
			$max_left  = max( $max_left, strlen( $row[0] ) );
			$max_right = max( $max_right, strlen( $row[1] ) );
		}

		$divider = sprintf( "+-%s-+-%s-+\n", str_pad( '', $max_left, '-' ), str_pad( '', $max_right, '-' ) );
		$output  = $divider;

		foreach ( $rows as $row ) {
			$output .= sprintf( "| %s | %s |\n", str_pad( $row[0], $max_left ), str_pad( $row[1], $max_right ) );
			$output .= $divider;
		}

		return $output;
	}
}
