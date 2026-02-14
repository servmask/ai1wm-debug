# ServMask Debug

Universal debug and support tool for the ServMask plugin ecosystem. Replaces `servmask-agent` and `servmask-client` as the single diagnostic and support access plugin.

## Overview

ServMask Debug is a standalone WordPress plugin that provides comprehensive diagnostics for sites running All-in-One WP Migration and its extensions. Core diagnostic tabs work without AI1WM installed; additional tabs appear when AI1WM is active.

## Requirements

- WordPress 4.0+
- PHP 5.3+
- No external dependencies

## Installation

1. Download the `servmask-debug` ZIP
2. Upload via **Plugins > Add New > Upload Plugin**, or extract to `wp-content/plugins/`
3. Activate the plugin
4. Navigate to **ServMask Debug** in the admin sidebar

## Features

### Diagnostic Tabs (always available)

**Environment** - PHP version, SAPI, memory limits, execution time, extensions (curl, openssl, zip, mbstring, etc.), WordPress version, debug constants, multisite status, cron config, server OS and software.

**Filesystem** - Key directory permissions, writability, ownership for ABSPATH, wp-content, plugins, themes, uploads, and AI1WM storage/backup paths. Disk space usage with status indicators. Temp directory check.

**Database** - MySQL/MariaDB version, charset, collation, table prefix. Per-table breakdown (engine, rows, data/index size) loaded via AJAX. Tables split into prefixed (WordPress) and non-prefixed groups. Total DB size and autoloaded options size.

**Plugins** - Active and inactive plugins with versions. Active theme and child theme detection. Full AI1WM ecosystem scan (base plugin + 24 known extensions) with installed vs. latest version comparison. Known plugin conflict detection (W3 Total Cache, Wordfence, Sucuri, Jetpack, etc.) with severity and explanation.

**Logs** - Discovers and displays WordPress debug.log, PHP error_log, AI1WM error logs (including per-nonce `error-log-*.log` files), and plugin run logs. Paginated viewer with file size display.

**Support** - Generate temporary login links for ServMask support staff with two access levels:
- **Debug Only**: Subscriber role with access to the debug plugin page and AI1WM (export/import/backups)
- **Full Administrator**: Full admin access to the entire WordPress site

Active access tokens table with Copy Link and Revoke controls. Confirmation checkbox required before generating. All support sessions are audit-logged.

**Audit Log** - Chronological log of all actions taken during support sessions. Per-token log files. Tracks page visits, plugin activations, theme changes, post modifications, user changes, option updates, login/logout. Session selector with friendly labels (username, access level, date). Delete individual audit logs.

**Help** - Displays the user guide (USER-GUIDE.md) as formatted HTML directly in the admin UI. Available to all users including support staff.

### AI1WM-Dependent Tabs (appear when AI1WM is active)

**Real-time Log** - Live streaming log of AI1WM export/import operations. Six logging channels: Pipeline Stages, Params Snapshot, Status Messages, File Exclusions, HTTP Loopback, Errors. Per-run log files with selector, download, and delete. Auto-scroll toggle. Enable/disable logger and select channels.

**Operations** - Current operation status (active/type/message). Detected issues with severity (stale status, low memory, low disk, short execution time, orphaned temp dirs). AI1WM scheduled cron jobs. Backup file listing. Storage directory contents.

### Filter Overrides (Real-time Log tab)

Override AI1WM filters for debugging without code changes:
- 9 preset overrides: chunk size, retry count, timeouts, blocking mode, SSL verification
- Target specific pipeline stages by priority number
- Exclusion patterns for content, media, plugins, themes
- Custom filter overrides with int/string/bool/PHP types
- Changes take effect on the next export/import operation

### Downloadable Report

One-click report generation aggregating all diagnostic data. Available as JSON download or copyable text format. Includes environment, filesystem, database, plugins, ecosystem, conflicts, and operations data.

## Architecture

```
servmask-debug/
  servmask-debug.php          # Plugin entry point
  constants.php                # All AI1WM_DEBUG_* constants
  functions.php                # Helper functions (ai1wm_debug_*)
  loader.php                   # Explicit require_once loading
  uninstall.php                # Cleanup on uninstall
  USER-GUIDE.md                # End-user documentation (shown in Help tab)
  DEBUG-GUIDE.md               # Internal debugging workflows for support staff
  lib/
    controller/
      class-ai1wm-debug-main-controller.php   # Menu, hooks, routing
      class-ai1wm-debug-ajax-controller.php   # AJAX endpoints
    model/
      class-ai1wm-debug-access.php            # Support access tokens
      class-ai1wm-debug-audit.php             # Support session audit
      class-ai1wm-debug-config.php            # File-based config
      class-ai1wm-debug-database.php          # Database diagnostics
      class-ai1wm-debug-environment.php       # PHP/WP/server info
      class-ai1wm-debug-filesystem.php        # Directory checks
      class-ai1wm-debug-filters.php           # Filter override engine
      class-ai1wm-debug-logger.php            # Real-time operation logger
      class-ai1wm-debug-logs.php              # Log file discovery
      class-ai1wm-debug-markdown.php          # Markdown to HTML converter
      class-ai1wm-debug-operations.php        # AI1WM operations status
      class-ai1wm-debug-plugins.php           # Plugin/theme analysis
      class-ai1wm-debug-report.php            # Report generation
      class-ai1wm-debug-security.php          # Capabilities and keys
    view/
      main/index.php                           # Page wrapper with tabs
      tabs/                                    # Tab view templates
      assets/css/servmask-debug.css
      assets/js/servmask-debug.js
  storage/                     # Runtime data (excluded from AI1WM exports)
    config.php                 # Plugin settings (JSON with PHP guard)
    logs/                      # Run logs and audit logs
```

## Technical Details

### Storage

All runtime data is stored in `storage/` inside the plugin directory. This directory is:
- Excluded from AI1WM exports via the `ai1wm_exclude_plugins_from_export` filter
- Protected with `.htaccess`, `web.config`, `index.php`, and `index.html`
- Configuration uses file-based JSON storage (not wp_options) so settings survive AI1WM imports

### Security

- Admin pages require `manage_options` capability (administrators)
- Support users get a custom `ai1wm_debug_view` capability
- Debug-only support users are Subscribers with targeted capabilities (`export`, `import`, `upload_files`, `ai1wm_debug_view`)
- Support and Audit tabs are blocked for generated support users (even full admin)
- All AJAX endpoints verify nonce + capability
- Temporary support users are marked with `_ai1wm_debug_support_user` meta
- Token-based login sets `_ai1wm_debug_support_session` meta for audit tracking
- Audit log files and config use PHP guards (`<?php exit; ?>`) to prevent direct access
- Log and config directories have web server deny rules

### Naming Conventions

- Constants: `AI1WM_DEBUG_*`
- Classes: `Ai1wm_Debug_*`
- Functions: `ai1wm_debug_*()`
- PHP 5.3 compatible: no namespaces, no traits, no short array syntax

## Uninstall

On plugin deletion, `uninstall.php` removes:
- All temporary support users (identified by `_ai1wm_debug_support_user` meta)
- The `storage/` directory and all its contents

## License

GNU General Public License v3.0 or later.

Copyright (C) 2014-2026 ServMask Inc.
