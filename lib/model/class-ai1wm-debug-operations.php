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

class Ai1wm_Debug_Operations {

	/**
	 * Get all operations data
	 *
	 * @return array
	 */
	public static function get_data() {
		if ( ! ai1wm_debug_is_ai1wm_active() ) {
			return array();
		}

		return array(
			'status'   => self::get_current_status(),
			'storage'  => self::get_storage_files(),
			'backups'  => self::get_backups(),
			'crons'    => self::get_ai1wm_crons(),
			'issues'   => self::get_common_issues(),
		);
	}

	/**
	 * Get current operation status
	 *
	 * @return array
	 */
	public static function get_current_status() {
		$status = array(
			'active' => false,
			'type'   => 'None',
			'message' => '',
		);

		if ( defined( 'AI1WM_STORAGE_PATH' ) ) {
			$status_file = AI1WM_STORAGE_PATH . DIRECTORY_SEPARATOR . 'status.js';
			if ( file_exists( $status_file ) ) {
				$content = @file_get_contents( $status_file );
				if ( $content ) {
					$data = json_decode( $content, true );
					if ( is_array( $data ) ) {
						$status['active']  = true;
						$status['type']    = isset( $data['type'] ) ? $data['type'] : 'Unknown';
						$status['message'] = isset( $data['message'] ) ? $data['message'] : '';
					}
				}
			}
		}

		return $status;
	}

	/**
	 * Get storage directory contents
	 *
	 * @return array
	 */
	public static function get_storage_files() {
		$files = array();

		if ( ! defined( 'AI1WM_STORAGE_PATH' ) || ! is_dir( AI1WM_STORAGE_PATH ) ) {
			return $files;
		}

		$dir = @opendir( AI1WM_STORAGE_PATH );
		if ( ! $dir ) {
			return $files;
		}

		while ( ( $entry = readdir( $dir ) ) !== false ) {
			if ( $entry === '.' || $entry === '..' ) {
				continue;
			}

			$path = AI1WM_STORAGE_PATH . DIRECTORY_SEPARATOR . $entry;
			$files[] = array(
				'name'     => $entry,
				'size'     => is_file( $path ) ? ai1wm_debug_size_format( filesize( $path ), 2 ) : 'DIR',
				'modified' => date( 'Y-m-d H:i:s', filemtime( $path ) ),
				'is_dir'   => is_dir( $path ),
			);
		}

		closedir( $dir );

		return $files;
	}

	/**
	 * Get backup files
	 *
	 * @return array
	 */
	public static function get_backups() {
		$backups = array();

		if ( ! defined( 'AI1WM_BACKUPS_PATH' ) || ! is_dir( AI1WM_BACKUPS_PATH ) ) {
			return $backups;
		}

		$dir = @opendir( AI1WM_BACKUPS_PATH );
		if ( ! $dir ) {
			return $backups;
		}

		while ( ( $entry = readdir( $dir ) ) !== false ) {
			if ( $entry === '.' || $entry === '..' ) {
				continue;
			}

			$path = AI1WM_BACKUPS_PATH . DIRECTORY_SEPARATOR . $entry;
			if ( is_file( $path ) && pathinfo( $entry, PATHINFO_EXTENSION ) === 'wpress' ) {
				$backups[] = array(
					'name'     => $entry,
					'size'     => ai1wm_debug_size_format( filesize( $path ), 2 ),
					'modified' => date( 'Y-m-d H:i:s', filemtime( $path ) ),
				);
			}
		}

		closedir( $dir );

		return $backups;
	}

	/**
	 * Get AI1WM cron jobs
	 *
	 * @return array
	 */
	public static function get_ai1wm_crons() {
		$crons = array();
		$all   = get_option( 'cron' );

		if ( ! is_array( $all ) ) {
			return $crons;
		}

		foreach ( $all as $timestamp => $hooks ) {
			if ( ! is_array( $hooks ) ) {
				continue;
			}

			foreach ( $hooks as $hook => $events ) {
				if ( strpos( $hook, 'ai1wm' ) !== false ) {
					foreach ( $events as $event ) {
						$crons[] = array(
							'hook'      => $hook,
							'next_run'  => date( 'Y-m-d H:i:s', $timestamp ),
							'schedule'  => isset( $event['schedule'] ) ? $event['schedule'] : 'Once',
						);
					}
				}
			}
		}

		return $crons;
	}

	/**
	 * Detect common issues
	 *
	 * @return array
	 */
	public static function get_common_issues() {
		$issues = array();

		// Stale status file
		if ( defined( 'AI1WM_STORAGE_PATH' ) ) {
			$status_file = AI1WM_STORAGE_PATH . DIRECTORY_SEPARATOR . 'status.js';
			if ( file_exists( $status_file ) ) {
				$age = time() - filemtime( $status_file );
				if ( $age > 3600 ) {
					$issues[] = array(
						'severity' => 'warning',
						'message'  => 'Stale status file detected (last modified ' . human_time_diff( filemtime( $status_file ) ) . ' ago). A previous operation may have failed.',
					);
				}
			}
		}

		// Low memory
		$memory_limit = ai1wm_debug_to_bytes( ini_get( 'memory_limit' ) );
		if ( $memory_limit > 0 && $memory_limit < 256 * 1048576 ) {
			$issues[] = array(
				'severity' => 'warning',
				'message'  => 'PHP memory limit (' . ini_get( 'memory_limit' ) . ') is below recommended 256M for large operations.',
			);
		}

		// Low disk space
		$free = function_exists( 'disk_free_space' ) ? @disk_free_space( ABSPATH ) : false;
		if ( $free !== false && $free < 500 * 1048576 ) {
			$issues[] = array(
				'severity' => 'error',
				'message'  => 'Low disk space (' . ai1wm_debug_size_format( $free, 2 ) . ' free). This may cause import/export failures.',
			);
		}

		// Short execution time
		$max_time = ini_get( 'max_execution_time' );
		if ( $max_time > 0 && $max_time < 30 ) {
			$issues[] = array(
				'severity' => 'warning',
				'message'  => 'PHP max_execution_time (' . $max_time . 's) is very short. Operations may time out.',
			);
		}

		// Orphaned temp directories in storage
		if ( defined( 'AI1WM_STORAGE_PATH' ) && is_dir( AI1WM_STORAGE_PATH ) ) {
			$dir = @opendir( AI1WM_STORAGE_PATH );
			if ( $dir ) {
				$orphaned = 0;
				while ( ( $entry = readdir( $dir ) ) !== false ) {
					if ( $entry === '.' || $entry === '..' ) {
						continue;
					}
					$path = AI1WM_STORAGE_PATH . DIRECTORY_SEPARATOR . $entry;
					if ( is_dir( $path ) && ( time() - filemtime( $path ) ) > 86400 ) {
						$orphaned++;
					}
				}
				closedir( $dir );

				if ( $orphaned > 0 ) {
					$issues[] = array(
						'severity' => 'warning',
						'message'  => $orphaned . ' orphaned temporary director' . ( $orphaned === 1 ? 'y' : 'ies' ) . ' found in storage (older than 24h).',
					);
				}
			}
		}

		return $issues;
	}
}
