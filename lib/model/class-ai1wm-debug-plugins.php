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

class Ai1wm_Debug_Plugins {

	/**
	 * Get all plugin/theme data
	 *
	 * @return array
	 */
	public static function get_data() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return array(
			'active_plugins'   => self::get_plugins_by_status( true ),
			'inactive_plugins' => self::get_plugins_by_status( false ),
			'active_theme'     => self::get_active_theme(),
			'inactive_themes'  => self::get_inactive_themes(),
			'ai1wm_ecosystem'  => self::get_ai1wm_ecosystem(),
			'known_conflicts'  => self::get_known_conflicts(),
		);
	}

	/**
	 * Get plugins by active/inactive status
	 *
	 * @param  boolean $active
	 * @return array
	 */
	public static function get_plugins_by_status( $active ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = array();

		foreach ( get_plugins() as $path => $info ) {
			$is_active = is_plugin_active( $path );
			if ( $active === $is_active ) {
				$plugins[] = array(
					'name'    => $info['Name'],
					'version' => $info['Version'],
					'author'  => $info['Author'],
					'path'    => $path,
				);
			}
		}

		return $plugins;
	}

	/**
	 * Get active theme info
	 *
	 * @return array
	 */
	public static function get_active_theme() {
		$theme = wp_get_theme();

		return array(
			'name'     => $theme->get( 'Name' ),
			'version'  => $theme->get( 'Version' ),
			'author'   => $theme->get( 'Author' ),
			'template' => get_template(),
			'is_child' => is_child_theme(),
			'parent'   => is_child_theme() ? $theme->parent()->get( 'Name' ) : '',
		);
	}

	/**
	 * Get inactive themes
	 *
	 * @return array
	 */
	public static function get_inactive_themes() {
		$inactive = array();
		$active   = array( get_template(), get_stylesheet() );
		$themes   = wp_get_themes();

		foreach ( $themes as $slug => $theme ) {
			if ( ! in_array( $slug, $active ) ) {
				$inactive[] = array(
					'name'    => $theme->get( 'Name' ),
					'version' => $theme->get( 'Version' ),
					'slug'    => $slug,
				);
			}
		}

		return $inactive;
	}

	/**
	 * Detect AI1WM ecosystem extensions with version comparison
	 *
	 * @return array
	 */
	public static function get_ai1wm_ecosystem() {
		$ecosystem = array();

		// Base plugin
		$base_installed = defined( 'AI1WM_PLUGIN_NAME' );
		$base_version   = defined( 'AI1WM_VERSION' ) ? AI1WM_VERSION : 'N/A';
		$base_latest    = $base_installed ? ai1wm_debug_get_base_plugin_latest_version() : '';

		$ecosystem[] = array(
			'name'       => 'All-in-One WP Migration (Base)',
			'constant'   => 'AI1WM_PLUGIN_NAME',
			'installed'  => $base_installed,
			'version'    => $base_version,
			'latest'     => $base_latest,
			'up_to_date' => empty( $base_latest ) || $base_latest === $base_version,
		);

		// Use Ai1wm_Extensions::get() when AI1WM is active for accurate data
		if ( $base_installed && class_exists( 'Ai1wm_Extensions' ) ) {
			foreach ( Ai1wm_Extensions::get() as $extension ) {
				$latest = ai1wm_debug_get_extension_latest_version( $extension['about'] );

				$ecosystem[] = array(
					'name'       => $extension['title'],
					'constant'   => '',
					'installed'  => true,
					'version'    => $extension['version'],
					'latest'     => $latest,
					'up_to_date' => empty( $latest ) || $latest === $extension['version'],
				);
			}
		}

		// Also show non-installed extensions from our known map
		$map = ai1wm_debug_get_extension_map();
		foreach ( $map as $constant => $name ) {
			$installed = defined( $constant );

			// Skip if already covered by Ai1wm_Extensions::get() above
			if ( $installed && $base_installed && class_exists( 'Ai1wm_Extensions' ) ) {
				continue;
			}

			if ( ! $installed ) {
				$ecosystem[] = array(
					'name'       => $name,
					'constant'   => $constant,
					'installed'  => false,
					'version'    => 'N/A',
					'latest'     => '',
					'up_to_date' => true,
				);
			}
		}

		return $ecosystem;
	}

	/**
	 * Detect known plugin conflicts
	 *
	 * @return array
	 */
	public static function get_known_conflicts() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$conflicts  = array();
		$all_active = get_option( 'active_plugins', array() );

		// TODO: Confirm plugin list and descriptions with support team
		$known = array(
			'w3-total-cache/w3-total-cache.php'         => 'W3 Total Cache may interfere with large imports due to object caching.',
			'wp-super-cache/wp-cache.php'               => 'WP Super Cache may serve stale pages after import.',
			'wordfence/wordfence.php'                   => 'Wordfence firewall may block large file uploads during import.',
			'sucuri-scanner/sucuri.php'                 => 'Sucuri may flag import operations as suspicious.',
			'jetpack/jetpack.php'                       => 'Jetpack sync may cause performance issues during large imports.',
			'updraftplus/updraftplus.php'               => 'UpdraftPlus backup schedules may conflict with AI1WM operations.',
			'backwpup/backwpup.php'                     => 'BackWPup schedules may conflict with AI1WM operations.',
			'better-wp-security/better-wp-security.php' => 'iThemes Security may block file operations during import.',
		);

		foreach ( $known as $plugin_path => $reason ) {
			if ( in_array( $plugin_path, $all_active ) ) {
				$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_path, false, false );
				$conflicts[] = array(
					'name'   => $plugin_data['Name'] ? $plugin_data['Name'] : $plugin_path,
					'reason' => $reason,
				);
			}
		}

		return $conflicts;
	}
}
