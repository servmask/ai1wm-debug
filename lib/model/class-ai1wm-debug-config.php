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

class Ai1wm_Debug_Config {

	/**
	 * PHP die guard prepended to config file to prevent direct HTTP access
	 */
	const PHP_GUARD = "<?php exit; ?>\n";

	/**
	 * In-memory cache of config values
	 *
	 * @var array|null
	 */
	private static $data = null;

	/**
	 * Get config file path
	 *
	 * @return string
	 */
	private static function file_path() {
		return AI1WM_DEBUG_STORAGE_PATH . DIRECTORY_SEPARATOR . 'config.php';
	}

	/**
	 * Load config from file into memory
	 *
	 * @return array
	 */
	private static function load() {
		if ( self::$data !== null ) {
			return self::$data;
		}

		$file = self::file_path();
		if ( file_exists( $file ) ) {
			$content = @file_get_contents( $file );
			if ( $content !== false ) {
				$json = preg_replace( '/^<\?php\s+exit;\s*\?>\s*/', '', $content );
				$decoded = json_decode( $json, true );
				if ( is_array( $decoded ) ) {
					self::$data = $decoded;
					return self::$data;
				}
			}
		}

		self::$data = array();
		return self::$data;
	}

	/**
	 * Save config to file
	 */
	private static function save() {
		if ( self::$data === null ) {
			return;
		}

		$dir = dirname( self::file_path() );
		if ( ! is_dir( $dir ) ) {
			@mkdir( $dir, 0755, true );
		}

		@file_put_contents(
			self::file_path(),
			self::PHP_GUARD . json_encode( self::$data, JSON_PRETTY_PRINT ),
			LOCK_EX
		);
	}

	/**
	 * Get a config value
	 *
	 * @param  string $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {
		$data = self::load();
		return isset( $data[ $key ] ) ? $data[ $key ] : $default;
	}

	/**
	 * Set a config value
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public static function set( $key, $value ) {
		self::load();
		self::$data[ $key ] = $value;
		self::save();
	}

	/**
	 * Delete a config key
	 *
	 * @param string $key
	 */
	public static function delete( $key ) {
		self::load();
		unset( self::$data[ $key ] );
		self::save();
	}
}
