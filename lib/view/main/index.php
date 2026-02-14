<?php if ( ! defined( 'ABSPATH' ) ) { die( 'Kangaroos cannot fly!' ); } ?>

<div class="wrap">
	<h1>ServMask Debug</h1>

	<nav class="nav-tab-wrapper">
		<?php foreach ( $tabs as $slug => $label ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'tab', $slug, admin_url( 'admin.php?page=servmask-debug' ) ) ); ?>"
			   class="nav-tab <?php echo $current_tab === $slug ? 'nav-tab-active' : ''; ?>">
				<?php echo esc_html( $label ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<div id="ai1wm-debug-tab-content" style="margin-top: 15px;">
		<?php ai1wm_debug_render( 'tabs/' . $current_tab, array( 'ai1wm_active' => $ai1wm_active ) ); ?>
	</div>

	<div style="margin-top: 20px;">
		<a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=ai1wm_debug_download_report&format=text&nonce=' . wp_create_nonce( AI1WM_DEBUG_NONCE ) ) ); ?>"
		   class="button button-primary" id="ai1wm-debug-download-text">
			Download Report (Text)
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=ai1wm_debug_download_report&format=json&nonce=' . wp_create_nonce( AI1WM_DEBUG_NONCE ) ) ); ?>"
		   class="button" id="ai1wm-debug-download-json">
			Download Report (JSON)
		</a>
		<button type="button" class="button" id="ai1wm-debug-copy-report">
			Copy to Clipboard
		</button>
	</div>
</div>
