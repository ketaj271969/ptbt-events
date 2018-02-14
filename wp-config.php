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
define('DB_NAME', 'ptbt-events');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

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
define('AUTH_KEY',         '/dYaE}n)-&_+T+AbdiO`zZP&gs5w?<KVK7hl3Mu+b;;e)}Y!w~ml$(Pi[o<|DzYG');
define('SECURE_AUTH_KEY',  '}RgA5;N3B|Kd/X>k;t)4E[ya#p-_2/j+=XE,i|Xuq[9nnA8&~-G(cDub;:VP%.]=');
define('LOGGED_IN_KEY',    '9=)B7tyK.`Io$-YypGg|-(v_k1*Vf_n*Dk?Rb]0Wj^|9p~^3=j=?8ZI~Fjj&,P3C');
define('NONCE_KEY',        'R^)cX$H$=71CXyf%K8)`6IkGNz`/-u~s1zV(m}^K+$z;3DKMA1*Aq<[^yu$]WICu');
define('AUTH_SALT',        's^vN@nYc$6U^;ALN ?#d_k`klvLD=$YAq)@&SoaK_~#/n2:k@l_.%e=U3Y>6I=#2');
define('SECURE_AUTH_SALT', '|Q,;pw}[?PW}9w3K3^SDHY-9FwtGmJAZ|h#Qe*t6K/jB ^.P5k?^!<3qm435QZ^ ');
define('LOGGED_IN_SALT',   'jO0SkO-+XaorZusy8j9OGyd0,<Km?X>.NS{xPl^f:`jq#yoq{dI@prhQJN48#f)O');
define('NONCE_SALT',       'c@wzhc&>DL4o7Yn0]%F6!iV.rw6+Q-)v;;<adAW?v}X>RW82%%dz;{0(1C#(eYTY');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'ptbte_';

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
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
