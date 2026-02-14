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

		$output .= self::section_header( 'ServMask Debug Report' );
		$output .= "Generated: " . $report['generated_at'] . "\n";
		$output .= "Plugin Version: " . $report['plugin'] . "\n";
		$output .= "\n";

		// Environment
		$output .= self::section_header( 'PHP Information' );
		$output .= self::format_rows( $report['environment']['php'] );

		$output .= self::section_header( 'WordPress Information' );
		$output .= self::format_rows( $report['environment']['wp'] );

		$output .= self::section_header( 'Server Information' );
		$output .= self::format_rows( $report['environment']['server'] );

		// Filesystem
		$output .= self::section_header( 'Filesystem' );
		foreach ( $report['filesystem']['directories'] as $dir ) {
			$output .= sprintf(
				"%-20s %-50s Exists: %-3s Writable: %-3s Perms: %s\n",
				$dir['label'],
				$dir['path'],
				$dir['exists'] ? 'Yes' : 'No',
				$dir['writable'] ? 'Yes' : 'No',
				$dir['perms']
			);
		}
		$output .= "\n";
		$output .= "Disk Free:  " . $report['filesystem']['disk']['free'] . "\n";
		$output .= "Disk Total: " . $report['filesystem']['disk']['total'] . "\n";
		$output .= "Temp Dir:   " . $report['filesystem']['temp']['path'] . " (Writable: " . ( $report['filesystem']['temp']['writable'] ? 'Yes' : 'No' ) . ")\n";

		// Database
		$output .= self::section_header( 'Database' );
		$db = $report['database'];
		$output .= "Version:    " . $db['version'] . "\n";
		$output .= "Name:       " . $db['name'] . "\n";
		$output .= "Host:       " . $db['host'] . "\n";
		$output .= "Charset:    " . $db['charset'] . "\n";
		$output .= "Prefix:     " . $db['prefix'] . "\n";
		$output .= "Total Size: " . $db['total_size'] . "\n";
		$output .= "Autoloaded: " . $db['autoloaded_size'] . "\n";

		// Plugins
		$plugins = $report['plugins'];

		$output .= self::section_header( 'AI1WM Ecosystem' );
		foreach ( $plugins['ai1wm_ecosystem'] as $ext ) {
			$version_info = $ext['version'];
			if ( $ext['installed'] && ! empty( $ext['latest'] ) ) {
				$version_info = sprintf( 'Installed: %s (Latest: %s)%s', $ext['version'], $ext['latest'], $ext['up_to_date'] ? '' : ' [OUTDATED]' );
			}
			$output .= sprintf(
				"%-40s %-15s %s\n",
				$ext['name'],
				$ext['installed'] ? 'Installed' : 'Not Installed',
				$version_info
			);
		}

		if ( ! empty( $plugins['known_conflicts'] ) ) {
			$output .= self::section_header( 'Known Conflicts' );
			foreach ( $plugins['known_conflicts'] as $conflict ) {
				$output .= "- " . $conflict['name'] . ": " . $conflict['reason'] . "\n";
			}
		}

		$output .= self::section_header( 'Active Plugins' );
		foreach ( $plugins['active_plugins'] as $plugin ) {
			$output .= sprintf( "- %s (v%s)\n", $plugin['name'], $plugin['version'] );
		}

		$output .= self::section_header( 'Inactive Plugins' );
		foreach ( $plugins['inactive_plugins'] as $plugin ) {
			$output .= sprintf( "- %s (v%s)\n", $plugin['name'], $plugin['version'] );
		}

		$output .= self::section_header( 'Active Theme' );
		$theme = $plugins['active_theme'];
		$output .= "Name:    " . $theme['name'] . "\n";
		$output .= "Version: " . $theme['version'] . "\n";
		$output .= "Child:   " . ( $theme['is_child'] ? 'Yes (Parent: ' . $theme['parent'] . ')' : 'No' ) . "\n";

		$output .= self::section_header( 'Inactive Themes' );
		foreach ( $plugins['inactive_themes'] as $t ) {
			$output .= sprintf( "- %s (v%s)\n", $t['name'], $t['version'] );
		}

		return $output;
	}

	/**
	 * Format a section header
	 *
	 * @param  string $title
	 * @return string
	 */
	private static function section_header( $title ) {
		$line = str_repeat( '=', 70 );
		return "\n" . $line . "\n " . $title . "\n" . $line . "\n";
	}

	/**
	 * Format rows of label/value/status arrays
	 *
	 * @param  array  $rows
	 * @return string
	 */
	private static function format_rows( $rows ) {
		$output = '';
		foreach ( $rows as $row ) {
			$status = isset( $row['status'] ) ? ( $row['status'] ? '[OK]' : '[!!]' ) : '';
			$output .= sprintf( "%-30s %-35s %s\n", $row['label'], $row['value'], $status );
		}
		return $output;
	}
}
