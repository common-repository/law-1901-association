<?php
/**
 * Display the members page.
 * from https://www.smashingmagazine.com/2011/11/native-admin-tables-wordpress/ (deprecated)
 * from https://supporthost.com/wp-list-table-tutorial/.
 *
 * @package Law_1901_Association
 */

/**
 * Class Law_1901_Association_Member_List_Table.
 */
class Law_1901_Association_Member_List_Table extends WP_List_Table {
	/**
	 * The column to sort by.
	 *
	 * @var string
	 */
	private $order_by = 'user_login';

	/**
	 * The order direction (asc or desc).
	 *
	 * @var string
	 */
	private $order_direction = 'asc';

	/**
	 * Constructor, we override the parent to pass our own arguments
	 * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => esc_html__( 'member', 'law-1901-association' ), // Singular label.
				'plural'   => esc_html__( 'members', 'law-1901-association' ), // plural label, also this well be one of the table css class.
				'ajax'     => false, // We won't support Ajax for this table.
			)
		);
	}

	/**
	 * Prepare the table with different parameters, pagination, columns and table elements.
	 */
	public function prepare_items() {
		global $wpdb;

		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$primary               = 'last_name';
		$this->_column_headers = array( $columns, $hidden, $sortable, $primary );

		// Check nonce if the search form was submitted.
		if ( isset( $_POST['_wpnonce'] ) &&
			! empty( $_REQUEST['s'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bulk-membres' )
		) {
			// reload page without search.
			wp_safe_redirect( admin_url( 'admin.php?page=law-1901-association%2Fmembers' ) );
			exit();
		}

		// Sanitize and prepare the search term.
		$search = isset( $_REQUEST['s'] ) ? $wpdb->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) : '';

		// Starts with a full user list, looking in mails if needed.
		if ( empty( $search ) ) {
			$list1 = get_users();
			$list2 = array();
		} else {
			$list1 = get_users(
				array(
					'search'         => '*' . $search . '*',
					'search_columns' => array( 'user_email' ),
				)
			);

			// Now get a list of using the meta_query for first_name and last_name.
			$args2 = array(
                // phpcs:ignore
                'meta_query' => array(
					'relation' => 'OR',
					array(
						'key'     => 'first_name',
						'value'   => $search,
						'compare' => 'LIKE',
					),
					array(
						'key'     => 'last_name',
						'value'   => $search,
						'compare' => 'LIKE',
					),
				),
			);

			// Fetch the results.
			$user_query2 = new WP_User_Query( $args2 );
			$list2       = $user_query2->get_results();
		}

		// Merge the results of the 2 first lists.
		$this->items = array_merge( $list1, $list2 );

		// Remove duplicates.
		$this->items = array_unique( $this->items, SORT_REGULAR );

		// Now get a list filtered by membership fee.
		$fee_filter = intval( $_REQUEST['fee-filter'] ?? -1 );
		if ( $fee_filter >= 0 && $fee_filter <= 3 ) {
			// No nonce because this will be included in links as well.
			$time_pay_limit = gmdate( 'Ymd', time() - intval( $_REQUEST['after_limit_delay'] ?? 0 ) * 7 * 24 * 3600 );
			switch ( $fee_filter ) {
				case 0:
					$args3 = array(
                        // phpcs:ignore
						'meta_query' => array(
							'relation' => 'OR',
							array(
								'key'     => 'membership_limit_date',
								'compare' => 'NOT EXISTS',
							),
							array(
								'key'     => 'membership_limit_date',
								'value'   => '',
								'compare' => '=',
							),
						),
					);
					break;

				case 1:
					$args3 = array(
                        // phpcs:ignore
                        'meta_query' => array(
							'relation' => 'AND',
							array(
								'key'     => 'membership_limit_date',
								'compare' => 'EXISTS',
							),
							array(
								'key'     => 'membership_limit_date',
								'value'   => '',
								'compare' => '!=',
							),
							array(
								'key'     => 'membership_limit_date',
								'value'   => $time_pay_limit,
								'compare' => '<',
							),
						),
					);
					break;

				case 2:
					$args3 = array(
                        // phpcs:ignore
                        'meta_query' => array(
							'relation' => 'AND',
							array(
								'key'     => 'membership_limit_date',
								'compare' => 'EXISTS',
							),
							array(
								'key'     => 'membership_limit_date',
								'value'   => '',
								'compare' => '!=',
							),
							array(
								'key'     => 'membership_limit_date',
								'value'   => gmdate( 'Ymd' ),
								'compare' => '<',
							),
							array(
								'key'     => 'membership_limit_date',
								'value'   => $time_pay_limit,
								'compare' => '>=',
							),
						),
					);
					break;

				case 3:
					$args3 = array(
                        // phpcs:ignore
                        'meta_query' => array(
							'relation' => 'AND',
							array(
								'key'     => 'membership_limit_date',
								'compare' => 'EXISTS',
							),
							array(
								'key'     => 'membership_limit_date',
								'value'   => '',
								'compare' => '!=',
							),
							array(
								'key'     => 'membership_limit_date',
								'value'   => gmdate( 'Ymd' ),
								'compare' => '>',
							),
						),
					);
					break;

				default:
					// Nothing to do but this should not append.
					$args3 = array();
			}

			// Fetch this list too.
			$user_query3 = new WP_User_Query( $args3 );
			$list3       = $user_query3->get_results();

			// Remove the results that are not in the 3rd list.
			$this->items = array_filter(
				$this->items,
				function ( $item ) use ( $list3 ) {
					foreach ( $list3 as $list3_item ) {
						if ( $item->ID === $list3_item->ID ) {
							// Found it, stop now.
							return true;
						}
					}

					// Not found.
					return false;
				}
			);
		}

		// If no sort, default to user_login.
		if ( ! empty( $_GET['orderby'] ) && in_array( $_GET['orderby'], array_keys( $this->get_columns() ), true ) ) {
			$this->order_by = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
		}

		// If no order, default to asc.
		if ( ! empty( $_GET['order'] ) && ( 'desc' === $_GET['order'] ) ) {
			$this->order_direction = 'desc';
		}

		// Sort result.
		usort( $this->items, array( &$this, 'usort_reorder' ) );

		// Pagination.
		$per_page     = 20;
		$current_page = $this->get_pagenum();
		$total_items  = ! empty( $this->items ) ? count( $this->items ) : 0;
		$this->items  = array_slice( $this->items, ( $current_page - 1 ) * $per_page, $per_page );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items, // Total number of items.
				'per_page'    => $per_page, // Items to show on a page.
				'total_pages' => ceil( $total_items / $per_page ), // Use ceil to round up.
			)
		);
	}

	/**
	 * Define the columns that are going to be used in the table.
	 *
	 * @return array $columns, the array of columns to use with the table
	 */
	public function get_columns(): array {
		return array(
			'first_name'            => esc_html__( 'First name', 'law-1901-association' ),
			'last_name'             => esc_html__( 'Last name', 'law-1901-association' ),
			'user_email'            => esc_html__( 'Email', 'law-1901-association' ),
			'membership_limit_date' => esc_html__( 'Membership fee', 'law-1901-association' ),
			'leading_team'          => esc_html__( 'Board member', 'law-1901-association' ),
		);
	}

	/**
	 * Define which columns are sortable.
	 *
	 * @return array[]
	 */
	protected function get_sortable_columns(): array {
		return array(
			'first_name'            => array( 'first_name', false ),
			'last_name'             => array( 'last_name', false ),
			'user_email'            => array( 'user_email', false ),
			'membership_limit_date' => array( 'membership_limit_date', false ),
			'leading_team'          => array( 'leading_team', false ),
		);
	}

	/**
	 * Display the rows of records in the table.
	 *
	 * @param stdClass $item The current item.
	 * @param string   $column_name The name of the column.
	 */
	protected function column_default( $item, $column_name ) {
		return $item->{$column_name};
	}

	/**
	 * Sorting function
	 *
	 * @param WP_User $item The current item.
	 *
	 * @return string
	 */
	protected function column_first_name( WP_User $item ): string {
		$actions = array(
			'Modifier' => '<a href="?page=law-1901-association%2Fmembers&id=' . $item->ID . '" target="_blank">' . esc_html__( 'Edit', 'law-1901-association' ) . '</a>',
		);

		return sprintf( '%1$s %2$s', $item->first_name, $this->row_actions( $actions ) );
	}

	/**
	 * Display the membership date in table.
	 *
	 * @param WP_User $item The current item.
	 *
	 * @return string
	 */
	protected function column_membership_limit_date( WP_User $item ): string {
		if ( empty( $item->membership_limit_date ) ) {
			return '';
		}

		$date = DateTime::createFromFormat( 'Ymd', $item->membership_limit_date );
		if ( false === $date ) {
			return '';
		}

		return $date->format( esc_attr__( 'm/d/Y', 'law-1901-association' ) );
	}

	/**
	 * Display the email in table.
	 *
	 * @param WP_User $item The current item.
	 *
	 * @return string
	 */
	protected function column_user_email( WP_User $item ): string {
		return '<a href="mailto:' . $item->user_email . '" target="_blank">' . $item->user_email . '</a>';
	}

	/**
	 * Display a textarea in the table.
	 *
	 * @param stdClass $item The current item.
	 * @param string   $column_name The name of the column.
	 *
	 * @return string
	 */
	protected function textarea_for_list( stdClass $item, string $column_name ): string {
		if ( empty( $item->{$column_name} ) ) {
			return '';
		}

		$short = '';

		// Text with a new line in it.
		if ( preg_match( "/\n/", $item->{$column_name} ) ) {
			$short = rtrim( strtok( $item->{$column_name}, "\n" ), "\r\n" );
		} elseif ( strlen( $item->{$column_name} ) > 30 ) {
			// Text too long.
			$short = $item->{$column_name};
		}

		// Display short text with tooltip.
		if ( ! empty( $short ) ) {
			return '<span ' .
					'title="' . str_replace( '"', '&quot;', $item->{$column_name} ) . '" ' .
					'style="cursor:help"' .
					'>' . nl2br( substr( $short, 0, 30 ) ) . '...</span>';
		}

		return $item->{$column_name};
	}

	/**
	 * Sort the data.
	 *
	 * @param WP_User $a First element to sort.
	 * @param WP_User $b Second element to sort.
	 *
	 * @return int
	 */
	protected function usort_reorder( WP_User $a, WP_User $b ): int {
		// Determine sort order.
		$result = strcmp( ! empty( $a->{$this->order_by} ) ? $a->{$this->order_by} : '', ! empty( $b->{$this->order_by} ) ? $b->{$this->order_by} : '' );

		// Send final sort direction to usort.
		return ( 'asc' === $this->order_direction ) ? $result : -$result;
	}
}
