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
define( 'DB_NAME', 'wp-ecom' );

/** Database username */
define( 'DB_USER', 'valet' );

/** Database password */
define( 'DB_PASSWORD', '12345' );

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
define( 'AUTH_KEY',         'xYeNFH:p@zNGC[1m5GgZ&99E8n*:#0e* y> p+ql^j]5pRe=4)u0a*(gGRJi)nMh' );
define( 'SECURE_AUTH_KEY',  'V 215i]}u5x6*:f^+o`AJoIYbd/Dr6o|biaoKcLd)&ZLBx)J}RFX*r=8~%}%bjb#' );
define( 'LOGGED_IN_KEY',    '<=1B8@f~f-M-heC%|hr)+]8w=?Qx:2l:oN@Tm FonFkVax8yLxJgjuYR3s+$e*k=' );
define( 'NONCE_KEY',        'G^zC~%Q81~,Imk8jEbXr[W^/eVA~!lZ!ksv>BUgh-/JY0JB{3ed}u3pT>c7$u{Ib' );
define( 'AUTH_SALT',        '`RQbH46v16`%OTE/?t?Q4jKfJKT+4BArm+x5WXi0=bCWzo7![GVN=+Y72hh$N{4,' );
define( 'SECURE_AUTH_SALT', '}i&#my.4g[wee!s@tKZc.mj}}@d~V8Q.>n=9`2|+9zQD-UBvUOB*{s=C4T|5f.#U' );
define( 'LOGGED_IN_SALT',   'qd039QJ*3-D,EW33CDrh~G}dH`iqb`YJO /SaDf{CJIqUF%5.+P~0I4{l&a==Ne)' );
define( 'NONCE_SALT',       'VmGNmfFrX^QPY|}*O29q|E^D$v8a}DSS(`YD<DHRiZ*/Vp5!.: f|%gW)tOocrFa' );

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
$table_prefix = 'Cartzy_';

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
