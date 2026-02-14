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

class Ai1wm_Debug_Filesystem {

	/**
	 * Get all filesystem data
	 *
	 * @return array
	 */
	public static function get_data() {
		return array(
			'directories' => self::get_directories(),
			'disk'        => self::get_disk_space(),
			'temp'        => self::get_temp_dir(),
		);
	}

	/**
	 * Get key directory info
	 *
	 * @return array
	 */
	public static function get_directories() {
		$upload_dir = wp_upload_dir();

		$dirs = array(
			array(
				'label' => 'WordPress Root (ABSPATH)',
				'path'  => ABSPATH,
			),
			array(
				'label' => 'wp-content',
				'path'  => WP_CONTENT_DIR,
			),
			array(
				'label' => 'Plugins',
				'path'  => WP_PLUGIN_DIR,
			),
			array(
				'label' => 'Themes',
				'path'  => get_theme_root(),
			),
			array(
				'label' => 'Uploads',
				'path'  => $upload_dir['basedir'],
			),
		);

		// AI1WM paths
		if ( defined( 'AI1WM_STORAGE_PATH' ) ) {
			$dirs[] = array(
				'label' => 'AI1WM Storage',
				'path'  => AI1WM_STORAGE_PATH,
			);
		}
		if ( defined( 'AI1WM_BACKUPS_PATH' ) ) {
			$dirs[] = array(
				'label' => 'AI1WM Backups',
				'path'  => AI1WM_BACKUPS_PATH,
			);
		}

		$result = array();
		foreach ( $dirs as $dir ) {
			$path   = $dir['path'];
			$exists = is_dir( $path );

			$entry = array(
				'label'    => $dir['label'],
				'path'     => ai1wm_debug_normalize_path( $path ),
				'exists'   => $exists,
				'writable' => $exists ? is_writable( $path ) : false,
				'perms'    => $exists ? substr( sprintf( '%o', fileperms( $path ) ), -4 ) : 'N/A',
			);

			// Ownership (POSIX only)
			if ( $exists && function_exists( 'posix_getpwuid' ) ) {
				$owner = posix_getpwuid( fileowner( $path ) );
				$group = posix_getgrgid( filegroup( $path ) );
				$entry['owner'] = $owner ? $owner['name'] : fileowner( $path );
				$entry['group'] = $group ? $group['name'] : filegroup( $path );
			} else {
				$entry['owner'] = 'N/A';
				$entry['group'] = 'N/A';
			}

			$result[] = $entry;
		}

		return $result;
	}

	/**
	 * Get disk space info
	 *
	 * @return array
	 */
	public static function get_disk_space() {
		$path = ABSPATH;

		$free  = function_exists( 'disk_free_space' ) ? @disk_free_space( $path ) : false;
		$total = function_exists( 'disk_total_space' ) ? @disk_total_space( $path ) : false;

		return array(
			'free'       => $free !== false ? ai1wm_debug_size_format( $free, 2 ) : 'Unavailable',
			'total'      => $total !== false ? ai1wm_debug_size_format( $total, 2 ) : 'Unavailable',
			'free_bytes'  => $free !== false ? $free : 0,
			'total_bytes' => $total !== false ? $total : 0,
			'status'     => $free !== false && $free > 100 * 1048576,
		);
	}

	/**
	 * Get temp directory info
	 *
	 * @return array
	 */
	public static function get_temp_dir() {
		$temp = sys_get_temp_dir();

		return array(
			'path'     => ai1wm_debug_normalize_path( $temp ),
			'writable' => is_writable( $temp ),
		);
	}
}
