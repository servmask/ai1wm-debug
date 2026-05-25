<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'Kangaroos cannot fly!' ); } ?>

<h2>Operations Diagnostics</h2>

<?php if ( ! ai1wm_debug_is_ai1wm_active() ) : ?>
	<div class="notice notice-warning inline">
		<p>All-in-One WP Migration is not active. Operations diagnostics require AI1WM to be installed and activated.</p>
	</div>
<?php else : ?>
	<?php $data = Ai1wm_Debug_Operations::get_data(); ?>

	<h3>Current Operation Status</h3>
	<table class="widefat striped ai1wm-debug-table">
		<tbody>
			<tr>
				<td><strong>Active Operation</strong></td>
				<td>
					<span class="ai1wm-debug-status ai1wm-debug-status-<?php echo $data['status']['active'] ? 'warn' : 'ok'; ?>">
						<?php echo $data['status']['active'] ? 'Yes' : 'No'; ?>
					</span>
				</td>
			</tr>
			<?php if ( $data['status']['active'] ) : ?>
				<tr>
					<td><strong>Type</strong></td>
					<td><?php echo esc_html( $data['status']['type'] ); ?></td>
				</tr>
				<tr>
					<td><strong>Message</strong></td>
					<td><?php echo esc_html( $data['status']['message'] ); ?></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<?php if ( ! empty( $data['issues'] ) ) : ?>
		<h3>Detected Issues</h3>
		<?php foreach ( $data['issues'] as $issue ) : ?>
			<div class="notice notice-<?php echo $issue['severity'] === 'error' ? 'error' : 'warning'; ?> inline">
				<p><?php echo esc_html( $issue['message'] ); ?></p>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php if ( ! empty( $data['crons'] ) ) : ?>
		<h3>AI1WM Scheduled Tasks</h3>
		<table class="widefat striped ai1wm-debug-table">
			<thead>
				<tr>
					<th>Hook</th>
					<th>Next Run</th>
					<th>Schedule</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $data['crons'] as $cron ) : ?>
					<tr>
						<td><code><?php echo esc_html( $cron['hook'] ); ?></code></td>
						<td><?php echo esc_html( $cron['next_run'] ); ?></td>
						<td><?php echo esc_html( $cron['schedule'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<?php if ( ! empty( $data['backups'] ) ) : ?>
		<h3>Backup Files</h3>
		<table class="widefat striped ai1wm-debug-table">
			<thead>
				<tr>
					<th>File</th>
					<th>Size</th>
					<th>Last Modified</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $data['backups'] as $backup ) : ?>
					<tr>
						<td><code><?php echo esc_html( $backup['name'] ); ?></code></td>
						<td><?php echo esc_html( $backup['size'] ); ?></td>
						<td><?php echo esc_html( $backup['modified'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<?php if ( ! empty( $data['storage'] ) ) : ?>
		<h3>Storage Directory Contents</h3>
		<table class="widefat striped ai1wm-debug-table">
			<thead>
				<tr>
					<th>Name</th>
					<th>Size</th>
					<th>Last Modified</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $data['storage'] as $file ) : ?>
					<tr>
						<td>
							<?php if ( $file['is_dir'] ) : ?>
								<strong><?php echo esc_html( $file['name'] ); ?>/</strong>
							<?php else : ?>
								<?php echo esc_html( $file['name'] ); ?>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $file['size'] ); ?></td>
						<td><?php echo esc_html( $file['modified'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
<?php endif; ?>
