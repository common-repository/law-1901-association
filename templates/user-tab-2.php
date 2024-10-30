<?php
/**
 * Display the second tab for the user page (from ACF).
 *
 * @package Law_1901_Association
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// To please code checker.
$user = $user ?? null;

acf_form_head();

$group = acf_get_field_group( $this->prefs['group_register'] );

acf_form(
	array(
		'post_id'      => 'user_' . $user->ID,
		'field_groups' => array( $group['ID'] ),
	)
);

?>

<div class="spacer"></div>
