# Security Policy

## Reporting a Vulnerability

**Do not open public issues for security vulnerabilities.**

Please report vulnerabilities via [GitHub's private vulnerability reporting](https://github.com/servmask/ai1wm-debug/security/advisories/new).

### Expected response time

We aim to respond within 7 days.

### Scope

ServMask Debug is a WordPress plugin that exposes diagnostic data and can grant temporary administrator-level access via time-limited support tokens. The primary security concerns are:

- Authentication bypass on the token login flow (`?ai1wm_debug_token=...`)
- Privilege escalation by a Debug Only support user
- Path traversal, arbitrary file read, or RCE in any AJAX endpoint
- Cross-site scripting (XSS) in any admin view or downloadable report
- Cross-site request forgery (CSRF) on any state-changing endpoint
- Insecure handling of stored support tokens or audit logs
- Weaknesses in the eval-gated filter override mechanism (when `AI1WM_DEBUG_ALLOW_EVAL` is defined)

### Out of scope

- Issues that require the attacker to already have administrator access
- Social-engineering attacks that depend on tricking a site admin into pasting a valid support URL (operational hygiene is covered in the README)
- Vulnerabilities in WordPress core, the All-in-One WP Migration plugin, or other third-party plugins
- Self-XSS and other low-impact theoretical findings without a clear attack path
