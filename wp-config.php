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
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'trefilieva-illustration' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

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
define( 'AUTH_KEY',         '}Zio>K#wE9*(&aB63]xWKa*(:iK/R-f,Oh4mDoru**5C@k:Lz@;khK~v8-J$D|U(' );
define( 'SECURE_AUTH_KEY',  '?;%,*FSn:Lo>0/EV>>jNwi8~LMIcLRLsxQ`L)$L&|6bS<ttPz;52<6{#yK{{zSfm' );
define( 'LOGGED_IN_KEY',    'v7u<LOhNEDn2dOB59KeN#+sCjYt5BNMzP$64q7t,J+cqhItFn2m$KBFsJ%+Q$J3[' );
define( 'NONCE_KEY',        'pqCq!Ne$as+p$br5<B^OxP<V#F)CwjYyRIZG PelRXkg1aTPS;U I-le`!LS1.Z*' );
define( 'AUTH_SALT',        '?riyus,D2o;X4.XO=,eO|k9a[K.Mp2kEr~)`wa0j2G0!/r!n]8;]bq!|ksdCcr]V' );
define( 'SECURE_AUTH_SALT', '-iR+h8`C6WE,EL(G&#GMe-!n_fWw5#t@/Z1JWW,]k+f R{@WHh3HdK5G}$cL_ZvP' );
define( 'LOGGED_IN_SALT',   '[^q:~9xOT(qQ0gK,o+cy6v#$gJxpLu_Rovy3jQ3I.%P.Rr?VvWo?8o3fw@ToDVxV' );
define( 'NONCE_SALT',       '9bFr&vuFG$Q7YOKL$rl:`6`oLrLQ|<R?#L?j|}E{ <amXf@H=y+DE2jbO<@P_]of' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'tril_';

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
