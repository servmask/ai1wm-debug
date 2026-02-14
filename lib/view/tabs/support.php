<?php if ( ! defined( 'ABSPATH' ) ) { die( 'Kangaroos cannot fly!' ); } ?>

<h2>Support Access</h2>

<?php if ( Ai1wm_Debug_Audit::is_support_session() || ! current_user_can( 'manage_options' ) ) : ?>
	<div class="notice notice-warning inline">
		<p>Only site administrators can manage support access tokens.</p>
	</div>
<?php else : ?>
	<h3>Grant Support Access</h3>
	<p>Generate a temporary login link for ServMask support staff.</p>

	<table class="form-table">
		<tr>
			<th scope="row"><label for="ai1wm-debug-access-level">Access Level</label></th>
			<td>
				<select id="ai1wm-debug-access-level">
					<option value="debug_only">Debug Plugin Only</option>
					<option value="full">Full Administrator</option>
				</select>

				<div id="ai1wm-debug-access-info-debug_only" class="ai1wm-debug-access-info">
					<p>This will:</p>
					<ul style="list-style: disc; margin-left: 20px;">
						<li>Create a temporary WordPress user with <strong>Subscriber</strong> role</li>
						<li>Grant access to the ServMask Debug plugin page</li>
						<li>Grant access to All-in-One WP Migration (export, import, backups, file uploads)</li>
						<li>No access to any other WordPress admin pages, settings, posts, or plugins</li>
						<li>All actions will be recorded in the Audit Log</li>
					</ul>
				</div>

				<div id="ai1wm-debug-access-info-full" class="ai1wm-debug-access-info" style="display:none;">
					<div class="notice notice-warning inline" style="margin: 10px 0;">
						<p><strong>Warning:</strong> This grants full control over your WordPress site.</p>
					</div>
					<p>This will:</p>
					<ul style="list-style: disc; margin-left: 20px;">
						<li>Create a temporary WordPress user with <strong>Administrator</strong> role</li>
						<li>Grant <strong>full access</strong> to the entire WordPress admin area</li>
						<li>Can modify settings, plugins, themes, posts, and users</li>
						<li>Can perform imports and exports</li>
						<li>All actions will be recorded in the Audit Log</li>
					</ul>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">Confirmation</th>
			<td>
				<label>
					<input type="checkbox" id="ai1wm-debug-access-confirm" />
					I understand that this will create a temporary user account with the selected access level and generate a login link that can be used to access this site
				</label>
			</td>
		</tr>
	</table>

	<p>
		<button type="button" class="button button-primary" id="ai1wm-debug-generate-access" disabled>Generate Support Link</button>
	</p>

	<div id="ai1wm-debug-access-result" style="display:none;">
		<div class="notice notice-success inline">
			<p>Support access link generated. Share this URL with ServMask support:</p>
		</div>
		<div class="notice notice-error inline" style="margin: 5px 0;">
			<p><strong>Do not share this link with anyone other than ServMask support staff.</strong> Anyone with this link can log in to your site with the selected access level.</p>
		</div>
		<p>
			<input type="text" id="ai1wm-debug-access-url" readonly class="large-text" />
			<button type="button" class="button" id="ai1wm-debug-copy-access-url">Copy URL</button>
		</p>
	</div>

	<?php $sessions = Ai1wm_Debug_Access::get_active_sessions(); ?>

	<h3>Active Access Tokens</h3>

	<?php if ( empty( $sessions ) ) : ?>
		<p>No active support sessions.</p>
	<?php else : ?>
		<table class="widefat striped ai1wm-debug-table">
			<thead>
				<tr>
					<th>Token</th>
					<th>Username</th>
					<th>Access Level</th>
					<th>Created</th>
					<th>Created By</th>
					<th>IP</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $sessions as $session ) : ?>
					<tr>
						<td><code><?php echo esc_html( $session['masked_token'] ); ?></code></td>
						<td><?php echo esc_html( $session['username'] ); ?></td>
						<td><?php echo esc_html( $session['level'] === 'full' ? 'Full Admin' : 'Debug Only' ); ?></td>
						<td><?php echo esc_html( $session['created_at'] ); ?></td>
						<td><?php echo esc_html( $session['created_by_name'] ); ?></td>
						<td><?php echo esc_html( $session['ip'] ); ?></td>
						<td>
							<button type="button" class="button ai1wm-debug-copy-token-url"
								data-url="<?php echo esc_attr( add_query_arg( 'ai1wm_debug_token', $session['token'], site_url( '/' ) ) ); ?>">
								Copy Link
							</button>
							<button type="button" class="button ai1wm-debug-revoke-access"
								data-token="<?php echo esc_attr( $session['token'] ); ?>">
								Revoke
							</button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p>
			<button type="button" class="button ai1wm-debug-button-danger" id="ai1wm-debug-revoke-all-access">Revoke All</button>
		</p>
	<?php endif; ?>
<?php endif; ?>
