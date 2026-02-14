<?php if ( ! defined( 'ABSPATH' ) ) { die( 'Kangaroos cannot fly!' ); } ?>

<?php $data = Ai1wm_Debug_Filesystem::get_data(); ?>

<h2>Key Directories</h2>
<table class="widefat striped ai1wm-debug-table">
	<thead>
		<tr>
			<th>Directory</th>
			<th>Path</th>
			<th>Exists</th>
			<th>Writable</th>
			<th>Permissions</th>
			<th>Owner</th>
			<th>Group</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $data['directories'] as $dir ) : ?>
			<tr>
				<td><?php echo esc_html( $dir['label'] ); ?></td>
				<td><code><?php echo esc_html( $dir['path'] ); ?></code></td>
				<td>
					<span class="ai1wm-debug-status ai1wm-debug-status-<?php echo $dir['exists'] ? 'ok' : 'error'; ?>">
						<?php echo $dir['exists'] ? 'Yes' : 'No'; ?>
					</span>
				</td>
				<td>
					<span class="ai1wm-debug-status ai1wm-debug-status-<?php echo $dir['writable'] ? 'ok' : 'error'; ?>">
						<?php echo $dir['writable'] ? 'Yes' : 'No'; ?>
					</span>
				</td>
				<td><code><?php echo esc_html( $dir['perms'] ); ?></code></td>
				<td><?php echo esc_html( $dir['owner'] ); ?></td>
				<td><?php echo esc_html( $dir['group'] ); ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<h2>Disk Space</h2>
<table class="widefat striped ai1wm-debug-table">
	<thead>
		<tr>
			<th>Metric</th>
			<th>Value</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>Free Space</td>
			<td><?php echo esc_html( $data['disk']['free'] ); ?></td>
			<td>
				<span class="ai1wm-debug-status ai1wm-debug-status-<?php echo $data['disk']['status'] ? 'ok' : 'warn'; ?>">
					<?php echo $data['disk']['status'] ? 'OK' : 'Low'; ?>
				</span>
			</td>
		</tr>
		<tr>
			<td>Total Space</td>
			<td><?php echo esc_html( $data['disk']['total'] ); ?></td>
			<td>
				<span class="ai1wm-debug-status ai1wm-debug-status-ok">OK</span>
			</td>
		</tr>
	</tbody>
</table>

<h2>Temp Directory</h2>
<table class="widefat striped ai1wm-debug-table">
	<thead>
		<tr>
			<th>Setting</th>
			<th>Value</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>Path</td>
			<td><code><?php echo esc_html( $data['temp']['path'] ); ?></code></td>
			<td>
				<span class="ai1wm-debug-status ai1wm-debug-status-ok">OK</span>
			</td>
		</tr>
		<tr>
			<td>Writable</td>
			<td><?php echo $data['temp']['writable'] ? 'Yes' : 'No'; ?></td>
			<td>
				<span class="ai1wm-debug-status ai1wm-debug-status-<?php echo $data['temp']['writable'] ? 'ok' : 'error'; ?>">
					<?php echo $data['temp']['writable'] ? 'OK' : 'Error'; ?>
				</span>
			</td>
		</tr>
	</tbody>
</table>
