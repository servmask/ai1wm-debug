<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'Kangaroos cannot fly!' ); } ?>

<?php $data = Ai1wm_Debug_Environment::get_data(); ?>

<h2>PHP Information</h2>
<table class="widefat striped ai1wm-debug-table">
	<thead>
		<tr>
			<th>Setting</th>
			<th>Value</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $data['php'] as $row ) : ?>
			<tr>
				<td><?php echo esc_html( $row['label'] ); ?></td>
				<td><?php echo esc_html( $row['value'] ); ?></td>
				<td>
					<span class="ai1wm-debug-status ai1wm-debug-status-<?php echo $row['status'] ? 'ok' : 'warn'; ?>">
						<?php echo $row['status'] ? 'OK' : 'Warning'; ?>
					</span>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<h2>WordPress Information</h2>
<table class="widefat striped ai1wm-debug-table">
	<thead>
		<tr>
			<th>Setting</th>
			<th>Value</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $data['wp'] as $row ) : ?>
			<tr>
				<td><?php echo esc_html( $row['label'] ); ?></td>
				<td><?php echo esc_html( $row['value'] ); ?></td>
				<td>
					<span class="ai1wm-debug-status ai1wm-debug-status-<?php echo $row['status'] ? 'ok' : 'warn'; ?>">
						<?php echo $row['status'] ? 'OK' : 'Warning'; ?>
					</span>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<h2>Server Information</h2>
<table class="widefat striped ai1wm-debug-table">
	<thead>
		<tr>
			<th>Setting</th>
			<th>Value</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $data['server'] as $row ) : ?>
			<tr>
				<td><?php echo esc_html( $row['label'] ); ?></td>
				<td><?php echo esc_html( $row['value'] ); ?></td>
				<td>
					<span class="ai1wm-debug-status ai1wm-debug-status-<?php echo $row['status'] ? 'ok' : 'warn'; ?>">
						<?php echo $row['status'] ? 'OK' : 'Warning'; ?>
					</span>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
