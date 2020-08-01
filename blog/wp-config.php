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
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'jalexsta_727' );

/** MySQL database username */
define( 'DB_USER', 'jalexsta_727' );

/** MySQL database password */
define( 'DB_PASSWORD', '1CEF6A29B0f8drt3s5a4z7' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'n@U0apSJ-[z%>u`g_x?Hbd9Qi E)-S~32P2!_sJkE+)@-;h=M&-D@4O a%{UW0MK');
define('SECURE_AUTH_KEY',  '_k>p$a#yf]mV$OPkS>ESRT[0KuNpv9M{(L,va|.pp#^7y,+5Oh,c&;(sF#@T$+*b');
define('LOGGED_IN_KEY',    '77y$>^-r=KV+H|`pjw+t_j81qf?qF:p)|NBs)&4}=XVaf*}=ZcDb>Qy|0888XJ[/');
define('NONCE_KEY',        'M|W{^oKB z7Zadh10JJX/kbsjiaY#5^0D;jJbW>K@W5Q4s7_IWw-5FP|;n)+f.|1');
define('AUTH_SALT',        'M>)+yc`]TeM2zi;e=#/r%(,vF.R|wQc9;B;+hRu6)l0xM_iRTb*+qDpe+k; B6=*');
define('SECURE_AUTH_SALT', 'A?j|mg!R~U0$^X!~G|D^EQ2P>0cvb6`zo`*ce]N+y$Ef[}) A+#+6^jU{g3*^|<`');
define('LOGGED_IN_SALT',   'JXet.dSTE5j&9R4fvBYq@<yZ|bTN`gBG(Ml]Kcm@,5-^m$*7M1wYb9~|#z[+Z!pX');
define('NONCE_SALT',       'CLk$I:[R07~Qf-}5$Y9U(.3Ys(sA|%+AXX_-Wp-1_s6jgL7f>p!Um0F}#~j[oymV');


/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = '727_';




/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
