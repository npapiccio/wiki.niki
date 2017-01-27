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

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'db647656195');

/** MySQL database username */
define('DB_USER', 'dbo647656195');

/** MySQL database password */
define('DB_PASSWORD', 'papusp3t9');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '&W1AXaoE>)@pAAMr^_DQB~ZgyZ#ogHxf3jAY (.!&D|wMM%w#D`aJECk|HcE;-tn');
define('SECURE_AUTH_KEY',  'hSKwgVEyND1[9NX-<#^_e~}325rR!/Zn$wsFMwu2^$G=^4MS^oFQwf[c`a t+fY)');
define('LOGGED_IN_KEY',    '.Qce!ovW}J5><~>#r<YPz@ 5Bxx8/3;b|?Ra(0X>|(b!JV9Uo(*k&_-g34=%OcS.');
define('NONCE_KEY',        '#O?}m]Nf@RS?.Fz,r<}1^mX:1BXO~&!$cjdB@Z^hTO.^_*7ggBcETI5o{98VWS+/');
define('AUTH_SALT',        '-,wlQ>9>,,d$mXmiBDP4vbktJmb9;Ti*#SJhxXVP5vMTm@|@GJLj.n-b=v8grFDU');
define('SECURE_AUTH_SALT', '*d?9rzo>*VNy%Tij%v=XeKS_cOx,+ZKf8)ES#Y_2){<9)Iw`8lsh^O;Tyi2F9PMa');
define('LOGGED_IN_SALT',   'XBjUTJW,A_P9cr-y@Q-PuLMgkj$,N$4Ebe9P+([)!%M>)(LE&(EPUV:zhaYt!_32');
define('NONCE_SALT',       'CM=s~,<G3Y;<YyS;/vn5[S&Fb2b~cxmV}cpdp./H~j<,-SnD%PW^:kn*)f5tYv|b');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
