<?php
/** Enable W3 Total Cache */
define('WP_CACHE', true); // Added by W3 Total Cache

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u557645733_blog_kasu' );

/** Database username */
define( 'DB_USER', 'u557645733_blog_kasu' );

/** Database password */
define( 'DB_PASSWORD', 'K;aZg&z0g' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'C!-^It[=9G2$i]d&W+}mn5(MHZzXTzvuEm>Rr&?}08Ovv#3;^%$$fB_gdF{g@l#H' );
define( 'SECURE_AUTH_KEY',  'z{5tWDiEZy.*ZDWG8-F7Pu{rH? &GDC*>Cp-OP?As8hhT8W{!mtz]YBQ+ugp%>#(' );
define( 'LOGGED_IN_KEY',    'q}}:6,V]oHt.16$;4G}_[P^I#4}`a!i^wY64hfozz~q{(BKHoLlg=qB0pHZ90YTq' );
define( 'NONCE_KEY',        '=Z[^r[N|xW>uZVZ*7v.j0mFsxuZVG5oLab[7[m}UMlN0gsdyZXS zKNRj(_I 8}g' );
define( 'AUTH_SALT',        'qVq>3RWUZ?v=@/pG$f@|9K@?xAfRxAgWE$;:WbvE:tup^T_]:bd,6Q&J&TMBLNu2' );
define( 'SECURE_AUTH_SALT', 'I.*]U!`]gt;E}iu&@<~c:xKgV.tMW#F03s{[yD`sPQV4lu#&~J?3Jf;<@R5,W{2.' );
define( 'LOGGED_IN_SALT',   '$l_:!.3dVSTFPty~ZPkamd%CIItPS]le~~BtB9oce@WGVa-hDF?aG( %L70[C%QV' );
define( 'NONCE_SALT',       'pAl-;mzqG(Dc[{QT]WD pL8a%wth!cM+=}z j(W/<7.A>D/MZKCF2SBn<ERkV*a2' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
