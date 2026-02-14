# ServMask Debug - User Guide

This guide explains how to use the ServMask Debug plugin to diagnose issues with your WordPress site and All-in-One WP Migration.

## Getting Started

After activating the plugin, click **ServMask Debug** in the WordPress admin sidebar. The plugin opens to the Environment tab by default.

The plugin works in two modes:
- **Standalone mode**: Shows Environment, Filesystem, Database, Plugins, Logs, Support, Audit Log, and Help tabs
- **Enhanced mode**: When All-in-One WP Migration is active, two additional tabs appear: Real-time Log and Operations

---

## Tabs

### Environment

Displays detailed information about your server, PHP, and WordPress configuration.

**What to look for:**
- **PHP Version**: Must be 5.3 or higher. Newer versions are recommended.
- **Memory Limit**: Should be at least 256 MB for reliable imports/exports. Values below this show a warning indicator.
- **Max Execution Time**: Should be at least 30 seconds. A value of 0 means unlimited.
- **PHP Extensions**: All listed extensions should show as enabled. Missing extensions (especially `curl`, `openssl`, `zip`) can cause import/export failures.
- **WP_DEBUG**: Shows whether WordPress debug mode is active.
- **Cron**: Check if `DISABLE_WP_CRON` is enabled, which can affect scheduled operations.

### Filesystem

Shows the status of key directories on your site.

**What to look for:**
- **Writable**: Every directory should show "Yes". A "No" means WordPress cannot write files there, which will cause export/import failures.
- **Permissions**: Typical permissions are `0755` for directories. `0777` works but is less secure.
- **Owner:Group**: Should match the PHP process user shown in the Environment tab. Mismatches can cause permission errors.
- **Disk Space**: Ensure you have enough free space for your backup files. The status indicator turns red when free space drops below 100 MB.

### Database

Displays your database configuration and a breakdown of all tables.

Click **Load Tables** to fetch the table listing (loaded separately because it can be slow on large databases).

**What to look for:**
- **Total Size**: The overall database size. Large databases take longer to export/import.
- **Autoloaded Options Size**: If this exceeds a few MB, it can slow down your site. Common culprit for slow imports.
- **Non-prefixed Tables**: Tables not using your WordPress prefix may belong to other applications sharing the database. These are shown separately.

### Plugins

Lists all plugins and themes on your site, with special attention to the AI1WM ecosystem.

**Sections:**
- **AI1WM Ecosystem**: Shows all installed AI1WM extensions with version numbers. If version info is available, it shows whether each extension is up to date.
- **Known Conflicts**: Lists any active plugins known to interfere with AI1WM operations. Each conflict includes a severity level and explanation of the issue.
- **Active/Inactive Plugins**: Full plugin inventory with versions.
- **Themes**: Active theme (including parent theme if using a child theme) and inactive themes.

### Logs

Reads existing log files from your WordPress installation.

**Available logs:**
- **WordPress debug.log**: PHP errors and warnings (requires `WP_DEBUG_LOG` to be enabled)
- **PHP error_log**: Server-level PHP errors
- **AI1WM error logs**: Export/import error logs, including per-operation logs with randomized names
- **Run logs**: Logs created by the Real-time Log feature

**Usage:**
1. Select a log file from the dropdown
2. Click **Load** to view its contents
3. The log displays the last 100 lines by default
4. Use **Load More** to see older entries

### Real-time Log (requires AI1WM)

Captures detailed logs during AI1WM export and import operations.

**Setting up:**
1. Toggle the logger to **Enabled**
2. Select which channels to capture:
   - **Pipeline Stages**: Logs each step of the export/import process
   - **Params Snapshot**: Dumps internal parameters (very verbose, usually not needed)
   - **Status Messages**: Captures progress updates
   - **File Exclusions**: Shows which files are being excluded
   - **HTTP Loopback**: Logs internal HTTP requests
   - **Errors**: Always active, captures full error details
3. Start an export or import in AI1WM
4. Return to this tab to see the live log stream

**Run logs:**
- Each export/import operation creates a separate log file
- Use the dropdown to switch between past runs
- Download or delete individual run logs
- "Current run (live)" shows the active operation in real time

**Filter Overrides:**

Override AI1WM internal settings without editing code. Useful for debugging specific issues:

- **Completed Timeout**: Increase if operations time out between stages (try 30 or 60)
- **Max Chunk Size**: Reduce if uploads fail on slow connections (try 1048576 for 1 MB)
- **Max Chunk Retries**: Increase if you see intermittent upload failures
- **HTTP Timeouts**: Increase if loopback requests time out (try 30 or 60)
- **Blocking Mode**: Enable for more reliable but slower operations
- **SSL Verify**: Usually leave disabled unless debugging certificate issues

**Targeting specific stages:**
Enter comma-separated priority numbers in the "Steps" field to apply an override only during specific pipeline stages. Leave empty to apply to all stages. Click "Pipeline Reference" to see all stages and their priority numbers.

**Exclusion patterns:**
Add file or directory patterns (one per line) to exclude from exports. These are appended to any existing exclusions.

**Custom overrides:**
Add overrides for any `ai1wm_*` filter. Choose the value type (int, string, bool, or php for custom code) and optionally target specific stages.

Click **Save Overrides** to apply. Changes take effect on the next operation.

### Operations (requires AI1WM)

Shows the current state of AI1WM operations and detects common issues.

**Sections:**
- **Current Operation**: Whether an export/import is running and its current status
- **Detected Issues**: Warnings and errors that may affect operations:
  - Stale status file (operation may have crashed)
  - Low memory (less than 256 MB)
  - Low disk space (less than 500 MB)
  - Short execution time (less than 30 seconds)
  - Orphaned temporary directories (leftover from crashed operations)
- **Scheduled Tasks**: AI1WM cron jobs and their next run times
- **Backup Files**: List of `.wpress` backup files with sizes and dates
- **Storage Contents**: Files in the AI1WM storage directory

### Support

Generate temporary login links for ServMask support staff to access your site.

**Access levels:**

**Debug Only:**
- Creates a temporary user with Subscriber role
- Can only access the ServMask Debug plugin page
- Can access All-in-One WP Migration (export, import, backups, file uploads)
- Cannot access any other WordPress admin pages, settings, posts, or plugins
- All actions are recorded in the Audit Log

**Full Administrator:**
- Creates a temporary user with Administrator role
- Full access to the entire WordPress admin area
- Can modify settings, plugins, themes, posts, and users
- Can perform imports and exports
- All actions are recorded in the Audit Log

**How to use:**
1. Select the access level
2. Read the description of what will be granted
3. Check the "I understand" confirmation checkbox
4. Click **Generate Support Link**
5. Copy the generated URL and share it **only with ServMask support staff**
6. The support user can visit the URL to log in automatically

**Managing active tokens:**
- The **Active Access Tokens** table shows all generated links
- Use **Copy Link** to copy a token's URL again
- Use **Revoke** to deactivate a specific token (deletes the temporary user and destroys their sessions)
- Use **Revoke All** to deactivate all tokens at once

**Important:** Do not share support links with anyone other than ServMask support staff. Anyone with the link can log in to your site with the selected access level.

### Audit Log

View a chronological record of all actions taken during support sessions.

**What is logged:**
- Token creation and revocation
- Login and logout
- Page visits (admin pages, not AJAX requests)
- Plugin activations and deactivations
- Theme changes
- Post and page modifications
- User creation and deletion
- Changes to important WordPress options

**Usage:**
1. Select a session from the dropdown (shows username, access level, and date)
2. Click **Load Entries** to view the log
3. Select "All sessions" to see entries from all support sessions combined
4. Use **Delete** to remove a specific session's audit log

### Help

Displays this user guide as formatted HTML directly in the plugin. Use this tab as a quick reference without leaving WordPress.

---

## Downloadable Report

Click **Download Report** at the bottom of any tab to generate a comprehensive diagnostic report. The report includes data from all diagnostic tabs in a structured format that can be shared with ServMask support.

You can also click **Copy Report** to copy the report as formatted text to your clipboard.

---

## Frequently Asked Questions

**Q: Does this plugin affect my site's performance?**
A: The plugin only runs when you visit its admin page. The real-time logger hooks into AI1WM operations only when explicitly enabled. There is no impact on front-end performance.

**Q: What happens to support users when I deactivate the plugin?**
A: Temporary support users remain in WordPress but lose their special capabilities. When you uninstall (delete) the plugin, all temporary support users are automatically deleted.

**Q: Where is plugin data stored?**
A: All data is stored in the `storage/` folder inside the plugin directory. Configuration uses file-based storage (not the WordPress database), so settings survive AI1WM imports that replace the database.

**Q: Is the plugin included in AI1WM exports?**
A: No. The plugin excludes itself from AI1WM exports via the `ai1wm_exclude_plugins_from_export` filter. This prevents debug settings and support tokens from being transferred to other sites.

**Q: Can support users see or manage other support tokens?**
A: No. The Support and Audit Log tabs are only accessible to real site administrators. Generated support users (even those with Full Administrator access) see a "restricted" message if they navigate to these tabs.

**Q: How do I completely remove the plugin?**
A: Deactivate and delete the plugin from the Plugins page. The uninstall process automatically removes all temporary support users and the storage directory.
