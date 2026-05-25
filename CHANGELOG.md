# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-05-25

First public release. Replaces `servmask-agent` and `servmask-client` as a single diagnostic and support access plugin for the All-in-One WP Migration ecosystem.

### Added

- Eleven diagnostic surfaces in a single admin page:
  - **Environment**: PHP, server, and WordPress runtime details, plus `upload_tmp_dir` and `file_uploads`.
  - **Filesystem**: per-directory permission, writability, ownership, and disk space.
  - **Database**: MySQL or MariaDB version, charset, collation, per-table breakdown, autoload weight.
  - **Plugins**: active and inactive plugins with versions, AI1WM ecosystem scan with installed vs latest comparison, known-conflict detection.
  - **Logs**: WordPress debug log, PHP error log, AI1WM error logs, plugin run logs.
  - **Real-time Log**: live export and import stream with six logging channels. AI1WM-dependent.
  - **Schedules**: scheduled backup diagnostics for the Pro and legacy extensions. AI1WM-dependent.
  - **Operations**: current operation status, detected issues, backup files, storage contents. AI1WM-dependent.
  - **Support**: time-limited token generation (Debug Only or Full Admin) with audit trail.
  - **Audit Log**: per-token session log with page visits, capability use, and option changes.
  - **Help**: built-in user guide rendered from `USER-GUIDE.md`.
- Filter override engine for AI1WM debugging without code changes: 9 preset overrides (chunk size, retries, timeouts, blocking mode, SSL verification), targeted pipeline stages, exclusion patterns, custom typed filters.
- Downloadable diagnostic reports in text or JSON.
- Email notification to the token creator (or site admin) when a support token is used, with time, IP, user agent, and a one-click revocation link.
- Public docs landing page at [debug.wp-migration.com](https://debug.wp-migration.com), served from `/docs` via GitHub Pages. Live-fetches the latest release and recent releases from the GitHub API.
- "Support Access Best Practices" section in the README covering token hygiene.
- Standalone design: core diagnostics work without All-in-One WP Migration installed.
- Build scripts and GitHub Actions release workflow.
- `LICENSE` file with the canonical GPL-3.0 text.
- `SECURITY.md` with the private vulnerability disclosure process via GitHub security advisories.

### Security

- Support tokens stored as SHA-256 hashes; plaintext never persisted.
- 72-hour token expiry, rate-limited login (5 attempts per 15 minutes per IP).
- All AJAX endpoints require nonce and capability checks; destructive ones additionally require `manage_options`.
- `download_realtime_log` filename validation enforces `/^(export|import)-\d{8}-\d{6}\.php$/`, matching `delete_run_log`.
- PHP `eval` in custom filter overrides gated behind the `AI1WM_DEBUG_ALLOW_EVAL` constant.
- `Referrer-Policy: no-referrer` on token login redirect.
- Audit log sanitized against injection.

[1.0.0]: https://github.com/servmask/ai1wm-debug/releases/tag/1.0.0
