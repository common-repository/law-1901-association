<?php
/**
 * Display the tabs for the user page.
 *
 * @package Law_1901_Association
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// to please code checker.
$url          = $url ?? '';
$current_tab  = $current_tab ?? 1;
$user         = $user ?? null;
$profile_page = $profile_page ?? false;

?>

<div class="law1901wrap">
	<div class="title">
		<?php
		if ( ! $profile_page ) {
			echo '<h1 class="fullwidth settings">' . esc_html__( 'Profile of', 'law-1901-association' );
			echo ' ' . esc_html( $user->first_name ) . ' ' . esc_html( $user->last_name );
			if ( ! empty( $user->poste_dans_le_bureau ) ) {
				echo ' - ' . esc_html( $user->poste_dans_le_bureau );
			}

			echo '</h1>';
		}
		?>
		<div class="tabs">
			<a
				href="<?php echo esc_url( $url ); ?>&tab=1" class="tab
								<?php
								if ( 1 === $current_tab ) {
									echo ' active';
								}
								?>
			"
			><?php esc_html_e( 'Identity', 'law-1901-association' ); ?></a>
			<a
				href="<?php echo esc_url( $url ); ?>&tab=2" class="tab
								<?php
								if ( 2 === $current_tab ) {
									echo ' active';
								}
								?>
			"
			><?php esc_html_e( 'Additional information', 'law-1901-association' ); ?></a>
			<?php if ( $this->can_manage_association() ) { ?>
				<a
					href="<?php echo esc_url( $url ); ?>&tab=3" class="tab
									<?php
									if ( 3 === $current_tab ) {
										echo ' active';
									}
									?>
				"
				><?php esc_html_e( 'Administrative information', 'law-1901-association' ); ?></a>
			<?php } ?>
		</div>
	</div>
</div>
