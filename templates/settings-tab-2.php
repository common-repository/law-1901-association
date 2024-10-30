<?php
/**
 * Display the first tab for the settings page (ACF linking).
 *
 * @package Law_1901_Association
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// load acf groups.
$groups = acf_get_field_groups();

/**
 * Render groups as select options.
 *
 * @param array  $groups List of groups.
 * @param string $selected Selected group.
 *
 * @return void
 */
function law1901_display_groups( array $groups, string $selected ) {
	foreach ( $groups as $group ) {
		echo '<option' .
			' value="' . esc_attr( $group['key'] ) . '" ' .
			( $group['key'] === $selected ? ' selected' : '' ) .
			'>' .
			esc_html( $group['title'] ) .
			'</option>';
	}
}

/**
 * Display checkbox for group location.
 *
 * @param string $group The group key.
 * @param array  $prefs The preferences.
 * @param array  $locations The locations.
 *
 * @return void
 */
function law1901_set_group_location( string $group, array $prefs, array $locations ) {
	// If location is OK, do not display it.
	if ( acf_get_field_group( $prefs[ $group ] )['location'] === $locations[ $group ] ) {
		return;
	}

	// Display location check, pre-checked.
	echo '<br/><br/><label><input type="checkbox" name="location_' . esc_attr( $group ) . '" checked>' . esc_html__( 'Define group display rules', 'law-1901-association' ) . '</label>';
}

?>

<form method="post">
	<div class="law1901wrap settings3">
		<h2><?php esc_html_e( '1) Last name and first name', 'law-1901-association' ); ?></h2>

		<span class="italic">
			<?php esc_html_e( 'The last name and first name already exists in WordPress but are not in the registration field. We need an ACF group just for registration and will copy it\'s values to the real fields.', 'law-1901-association' ); ?>
		</span>

		<p>
			<label for="group-name"><?php esc_html_e( 'Last name/first name group', 'law-1901-association' ); ?></label><br/>
			<select name="group_name" id="group-name">
				<option value=""></option>
				<option value="0"><?php esc_html_e( 'Create group', 'law-1901-association' ); ?></option>
				<?php law1901_display_groups( $groups, $this->prefs['group_name'] ); ?>
			</select>
			<?php if ( ! empty( $this->prefs['group_name'] ) ) { /* display first name and last name fields */ ?>
				<br/>
				<label for="field-first-name"><?php esc_html_e( 'First name field', 'law-1901-association' ); ?></label><br/>
				<select name="field_first_name" id="field-first-name">
					<option value=""></option>
					<?php
					$fields = acf_get_fields( $this->prefs['group_name'] );
					foreach ( $fields as $field ) {
						?>
						<option
							value="<?php echo esc_attr( $field['key'] ); ?>"
							<?php
							if ( $field['key'] === $this->prefs['field_first_name'] ) {
								echo 'selected';
							}
							?>
						>
							<?php echo esc_html( $field['label'] ); ?>
						</option>
					<?php } ?>
				</select>
				<br/>
				<label for="field-last-name"><?php esc_html_e( 'Last name field', 'law-1901-association' ); ?></label><br/>
				<select name="field_last_name" id="field-last-name">
					<option value=""></option>
					<?php
					foreach ( $fields as $field ) {
						?>
						<option
							value="<?php echo esc_attr( $field['key'] ); ?>"
							<?php
							if ( $field['key'] === $this->prefs['field_last_name'] ) {
								echo 'selected';
							}
							?>
						>
							<?php echo esc_html( $field['label'] ); ?>
						</option>
					<?php } ?>
				</select>
				<?php
				law1901_set_group_location( 'group_name', $this->prefs, $this->locations );
			}
			?>
		</p>
	</div>

	<div class="law1901wrap settings3">
		<h2><?php esc_html_e( '2) Registration data', 'law-1901-association' ); ?></h2>

		<span class="italic">
			<?php esc_html_e( 'We need a group for registration data. This fields are not mandatory for the plugin to work, but you can put there anything you want.', 'law-1901-association' ); ?>
		</span>

		<p>
			<label for="group-register"><?php esc_html_e( 'Registration group', 'law-1901-association' ); ?></label><br/>
			<select name="group_register" id="group-register">
				<option value=""></option>
				<option value="0"><?php esc_html_e( 'Create group', 'law-1901-association' ); ?></option>
				<?php law1901_display_groups( $groups, $this->prefs['group_register'] ); ?>
			</select>
			<?php
			if ( ! empty( $this->prefs['group_register'] ) ) {
				law1901_set_group_location( 'group_register', $this->prefs, $this->locations );
			}
			?>
		</p>
	</div>

	<div class="law1901wrap settings3">
		<h2><?php esc_html_e( '3) Administratif data', 'law-1901-association' ); ?></h2>

		<span class="italic">
			<?php esc_html_e( 'We need a group for administrative date. This are not visible by members. It includes membership fees that will be used to lock some pages.', 'law-1901-association' ); ?>
		</span>

		<p>
			<label for="group-administrative"><?php esc_html_e( 'Administrative data group', 'law-1901-association' ); ?></label><br/>
			<select name="group_administrative" id="group-administrative">
				<option value=""></option>
				<option value="0"><?php esc_html_e( 'Create group', 'law-1901-association' ); ?></option>
				<?php law1901_display_groups( $groups, $this->prefs['group_administrative'] ); ?>
			</select>
			<?php if ( ! empty( $this->prefs['group_administrative'] ) ) { /* display membership limit date field */ ?>
				<br/>
				<label for="field-membership-limit-date"><?php esc_html_e( 'Membership fees field', 'law-1901-association' ); ?></label><br/>
				<select name="field_membership_limit_date" id="field-membership-limit-date">
					<option value=""></option>
					<?php
					$fields = acf_get_fields( $this->prefs['group_administrative'] );
					foreach ( $fields as $field ) {
						?>
						<option
							value="<?php echo esc_attr( $field['key'] ); ?>"
							<?php
							if ( $field['key'] === $this->prefs['field_membership_limit_date'] ) {
								echo 'selected';
							}
							?>
						>
							<?php echo esc_html( $field['label'] ); ?>
						</option>
					<?php } ?>
				</select>
				<br/>
				<label for="field-leading-team"><?php esc_html_e( 'Board member field', 'law-1901-association' ); ?></label><br/>
				<select name="field_leading_team" id="field-leading-team">
					<option value=""></option>
					<?php
					foreach ( $fields as $field ) {
						?>
						<option
							value="<?php echo esc_attr( $field['key'] ); ?>"
							<?php
							if ( $field['key'] === $this->prefs['field_leading_team'] ) {
								echo 'selected';
							}
							?>
						>
							<?php echo esc_html( $field['label'] ); ?>
						</option>
					<?php } ?>
				</select>
				<?php
				law1901_set_group_location( 'group_administrative', $this->prefs, $this->locations );
			}
			?>
		</p>
	</div>

	<div class="law1901wrap settings">
		<p class="submit">
			<input
				type="submit"
				name="submit"
				id="submit"
				class="button button-primary"
				value="<?php echo esc_attr( 'Save changes' ); ?>"
			>
		</p>
	</div>

	<?php
	// Add nonce.
	wp_nonce_field( 'settings' );
	?>

</form>

<div class="spacer"></div>
