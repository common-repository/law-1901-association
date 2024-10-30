<?php
/**
 * Display the third tab for the settings page (information about plugin extensions).
 *
 * @package Law_1901_Association
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

?>

<div class="law1901wrap settings">
	<p><?php esc_html_e( 'Hello everybody, this plugin is free and will remain free. I have some plans for add-ons that will need some more work and that I plan to create and sell at a small price.', 'law-1901-association' ); ?></p>

	<p><?php esc_html_e( 'If you want to be warned about the add-ons release, please check the box below to have a WordPress notification. It will also make me know that you have som interest in it.', 'law-1901-association' ); ?></p>

	<p><?php esc_html_e( 'I will be pleased if you tell me what you think about this plugin in the support page.', 'law-1901-association' ); ?></p>
</div>

<div class="law1901wrap settings2">
	<form method="post">
		<input type="hidden" name="placeholder" value="1"> <!-- to avoid having an empty $_POST -->
		<p>
			<label>
				<input
					type="checkbox"
					name="notify_addon_1"
					<?php echo $this->prefs['notify_addon_1'] ? 'checked' : ''; ?>
				>
				<?php esc_html_e( 'Advance tuning of plugin home page, the memberlist columns and the fields of the memberlist and profile page', 'law-1901-association' ); ?>
			</label>
		</p>
		<p>
			<label>
				<input
					type="checkbox"
					name="notify_addon_2"
					<?php echo $this->prefs['notify_addon_2'] ? 'checked' : ''; ?>
				>
				<?php esc_html_e( 'Email sending (create groups and send emails to them: notice of the General Meeting, for some activities, reminder for members that have to pay, ...)', 'law-1901-association' ); ?>
			</label>
		</p>
		<p>
			<label>
				<input
					type="checkbox"
					name="notify_addon_3"
					<?php echo $this->prefs['notify_addon_3'] ? 'checked' : ''; ?>
				>
				<?php esc_html_e( 'Allow members to pay membership fees online with membership end date automatic update', 'law-1901-association' ); ?>
			</label>
		</p>
		<p class="submit"><input type="submit" value="Enregistrer" class="button button-primary"></p>

		<?php
		// Add nonce.
		wp_nonce_field( 'settings' );
		?>

	</form>
</div>
