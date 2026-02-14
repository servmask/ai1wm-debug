<?php if ( ! defined( 'ABSPATH' ) ) { die( 'Kangaroos cannot fly!' ); } ?>

<?php $data = Ai1wm_Debug_Database::get_data(); ?>

<h2>Database Overview</h2>
<table class="widefat striped ai1wm-debug-table">
	<thead>
		<tr>
			<th>Setting</th>
			<th>Value</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>Database Version</td>
			<td><?php echo esc_html( $data['version'] ); ?></td>
		</tr>
		<tr>
			<td>Database Name</td>
			<td><?php echo esc_html( $data['name'] ); ?></td>
		</tr>
		<tr>
			<td>Database Host</td>
			<td><?php echo esc_html( $data['host'] ); ?></td>
		</tr>
		<tr>
			<td>Charset</td>
			<td><?php echo esc_html( $data['charset'] ); ?></td>
		</tr>
		<tr>
			<td>Collation</td>
			<td><?php echo esc_html( $data['collate'] ); ?></td>
		</tr>
		<tr>
			<td>Table Prefix</td>
			<td><?php echo esc_html( $data['prefix'] ); ?></td>
		</tr>
		<tr>
			<td>Total Database Size</td>
			<td><?php echo esc_html( $data['total_size'] ); ?></td>
		</tr>
		<tr>
			<td>Autoloaded Options Size</td>
			<td><?php echo esc_html( $data['autoloaded_size'] ); ?></td>
		</tr>
	</tbody>
</table>

<h2>Database Tables (<?php echo esc_html( $data['prefix'] ); ?>*)</h2>
<p>
	<button type="button" class="button" id="ai1wm-debug-load-tables">Load Tables</button>
	<span id="ai1wm-debug-tables-loading" style="display:none;">Loading...</span>
</p>
<table class="widefat striped ai1wm-debug-table" id="ai1wm-debug-tables-list" style="display:none;">
	<thead>
		<tr>
			<th>Table Name</th>
			<th>Engine</th>
			<th>Rows</th>
			<th>Data Size</th>
			<th>Index Size</th>
			<th>Total Size</th>
		</tr>
	</thead>
	<tbody>
	</tbody>
</table>

<div id="ai1wm-debug-non-prefixed-wrapper" style="display:none; margin-top: 2em;">
	<h2>Non-Prefixed Tables</h2>
	<p class="description">Tables that do not use the WordPress prefix <code><?php echo esc_html( $data['prefix'] ); ?></code>. These may belong to other applications or old WordPress installations.</p>
	<table class="widefat striped ai1wm-debug-table" id="ai1wm-debug-non-prefixed-list">
		<thead>
			<tr>
				<th>Table Name</th>
				<th>Engine</th>
				<th>Rows</th>
				<th>Data Size</th>
				<th>Index Size</th>
				<th>Total Size</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>
