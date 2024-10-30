<?php
/**
 * Display the tabs for the settings page.
 *
 * @package Law_1901_Association
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// To please code checker.
$url         = $url ?? '';
$current_tab = $current_tab ?? 1;

?>

<div class="title">
	<h1 class="fullwidth settings"><?php esc_html_e( 'Law-1901-association plugin settings', 'law-1901-association' ); ?></h1>
	<div class="tabs">
		<a
			href="<?php echo esc_url( $url ); ?>&tab=1" class="tab
							<?php
							if ( 1 === $current_tab ) {
								echo ' active';
							}
							?>
		"
		><?php esc_html_e( 'General settings', 'law-1901-association' ); ?></a>
		<a
			href="<?php echo esc_url( $url ); ?>&tab=2" class="tab
							<?php
							if ( 2 === $current_tab ) {
								echo ' active';
							}
							?>
		"
		><?php esc_html_e( 'ACF groups', 'law-1901-association' ); ?></a>
		<a
			href="<?php echo esc_url( $url ); ?>&tab=3" class="tab
							<?php
							if ( 3 === $current_tab ) {
								echo ' active';
							}
							?>
		"
		><?php esc_html_e( 'Add-ons', 'law-1901-association' ); ?></a>
	</div>
</div>
