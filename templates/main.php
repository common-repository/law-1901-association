<?php
/**
 * Display the main page for the plugin.
 *
 * @package Law_1901_Association
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// To please code checker.
$notmembers        = $notmembers ?? array();
$old               = $old ?? array();
$late              = $late ?? array();
$members           = $members ?? array();
$after_limit_delay = $after_limit_delay ?? 0;
$url               = 'admin.php?page=law-1901-association%2Fmembers&after_limit_delay=' . $after_limit_delay;

?>

<div class="law1901wrap">
	<h1 class="fullwidth"><?php esc_html_e( 'Association administration', 'law-1901-association' ); ?></h1>

	<table class="wp-list-table widefat fixed striped table-view-list-members">
		<thead>
			<tr>
				<th colspan="4"><?php esc_html_e( 'Members sorted by membership fee', 'law-1901-association' ); ?></th>
			</tr>
			<tr>
				<th>
					<a href="<?php echo esc_url( $url ); ?>&fee-filter=0">
					<?php esc_html_e( 'No membership fee', 'law-1901-association' ); ?>
					</a>
				</th>
				<th>
					<a href="<?php echo esc_url( $url ); ?>&fee-filter=1">
					<?php esc_html_e( 'Past members', 'law-1901-association' ); ?>
					</a>
				</th>
				<th>
					<a href="<?php echo esc_url( $url ); ?>&fee-filter=2">
					<?php esc_html_e( 'Membership fee to be paid', 'law-1901-association' ); ?>
					</a>
				</th>
				<th>
					<a href="<?php echo esc_url( $url ); ?>&fee-filter=3">
					<?php esc_html_e( 'Membership fee paid', 'law-1901-association' ); ?>
					</a>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<a href="<?php echo esc_url( $url ); ?>&fee-filter=0">
						<?php echo count( $notmembers ); ?>
					</a>
				</td>
				<td>
					<a href="<?php echo esc_url( $url ); ?>&fee-filter=1">
					<?php echo count( $old ); ?>
					</a>
				</td>
				<td>
					<a href="<?php echo esc_url( $url ); ?>&fee-filter=2">
					<?php echo count( $late ); ?>
					</a>
				</td>
				<td>
					<a href="<?php echo esc_url( $url ); ?>&fee-filter=3">
					<?php echo count( $members ); ?>
					</a>
				</td>
			</tr>
		</tbody>
	</table>
</div>
