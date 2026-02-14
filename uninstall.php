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

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die( 'Kangaroos cannot fly!' );
}

// Remove options
delete_option( 'ai1wm_debug_secret_key' );
delete_option( 'ai1wm_debug_access_tokens' );
delete_option( 'ai1wm_debug_logger_enabled' );
delete_option( 'ai1wm_debug_logger_verbosity' );

// Delete temporary support users
$support_users = get_users( array(
	'meta_key'   => '_ai1wm_debug_support_user',
	'meta_value' => '1',
) );

foreach ( $support_users as $user ) {
	require_once ABSPATH . 'wp-admin/includes/user.php';
	wp_delete_user( $user->ID );
}

// Remove storage directory
$storage_path = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'storage';
if ( is_dir( $storage_path ) ) {
	$iterator = new RecursiveDirectoryIterator( $storage_path, RecursiveDirectoryIterator::SKIP_DOTS );
	$files    = new RecursiveIteratorIterator( $iterator, RecursiveIteratorIterator::CHILD_FIRST );

	foreach ( $files as $file ) {
		if ( $file->isDir() ) {
			rmdir( $file->getRealPath() );
		} else {
			unlink( $file->getRealPath() );
		}
	}

	rmdir( $storage_path );
}
