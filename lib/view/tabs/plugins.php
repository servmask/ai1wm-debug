<?php if ( ! defined( 'ABSPATH' ) ) { die( 'Kangaroos cannot fly!' ); } ?>

<?php $data = Ai1wm_Debug_Plugins::get_data(); ?>

<h2>AI1WM Ecosystem</h2>
<table class="widefat striped ai1wm-debug-table">
	<thead>
		<tr>
			<th>Extension</th>
			<th>Status</th>
			<th>Installed Version</th>
			<th>Latest Version</th>
			<th>Up to Date</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $data['ai1wm_ecosystem'] as $ext ) : ?>
			<tr>
				<td><?php echo esc_html( $ext['name'] ); ?></td>
				<td>
					<span class="ai1wm-debug-status ai1wm-debug-status-<?php echo $ext['installed'] ? 'ok' : 'neutral'; ?>">
						<?php echo $ext['installed'] ? 'Installed' : 'Not Installed'; ?>
					</span>
				</td>
				<td><?php echo esc_html( $ext['version'] ); ?></td>
				<td><?php echo esc_html( ! empty( $ext['latest'] ) ? $ext['latest'] : 'N/A' ); ?></td>
				<td>
					<?php if ( $ext['installed'] && ! empty( $ext['latest'] ) ) : ?>
						<span class="ai1wm-debug-status ai1wm-debug-status-<?php echo $ext['up_to_date'] ? 'ok' : 'warn'; ?>">
							<?php echo $ext['up_to_date'] ? 'Yes' : 'Outdated'; ?>
						</span>
					<?php else : ?>
						-
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php if ( ! empty( $data['known_conflicts'] ) ) : ?>
<h2>Known Conflicts</h2>
<div class="notice notice-warning inline">
	<p>The following active plugins have known interactions with AI1WM:</p>
</div>
<table class="widefat striped ai1wm-debug-table">
	<thead>
		<tr>
			<th>Plugin</th>
			<th>Details</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $data['known_conflicts'] as $conflict ) : ?>
			<tr>
				<td><?php echo esc_html( $conflict['name'] ); ?></td>
				<td><?php echo esc_html( $conflict['reason'] ); ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>

<h2>Active Plugins (<?php echo count( $data['active_plugins'] ); ?>)</h2>
<table class="widefat striped ai1wm-debug-table">
	<thead>
		<tr>
			<th>Plugin</th>
			<th>Version</th>
			<th>Author</th>
		</tr>
	</thead>
	<tbody>
		<?php if ( empty( $data['active_plugins'] ) ) : ?>
			<tr><td colspan="3">No active plugins found.</td></tr>
		<?php else : ?>
			<?php foreach ( $data['active_plugins'] as $plugin ) : ?>
				<tr>
					<td><?php echo esc_html( $plugin['name'] ); ?></td>
					<td><?php echo esc_html( $plugin['version'] ); ?></td>
					<td><?php echo esc_html( $plugin['author'] ); ?></td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>

<h2>Inactive Plugins (<?php echo count( $data['inactive_plugins'] ); ?>)</h2>
<table class="widefat striped ai1wm-debug-table">
	<thead>
		<tr>
			<th>Plugin</th>
			<th>Version</th>
			<th>Author</th>
		</tr>
	</thead>
	<tbody>
		<?php if ( empty( $data['inactive_plugins'] ) ) : ?>
			<tr><td colspan="3">No inactive plugins found.</td></tr>
		<?php else : ?>
			<?php foreach ( $data['inactive_plugins'] as $plugin ) : ?>
				<tr>
					<td><?php echo esc_html( $plugin['name'] ); ?></td>
					<td><?php echo esc_html( $plugin['version'] ); ?></td>
					<td><?php echo esc_html( $plugin['author'] ); ?></td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>

<h2>Active Theme</h2>
<table class="widefat striped ai1wm-debug-table">
	<tbody>
		<tr>
			<td><strong>Name</strong></td>
			<td><?php echo esc_html( $data['active_theme']['name'] ); ?></td>
		</tr>
		<tr>
			<td><strong>Version</strong></td>
			<td><?php echo esc_html( $data['active_theme']['version'] ); ?></td>
		</tr>
		<tr>
			<td><strong>Template</strong></td>
			<td><?php echo esc_html( $data['active_theme']['template'] ); ?></td>
		</tr>
		<tr>
			<td><strong>Child Theme</strong></td>
			<td><?php echo $data['active_theme']['is_child'] ? 'Yes (Parent: ' . esc_html( $data['active_theme']['parent'] ) . ')' : 'No'; ?></td>
		</tr>
	</tbody>
</table>

<?php if ( ! empty( $data['inactive_themes'] ) ) : ?>
<h2>Inactive Themes (<?php echo count( $data['inactive_themes'] ); ?>)</h2>
<table class="widefat striped ai1wm-debug-table">
	<thead>
		<tr>
			<th>Theme</th>
			<th>Version</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $data['inactive_themes'] as $theme ) : ?>
			<tr>
				<td><?php echo esc_html( $theme['name'] ); ?></td>
				<td><?php echo esc_html( $theme['version'] ); ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>
