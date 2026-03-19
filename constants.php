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

// Version
define( 'AI1WM_DEBUG_VERSION', 'develop' );

// Plugin name
define( 'AI1WM_DEBUG_PLUGIN_NAME', 'servmask-debug' );

// Paths
define( 'AI1WM_DEBUG_LIB_PATH', AI1WM_DEBUG_PATH . DIRECTORY_SEPARATOR . 'lib' );
define( 'AI1WM_DEBUG_CONTROLLER_PATH', AI1WM_DEBUG_LIB_PATH . DIRECTORY_SEPARATOR . 'controller' );
define( 'AI1WM_DEBUG_MODEL_PATH', AI1WM_DEBUG_LIB_PATH . DIRECTORY_SEPARATOR . 'model' );
define( 'AI1WM_DEBUG_VIEW_PATH', AI1WM_DEBUG_LIB_PATH . DIRECTORY_SEPARATOR . 'view' );
define( 'AI1WM_DEBUG_ASSETS_PATH', AI1WM_DEBUG_VIEW_PATH . DIRECTORY_SEPARATOR . 'assets' );

// Storage (inside plugin directory, excluded from AI1WM export/import)
define( 'AI1WM_DEBUG_STORAGE_PATH', AI1WM_DEBUG_PATH . DIRECTORY_SEPARATOR . 'storage' );

// Log settings
define( 'AI1WM_DEBUG_LOGS_PATH', AI1WM_DEBUG_STORAGE_PATH . DIRECTORY_SEPARATOR . 'logs' );
define( 'AI1WM_DEBUG_LOG_MAX_SIZE', 10485760 ); // 10 MB

// Config keys (stored in config.json)
define( 'AI1WM_DEBUG_SECRET_KEY_OPTION', 'secret_key' );
define( 'AI1WM_DEBUG_ACCESS_TOKENS_OPTION', 'access_tokens' );
define( 'AI1WM_DEBUG_LOGGER_ENABLED_OPTION', 'logger_enabled' );
define( 'AI1WM_DEBUG_LOGGER_VERBOSITY_OPTION', 'logger_verbosity' );
define( 'AI1WM_DEBUG_LOGGER_CHANNELS_OPTION', 'logger_channels' );
define( 'AI1WM_DEBUG_FILTER_OVERRIDES_OPTION', 'filter_overrides' );

// Verbosity levels
define( 'AI1WM_DEBUG_VERBOSITY_ERROR', 1 );
define( 'AI1WM_DEBUG_VERBOSITY_WARNING', 2 );
define( 'AI1WM_DEBUG_VERBOSITY_INFO', 3 );
define( 'AI1WM_DEBUG_VERBOSITY_DEBUG', 4 );

// Capabilities
define( 'AI1WM_DEBUG_VIEW_CAPABILITY', 'ai1wm_debug_view' );

// Nonce
define( 'AI1WM_DEBUG_NONCE', 'ai1wm_debug_nonce' );
