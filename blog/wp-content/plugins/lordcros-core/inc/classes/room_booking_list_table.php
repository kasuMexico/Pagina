<?php
/*
 * Room Booking List Table Class
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( ! class_exists( 'LordCros_Core_Room_Booking_List_Table') ) {
	class LordCros_Core_Room_Booking_List_Table extends WP_List_Table {

		function __construct() {
			global $status, $page;
			parent::__construct( array(
				'singular'	=> '_booking',		//singular name of the listed records
				'plural'	=> 'room_bookings',	//plural name of the listed records
				'ajax'		=> false,			//does this table support ajax?
			) );
		}

		function column_default( $item, $column_name ) {
			switch( $column_name ) {
				case 'id':
				case 'date_from':
				case 'date_to':
				case 'created':
				case 'total_price':
					return $item[ $column_name ];
				default:
					return print_r( $item, true ); //Show the whole array for troubleshooting purposes
			}
		}

		function column_customer_name( $item ) {
			//Build row actions
			$link_pattern = 'admin.php?page=%1$s&action=%2$s&booking_id=%3$s';
			$actions = array(
				'edit'		=> '<a href="' . esc_url( sprintf( $link_pattern, 'room_bookings', 'edit', $item['id'] ) ) . '">' . esc_html__( 'Edit', 'lordcros-core' ) . '</a>',
				'delete'	=> '<a href="' . esc_url( sprintf( $link_pattern, 'room_bookings', 'delete', $item['id'] . '&_wpnonce=' . wp_create_nonce( 'booking_delete' ) ) ) . '">' . esc_html__( 'Delete', 'lordcros-core' ) . '</a>',
			);
			$content = '<a href="' . esc_url( sprintf( $link_pattern, 'room_bookings', 'edit', $item['id'] ) ) . '">' . esc_html( $item['first_name'] . ' ' . $item['last_name'] ) . '</a>';
			//Return the title contents
			return sprintf( '%1$s %2$s', $content , $this->row_actions( $actions ) );
		}

		function column_room_name( $item ) {
			return '<a href="' . esc_url( get_edit_post_link( $item['post_id'] ) ) . '">' . esc_html( $item['room_name'] ) . '</a>';
		}

		function column_status( $item ) {
			switch( $item['status'] ) {
				case 'pending':
					return esc_html__( 'Pending', 'lordcros-core' );
				case 'new':
					return esc_html__( 'New', 'lordcros-core' );
				case 'confirmed':
					return esc_html__( 'Confirmed', 'lordcros-core' );
				case 'canceled':
					return esc_html__( 'Canceled', 'lordcros-core' );
				}
			return $item['status'];
		}

		function column_cb( $item ) {
			return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id'] );
		}

		function get_columns() {
			$columns = array(
				'cb'				=> '<input type="checkbox" />', //Render a checkbox instead of text
				'id'				=> esc_html__( 'ID', 'lordcros-core' ),
				'customer_name'		=> esc_html__( 'Customer Name', 'lordcros-core' ),
				'date_from'			=> esc_html__( 'Date From', 'lordcros-core' ),
				'date_to'			=> esc_html__( 'Date To', 'lordcros-core' ),
				'room_name'			=> esc_html__( 'Room Name', 'lordcros-core' ),
				'total_price'		=> esc_html__( 'Price', 'lordcros-core' ),
				'created'			=> esc_html__( 'Created Date', 'lordcros-core' ),
				'status'			=> esc_html__( 'Status', 'lordcros-core' ),
			);
			return $columns;
		}

		function get_sortable_columns() {
			$sortable_columns = array(
				'id'		=> array( 'id', false ),
				'date_from'	=> array( 'date_from', false ),
				'date_to'	=> array( 'date_to', false ),
				'room_name'	=> array( 'room_name', false ),
				'status'	=> array( 'status', false ),
			);
			return $sortable_columns;
		}

		function get_bulk_actions() {
			$actions = array(
				'bulk_delete'			=> esc_html__( 'Delete', 'lordcros-core' ),
				'bulk_mark_new'			=> esc_html__( 'Mark as New', 'lordcros-core' ),
				'bulk_mark_confirmed'	=> esc_html__( 'Mark as Confirmed', 'lordcros-core' ),
				'bulk_mark_canceled'	=> esc_html__( 'Mark as Canceled', 'lordcros-core' ),
			);
			return $actions;
		}

		function process_bulk_action() {
			global $wpdb;
			//Detect when a bulk action is being triggered...

			if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

				$nonce = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
				$action = 'bulk-' . $this->_args['plural'];

				if ( ! wp_verify_nonce( $nonce, $action ) )
					wp_die( 'Sorry, your nonce did not verify' );
			}

			if ( 'bulk_delete' === $this->current_action() ) {
				$selected_ids = $_GET[ $this->_args['singular'] ];
				$how_many = count( $selected_ids );
				$placeholders = array_fill( 0, $how_many, '%d' );
				$format = implode( ', ', $placeholders );
				
				$current_user_id = get_current_user_id();
				$post_table_name = esc_sql( $wpdb->prefix . 'posts' );
				$sql = '';

				$sql = sprintf( 'DELETE FROM %1$s WHERE id IN ( %2$s )', LORDCROS_ROOM_BOOKINGS_TABLE, "$format" );
			
				$wpdb->query( $wpdb->prepare( $sql, $selected_ids ) );

			} elseif ( 'bulk_mark_new' === $this->current_action() || 'bulk_mark_confirmed' === $this->current_action() || 'bulk_mark_canceled' === $this->current_action() ) {
				$selected_ids = $_GET[ $this->_args['singular'] ];
				$how_many = count( $selected_ids );
				$placeholders = array_fill( 0, $how_many, '%d' );
				$format = implode( ', ', $placeholders );
				$current_user_id = get_current_user_id();
				$post_table_name = esc_sql( $wpdb->prefix . 'posts' );
				$sql = '';
				switch( $this->current_action() ) {
					case 'bulk_mark_new':
						$status = 'new';
						break;
					case 'bulk_mark_confirmed':
						$status = 'confirmed';
						break;
					case 'bulk_mark_canceled':
						$status = 'canceled';
						break;
				}
			
				$sql = sprintf( 'UPDATE %1$s SET status="%2$s" WHERE id IN (%3$s)', LORDCROS_ROOM_BOOKINGS_TABLE, $status, "$format" );

				$wpdb->query( $wpdb->prepare( $sql, $selected_ids ) );
				wp_redirect( admin_url( 'admin.php?page=room_bookings&bulk_update=true&items=' . $how_many ) );
				exit;
			}
		}

		function prepare_items() {
			global $wpdb;
			
			$per_page = 10;
			$columns = $this->get_columns();
			$hidden = array();
			$sortable = $this->get_sortable_columns();
			
			$this->_column_headers = array( $columns, $hidden, $sortable );
			$this->process_bulk_action();
			
			$bookingby = ( ! empty( $_REQUEST['bookingby'] ) ) ? sanitize_sql_bookingby( $_REQUEST['bookingby'] ) : 'id'; //If no sort, default to title
			$booking = ( ! empty( $_REQUEST['booking'] ) ) ? sanitize_text_field( $_REQUEST['booking'] ) : 'desc'; //If no booking, default to desc
			$current_page = $this->get_pagenum();
			$post_table_name  = esc_sql( $wpdb->prefix . 'posts' );

			$where = "1=1";
			if ( ! empty( $_REQUEST['post_id'] ) ) {
				$where .= " AND lordcros_booking.post_id = '" . esc_sql( lordcros_core_room_org_id( $_REQUEST['post_id'] ) ) . "'";
			}
			if ( ! empty( $_REQUEST['date_from'] ) ) {
				$where .= " AND lordcros_booking.date_from >= '" . esc_sql( $_REQUEST['date_from'] ) . "'";
			}
			if ( ! empty( $_REQUEST['date_to'] ) ) {
				$where .= " AND lordcros_booking.date_to <= '" . esc_sql( $_REQUEST['date_to'] ) . "'";
			}
			if ( ! empty( $_REQUEST['status'] ) ) {
				$where .= " AND lordcros_booking.status = '" . esc_sql( $_REQUEST['status'] ) . "'";
			}

			$sql = $wpdb->prepare( 'SELECT lordcros_booking.*, room.post_title as room_name
				FROM %1$s as lordcros_booking
				INNER JOIN %2$s as room ON lordcros_booking.post_id = room.ID
				WHERE ' . $where . ' ORDER BY %3$s %4$s
				LIMIT %5$s, %6$s', LORDCROS_ROOM_BOOKINGS_TABLE, $post_table_name, $bookingby, $booking, $per_page * ( $current_page - 1 ), $per_page );

			$data = $wpdb->get_results( $sql, ARRAY_A );

			$sql = $wpdb->prepare( 'SELECT COUNT( * ) FROM %1$s as lordcros_booking INNER JOIN %2$s as room ON lordcros_booking.post_id = room.ID WHERE ' . $where, LORDCROS_ROOM_BOOKINGS_TABLE, $post_table_name );
			$total_items = $wpdb->get_var( $sql );

			$this->items = $data;
			$this->set_pagination_args( array(
				'total_items'	=> $total_items,
				'per_page'		=> $per_page,
				'total_pages'	=> ceil( $total_items / $per_page )
			) );
		}
	}
}