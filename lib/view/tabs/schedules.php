<?php if ( ! defined( 'ABSPATH' ) ) { die( 'Kangaroos cannot fly!' ); } ?>

<h2>Scheduled Tasks</h2>

<?php if ( ! ai1wm_debug_is_ai1wm_active() ) : ?>
	<div class="notice notice-warning inline">
		<p>All-in-One WP Migration is not active. Schedule diagnostics require AI1WM to be installed and activated.</p>
	</div>
<?php else : ?>
	<?php $data = Ai1wm_Debug_Schedules::get_data(); ?>

	<?php if ( ! empty( $data['issues'] ) ) : ?>
		<h3>Issues Detected</h3>
		<?php foreach ( $data['issues'] as $issue ) : ?>
			<div class="notice notice-<?php echo $issue['severity'] === 'error' ? 'error' : ( $issue['severity'] === 'info' ? 'info' : 'warning' ); ?> inline">
				<p><?php echo esc_html( $issue['message'] ); ?></p>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php if ( ! empty( $data['pro_events'] ) ) : ?>
		<h3>Schedule Events (Pro)</h3>
		<table class="widefat striped ai1wm-debug-table">
			<thead>
				<tr>
					<th>Title</th>
					<th>Type</th>
					<th>Storage</th>
					<th>Status</th>
					<th>Schedule</th>
					<th>Next Run</th>
					<th>Last Run</th>
					<th>Retention</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $data['pro_events'] as $event ) : ?>
					<tr>
						<td>
							<strong><?php echo esc_html( $event['title'] ); ?></strong>
							<?php if ( $event['incremental'] ) : ?>
								<br /><small>Incremental</small>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $event['type'] ); ?></td>
						<td><?php echo esc_html( $event['storage'] ); ?></td>
						<td>
							<span class="ai1wm-debug-status ai1wm-debug-status-<?php
								if ( $event['is_running'] ) {
									echo 'warn';
								} elseif ( $event['status'] === 'Enabled' ) {
									echo 'ok';
								} else {
									echo 'neutral';
								}
							?>">
								<?php
								if ( $event['is_running'] ) {
									echo 'Running';
								} else {
									echo esc_html( $event['status'] );
								}
								?>
							</span>
						</td>
						<td><?php echo esc_html( $event['schedule'] ); ?></td>
						<td><?php echo $event['next_run'] ? esc_html( $event['next_run'] ) : '<em>Not scheduled</em>'; ?></td>
						<td>
							<span class="ai1wm-debug-status ai1wm-debug-status-<?php
								if ( $event['last_run'] === 'Failed' ) {
									echo 'error';
								} elseif ( $event['last_run'] === 'Success' ) {
									echo 'ok';
								} elseif ( $event['last_run'] === 'Running' ) {
									echo 'warn';
								} else {
									echo 'neutral';
								}
							?>">
								<?php echo esc_html( $event['last_run'] ); ?>
							</span>
						</td>
						<td><?php echo esc_html( $event['retention'] ); ?></td>
					</tr>
					<?php if ( ! empty( $event['recent_logs'] ) ) : ?>
						<tr>
							<td colspan="8" style="padding: 0;">
								<details style="padding: 8px 10px;">
									<summary style="cursor: pointer; font-size: 12px;">Recent log entries (<?php echo count( $event['recent_logs'] ); ?>)</summary>
									<pre class="ai1wm-debug-log-content" style="margin: 8px 0 0; max-height: 150px; overflow: auto; font-size: 12px;"><?php
										foreach ( $event['recent_logs'] as $log ) {
											$log = (array) $log;
											$time    = isset( $log['time'] ) ? date( 'Y-m-d H:i:s', $log['time'] ) : '?';
											$status  = isset( $log['status'] ) ? $log['status'] : '?';
											$message = isset( $log['message'] ) && $log['message'] ? ' - ' . $log['message'] : '';
											echo esc_html( '[' . $time . '] ' . $status . $message ) . "\n";
										}
									?></pre>
								</details>
							</td>
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<?php if ( ! empty( $data['legacy_schedules'] ) ) : ?>
		<h3>Legacy Extension Schedules</h3>
		<p class="description">These are storage-specific scheduled exports registered by individual AI1WM extensions.</p>
		<table class="widefat striped ai1wm-debug-table">
			<thead>
				<tr>
					<th>Storage</th>
					<th>Interval</th>
					<th>Next Run</th>
					<th>Status</th>
					<th>Hook</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $data['legacy_schedules'] as $sched ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $sched['storage'] ); ?></strong></td>
						<td><?php echo esc_html( $sched['interval'] ); ?></td>
						<td><?php echo esc_html( $sched['next_run'] ); ?></td>
						<td>
							<span class="ai1wm-debug-status ai1wm-debug-status-<?php echo $sched['is_overdue'] ? 'warn' : 'ok'; ?>">
								<?php echo $sched['is_overdue'] ? 'Overdue' : 'Scheduled'; ?>
							</span>
						</td>
						<td><code style="font-size: 11px;"><?php echo esc_html( $sched['hook'] ); ?></code></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<?php if ( empty( $data['pro_events'] ) && empty( $data['legacy_schedules'] ) ) : ?>
		<div class="notice notice-info inline">
			<p>No scheduled tasks found. Scheduled backups require AI1WM Pro or a storage extension with scheduling enabled.</p>
		</div>
	<?php endif; ?>

	<h3>All AI1WM Cron Entries</h3>
	<?php if ( ! empty( $data['cron_entries'] ) ) : ?>
		<p class="description">Raw WP-Cron entries containing "ai1wm" hooks.</p>
		<table class="widefat striped ai1wm-debug-table">
			<thead>
				<tr>
					<th>Hook</th>
					<th>Next Run</th>
					<th>Recurrence</th>
					<th>Status</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $data['cron_entries'] as $entry ) : ?>
					<tr>
						<td><code style="font-size: 11px;"><?php echo esc_html( $entry['hook'] ); ?></code></td>
						<td><?php echo esc_html( $entry['next_run'] ); ?></td>
						<td><?php echo esc_html( $entry['schedule'] ); ?></td>
						<td>
							<span class="ai1wm-debug-status ai1wm-debug-status-<?php echo $entry['is_overdue'] ? 'warn' : 'ok'; ?>">
								<?php echo $entry['is_overdue'] ? 'Overdue' : 'OK'; ?>
							</span>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php else : ?>
		<p>No AI1WM cron entries found in the WP-Cron table.</p>
	<?php endif; ?>
<?php endif; ?>
