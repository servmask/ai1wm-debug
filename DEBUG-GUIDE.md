# Debugging Guide

This guide walks you through using ServMask Debug to diagnose customer issues with All-in-One WP Migration. It is written for ServMask support staff and developers.

## Quick Start

1. Ask the customer to install and activate the plugin
2. Ask them to go to **ServMask Debug > Support** tab and generate an access link
3. Once they send you the link, log in and start with the **Environment** tab to get a baseline
4. Use **Download Report** to grab a snapshot you can review offline

**Without remote access:** If you only need diagnostics, ask the customer to click **Download Report (Text)** from the plugin page and send you the file. This covers environment, filesystem, database, and plugin data without needing a support link.

## Access Levels

Before requesting access, understand what each level allows:

### Debug Only

- View all diagnostic tabs (Environment, Filesystem, Database, Plugins, Logs, Help)
- View Real-time Log and Operations tabs (if AI1WM is active)
- Run exports and imports via AI1WM
- Download diagnostic reports and run logs
- **Cannot** enable/disable the real-time logger
- **Cannot** change filter overrides
- **Cannot** access any other WordPress admin pages

### Full Administrator

Everything Debug Only can do, plus:
- Enable/disable the real-time logger and select channels
- Set filter overrides (timeouts, chunk size, blocking mode, etc.)
- Access the full WordPress admin (settings, plugins, themes, posts, users)
- **Cannot** access the Support or Audit Log tabs (reserved for the site admin)

**Rule of thumb:** Request Debug Only for basic diagnostics. Request Full Administrator when you need to enable the logger, set filter overrides, or change site settings.

## First Steps for Any Ticket

Before diving into a specific issue, always check these:

1. **Environment tab** - Look for red/warning indicators. Common blockers:
   - Memory limit below 256 MB
   - Max execution time below 30 seconds
   - Missing PHP extensions (`curl`, `openssl`, `zip`)
2. **Filesystem tab** - Any directory showing "No" for writable is a problem
3. **Plugins tab** - Check for known conflicts and outdated AI1WM extensions
4. **Operations tab** (if AI1WM active) - Shows detected issues in one place

## Common Scenarios

> **Note:** Scenarios that involve enabling the real-time logger or changing filter overrides require Full Administrator access. If you only have Debug Only access, ask the customer to upgrade your access level before proceeding with those steps.

### Export Fails or Hangs

**Diagnose (any access level):**
1. Check **Environment** for low memory (< 256 MB) or short execution time (< 30 seconds)
2. Check **Filesystem** for disk space - exports need room for the full `.wpress` file
3. Check **Plugins** for known conflicts (Wordfence, Sucuri, and security plugins often block large file operations)

**Investigate (Full Administrator):**
4. Enable **Real-time Log** with the Pipeline Stages and Errors channels, then reproduce the export
5. Look at where the pipeline stops - the last logged stage tells you what failed

**If it hangs between stages (Full Administrator):**
- Increase **Completed Timeout** in Filter Overrides (default 10s, try 30 or 60)
- Enable **Blocking Mode** for exports (`http_export_blocking`) to rule out async issues

**If it fails on large sites (Full Administrator):**
- Reduce **Max Chunk Size** (default 2 MB, try 1 MB = 1048576 bytes)
- Increase **Max Chunk Retries** (default 3, try 10)

### Import Fails or Hangs

**Diagnose (any access level):**
1. Check **Environment** for low memory, short execution time, and low disk space
2. Check **Filesystem** - the `wp-content/ai1wm-backups` and `wp-content/plugins` directories must be writable

**Investigate (Full Administrator):**
3. Enable **Real-time Log** with Pipeline Stages, Status Messages, and Errors
4. Reproduce the import and check which stage fails

**If it times out during upload (Full Administrator):**
- Reduce **Max Chunk Size** to 1 MB
- Increase **http_import_timeout** (default 10s, try 30 or 60)

**If it fails after upload (any access level):**
- Check for low memory - database imports need RAM for large queries
- Look at the Errors channel in the real-time log for the actual error message
- Check **Operations** tab for orphaned temp directories from previous crashed attempts

### "Unable to export/import" Error

1. Go to **Operations** tab
2. Check for a stale status file (flagged if not modified in over 1 hour) - this means a previous operation crashed
3. Check for orphaned temp directories (flagged if older than 24 hours)
4. Look at **Logs** tab for AI1WM error logs - select the per-nonce error log files for details

### Loopback / HTTP Errors

**Diagnose (any access level):**
1. Check **Operations** tab for HTTP-related issues
2. Check **Plugins** tab for security plugins or WAFs that may be blocking internal requests

**Investigate (Full Administrator):**
3. Enable **Real-time Log** with the HTTP Loopback channel
4. Run the operation and check the logged URLs and timeouts
5. If loopback requests fail:
   - Increase HTTP timeout overrides (try 30 or 60 seconds)
   - Enable blocking mode to switch from async to synchronous requests

If a security plugin is suspected, ask the customer to temporarily deactivate it for testing.

### Slow Operations

**Diagnose (any access level):**
1. Check **Database** tab - click **Load Tables** and look at:
   - Total database size (large DBs = slow exports/imports)
   - Autoloaded options size (a few MB is already a problem)
   - Individual table sizes to find bloated tables
2. Check **Environment** for memory and execution time
3. Check **Plugins** for conflict plugins that add overhead (Jetpack sync, backup plugins running simultaneously)

**Investigate (Full Administrator):**
4. Enable **Real-time Log** with all channels to identify which pipeline stage is slow

### Permission Errors

1. Check **Filesystem** tab - every directory should show writable = "Yes"
2. Compare **Owner:Group** values across directories - they should match the PHP process user shown in Environment
3. If permissions are wrong, ask the customer or their hosting provider to set `0755` for directories
4. If `wp-content/ai1wm-backups` doesn't exist or isn't writable, AI1WM cannot store backups

### SSL / Certificate Issues

**Diagnose (any access level):**
1. Check **Environment** for OpenSSL extension and version

**Investigate (Full Administrator):**
2. Enable **SSL Verify** (`http_export_sslverify` / `http_import_sslverify`) in Filter Overrides to confirm SSL is the problem
3. If enabling SSL verify causes failures, the site has certificate issues - escalate to the customer's hosting provider

## Using Filter Overrides

Filter Overrides let you tweak AI1WM behavior without editing code. Access them from the **Real-time Log** tab. Requires Full Administrator access.

### Preset Overrides

| Override | Default | When to change |
|---|---|---|
| Completed Timeout | 10s | Operations hang between stages |
| Max Chunk Size | 2 MB | Upload failures on slow connections |
| Max Chunk Retries | 3 | Intermittent upload failures |
| HTTP Export Timeout | 10s | Export loopback timeouts |
| HTTP Import Timeout | 10s | Import loopback timeouts |
| Export Blocking Mode | off | Debug async issues, slow but reliable |
| Import Blocking Mode | off | Debug async issues, slow but reliable |
| Export SSL Verify | off | Diagnose export certificate problems |
| Import SSL Verify | off | Diagnose import certificate problems |

### Targeting Specific Stages

If an issue only occurs at a specific pipeline stage, enter the stage priority number in the "Steps" field. Click **Pipeline Reference** in the UI to see all stages:

**Export stages:** Init (5), Compatibility (10), Archive (30), Config (50), Enumerate Content/Media/Plugins/Themes (100-140), Export (150-200), Download (250), Clean (300)

**Import stages:** Upload (5), Compatibility (10), Validate (50), Check Compression (70), Confirm (100), Import (250-330), Done (350), Clean (400)

### Custom Overrides

For any `ai1wm_*` filter not covered by presets, add a custom override. Choose the value type:
- **int** - numeric values
- **string** - text values
- **bool** - true/false
- **php** - arbitrary PHP expression (use with caution)

## Using Real-time Logging

Requires Full Administrator access to enable the logger and select channels. Debug Only users can view existing log data but cannot toggle the logger.

### Channel Selection

- **Pipeline Stages** - Always enable. Shows which stage the operation is in.
- **Status Messages** - Enable for progress details (what's being processed).
- **Errors** - Always active. Shows full error details when something fails.
- **HTTP Loopback** - Enable when debugging timeout or connectivity issues.
- **File Exclusions** - Enable when debugging why specific files are or aren't in the export.
- **Params Snapshot** - Very verbose. Only enable when you need to see internal AI1WM parameters.

### Workflow

1. Enable the logger and select channels
2. Start the export/import in another browser tab
3. Watch the live stream on the Real-time Log tab
4. After the operation, the run log is saved and available from the dropdown
5. Download the run log to attach to the ticket

## Remote Debugging with Support Access

### When to Use

- **No access needed:** Ask the customer to download and send the diagnostic report
- **Debug Only:** You need to browse the diagnostic tabs yourself or run an export/import
- **Full Administrator:** You need to enable the logger, set filter overrides, or change site settings

### Workflow

1. Ask the customer to go to the **Support** tab
2. Tell them which access level you need (Debug Only or Full Administrator)
3. They select the level, confirm, and click **Generate Support Link**
4. They share the URL with you securely (not in a public forum)
5. Visit the URL to log in automatically
6. Do your debugging - all actions are recorded in the Audit Log
7. When done, tell the customer to **Revoke** your token from the Support tab

### What Gets Audited

Everything you do during a support session is logged:
- Pages you visit
- Plugins you activate/deactivate
- Theme changes
- Post/page modifications
- Option changes
- Login and logout times

The customer can review all actions in the **Audit Log** tab.

## Generating Reports

### For Tickets

1. Click **Download Report (Text)** for a human-readable report
2. Attach it to the support ticket
3. The report includes environment, filesystem, database, plugins, and operations data

### For Escalation

1. Click **Download Report (JSON)** for a machine-parseable version
2. Use **Copy to Clipboard** to paste directly into internal tools

## Checklist Before Closing a Ticket

- [ ] If you enabled the real-time logger, disable it before handing back
- [ ] Tell the customer to revoke your support access token
- [ ] Note any filter overrides you set so the customer can remove them later if needed
- [ ] Attach the diagnostic report and any relevant run logs to the ticket
