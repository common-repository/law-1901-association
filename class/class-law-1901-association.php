<?php
/**
 * Class Law_1901_Association (main plugin class).
 *
 * @package Law_1901_Association
 */

/**
 * Law_1901_Association class.
 */
class Law_1901_Association {
	/**
	 * The version of the plugin.
	 *
	 * @var string $plugin_version
	 */
	protected $plugin_version = '1.0';

	/**
	 * The name of the plugin.
	 *
	 * @var string $plugin_name
	 */
	protected $plugin_name = 'law-1901-association';
	/**
	 * The short name of the plugin (for menu).
	 *
	 * @var string $short_name
	 */
	protected $short_name = 'Association';

	/**
	 * URL to fetch news.
	 *
	 * @var string $news_url
	 */
	protected $news_url = 'https://www.grison.pro/wordpress/plugins/law1901/news.php';

	/**
	 * The preferences for the plugin.
	 *
	 * @var array $prefs
	 */
	public $prefs = array();

	/**
	 * True if member is active, null if not initialized.
	 *
	 * @var bool|null $is_active_member
	 */
	private $is_active_member = null;

	/**
	 * True if user can manage association, null if not initialized.
	 *
	 * @var null $can_manage
	 */
	private $can_manage = null;

	/**
	 * The locations for ACF.
	 *
	 * @var array[] $locations
	 */
	private $locations = array(
		'group_name'           =>
			array(
				array(
					array(
						'param'    => 'user_form',
						'operator' => '==',
						'value'    => 'register',
					),
				),
			),
		'group_register'       =>
			array(
				array(
					array(
						'param'    => 'user_form',
						'operator' => '==',
						'value'    => 'all',
					),
				),
				array(
					array(
						'param'    => 'law_1901',
						'operator' => '==',
						'value'    => 'members',
					),
				),
			),
		'group_administrative' =>
			array(
				array(
					array(
						'param'    => 'user_form',
						'operator' => '==',
						'value'    => 'all',
					),
					array(
						'param'    => 'current_user_role',
						'operator' => '==',
						'value'    => 'administrator',
					),
				),
				array(
					array(
						'param'    => 'law_1901',
						'operator' => '==',
						'value'    => 'members',
					),
					array(
						'param'    => 'current_user_role',
						'operator' => '==',
						'value'    => 'administrator',
					),
				),
			),
	);

	/**
	 * Law_1901_Association constructor
	 */
	public function __construct() {
		// Load plugin prefs.
		$this->load_plugin_prefs();

		// Remove login field from registration if needed.
		if ( $this->prefs['register_control'] ) {
			// Also remove errors that come with that field.
			add_filter( 'registration_errors', array( $this, 'remove_login_errors' ), 20, 3 );
		}

		// Add plugin settings page.
		add_action( 'admin_menu', array( $this, 'plugin_admin_menu' ), 1000 );

		// Add plugin assets for admins.
		add_action( 'login_enqueue_scripts', array( $this, 'plugin_css_and_js' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_admin_css_and_js' ) );

		// Copy first name and last name from acf to the right fields.
		add_action( 'user_register', array( $this, 'user_register' ) );

		// Add shortcode for restrictions.
		add_shortcode( $this->prefs['restriction'], array( $this, 'restriction_shortcode' ) );

		// Add profile shortcode.
		add_shortcode( 'law-1901-association-profile', array( $this, 'profile_shortcode' ) );

		// Add ajax action for dismiss button on notification.
		add_action( 'wp_ajax_dismiss_law1901_notice', array( $this, 'dismiss_law1901_notice' ) );

		// Allow redirect.
		add_action( 'init', array( $this, 'do_output_buffer' ) );

		// Load translations.
		add_action( 'plugins_loaded', array( $this, 'load_translations' ) );

		// We need ACF to work.
		add_action( 'admin_notices', array( $this, 'check_acf' ) );

		// Try to contact server for addon updates.
		$this->call_for_updates();
	}

	/**
	 * Load translations.
	 */
	public function load_translations() {
		load_plugin_textdomain( strtolower( $this->plugin_name ), false, dirname( plugin_basename( __FILE__ ) ) . '/../languages/' );
	}

	/**
	 * Check if ACF is loaded and display a popup if needed.
	 */
	public function check_acf() {
		if ( ! is_plugin_active( 'advanced-custom-fields/acf.php' ) ) {
			wp_admin_notice(
				__(
					'The <strong>Advanced Custom Fields (ACF)</strong> plugin is needed by the plugin <strong>law-1901-association</strong>.',
					'law-1901-association'
				),
				array(
					'type'        => 'error',
					'dismissible' => true,
				)
			);
		}
	}

	/**
	 * Needed for settings page, saving caused a blank page without that because of headers already sent.
	 */
	public function do_output_buffer(): void {
		ob_start();
	}

	/**
	 * Filter errors when controlling login.
	 *
	 * @param WP_Error $wp_errors The WP_Error object.
	 * @return WP_Error
	 */
	public function remove_login_errors( WP_Error $wp_errors ): WP_Error {
		// Always remove empty username error.
		if ( isset( $wp_errors->errors['empty_username'] ) ) {
			unset( $wp_errors->errors['empty_username'] );
		}

		return $wp_errors;
	}

	/**
	 * Add plugin css and js for admin.
	 *
	 * @return void
	 */
	public function plugin_css_and_js(): void {
		// Remove login field from registration if needed.
		if ( $this->prefs['register_control'] ) {
			wp_add_inline_style(
				'law-1901-association',
				'label[for=user_email]:after {content: " *";color: red;} #registerform > p:first-child { display: none; }'
			);

			// Need to add javascript for register page.
			wp_enqueue_script(
				'law1901_register_js',
				plugins_url( '../js/register.js', __FILE__ ),
				array( 'jquery' ),
				$this->plugin_version,
				true
			);
		}
	}

	/**
	 * Add plugin css and js for admin.
	 *
	 * @return void
	 */
	public function plugin_admin_css_and_js(): void {
		// Need to add javascript to handle popup closing.
		wp_enqueue_script(
			'law1901_popup_js',
			plugins_url( '../js/popup.js', __FILE__ ),
			array( 'jquery' ),
			$this->plugin_version,
			true
		);

		// must know if we are in current plugin page.
		$screen = get_current_screen();
		if ( $screen && strpos( $screen->id, $this->plugin_name ) !== false ) {
			// Load admin assets.
			wp_enqueue_style( 'law1901_css', plugins_url( '../law-1901-association.css', __FILE__ ), array(), $this->plugin_version );
		}
	}

	/**
	 * Add plugin admin menu entry.
	 */
	public function plugin_admin_menu(): void {
		add_menu_page(
			$this->short_name,
			$this->short_name,
			'read',
			$this->plugin_name,
			'',
			'dashicons-groups',
			4
		);

		add_submenu_page(
			$this->plugin_name,
			esc_html__( 'Home', 'law-1901-association' ),
			esc_html__( 'Home', 'law-1901-association' ),
			'read',
			$this->plugin_name,
			array( $this, 'plugin_main_page' )
		);

		if ( $this->can_manage_association() ) {
			add_submenu_page(
				$this->plugin_name,
				esc_html__( 'Members', 'law-1901-association' ),
				esc_html__( 'Members', 'law-1901-association' ),
				'read',
				$this->plugin_name . '/members',
				array( $this, 'plugin_members_page' )
			);

			add_submenu_page(
				$this->plugin_name,
				esc_html__( 'My profile', 'law-1901-association' ),
				esc_html__( 'My profile', 'law-1901-association' ),
				'read',
				$this->plugin_name . '%2Fmembers&id=' . get_current_user_id(),
				array( $this, 'plugin_members_page' )
			);

			add_submenu_page(
				$this->plugin_name,
				esc_html__( 'Settings', 'law-1901-association' ),
				esc_html__( 'Settings', 'law-1901-association' ),
				'read',
				$this->plugin_name . '/settings',
				array( $this, 'plugin_settings' )
			);
		} else {
			add_submenu_page(
				$this->plugin_name,
				esc_html__( 'My profile', 'law-1901-association' ),
				esc_html__( 'My profile', 'law-1901-association' ),
				'read',
				$this->plugin_name . '/members',
				array( $this, 'plugin_members_page' )
			);
		}
	}

	/**
	 * Main association page.
	 */
	public function plugin_main_page(): void {
		// Nothing to display if ACF plugin is not active.
		if ( ! is_plugin_active( 'advanced-custom-fields/acf.php' ) ) {
			echo wp_kses( '<div class="law1901wrap">' . __( 'Sorry, this page cannot be rendered.', 'law-1901-association' ) . '</div>', array( 'div' => array( 'class' ) ) );
			return;
		}

		// Fetch all users.
		$users = get_users();

		// Fetch limit date from acf for each user.
		foreach ( $users as $user ) {
			$user->membership_limit_date = get_field( $this->prefs['field_membership_limit_date'], 'user_' . $user->ID );
		}

		// Sort users by membership status.
		$notmembers     = array();
		$old            = array();
		$late           = array();
		$members        = array();
		$time_pay_limit = null;
		foreach ( $users as $user ) {
			// Convert dates to timestamp.
			if ( ! is_null( $user->membership_limit_date ) ) {
				$user->membership_limit_date = $this->acf_date_to_timestamp( $user->membership_limit_date );
				$time_pay_limit              = time() - intval( $this->prefs['after_limit_delay'] ) * 7 * 24 * 3600;
			}

			// Sort by membership status. Variables will be used in template.
			if ( is_null( $user->membership_limit_date ) || ( 0 === $user->membership_limit_date ) ) {
				$notmembers[] = $user;
			} elseif ( time() <= $user->membership_limit_date ) {
				$members[] = $user;
			} elseif ( ! is_null( $time_pay_limit ) && ( $time_pay_limit <= $user->membership_limit_date ) ) {
				$late[] = $user;
			} else {
				$old[] = $user;
			}
		}

		// need this variable for the filter.
		$after_limit_delay = $this->prefs['after_limit_delay'];

		// Render page.
		include_once __DIR__ . '/../templates/main.php';
	}

	/**
	 * Members page.
	 *
	 * @param bool $profile_page set to true to display profile page (with self user id).
	 *
	 * @return string
	 */
	public function plugin_members_page( bool $profile_page = false ): string {
		// Nothing to display if ACF plugin is not active.
		if ( ! is_plugin_active( 'advanced-custom-fields/acf.php' ) ) {
			echo wp_kses( '<div class="law1901wrap">' . __( 'Sorry, this page cannot be rendered.', 'law-1901-association' ) . '</div>', array( 'div' => array( 'class' ) ) );
			return '';
		}

		// Need to capture content?
		if ( $profile_page ) {
			ob_start();
		}

		// Profile page.
		// phpcs:ignore
		if ( ! empty( $_GET['id'] ) || $profile_page || ! $this->can_manage_association() ) {
			// Force id to user id if no rights to manage.
			if ( $profile_page || ! $this->can_manage_association() ) {
				$id = get_current_user_id();
			} else {
				// Just accessing a specific page, no need to check nonce.
				// phpcs:ignore
				$id = intval( $_GET['id'] );
			}

			// Get user.
			$user = get_user_by( 'id', $id );

			// Get acf fields values.
			$args  = array(
				'post_type'   => 'acf-field',
				'post_status' => 'publish',
				'numberposts' => -1,
			);
			$posts = get_posts( $args );

			$fields = array();
			foreach ( $posts as $post ) {
				// I don't choose how the data is stored.
                // phpcs:ignore
				$data = unserialize( $post->post_content );
				if ( isset( $data['choices'] ) ) {
					$fields[ $post->post_excerpt ] = $data['choices'];
				}
			}

			// Save POST data to database.
			if ( ! empty( $_POST ) &&
				( ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'member-' . $id ) ) || acf_verify_nonce( 'acf_form' ) )
			) {
				// Save user data from first tab.
				if ( isset( $_POST['first_name'] ) && isset( $_POST['last_name'] ) && isset( $_POST['email'] ) ) {
					// Save user data.
					$user->first_name = sanitize_text_field( wp_unslash( $_POST['first_name'] ) );
					$user->last_name  = sanitize_text_field( wp_unslash( $_POST['last_name'] ) );

					// Do not allow to change email on self-profile page unless admin.
					if ( ( get_current_user_id() === $id ) && ! $this->can_manage_association() ) {
						$user->user_email = sanitize_text_field( wp_unslash( $_POST['email'] ) );
					}
					wp_update_user( $user );
				} elseif ( ! empty( $_POST['acf'] ) && is_array( $_POST['acf'] ) && ( ! isset( $_POST['acf'][ $this->prefs['field_membership_limit_date'] ] ) || $this->can_manage_association() ) ) {
					// Do not allow non-admins to save third tab data.
					$acf_data = array_map( 'sanitize_text_field', wp_unslash( $_POST['acf'] ) );
					foreach ( $acf_data as $field => $value ) {
						update_field( $field, $value, 'user_' . $user->ID );
					}
				}
			}

			// Feed useful some variable.
			$can_manage = $this->can_manage_association();

			// Handle tabs.
			if ( isset( $_SERVER['REQUEST_URI'] ) ) {
				$url         = preg_replace( '/&tab=[^&]+/', '', sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
				$current_tab = intval( $_GET['tab'] ?? 1 );
				if ( ! file_exists( __DIR__ . '/../templates/user-tab-' . $current_tab . '.php' ) ) {
					$current_tab = 1;
				}

				// For profile page, we need to add a fake arg in order to have "&tab=" working.
				if ( $profile_page && ( strpos( $url, '?' ) === false ) ) {
					$url .= '?profile=1';
				}

				// Render page.
				include_once __DIR__ . '/../templates/user-tabs.php';
				include_once __DIR__ . '/../templates/user-tab-' . $current_tab . '.php';
			}
		} else {
			// Must be allowed to manage.
			if ( ! $this->can_manage_association() ) {
				$this->access_error();
			}

			// List users.
			$list = new Law_1901_Association_Member_List_Table();
			$list->prepare_items();

			// need this variable for the filter.
			$after_limit_delay = $this->prefs['after_limit_delay'];

			// Render page.
			include_once __DIR__ . '/../templates/members.php';
		}

		// Need to capture content?
		if ( $profile_page ) {
			return ob_get_clean();
		}

		// Still need to return something.
		return '';
	}

	/**
	 * Plugin settings.
	 */
	public function plugin_settings(): void {
		// Nothing to display if ACF plugin is not active.
		if ( ! is_plugin_active( 'advanced-custom-fields/acf.php' ) ) {
			echo wp_kses( '<div class="law1901wrap">' . __( 'Sorry, this page cannot be rendered.', 'law-1901-association' ) . '</div>', array( 'div' => array( 'class' ) ) );
			return;
		}

		// Must be allowed to manage.
		if ( ! $this->can_manage_association() ) {
			$this->access_error();
		}

		// Save form.
		if ( ! empty( $_POST ) &&
		isset( $_POST['_wpnonce'] ) &&
		wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'settings' )
		) {
			switch ( $_GET['tab'] ?? 1 ) {
				// General settings.
				case '1':
					foreach ( array( 'register_control', 'after_limit_delay', 'after_limit_access', 'restriction', 'restriction_page' ) as $pref ) {
						$this->prefs[ $pref ] = sanitize_text_field( wp_unslash( $_POST[ $pref ] ?? 0 ) );
						update_option( 'law1901_' . $pref, $this->prefs[ $pref ] );
					}
					break;

				// ACF groups.
				case '2':
					// Remove "group name".
					if ( isset( $_POST['group_name'] ) && ( '' === $_POST['group_name'] ) ) {
						$this->prefs['group_name']       = '';
						$this->prefs['field_first_name'] = '';
						$this->prefs['field_last_name']  = '';
					} elseif ( '0' === $_POST['group_name'] ) {
						// Create "group name".
						$this->prefs['group_name']       = 'group_' . uniqid();
						$this->prefs['field_first_name'] = 'field_' . uniqid();
						$this->prefs['field_last_name']  = 'field_' . uniqid();
						acf_import_field_group(
							array(
								'key'        => $this->prefs['group_name'],
								'title'      => esc_html__( 'Last name and first name (register only)', 'law-1901-association' ),
								'fields'     => array(
									array(
										'key'      => $this->prefs['field_last_name'],
										'label'    => esc_html__( 'Last name', 'law-1901-association' ),
										'name'     => 'last_name',
										'type'     => 'text',
										'required' => 1,
									),
									array(
										'key'      => $this->prefs['field_first_name'],
										'label'    => esc_html__( 'First name', 'law-1901-association' ),
										'name'     => 'fist_name',
										'type'     => 'text',
										'required' => 1,
									),
								),
								'style'      => 'seamless',
								'menu_order' => 0,
								'location'   => $this->locations['group_name'],
							)
						);
					} elseif ( isset( $_POST['group_name'] ) && isset( $_POST['field_first_name'] ) && isset( $_POST['field_last_name'] ) ) {
						$this->prefs['group_name']       = sanitize_text_field( wp_unslash( $_POST['group_name'] ) );
						$this->prefs['field_first_name'] = sanitize_text_field( wp_unslash( $_POST['field_first_name'] ) );
						$this->prefs['field_last_name']  = sanitize_text_field( wp_unslash( $_POST['field_last_name'] ) );
					}

					update_option( 'law1901_group_name', $this->prefs['group_name'] );
					update_option( 'law1901_field_first_name', $this->prefs['field_first_name'] ?? '' );
					update_option( 'law1901_field_last_name', $this->prefs['field_last_name'] ?? '' );

					// Remove "group register".
					if ( isset( $_POST['group_register'] ) && ( '' === $_POST['group_register'] ) ) {
						$this->prefs['group_register'] = '';
					} elseif ( '0' === $_POST['group_register'] ) {
						// Create "group register".
						$this->prefs['group_register'] = 'group_' . uniqid();
						acf_import_field_group(
							array(
								'key'        => $this->prefs['group_register'],
								'title'      => esc_html__( 'Register information', 'law-1901-association' ),
								'fields'     => array(),
								'style'      => 'seamless',
								'menu_order' => 1,
								'location'   => $this->locations['group_register'],
							)
						);
					} else {
						$this->prefs['group_register'] = sanitize_text_field( wp_unslash( $_POST['group_register'] ) );
					}
					update_option( 'law1901_group_register', $this->prefs['group_register'] );

					// Remove "group administrative".
					if ( isset( $_POST['group_administrative'] ) && ( '' === $_POST['group_administrative'] ) ) {
						$this->prefs['group_administrative']        = '';
						$this->prefs['field_leading_team']          = '';
						$this->prefs['field_membership_limit_date'] = '';
					} elseif ( '0' === $_POST['group_administrative'] ) {
						// Create "group administrative".
						$this->prefs['group_administrative']        = 'group_' . uniqid();
						$this->prefs['field_leading_team']          = 'field_' . uniqid();
						$this->prefs['field_membership_limit_date'] = 'field_' . uniqid();
						acf_import_field_group(
							array(
								'key'        => $this->prefs['group_administrative'],
								'title'      => esc_html__( 'Administrative information', 'law-1901-association' ),
								'fields'     => array(
									array(
										'key'           => $this->prefs['field_membership_limit_date'],
										'label'         => esc_html__( 'Membership fee', 'law-1901-association' ),
										'name'          => 'membership_limit_date',
										'type'          => 'date_picker',
										'return_format' => 'Ymd',
									),
									array(
										'key'     => $this->prefs['field_leading_team'],
										'label'   => esc_html__( 'Board member', 'law-1901-association' ),
										'name'    => 'leading_team',
										'type'    => 'select',
										'choices' => array(
											'' => '',
											esc_html( 'President' ) => esc_html__( 'President', 'law-1901-association' ),
											esc_html( 'Vice President' ) => esc_html__( 'Vice President', 'law-1901-association' ),
											esc_html( 'Secretary' ) => esc_html__( 'Secretary', 'law-1901-association' ),
											esc_html( 'Assistant Secretary' ) => esc_html__( 'Assistant Secretary', 'law-1901-association' ),
											esc_html( 'Treasurer' ) => esc_html__( 'Treasurer', 'law-1901-association' ),
											esc_html( 'Assistant Treasurer' ) => esc_html__( 'Assistant Treasurer', 'law-1901-association' ),
										),
									),
								),
								'style'      => 'seamless',
								'menu_order' => 2,
								'location'   => $this->locations['group_administrative'],
							)
						);
					} elseif ( isset( $_POST['group_administrative'] ) && isset( $_POST['field_leading_team'] ) && isset( $_POST['field_membership_limit_date'] ) ) {
						$this->prefs['group_administrative']        = sanitize_text_field( wp_unslash( $_POST['group_administrative'] ) );
						$this->prefs['field_leading_team']          = sanitize_text_field( wp_unslash( $_POST['field_leading_team'] ) );
						$this->prefs['field_membership_limit_date'] = sanitize_text_field( wp_unslash( $_POST['field_membership_limit_date'] ) );
					}

					update_option( 'law1901_group_administrative', $this->prefs['group_administrative'] );
					update_option( 'law1901_field_leading_team', $this->prefs['field_leading_team'] );
					update_option( 'law1901_field_membership_limit_date', $this->prefs['field_membership_limit_date'] );

					// Update locations for already existing groups.
					foreach ( $this->locations as $name => $location ) {
						if ( isset( $_POST[ 'location_' . $name ] ) ) {
							$group             = acf_get_field_group( $this->prefs[ $name ] );
							$group['location'] = $location;
							acf_update_field_group( $group );
						}
					}
					break;

				case '3':
					foreach ( array( 'notify_addon_1', 'notify_addon_2', 'notify_addon_3' ) as $pref ) {
						$oldpref[ $pref ]     = $this->prefs[ $pref ];
						$this->prefs[ $pref ] = intval( ( $_POST[ $pref ] ?? '' ) === 'on' );
						update_option( 'law1901_' . $pref, $this->prefs[ $pref ] );

						// If notification is set from off to on, reset displayed status, so we can display it again.
						if ( ( $oldpref[ $pref ] !== $this->prefs[ $pref ] ) && $this->prefs[ $pref ] ) {
							update_option( 'law1901_' . $pref . '_content', '' );
						}
					}
					break;

				default:
			}
		}

		// Handle tabs.
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$url         = preg_replace( '/&tab=[^&]+/', '', sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
			$current_tab = intval( $_GET['tab'] ?? 1 );
			if ( ! file_exists( __DIR__ . '/../templates/settings-tab-' . $current_tab . '.php' ) ) {
				$current_tab = 1;
			}

			include_once __DIR__ . '/../templates/settings-tabs.php';
			include_once __DIR__ . '/../templates/settings-tab-' . $current_tab . '.php';
		}
	}

	/**
	 * Load plugin prefs, using defaults for first time.
	 *
	 * @return void
	 */
	private function load_plugin_prefs(): void {
		$prefs = array(
			'addon_last_query'            => 0,
			'after_limit_delay'           => 8,
			'after_limit_access'          => 1,
			'field_first_name'            => '',
			'field_last_name'             => '',
			'field_leading_team'          => '',
			'field_membership_limit_date' => '',
			'group_administrative'        => '',
			'group_name'                  => '',
			'group_register'              => '',
			'notify_addon_1'              => 0,
			'notify_addon_2'              => 0,
			'notify_addon_3'              => 0,
			'notify_addon_1_content'      => '',
			'notify_addon_2_content'      => '',
			'notify_addon_3_content'      => '',
			'register_control'            => 1,
			'restriction'                 => 'restriction',
			'restriction_page'            => null,
		);

		// Load prefs from acf.
		$this->prefs = array();
		foreach ( $prefs as $pref => $default ) {
			$this->prefs[ $pref ] = get_option( 'law1901_' . $pref, $default );
		}
	}

	/**
	 * Copy first name and last name from acf to the right fields.
	 *
	 * @param int $user_id The user id.
	 *
	 * @return void
	 */
	public function user_register( int $user_id ): void {
		// Get first name from prefs and copy it from acf to user meta.
		$first_name = acf_get_field( $this->prefs['field_first_name'] );
		if ( ! empty( $first_name['name'] ) ) {
			update_user_meta( $user_id, 'first_name', get_field( $first_name['name'], 'user_' . $user_id ) );
		}

		// Get last name from prefs and copy it from acf to user meta.
		$last_name = acf_get_field( $this->prefs['field_last_name'] );
		if ( ! empty( $last_name['name'] ) ) {
			update_user_meta( $user_id, 'last_name', get_field( $last_name['name'], 'user_' . $user_id ) );
		}
	}

	/**
	 * This shortcode must lock page access to non-active members.
	 *
	 * @return string
	 */
	public function restriction_shortcode(): string {
		// Admin must not be redirected when editing pages.
		if ( is_admin() ) {
			return '';
		}

		// Active member can see the page.
		if ( $this->is_active_member() ) {
			return '';
		}

		// Redirect if not logged only.
		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( wp_login_url() );
			exit();
		}

		// Redirect to error page from prefs.
		if ( ! empty( $this->prefs['restriction_page'] ) ) {
			wp_safe_redirect( get_permalink( $this->prefs['restriction_page'] ) );
			exit();
		}

		// Default error message (fatal error, stop here).
		$this->access_error();

		// fake return to avoid php error (because program is already stopped).
		return '';
	}

	/**
	 * This shortcode display profile members in front website.
	 *
	 * @return string
	 */
	public function profile_shortcode(): string {
		// Redirect if not logged.
		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( wp_login_url() );
			exit();
		}

		// Will need ACF CSS and JS.
		acf_form_head();

		return $this->plugin_members_page( true );
	}

	/**
	 * Check if member is active.
	 *
	 * @return bool
	 */
	public function is_active_member(): bool {
		if ( is_null( $this->is_active_member ) ) {
			$this->is_active_member = $this->get_is_active_member();
		}

		return $this->is_active_member;
	}

	/**
	 * Set the internal variable that say if user is an active member.
	 *
	 * @return bool
	 */
	private function get_is_active_member(): bool {
		// User must be logged in.
		if ( ! is_user_logged_in() ) {
			return false;
		}

		// Check prefs are OK, or we won't be able to check membership.
		if ( empty( $this->prefs['field_membership_limit_date'] ) || empty( $this->prefs['after_limit_delay'] ) ) {
			return false;
		}

		// Get membership field.
		$membership = acf_get_field( $this->prefs['field_membership_limit_date'] );
		if ( empty( $membership['name'] ) ) {
			return false;
		}

		// Get membership date.
		$membership_date = get_field( $membership['name'], 'user_' . get_current_user_id() );
		if ( empty( $membership_date ) ) {
			return false;
		}

		// Check membership date.
		$limit = $this->acf_date_to_timestamp( $membership_date );

		// If we don't allow to access after limit, we must check if we are before limit.
		if ( ! $this->prefs['after_limit_access'] ) {
			return time() <= $limit;
		}

		// If we allow to access after limit, we must check if we are before limit or after limit.
		return time() <= $limit + $this->prefs['after_limit_delay'] * 604800; // 1 week in seconds.
	}

	/**
	 * Check if user can manage the association.
	 *
	 * @return bool
	 */
	public function can_manage_association(): bool {
		if ( is_null( $this->can_manage ) ) {
			$this->can_manage = $this->get_can_manage_association();
		}

		return $this->can_manage;
	}

	/**
	 * Set the internal variable that say if user can manage the association.
	 *
	 * @return bool
	 */
	private function get_can_manage_association(): bool {
		// An admin always can manage.
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		// If user is not a valid member, he cannot manage.
		if ( ! $this->is_active_member() ) {
			return false;
		}

		// Check prefs are OK, or we won't be able to check membership.
		if ( empty( $this->prefs['field_leading_team'] ) ) {
			return false;
		}

		// User must be in leading team.
		$leading_team = acf_get_field( $this->prefs['field_leading_team'] );
		if ( empty( $leading_team['name'] ) ) {
			return false;
		}

		return ! empty( get_field( $leading_team['name'], 'user_' . get_current_user_id() ) );
	}

	/**
	 * End page rendering in admin with fatal error.
	 *
	 * @return void
	 */
	private function access_error(): void {
		echo '<h2 class="error red">' . esc_html__( 'You are not allowed to access this page.', 'law-1901-association' ) . '</h2>';
		exit();
	}

	/**
	 * Convert ACF date to timestamp.
	 *
	 * @param string $date The date to convert.
	 * @return int
	 */
	private function acf_date_to_timestamp( string $date ): int {
		if ( empty( $date ) ) {
			return 0;
		}

		return DateTime::createFromFormat( 'Ymd', $date )->getTimestamp();
	}

	/**
	 * Call ajax for updates and display notifications.
	 *
	 * @return void
	 */
	private function call_for_updates() {
		// Render already displayed notifications.
		foreach ( array( 'notify_addon_1', 'notify_addon_2', 'notify_addon_3' ) as $pref ) {
			// User does not want this one.
			if ( 0 === $this->prefs[ $pref ] ) {
				continue;
			}

			// Empty notification or user already dismissed this one.
			if ( ( '' === $this->prefs[ $pref . '_content' ] ) || ( 'X' === $this->prefs[ $pref . '_content' ] ) ) {
				continue;
			}

			// Display notification.
			add_action( 'admin_notices', array( $this, 'display_notification_' . $pref ) );
		}

		// Do not call for updates too often.
		if ( $this->prefs['addon_last_query'] >= time() - 86400 ) {
			return;
		}

		// Call for updates.
		if ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			$response = wp_remote_get( $this->news_url . '?lang=' . sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) );
			if ( ! is_wp_error( $response ) ) {
				try {
					$data = json_decode( $response['body'], true );
					foreach ( $data as $key => $value ) {
						if ( 'X' !== $this->prefs[ 'notify_addon_' . ( $key + 1 ) . '_content' ] ) {
							$this->prefs[ 'notify_addon_' . ( $key + 1 ) . '_content' ] = $value;
							update_option( 'law1901_notify_addon_' . ( $key + 1 ) . '_content', $value );
						}
					}
					// phpcs:ignore
					} catch ( Exception $e ) {
					// Nothing to do here, will skip this answer.
				}
			}
		}

		// Save current time in last query field.
		$this->prefs['addon_last_query'] = time();
		update_option( 'law1901_addon_last_query', $this->prefs['addon_last_query'] );
	}

	/**
	 * Display notification for first addon.
	 *
	 * @return void
	 */
	public function display_notification_notify_addon_1(): void {
		$this->display_notification( 1 );
	}

	/**
	 * Display notification for second addon.
	 *
	 * @return void
	 */
	public function display_notification_notify_addon_2(): void {
		$this->display_notification( 2 );
	}

	/**
	 * Display notification for third addon.
	 *
	 * @return void
	 */
	public function display_notification_notify_addon_3(): void {
		$this->display_notification( 3 );
	}

	/**
	 * Display notification.
	 *
	 * @param string $notification The notification to display.
	 *
	 * @return void
	 */
	private function display_notification( string $notification ): void {
		?>
		<div
			id="law-1901-association-notice"
			class="notice notice-warning is-dismissible"
			data-notification="<?php echo esc_attr( $notification ); ?>"
		>
			<p><?php nl2br( esc_html( $this->prefs[ 'notify_addon_' . $notification . '_content' ] ) ); ?></p>
		</div>
		<?php
	}

	/**
	 * Handle click on dismiss button to hide notification.
	 *
	 * @return void
	 */
	public function dismiss_law1901_notice() {
		if ( isset( $_POST['notification'] ) &&
		( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'ajax-nonce' ) )
		) {
			$notification = sanitize_text_field( wp_unslash( $_POST['notification'] ) );
			if ( in_array( $notification, array( 1, 2, 3 ), true ) ) {
				update_option( 'law1901_notify_addon_' . $notification . '_content', 'X' );
			}
		}
	}
}
