<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'pictobooth' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '*(,(JM4w,ao^w45bRCl-ULClaJhbAYh/uCz,V-t5tCDk!qw@dkL9Jx`A4~q$<u&k' );
define( 'SECURE_AUTH_KEY',  'n)4jpO:j]qLd}YJI)rPd@):5!9/3uQR=}J*HbhQ@5 G.[Gn|=;L$b8+ePs.j/}a:' );
define( 'LOGGED_IN_KEY',    'nq+Ll1^yjzU(]s5jq-UliM@riE{HXm`C*pxWj.WXno0S >heO}e/7/-ifcy/xaqm' );
define( 'NONCE_KEY',        '{xhp/ ox2]n-99Q1eaQP02#r{oH 0BY1?ARj Ik-_2({`b&Bs^M>Rm2Uzmj/}(*K' );
define( 'AUTH_SALT',        'F1>yfl?v&7O@MCCro?I,Y[$KttI`&Sa@ 1{Cay^1,OyDG+OI-,a;>]I&4m>~/Lo!' );
define( 'SECURE_AUTH_SALT', 'FDm<J&*j@1,*~#Y*K4&<4x&Jtuu|E!A%|]X>wPf1kb0^`%e+ivwNsj(rz`[Xh#[h' );
define( 'LOGGED_IN_SALT',   'Unj@7mmxU!R&~AGR&(,h-;Ze,Dtf2w-#B,S+*p 0]X%@o) kPL@xE9}<wkpV`OY ' );
define( 'NONCE_SALT',       'h{k:($[xDOT%#8/}c2KD9qh.!rq!s).WG];,x:g)J*u|zcF`yBXNdznPy ~YOt32' );

/**#@-*/

/**
 * WordPress Database Table prefix.
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

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
