<?php
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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'smillegaming' );

/** Database username */
define( 'DB_USER', 'smillegaming' );

/** Database password */
define( 'DB_PASSWORD', 'rTQUqajssoy44KXyb6Y7' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1:3306' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          'Se>m+s@u5=Q,LJ/8Ps64:s4<=ubjW`?~{H!lf?D!fX3~&E#T]D^/S]d-0%0YdFdK' );
define( 'SECURE_AUTH_KEY',   '1e6O:WlI9fA^q|EFQ-ui-I,MQ%_(/ul{a:6b`A&,~}h}h$9WC@}G>4)OiMDrOpl)' );
define( 'LOGGED_IN_KEY',     'pT%4]P FCNsR0=twYL`w|AnJQQ]&XiO]EH!atJ>[!{?Vp8,,`Pi!T_Rsq3w-w4t;' );
define( 'NONCE_KEY',         'h*ckJ}])H0a{(6&6]T@z:K|? Q3~Elg!VjKlbsr4i>0ElJra_-GM?,eM/=AL_n6i' );
define( 'AUTH_SALT',         'Q8JnUTJ[OrBqtK;{Rb^A%jmA^a=F+jfyt~1I<DWa(GJ`G+[t..t+Lt8@n4g~csoT' );
define( 'SECURE_AUTH_SALT',  'qS<8(A)}cfp..Xb(%P9v2$MyC=&j_tJyj)P/A}IbVQr2&JcuI}Yqg*p3o-d!w_N<' );
define( 'LOGGED_IN_SALT',    'P3-?D:K%vJl- @P@pXQy p5D&C@bM5yYufGo(o,B0qWkFI&dx.,lb^qj<kpB>9mI' );
define( 'NONCE_SALT',        '|QMB2+{Z}8|}h%PBn[W>Gpuw P1^R*!tlwT^PZeZDZ=vX/#e=b4F$sl)]]klCg4B' );

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
define( 'WP_DEBUG', true ); // Modo de depuração ativo
define( 'WP_DEBUG_LOG', true ); // Registrar erros em um log
define( 'WP_DEBUG_DISPLAY', false ); // Não exibir erros na tela

/* Add any custom values between this line and the "stop editing" line. */

define( 'FS_METHOD', 'direct' );
define( 'CONCATENATE_SCRIPTS', false );
define( 'AUTOSAVE_INTERVAL', 600 );
define( 'WP_POST_REVISIONS', 5 );
define( 'EMPTY_TRASH_DAYS', 21 );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
