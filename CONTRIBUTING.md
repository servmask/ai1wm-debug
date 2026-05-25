# Contributing to ServMask Debug

Thanks for your interest in contributing. This plugin is the diagnostic and support access tool for the [All-in-One WP Migration](https://wordpress.org/plugins/all-in-one-wp-migration/) ecosystem, used by ServMask support staff and WordPress developers. PRs and issues are welcome.

## Getting started

The plugin is a standard WordPress plugin. To work on it locally:

1. Clone this repository into your WordPress install at `wp-content/plugins/servmask-debug/`.
2. Activate the plugin from the WordPress admin (**Plugins > Installed Plugins**).
3. Open **ServMask Debug** in the admin sidebar to use it.

Many features become richer when [All-in-One WP Migration](https://wordpress.org/plugins/all-in-one-wp-migration/) is also installed, but the core diagnostic tabs work standalone.

### Building a release ZIP

The same build script used by CI is checked into `bin/build.sh`:

```bash
bash bin/build.sh
```

This produces `dist/servmask-debug.zip` with the plugin version replaced by `git describe`. The GitHub Actions release workflow runs the equivalent script at `.github/actions/build/build.sh` when you publish a GitHub release.

## Development workflow

1. Fork the repository.
2. Create a feature branch from `master`.
3. Make your changes.
4. Verify the plugin still loads on a fresh WordPress install with the lowest supported PHP version (5.3).
5. Open a pull request against `master`.

### Commit messages

Write imperative-style subjects (`Add token expiry`, `Fix XSS in audit log`) followed by an optional body explaining the why. Keep the subject under 70 characters.

## Code style

### PHP

This plugin targets PHP 5.3 and above so it runs on the same hosts as All-in-One WP Migration. That means:

- **No namespaces** (PHP 5.3 does technically support them, but the AI1WM ecosystem avoids them for consistency).
- **No traits** (PHP 5.4+).
- **No short array syntax** (`[]`). Use `array()`.
- **No null coalescing** (`??`), no spread, no return types, no scalar type hints.
- Use the existing prefixes: classes `Ai1wm_Debug_`, constants `AI1WM_DEBUG_`, functions `ai1wm_debug_`.

Indentation is **tabs** (4-wide) across PHP, JS, CSS, and HTML, matching the rest of the AI1WM ecosystem. The `.editorconfig` file enforces this.

### Markdown and YAML

- Markdown: tabs for indentation, preserve trailing whitespace.
- YAML: 2 spaces, no tabs.

## Reporting bugs

Use the [issue tracker](https://github.com/servmask/ai1wm-debug/issues). The bug-report template asks for WordPress version, PHP version, plugin version, and whether AI1WM is installed.

For **security vulnerabilities**, do not open a public issue. See [SECURITY.md](SECURITY.md).

## License

By contributing, you agree that your contributions will be licensed under the [GNU General Public License v3.0 or later](LICENSE).
