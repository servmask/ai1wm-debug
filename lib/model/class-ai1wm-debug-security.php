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

class Ai1wm_Debug_Security {

	/**
	 * Get or generate the secret key
	 *
	 * @return string
	 */
	public static function get_secret_key() {
		$key = get_option( AI1WM_DEBUG_SECRET_KEY_OPTION );

		if ( empty( $key ) ) {
			$key = self::generate_random_hex( 32 );
			update_option( AI1WM_DEBUG_SECRET_KEY_OPTION, $key );
		}

		return $key;
	}

	/**
	 * Verify a secret key
	 *
	 * @param  string  $provided_key
	 * @return boolean
	 */
	public static function verify_secret_key( $provided_key ) {
		$stored_key = self::get_secret_key();

		if ( function_exists( 'hash_equals' ) ) {
			return hash_equals( $stored_key, $provided_key );
		}

		return $stored_key === $provided_key;
	}

	/**
	 * Regenerate the secret key
	 *
	 * @return string
	 */
	public static function regenerate_secret_key() {
		$key = self::generate_random_hex( 32 );
		update_option( AI1WM_DEBUG_SECRET_KEY_OPTION, $key );
		return $key;
	}

	/**
	 * Generate a random hex string
	 *
	 * @param  int    $length Number of hex characters
	 * @return string
	 */
	public static function generate_random_hex( $length = 32 ) {
		if ( function_exists( 'random_bytes' ) ) {
			return bin2hex( random_bytes( $length / 2 ) );
		}

		if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
			return bin2hex( openssl_random_pseudo_bytes( $length / 2 ) );
		}

		// Fallback for older PHP
		$hex = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$hex .= dechex( mt_rand( 0, 15 ) );
		}
		return $hex;
	}
}
