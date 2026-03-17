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

class Ai1wm_Debug_Filters {

	/**
	 * Preset filter definitions
	 *
	 * @return array
	 */
	public static function get_preset_definitions() {
		return array(
			'completed_timeout' => array(
				'filter'      => 'ai1wm_completed_timeout',
				'label'       => 'Completed Timeout',
				'description' => 'Seconds between pipeline stage iterations. Increase if stages time out on slow servers.',
				'type'        => 'int',
				'default'     => 10,
				'unit'        => 'seconds',
			),
			'max_chunk_size' => array(
				'filter'      => 'ai1wm_max_chunk_size',
				'label'       => 'Max Chunk Size',
				'description' => 'Upload chunk size in bytes. Reduce for flaky connections, increase for fast ones.',
				'type'        => 'int',
				'default'     => 2097152,
				'unit'        => 'bytes',
			),
			'max_chunk_retries' => array(
				'filter'      => 'ai1wm_max_chunk_retries',
				'label'       => 'Max Chunk Retries',
				'description' => 'How many times to retry a failed chunk upload.',
				'type'        => 'int',
				'default'     => 3,
				'unit'        => '',
			),
			'http_export_timeout' => array(
				'filter'      => 'ai1wm_http_export_timeout',
				'label'       => 'HTTP Export Timeout',
				'description' => 'Loopback request timeout for export pipeline (WP_CLI / cron).',
				'type'        => 'int',
				'default'     => 10,
				'unit'        => 'seconds',
			),
			'http_import_timeout' => array(
				'filter'      => 'ai1wm_http_import_timeout',
				'label'       => 'HTTP Import Timeout',
				'description' => 'Loopback request timeout for import pipeline (WP_CLI / cron).',
				'type'        => 'int',
				'default'     => 10,
				'unit'        => 'seconds',
			),
			'http_export_blocking' => array(
				'filter'      => 'ai1wm_http_export_blocking',
				'label'       => 'HTTP Export Blocking',
				'description' => 'Make export loopback requests blocking. Useful for debugging but slower.',
				'type'        => 'bool',
				'default'     => false,
				'unit'        => '',
			),
			'http_import_blocking' => array(
				'filter'      => 'ai1wm_http_import_blocking',
				'label'       => 'HTTP Import Blocking',
				'description' => 'Make import loopback requests blocking. Useful for debugging but slower.',
				'type'        => 'bool',
				'default'     => false,
				'unit'        => '',
			),
			'http_export_sslverify' => array(
				'filter'      => 'ai1wm_http_export_sslverify',
				'label'       => 'HTTP Export SSL Verify',
				'description' => 'Enable SSL verification for export loopback requests.',
				'type'        => 'bool',
				'default'     => false,
				'unit'        => '',
			),
			'http_import_sslverify' => array(
				'filter'      => 'ai1wm_http_import_sslverify',
				'label'       => 'HTTP Import SSL Verify',
				'description' => 'Enable SSL verification for import loopback requests.',
				'type'        => 'bool',
				'default'     => false,
				'unit'        => '',
			),
		);
	}

	/**
	 * Get saved overrides
	 *
	 * @return array
	 */
	public static function get_overrides() {
		$defaults = array(
			'presets'    => array(),
			'exclusions' => array(
				'content' => '',
				'media'   => '',
				'plugins' => '',
				'themes'  => '',
			),
			'custom'     => array(),
		);

		$saved = Ai1wm_Debug_Config::get( AI1WM_DEBUG_FILTER_OVERRIDES_OPTION, array() );
		return array_merge( $defaults, $saved );
	}

	/**
	 * Get export pipeline stages (priority => name)
	 *
	 * @return array
	 */
	public static function get_export_stages() {
		return array(
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
	}

	/**
	 * Get import pipeline stages (priority => name)
	 *
	 * @return array
	 */
	public static function get_import_stages() {
		return array(
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
	}

	/**
	 * Get current pipeline priority from AI1WM's global params
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
	 * Initialize filter overrides
	 */
	public static function init() {
		if ( ! ai1wm_debug_is_ai1wm_active() ) {
			return;
		}

		$overrides = self::get_overrides();
		$presets   = self::get_preset_definitions();

		// Register preset overrides
		foreach ( $overrides['presets'] as $key => $preset ) {
			if ( empty( $preset['enabled'] ) || ! isset( $presets[ $key ] ) ) {
				continue;
			}

			$filter = $presets[ $key ]['filter'];
			$value  = $preset['value'];
			$type   = $presets[ $key ]['type'];
			$steps  = isset( $preset['steps'] ) ? self::parse_steps( $preset['steps'] ) : array();

			add_filter( $filter, self::make_override_callback( $filter, $value, $type, $steps ), 999 );
		}

		// Register exclusion overrides
		$exclusion_map = array(
			'content' => 'ai1wm_exclude_content_from_export',
			'media'   => 'ai1wm_exclude_media_from_export',
			'plugins' => 'ai1wm_exclude_plugins_from_export',
			'themes'  => 'ai1wm_exclude_themes_from_export',
		);

		foreach ( $exclusion_map as $key => $filter ) {
			$patterns = isset( $overrides['exclusions'][ $key ] ) ? trim( $overrides['exclusions'][ $key ] ) : '';
			if ( empty( $patterns ) ) {
				continue;
			}

			$pattern_array = array_filter( array_map( 'trim', explode( "\n", $patterns ) ) );
			if ( ! empty( $pattern_array ) ) {
				add_filter( $filter, self::make_exclusion_callback( $filter, $pattern_array ), 999 );
			}
		}

		// Register custom filter overrides
		if ( ! empty( $overrides['custom'] ) ) {
			foreach ( $overrides['custom'] as $custom ) {
				if ( empty( $custom['enabled'] ) || empty( $custom['filter'] ) ) {
					continue;
				}

				// Only allow ai1wm_ prefixed filters for safety
				if ( strpos( $custom['filter'], 'ai1wm_' ) !== 0 ) {
					continue;
				}

				$steps = isset( $custom['steps'] ) ? self::parse_steps( $custom['steps'] ) : array();
				add_filter( $custom['filter'], self::make_override_callback( $custom['filter'], $custom['value'], $custom['type'], $steps ), 999 );
			}
		}
	}

	/**
	 * Parse steps string into array of priority numbers
	 *
	 * @param  string $steps Comma-separated priority numbers
	 * @return array
	 */
	private static function parse_steps( $steps ) {
		if ( empty( $steps ) ) {
			return array();
		}

		$result = array();
		$parts  = explode( ',', $steps );
		foreach ( $parts as $part ) {
			$num = intval( trim( $part ) );
			if ( $num > 0 ) {
				$result[] = $num;
			}
		}
		return $result;
	}

	/**
	 * Create a closure-like callback for overriding a filter value
	 *
	 * Uses a static registry to avoid closures (PHP 5.3 compat)
	 *
	 * @param  string $filter Filter name
	 * @param  mixed  $value  Override value
	 * @param  string $type   Value type (int, bool, string)
	 * @param  array  $steps  Pipeline priorities to restrict to (empty = all)
	 * @return array  Callback
	 */
	private static function make_override_callback( $filter, $value, $type, $steps = array() ) {
		// Store in registry
		self::$override_registry[ $filter ] = array(
			'value' => $value,
			'type'  => $type,
			'steps' => $steps,
		);

		return array( 'Ai1wm_Debug_Filters', 'apply_override_' . md5( $filter ) );
	}

	/**
	 * Create a callback for appending exclusion patterns
	 *
	 * @param  string $filter   Filter name
	 * @param  array  $patterns Patterns to add
	 * @return array  Callback
	 */
	private static function make_exclusion_callback( $filter, $patterns ) {
		self::$exclusion_registry[ $filter ] = $patterns;

		return array( 'Ai1wm_Debug_Filters', 'apply_exclusion_' . md5( $filter ) );
	}

	/**
	 * Override registry
	 *
	 * @var array
	 */
	private static $override_registry = array();

	/**
	 * Exclusion registry
	 *
	 * @var array
	 */
	private static $exclusion_registry = array();

	/**
	 * Generic filter callback — dispatches to the right override from registry
	 *
	 * @param  string $name Method name
	 * @param  array  $args Arguments
	 * @return mixed
	 */
	public static function __callStatic( $name, $args ) {
		// Override callbacks: apply_override_{md5}
		if ( strpos( $name, 'apply_override_' ) === 0 ) {
			$filter = current_filter();
			if ( isset( self::$override_registry[ $filter ] ) ) {
				$entry = self::$override_registry[ $filter ];

				// Check step restriction
				$current_priority = self::get_current_priority();
				if ( ! empty( $entry['steps'] ) && ! in_array( $current_priority, $entry['steps'] ) ) {
					// Not in the allowed steps, pass through unchanged
					return isset( $args[0] ) ? $args[0] : null;
				}

				$original = isset( $args[0] ) ? $args[0] : null;

				if ( $entry['type'] === 'php' ) {
					$value = self::eval_php( $entry['value'], $original );
				} else {
					$value = self::cast_value( $entry['value'], $entry['type'] );
				}

				// Log the override if logger is active
				if ( class_exists( 'Ai1wm_Debug_Logger' ) && Ai1wm_Debug_Logger::is_enabled() ) {
					$step_info = ! empty( $entry['steps'] ) ? ' [step ' . $current_priority . ']' : '';
					Ai1wm_Debug_Logger::write( 'OVERRIDE', $filter . $step_info . ': ' . print_r( $original, true ) . ' => ' . print_r( $value, true ) );
				}

				return $value;
			}
		}

		// Exclusion callbacks: apply_exclusion_{md5}
		if ( strpos( $name, 'apply_exclusion_' ) === 0 ) {
			$filter = current_filter();
			if ( isset( self::$exclusion_registry[ $filter ] ) ) {
				$existing = isset( $args[0] ) && is_array( $args[0] ) ? $args[0] : array();
				$added    = self::$exclusion_registry[ $filter ];

				if ( class_exists( 'Ai1wm_Debug_Logger' ) && Ai1wm_Debug_Logger::is_enabled() ) {
					Ai1wm_Debug_Logger::write( 'OVERRIDE', $filter . ': adding exclusions: ' . implode( ', ', $added ) );
				}

				return array_merge( $existing, $added );
			}
		}

		return isset( $args[0] ) ? $args[0] : null;
	}

	/**
	 * Cast a value to the specified type
	 *
	 * @param  mixed  $value
	 * @param  string $type
	 * @return mixed
	 */
	private static function cast_value( $value, $type ) {
		switch ( $type ) {
			case 'int':
				return intval( $value );
			case 'bool':
				return (bool) $value;
			case 'string':
			default:
				return (string) $value;
		}
	}

	/**
	 * Execute PHP code as a filter callback
	 *
	 * The code receives $value (current filter value) and $params (AI1WM global params).
	 * Must return the new value.
	 *
	 * Example: return $value * 2;
	 * Example: if ($value > 1048576) { return 524288; } return $value;
	 *
	 * @param  string $code     PHP code to execute
	 * @param  mixed  $value    Current filter value
	 * @return mixed
	 */
	private static function eval_php( $code, $value ) {
		// Require explicit opt-in via wp-config.php constant
		if ( ! defined( 'AI1WM_DEBUG_ALLOW_EVAL' ) || ! AI1WM_DEBUG_ALLOW_EVAL ) {
			return $value;
		}

		$params = isset( $GLOBALS['ai1wm_params'] ) ? $GLOBALS['ai1wm_params'] : array();

		try {
			$result = eval( $code );
		} catch ( Exception $e ) {
			if ( class_exists( 'Ai1wm_Debug_Logger' ) && Ai1wm_Debug_Logger::is_enabled() ) {
				Ai1wm_Debug_Logger::write( 'ERROR', 'PHP filter eval failed: ' . $e->getMessage() );
			}
			return $value;
		}

		// If code didn't return anything, keep original value
		if ( $result === null ) {
			return $value;
		}

		return $result;
	}
}
