<?php
/**
 * Display the first tab for the settings page (general parameters).
 *
 * @package Law_1901_Association
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

?>

<h2><?php esc_html_e( 'General settings', 'law-1901-association' ); ?></h2>

<form method="post">
	<div class="law1901wrap settings2">
		<p>
			<label for="register-control">
				<input
					type="checkbox"
					name="register_control"
					id="register-control"
					value="1"
					<?php
					if ( $this->prefs['register_control'] ) {
						echo 'checked';
					}
					?>
				>
				<?php esc_html_e( 'Update the register form (hide the login field, add a red mark on the email, ...)', 'law-1901-association' ); ?>
			</label>
		</p>

		<p>
			<label for="after-limit-delay">
				<?php esc_html_e( 'Delay (in weeks) before a member that has to pay it\'s fee is no more considered as a member', 'law-1901-association' ); ?>
			</label>
			<input
				type="number"
				name="after_limit_delay"
				id="after-limit-delay"
				value="<?php echo intval( $this->prefs['after_limit_delay'] ); ?>"
				class="number"
			>
		</p>

		<p>
			<label for="after-limit-access">
				<input
					type="checkbox"
					name="after_limit_access"
					id="after-limit-access"
					value="1"
					<?php
					if ( $this->prefs['after_limit_access'] ) {
						echo 'checked';
					}
					?>
				>
				<?php esc_html_e( 'Allow access to restricted pages when member has to pay but before definitive exclusion', 'law-1901-association' ); ?>
			</label>
		</p>

		<p>
			<label for="after-limit-delay">
				<?php esc_html_e( 'Shortcode text to restrict page access to members', 'law-1901-association' ); ?>
			</label>
			<label for="restriction"></label><input
				type="text"
				name="restriction"
				id="restriction"
				value="<?php echo esc_attr( $this->prefs['restriction'] ); ?>"
			>
		</p>

		<p>
			<label for="after-limit-delay">
				<?php esc_html_e( 'Page to display if a user is logged in, but don\'t have access to restricted pages', 'law-1901-association' ); ?>
			</label>
			<label for="restriction-page"></label><select
				name="restriction_page"
				id="restriction-page"
			>
				<option value=""><?php esc_html_e( 'Default page', 'law-1901-association' ); ?></option>
				<?php
				$all_pages = get_pages();
				foreach ( $all_pages as $the_page ) {
					echo '<option value="' . esc_attr( $the_page->ID ) . '"';
					if ( $this->prefs['restriction_page'] === $the_page->ID ) {
						echo ' selected';
					}
					echo '>' . esc_html( $the_page->post_title ) . '</option>';
				}
				?>
			</select>
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
