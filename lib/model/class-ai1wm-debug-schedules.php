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

class Ai1wm_Debug_Schedules {

	/**
	 * Legacy extension hook prefix-to-storage mapping
	 *
	 * @var array
	 */
	private static $storage_map = array(
		'ai1wmze_azure'        => 'Azure Storage',
		'ai1wmae_b2'           => 'Backblaze B2',
		'ai1wmbe_box'          => 'Box',
		'ai1wmie_digitalocean' => 'DigitalOcean Spaces',
		'ai1wmxe_direct'       => 'Direct',
		'ai1wmde_dropbox'      => 'Dropbox',
		'ai1wmfe_ftp'          => 'FTP',
		'ai1wmce_gcloud'       => 'Google Cloud Storage',
		'ai1wmge_gdrive'       => 'Google Drive',
		'ai1wmre_glacier'      => 'Amazon Glacier',
		'ai1wmee_mega'         => 'Mega',
		'ai1wmoe_onedrive'     => 'OneDrive',
		'ai1wmpe_pcloud'       => 'pCloud',
		'ai1wmne_s3-client'    => 'S3 Client',
		'ai1wmse_s3'           => 'Amazon S3',
		'ai1wmle_url'          => 'URL',
		'ai1wmwe_webdav'       => 'WebDAV',
		'ai1wmhe_alibaba'      => 'Alibaba Cloud',
		'ai1wmje_wasabi'       => 'Wasabi',
	);

	/**
	 * Pro plugin hook prefix-to-storage mapping
	 *
	 * @var array
	 */
	private static $pro_storage_map = array(
		'ai1wmke_azure'        => 'Azure Storage',
		'ai1wmke_b2'           => 'Backblaze B2',
		'ai1wmke_box'          => 'Box',
		'ai1wmke_digitalocean' => 'DigitalOcean Spaces',
		'ai1wmke_direct'       => 'Direct',
		'ai1wmke_dropbox'      => 'Dropbox',
		'ai1wmke_ftp'          => 'FTP',
		'ai1wmke_gcloud'       => 'Google Cloud Storage',
		'ai1wmke_gdrive'       => 'Google Drive',
		'ai1wmke_glacier'      => 'Amazon Glacier',
		'ai1wmke_mega'         => 'Mega',
		'ai1wmke_onedrive'     => 'OneDrive',
		'ai1wmke_pcloud'       => 'pCloud',
		'ai1wmke_s3-client'    => 'S3 Client',
		'ai1wmke_s3'           => 'Amazon S3',
		'ai1wmke_url'          => 'URL',
		'ai1wmke_webdav'       => 'WebDAV',
		'ai1wmke_alibaba'      => 'Alibaba Cloud',
		'ai1wmke_wasabi'       => 'Wasabi',
	);

	/**
	 * Get all schedule data
	 *
	 * @return array
	 */
	public static function get_data() {
		return array(
			'pro_events'       => self::get_pro_events(),
			'legacy_schedules' => self::get_legacy_schedules(),
			'cron_entries'     => self::get_ai1wm_cron_entries(),
			'issues'           => self::get_issues(),
		);
	}

	/**
	 * Get Pro schedule events from ai1wmke_schedule_events option
	 *
	 * @return array
	 */
	public static function get_pro_events() {
		$events = array();
		$raw    = get_option( 'ai1wmke_schedule_events' );

		if ( ! is_array( $raw ) ) {
			return $events;
		}

		foreach ( $raw as $event_data ) {
			if ( ! is_array( $event_data ) && ! is_object( $event_data ) ) {
				continue;
			}

			$event_data = (array) $event_data;
			$event_id   = isset( $event_data['event_id'] ) ? $event_data['event_id'] : '';

			if ( empty( $event_id ) ) {
				continue;
			}

			// Get last run status
			$last_run = get_option( 'ai1wmke_schedule_event_last_run_' . $event_id, 'None' );

			// Get event logs (last 30 entries)
			$logs = get_option( 'ai1wmke_schedule_event_log_' . $event_id, array() );
			if ( ! is_array( $logs ) ) {
				$logs = array();
			}

			// Get next scheduled run from WP cron
			$next_run = self::get_next_cron_run( 'ai1wmke_schedule_event', array( $event_id ) );

			// Build schedule description
			$schedule = isset( $event_data['schedule'] ) ? (array) $event_data['schedule'] : array();
			$schedule_desc = self::format_schedule( $schedule );

			// Build retention description
			$retention      = isset( $event_data['retention'] ) ? (array) $event_data['retention'] : array();
			$retention_desc = self::format_retention( $retention );

			// Notification info
			$notification = isset( $event_data['notification'] ) ? (array) $event_data['notification'] : array();

			// Storage name
			$storage_key  = isset( $event_data['storage'] ) ? $event_data['storage'] : 'file';
			$storage_name = self::resolve_storage_name( $storage_key );

			$events[] = array(
				'event_id'       => $event_id,
				'title'          => isset( $event_data['title'] ) ? $event_data['title'] : 'Untitled',
				'type'           => isset( $event_data['type'] ) ? $event_data['type'] : 'Export',
				'storage'        => $storage_name,
				'status'         => isset( $event_data['status'] ) ? $event_data['status'] : 'Disabled',
				'repeating'      => ! empty( $event_data['repeating'] ),
				'is_running'     => ! empty( $event_data['is_running'] ),
				'schedule'       => $schedule_desc,
				'next_run'       => $next_run,
				'last_run'       => $last_run,
				'retention'      => $retention_desc,
				'notification'   => self::format_notification( $notification ),
				'incremental'    => ! empty( $event_data['incremental'] ),
				'recent_logs'    => array_slice( array_reverse( $logs ), 0, 5 ),
			);
		}

		return $events;
	}

	/**
	 * Get legacy extension schedules from WP cron table
	 *
	 * @return array
	 */
	public static function get_legacy_schedules() {
		$schedules = array();
		$all_crons = get_option( 'cron' );

		if ( ! is_array( $all_crons ) ) {
			return $schedules;
		}

		// Collect all legacy hooks: ai1wm{prefix}_{storage}_{interval}_export
		$intervals = array( 'hourly', 'daily', 'weekly', 'monthly' );
		$all_maps  = array_merge( self::$storage_map, self::$pro_storage_map );

		foreach ( $all_crons as $timestamp => $hooks ) {
			if ( ! is_array( $hooks ) ) {
				continue;
			}

			foreach ( $hooks as $hook => $events ) {
				// Check if this matches the legacy pattern
				foreach ( $all_maps as $prefix => $storage_name ) {
					foreach ( $intervals as $interval ) {
						$expected_hook = $prefix . '_' . $interval . '_export';
						if ( $hook === $expected_hook ) {
							$schedules[] = array(
								'hook'         => $hook,
								'storage'      => $storage_name,
								'interval'     => ucfirst( $interval ),
								'next_run'     => date( 'Y-m-d H:i:s', $timestamp ),
								'next_run_ts'  => $timestamp,
								'is_overdue'   => $timestamp < time(),
							);
						}
					}
				}
			}
		}

		return $schedules;
	}

	/**
	 * Get all AI1WM-related cron entries from the WP cron table
	 *
	 * @return array
	 */
	public static function get_ai1wm_cron_entries() {
		$entries   = array();
		$all_crons = get_option( 'cron' );

		if ( ! is_array( $all_crons ) ) {
			return $entries;
		}

		foreach ( $all_crons as $timestamp => $hooks ) {
			if ( ! is_array( $hooks ) ) {
				continue;
			}

			foreach ( $hooks as $hook => $events ) {
				if ( strpos( $hook, 'ai1wm' ) === false ) {
					continue;
				}

				foreach ( $events as $key => $event ) {
					$entries[] = array(
						'hook'      => $hook,
						'next_run'  => date( 'Y-m-d H:i:s', $timestamp ),
						'schedule'  => isset( $event['schedule'] ) ? $event['schedule'] : 'once',
						'args'      => ! empty( $event['args'] ) ? $event['args'] : array(),
						'is_overdue' => $timestamp < time(),
					);
				}
			}
		}

		return $entries;
	}

	/**
	 * Detect schedule-related issues
	 *
	 * @return array
	 */
	public static function get_issues() {
		$issues = array();

		// WP-Cron disabled
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			$alt_cron = defined( 'ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON;
			if ( $alt_cron ) {
				$issues[] = array(
					'severity' => 'info',
					'message'  => 'WP-Cron is disabled but ALTERNATE_WP_CRON is active. Scheduled tasks will run when users visit the site.',
				);
			} else {
				$issues[] = array(
					'severity' => 'error',
					'message'  => 'WP-Cron is disabled (DISABLE_WP_CRON is true) and no alternative cron is configured. Scheduled backups will NOT run unless a system cron job calls wp-cron.php.',
				);
			}
		}

		// Check Pro events for issues
		$pro_events = self::get_pro_events();
		foreach ( $pro_events as $event ) {
			// Stuck in running state
			if ( $event['is_running'] ) {
				$issues[] = array(
					'severity' => 'warning',
					'message'  => 'Schedule event "' . $event['title'] . '" is stuck in Running state. It may have failed without completing.',
				);
			}

			// Enabled but no next run scheduled
			if ( $event['status'] === 'Enabled' && $event['repeating'] && empty( $event['next_run'] ) ) {
				$issues[] = array(
					'severity' => 'error',
					'message'  => 'Schedule event "' . $event['title'] . '" is enabled but has no upcoming cron entry. It will not run.',
				);
			}

			// Last run failed
			if ( $event['last_run'] === 'Failed' ) {
				$error_msg = '';
				if ( ! empty( $event['recent_logs'] ) ) {
					foreach ( $event['recent_logs'] as $log ) {
						$log = (array) $log;
						if ( isset( $log['status'] ) && $log['status'] === 'Failed' && ! empty( $log['message'] ) ) {
							$error_msg = $log['message'];
							break;
						}
					}
				}
				$msg = 'Schedule event "' . $event['title'] . '" last run failed.';
				if ( $error_msg ) {
					$msg .= ' Error: ' . $error_msg;
				}
				$issues[] = array(
					'severity' => 'error',
					'message'  => $msg,
				);
			}
		}

		// Check legacy schedules for overdue runs
		$legacy = self::get_legacy_schedules();
		foreach ( $legacy as $sched ) {
			if ( $sched['is_overdue'] ) {
				$overdue_by = time() - $sched['next_run_ts'];
				if ( $overdue_by > 3600 ) {
					$issues[] = array(
						'severity' => 'warning',
						'message'  => 'Legacy schedule "' . $sched['storage'] . ' ' . $sched['interval'] . '" is overdue by ' . human_time_diff( $sched['next_run_ts'] ) . '. WP-Cron may not be firing.',
					);
				}
			}
		}

		// Check all cron entries for overdue AI1WM tasks
		$cron_entries = self::get_ai1wm_cron_entries();
		$overdue_count = 0;
		foreach ( $cron_entries as $entry ) {
			if ( $entry['is_overdue'] ) {
				$overdue_count++;
			}
		}
		if ( $overdue_count > 3 ) {
			$issues[] = array(
				'severity' => 'warning',
				'message'  => $overdue_count . ' AI1WM cron entries are overdue. WP-Cron may not be functioning correctly.',
			);
		}

		return $issues;
	}

	/**
	 * Get next scheduled run time for a hook with specific args
	 *
	 * @param  string $hook
	 * @param  array  $args
	 * @return string Empty string if not scheduled
	 */
	private static function get_next_cron_run( $hook, $args = array() ) {
		$all_crons = get_option( 'cron' );

		if ( ! is_array( $all_crons ) ) {
			return '';
		}

		foreach ( $all_crons as $timestamp => $hooks ) {
			if ( ! is_array( $hooks ) || ! isset( $hooks[ $hook ] ) ) {
				continue;
			}

			foreach ( $hooks[ $hook ] as $key => $event ) {
				$event_args = isset( $event['args'] ) ? $event['args'] : array();

				// Match by first arg (event_id)
				if ( ! empty( $args ) && ! empty( $event_args ) ) {
					if ( (string) $event_args[0] === (string) $args[0] ) {
						return date( 'Y-m-d H:i:s', $timestamp );
					}
				} elseif ( empty( $args ) && empty( $event_args ) ) {
					return date( 'Y-m-d H:i:s', $timestamp );
				}
			}
		}

		return '';
	}

	/**
	 * Resolve a storage key to a human-readable name
	 *
	 * @param  string $key
	 * @return string
	 */
	private static function resolve_storage_name( $key ) {
		$names = array(
			'file'         => 'Local (File)',
			'dropbox'      => 'Dropbox',
			's3'           => 'Amazon S3',
			'gdrive'       => 'Google Drive',
			'onedrive'     => 'OneDrive',
			'ftp'          => 'FTP',
			'gcloud'       => 'Google Cloud Storage',
			'azure'        => 'Azure Storage',
			'glacier'      => 'Amazon Glacier',
			'b2'           => 'Backblaze B2',
			'box'          => 'Box',
			'mega'         => 'Mega',
			'pcloud'       => 'pCloud',
			'digitalocean' => 'DigitalOcean Spaces',
			'webdav'       => 'WebDAV',
			's3-client'    => 'S3 Client',
			'direct'       => 'Direct',
			'url'          => 'URL',
			'alibaba'      => 'Alibaba Cloud',
			'wasabi'       => 'Wasabi',
		);

		return isset( $names[ $key ] ) ? $names[ $key ] : ucfirst( $key );
	}

	/**
	 * Format a schedule array into a human-readable string
	 *
	 * @param  array  $schedule
	 * @return string
	 */
	private static function format_schedule( $schedule ) {
		if ( empty( $schedule ) ) {
			return 'Not configured';
		}

		$interval = isset( $schedule['interval'] ) ? $schedule['interval'] : '';
		$hour     = isset( $schedule['hour'] ) ? str_pad( $schedule['hour'], 2, '0', STR_PAD_LEFT ) : '00';
		$minute   = isset( $schedule['minute'] ) ? str_pad( $schedule['minute'], 2, '0', STR_PAD_LEFT ) : '00';

		switch ( $interval ) {
			case 'Hourly':
				return 'Every hour at :' . $minute;
			case 'Daily':
				return 'Daily at ' . $hour . ':' . $minute;
			case 'Weekly':
				$day = isset( $schedule['weekday'] ) ? ucfirst( $schedule['weekday'] ) : '';
				return 'Weekly on ' . $day . ' at ' . $hour . ':' . $minute;
			case 'Monthly':
				$day = isset( $schedule['day'] ) ? $schedule['day'] : '1';
				return 'Monthly on day ' . $day . ' at ' . $hour . ':' . $minute;
			case 'N-Hour':
				$n = isset( $schedule['n'] ) ? $schedule['n'] : '?';
				return 'Every ' . $n . ' hours';
			case 'N-Days':
				$n = isset( $schedule['n'] ) ? $schedule['n'] : '?';
				return 'Every ' . $n . ' days at ' . $hour . ':' . $minute;
			default:
				return $interval ? $interval : 'Unknown';
		}
	}

	/**
	 * Format retention settings into a human-readable string
	 *
	 * @param  array  $retention
	 * @return string
	 */
	private static function format_retention( $retention ) {
		if ( empty( $retention ) ) {
			return 'None';
		}

		$parts = array();
		$backups = isset( $retention['backups'] ) ? intval( $retention['backups'] ) : 0;
		$days    = isset( $retention['days'] ) ? intval( $retention['days'] ) : 0;
		$total   = isset( $retention['total'] ) ? intval( $retention['total'] ) : 0;
		$unit    = isset( $retention['total_unit'] ) ? $retention['total_unit'] : 'MB';

		if ( $backups > 0 ) {
			$parts[] = 'Keep ' . $backups . ' backup' . ( $backups > 1 ? 's' : '' );
		}
		if ( $days > 0 ) {
			$parts[] = $days . ' day' . ( $days > 1 ? 's' : '' );
		}
		if ( $total > 0 ) {
			$parts[] = 'Max ' . $total . $unit;
		}

		return ! empty( $parts ) ? implode( ', ', $parts ) : 'Unlimited';
	}

	/**
	 * Format notification settings
	 *
	 * @param  array  $notification
	 * @return string
	 */
	private static function format_notification( $notification ) {
		if ( empty( $notification ) ) {
			return 'Off';
		}

		$status   = isset( $notification['status'] ) ? $notification['status'] : 'Disabled';
		$reminder = isset( $notification['reminder'] ) ? $notification['reminder'] : 'Off';

		if ( $status === 'Disabled' || $reminder === 'Off' ) {
			return 'Off';
		}

		$email = isset( $notification['email'] ) ? $notification['email'] : '';
		$desc  = $reminder;
		if ( $email ) {
			$desc .= ' (' . $email . ')';
		}

		return $desc;
	}
}
