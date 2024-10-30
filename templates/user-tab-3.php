<?php
/**
 * Display the third tab for the user page (from ACF but restricted to admins).
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

$group = acf_get_field_group( $this->prefs['group_administrative'] );

acf_form(
	array(
		'post_id'      => 'user_' . $user->ID,
		'field_groups' => array( $group['ID'] ),
	)
);

?>

<div class="spacer"></div>
