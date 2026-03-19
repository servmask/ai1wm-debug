<?php
/**
 * Plugin Name: ServMask Debug
 * Plugin URI: https://servmask.com/
 * Description: Universal debug and support tool for the ServMask plugin ecosystem
 * Author: ServMask
 * Author URI: https://servmask.com/
 * Version: develop
 * Network: True
 *
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

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Kangaroos cannot fly!' );
}

// Plugin Basename
define( 'AI1WM_DEBUG_PLUGIN_BASENAME', basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) );

// Plugin Path
define( 'AI1WM_DEBUG_PATH', dirname( __FILE__ ) );

// Plugin URL
define( 'AI1WM_DEBUG_URL', plugins_url( '', __FILE__ ) );

require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'constants.php';
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'loader.php';
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'functions.php';

$ai1wm_debug_main_controller = new Ai1wm_Debug_Main_Controller();
