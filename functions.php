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

/**
 * Check if All-in-One WP Migration is active
 *
 * @return boolean
 */
function ai1wm_debug_is_ai1wm_active() {
	return defined( 'AI1WM_PLUGIN_NAME' );
}

/**
 * Render a view template
 *
 * @param  string $view View file path relative to view directory
 * @param  array  $args Variables to extract into the view scope
 * @return void
 */
function ai1wm_debug_render( $view, $args = array() ) {
	$file = AI1WM_DEBUG_VIEW_PATH . DIRECTORY_SEPARATOR . $view . '.php';
	if ( file_exists( $file ) ) {
		extract( $args );
		include $file;
	}
}

/**
 * Format bytes into human-readable size
 *
 * @param  int    $bytes
 * @param  int    $decimals
 * @return string
 */
function ai1wm_debug_size_format( $bytes, $decimals = 0 ) {
	$units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB' );

	$bytes = max( $bytes, 0 );
	$pow   = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
	$pow   = min( $pow, count( $units ) - 1 );

	$bytes /= pow( 1024, $pow );

	return number_format( $bytes, $decimals ) . ' ' . $units[$pow];
}

/**
 * Convert size string to bytes
 *
 * @param  string $size_str
 * @return int
 */
function ai1wm_debug_to_bytes( $size_str ) {
	switch ( substr( $size_str, -1 ) ) {
		case 'M':
		case 'm':
			return (int) $size_str * 1048576;
		case 'K':
		case 'k':
			return (int) $size_str * 1024;
		case 'G':
		case 'g':
			return (int) $size_str * 1073741824;
		default:
			return (int) $size_str;
	}
}

/**
 * Convert boolean to Yes/No label
 *
 * @param  boolean $value
 * @return string
 */
function ai1wm_debug_bool_label( $value ) {
	return $value ? 'Yes' : 'No';
}

/**
 * Read last N lines from a file
 *
 * @param  string $file  File path
 * @param  int    $lines Number of lines to read
 * @return string
 */
function ai1wm_debug_tail_file( $file, $lines = 100 ) {
	if ( ! is_file( $file ) || ! is_readable( $file ) ) {
		return '';
	}

	$handle = fopen( $file, 'r' );
	if ( ! $handle ) {
		return '';
	}

	$buffer    = array();
	$line_count = 0;

	while ( ! feof( $handle ) ) {
		$line = fgets( $handle );
		if ( $line !== false ) {
			$buffer[] = $line;
			$line_count++;

			if ( $line_count > $lines * 2 ) {
				$buffer = array_slice( $buffer, -$lines );
				$line_count = count( $buffer );
			}
		}
	}

	fclose( $handle );

	$buffer = array_slice( $buffer, -$lines );

	return implode( '', $buffer );
}

/**
 * Get known AI1WM extension map
 *
 * @return array
 */
function ai1wm_debug_get_extension_map() {
	return array(
		'AI1WMZE_PLUGIN_NAME' => 'Microsoft Azure Storage',
		'AI1WMAE_PLUGIN_NAME' => 'Backblaze B2',
		'AI1WMVE_PLUGIN_NAME' => 'Backup',
		'AI1WMBE_PLUGIN_NAME' => 'Box',
		'AI1WMIE_PLUGIN_NAME' => 'DigitalOcean Spaces',
		'AI1WMXE_PLUGIN_NAME' => 'Direct',
		'AI1WMDE_PLUGIN_NAME' => 'Dropbox',
		'AI1WMTE_PLUGIN_NAME' => 'File',
		'AI1WMFE_PLUGIN_NAME' => 'FTP',
		'AI1WMCE_PLUGIN_NAME' => 'Google Cloud Storage',
		'AI1WMGE_PLUGIN_NAME' => 'Google Drive',
		'AI1WMRE_PLUGIN_NAME' => 'Amazon Glacier',
		'AI1WMEE_PLUGIN_NAME' => 'Mega',
		'AI1WMME_PLUGIN_NAME' => 'Multisite',
		'AI1WMOE_PLUGIN_NAME' => 'OneDrive',
		'AI1WMPE_PLUGIN_NAME' => 'pCloud',
		'AI1WMNE_PLUGIN_NAME' => 'S3 Client',
		'AI1WMSE_PLUGIN_NAME' => 'Amazon S3',
		'AI1WMUE_PLUGIN_NAME' => 'Unlimited',
		'AI1WMLE_PLUGIN_NAME' => 'URL',
		'AI1WMWE_PLUGIN_NAME' => 'WebDAV',
		'AI1WMKE_PLUGIN_NAME' => 'Pro',
		'AI1WMJE_PLUGIN_NAME' => 'Wasabi',
		'AI1WMHE_PLUGIN_NAME' => 'Alibaba Cloud',
	);
}

/**
 * Normalize file path separators
 *
 * @param  string $path
 * @return string
 */
function ai1wm_debug_normalize_path( $path ) {
	$path = str_replace( '\\', '/', $path );
	$path = preg_replace( '|(?<=.)/+|', '/', $path );
	if ( ':' === substr( $path, 1, 1 ) ) {
		$path = ucfirst( $path );
	}
	return $path;
}

/**
 * Check if current user can access the debug plugin
 *
 * @return boolean
 */
function ai1wm_debug_current_user_can() {
	return current_user_can( 'manage_options' ) || current_user_can( AI1WM_DEBUG_VIEW_CAPABILITY );
}
