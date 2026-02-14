<?php if ( ! defined( 'ABSPATH' ) ) { die( 'Kangaroos cannot fly!' ); } ?>

<h2>Support Access</h2>

<?php if ( ! current_user_can( 'manage_options' ) ) : ?>
	<div class="notice notice-warning inline">
		<p>Only administrators can manage support access tokens.</p>
	</div>
<?php else : ?>
	<h3>Grant Support Access</h3>
	<p>Generate a temporary login link for ServMask support staff. The link will create a temporary WordPress user with the selected access level.</p>

	<table class="form-table">
		<tr>
			<th scope="row"><label for="ai1wm-debug-access-level">Access Level</label></th>
			<td>
				<select id="ai1wm-debug-access-level">
					<option value="debug_only">Debug Plugin Only (subscriber + debug view)</option>
					<option value="full">Full Admin (administrator role)</option>
				</select>
				<p class="description">
					<strong>Debug Only:</strong> Can only access the ServMask Debug page.<br>
					<strong>Full Admin:</strong> Has full administrator access to the site.
				</p>
			</td>
		</tr>
	</table>

	<p>
		<button type="button" class="button button-primary" id="ai1wm-debug-generate-access">Generate Support Link</button>
	</p>

	<div id="ai1wm-debug-access-result" style="display:none;">
		<div class="notice notice-success inline">
			<p>Support access link generated. Share this URL with ServMask support:</p>
		</div>
		<p>
			<input type="text" id="ai1wm-debug-access-url" readonly class="large-text" />
			<button type="button" class="button" id="ai1wm-debug-copy-access-url">Copy URL</button>
		</p>
	</div>

	<?php $sessions = Ai1wm_Debug_Access::get_active_sessions(); ?>

	<h3>Active Sessions (<?php echo count( $sessions ); ?>)</h3>

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
			<button type="button" class="button" id="ai1wm-debug-revoke-all-access">Revoke All</button>
		</p>
	<?php endif; ?>
<?php endif; ?>
