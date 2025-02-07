<?php

defined( 'ABSPATH' ) || exit;

/*
 * Create Custom Tables
 */
if ( ! function_exists( 'lordcros_core_create_extra_tables' ) ) {
	function lordcros_core_create_extra_tables() {
		global $wpdb;
		$installed_db_ver = get_option( 'lordcros_db_version' );

		if ( $installed_db_ver != LORDCROS_DB_VERSION ) {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			
			$sql = "CREATE TABLE " . LORDCROS_ROOM_BOOKINGS_TABLE . " (
						id bigint(20) NOT NULL AUTO_INCREMENT,
						first_name varchar(255) DEFAULT NULL,
						last_name varchar(255) DEFAULT NULL,
						email varchar(255) DEFAULT NULL,
						phone varchar(255) DEFAULT NULL,
						country varchar(255) DEFAULT NULL,
						address1 varchar(255) DEFAULT NULL,
						address2 varchar(255) DEFAULT NULL,
						city varchar(255) DEFAULT NULL,
						state varchar(255) DEFAULT NULL,
						zip varchar(255) DEFAULT NULL,
						arrival varchar(255) DEFAULT NULL,
						special_requirements text CHARACTER SET latin1,
						total_price decimal(16,2) DEFAULT '0.00',
						room_price decimal(16,2) DEFAULT '0.00',
						service_price decimal(16,2) DEFAULT '0.00',
						discounted_price decimal(16,2) DEFAULT '0.00',
						adults tinyint(1) unsigned DEFAULT '0',
						kids tinyint(1) unsigned DEFAULT '0',
						date_from date DEFAULT NULL,
						date_to date DEFAULT NULL,
						extra_service text CHARACTER SET latin1,
						post_id bigint(20) DEFAULT NULL,
						coupon_code varchar(255) DEFAULT NULL,
						payment varchar(255) DEFAULT NULL,
						transaction_id varchar(255) DEFAULT NULL,
						woo_order_id bigint(20) DEFAULT NULL,
						woo_product_id bigint(20) DEFAULT NULL,
						status varchar(20) DEFAULT 'new',
						deposit_paid tinyint(1) DEFAULT '0',
						deposit_price decimal(16,2) DEFAULT '0.00',
						currency_symbol varchar(8) DEFAULT NULL,
						other text CHARACTER SET latin1,
						created datetime DEFAULT NULL,
						mail_sent tinyint(1) DEFAULT '0',
						updated datetime DEFAULT NULL,
						user_id bigint(20) unsigned DEFAULT NULL,
						PRIMARY KEY	(id)
					) DEFAULT CHARSET=utf8;";
			dbDelta($sql);

			update_option( 'lordcros_db_version', LORDCROS_DB_VERSION );
		}

		if ( '1.0' <= $installed_db_ver ) { 

			// add "user_id" column into lordcros_room_order table
			$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
				WHERE table_name = '" . LORDCROS_ROOM_BOOKINGS_TABLE . "' AND column_name = 'user_id'" );

			if ( empty( $row ) ) { 
				$wpdb->query( "ALTER TABLE " . LORDCROS_ROOM_BOOKINGS_TABLE . " ADD user_id bigint(20) unsigned DEFAULT NULL" );
			}
	  	}

	}
}