<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'Kangaroos cannot fly!' ); } ?>

<?php
$content = '';

if ( ! empty( $markdown_file ) && file_exists( $markdown_file ) ) {
	$content = file_get_contents( $markdown_file );
}
?>

<?php if ( $content ) : ?>
	<div class="ai1wm-debug-help">
		<?php echo Ai1wm_Debug_Markdown::to_html( $content ); ?>
	</div>
<?php else : ?>
	<p>Documentation not found.</p>
<?php endif; ?>
