<?php
/**
 * Display the first tab of the user settings page (user data).
 *
 * @package Law_1901_Association
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// To please code checker.
$can_manage   = $can_manage ?? false;
$user         = $user ?? null;
$profile_page = $profile_page ?? false;

?>

<form method="post">
	<div class="form-table table-view-member">
		<div class="acf-field acf-field-text">
			<div class="acf-label">
				<label for="first_name"><strong><?php esc_html_e( 'First name', 'law-1901-association' ); ?></strong></label>
			</div>
			<div class="acf-input">
				<input type="text" id="first_name" name="first_name" value="<?php echo esc_attr( $user->first_name ); ?>">
			</div>
		</div>

		<div class="acf-field acf-field-text">
			<div class="acf-label">
				<label for="last_name"><strong><?php esc_html_e( 'Last name', 'law-1901-association' ); ?></strong></label>
			</div>
			<div class="acf-input">
				<input type="text" id="last_name" name="last_name" value="<?php echo esc_attr( $user->last_name ); ?>">
			</div>
		</div>

		<div class="acf-field acf-field-text">
			<div class="acf-label">
				<label for="email"><strong><?php esc_html_e( 'Email', 'law-1901-association' ); ?></strong></label>
			</div>
			<div class="acf-input">
				<input type="text" id="email" name="email" value="<?php echo esc_attr( $user->user_email ); ?>" <?php echo $profile_page ? 'disabled' : ''; ?>>
			</div>
		</div>

		<?php

		// Display member limit date if not admin.
		if ( ! $can_manage ) {
			?>
			<div class="acf-field acf-field-text">
				<div class="acf-label">
					<label for="membership_limit_date"><strong><?php esc_html_e( 'Membership end date', 'law-1901-association' ); ?></strong></label>
				</div>
				<div class="acf-input">
					<?php
						echo esc_html(
							DateTime::createFromFormat(
								'Ymd',
								$user->membership_limit_date
							)->format( esc_attr__( 'm/d/Y', 'law-1901-association' ) )
						);
					?>
				</div>
			</div>
			<?php
		}

		?>
	</div>

	<div class="law1901wrap settings">
		<p class="submit">
			<input
				type="submit"
				name="submit"
				id="submit"
				class="button button-primary"
				value="<?php echo esc_attr( 'Update' ); ?>"
			>
			<span class="spinner"></span>
		</p>
	</div>

	<?php
		// Add nonce.
		wp_nonce_field( 'member-' . $user->ID );
	?>

</form>

<div class="spacer"></div>
