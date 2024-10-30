<?php
/**
 * Display the members page.
 *
 * @package Law_1901_Association
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// To please code checker.
$list              = $list ?? new Law_1901_Association_Member_List_Table();
$after_limit_delay = $after_limit_delay ?? 0;

// Filter options. No nonce because this will be included in links as well.
// phpcs:ignore
$filter        = intval( $_REQUEST['fee-filter'] ?? -1 );
$filter_values = array(
	-1 => esc_html__( 'Filter membership fee', 'law-1901-association' ),
	0  => esc_html__( 'No membership fee', 'law-1901-association' ),
	1  => esc_html__( 'Past members', 'law-1901-association' ),
	2  => esc_html__( 'Membership fee to be paid', 'law-1901-association' ),
	3  => esc_html__( 'Membership fee paid', 'law-1901-association' ),
);

?>

<div class="law1901wrap">
	<h1 class="fullwidth"><?php esc_html_e( 'Association members', 'law-1901-association' ); ?></h1>

	<form method="post">
		<?php
			// We always want search box, even if no item is here due to filter so we temporarly add a fake entry in list and remove it after field display.
			$empty_list = ! $list->has_items();
		if ( $empty_list ) {
			$list->items = array( 'fake' );
		}
			$list->search_box( __( 'Search Users' ), 'search_id' );
		if ( $empty_list ) {
			$list->items = array();
		}

			echo '<input type="hidden" name="after_limit_delay" value="' . esc_attr( $after_limit_delay ) . '" />';
			echo '<label for="fee-filter"></label>';
			echo '<select id="fee-filter" name="fee-filter">';
		foreach ( $filter_values as $key => $value ) {
			echo '<option value="' . esc_attr( $key ) . '"';
			if ( $filter === $key ) {
				echo ' selected';
			}
			echo '>' . esc_html( $value ) . '</option>';
		}

			echo '</select>';
			echo '</form>';
			$list->display();
		?>
</div>
