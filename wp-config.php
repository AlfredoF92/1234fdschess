<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', "bmchessdatabase" );

/** Database username */
define( 'DB_USER', "root" );

/** Database password */
define( 'DB_PASSWORD', "" );

/** Database hostname */
define( 'DB_HOST', "localhost" );

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
define( 'AUTH_KEY',         'ixw0kyatjcrfoouwtyjffa7qjuiwiraze9anibmwimo7xdtamwbjrhqsq3hpjdxw' );
define( 'SECURE_AUTH_KEY',  'pphwkbjwgpsleia4p6znvnnt9o4ecqtmshj8nnwsmaxqoxokr9zl9wrwlkvkizne' );
define( 'LOGGED_IN_KEY',    'j0hpos26g5cjcdlguiasgiefxqteytyeqyyeawelet51sitqf0gcnbfyryivn3ok' );
define( 'NONCE_KEY',        'bhdmqrej1ybucamioks6eenkihzwurmnbbp7cpzopiyj29em2gu0h4vp5g9mgyfs' );
define( 'AUTH_SALT',        'xcfylao2ys7tp6vwomajsti0uepwy5rn9mkonf0pjf4ammp8r2drmnm0kni093mw' );
define( 'SECURE_AUTH_SALT', 'zf2xvavqjxnf9cv3jvxdgx35oh6xza9e09tczowmgtbzb3y3dmhjqu0mydefnd2q' );
define( 'LOGGED_IN_SALT',   'xncpdhxpadn7b3hfwn3fpda39twcrqdsyolqt7gqhrypkn1ahvrobocpu9mngpnc' );
define( 'NONCE_SALT',       '93nx7q51gvtukiofimh2nkjfchqbsd5yw2nrno6jyr1ilt0zmeljksmqdwbk9jce' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wptf_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
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
