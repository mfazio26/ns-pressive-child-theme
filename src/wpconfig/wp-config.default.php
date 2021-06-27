<?php
/**
 * Default config settings
 *
 * Enter any WordPress config settings that are default to all environments
 * in this file. These can then be overridden in the environment config files.
 * 
 * Please note if you add constants in this file (i.e. define statements) 
 * these cannot be overridden in environment config files.
 * 
 * @package    Studio 24 WordPress Multi-Environment Config
 * @version    1.0
 * @author     Studio 24 Ltd  <info@studio24.net>
 */
  

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         '');
define('SECURE_AUTH_KEY',  '');
define('LOGGED_IN_KEY',    '');
define('NONCE_KEY',        '');
define('AUTH_SALT',        '');
define('SECURE_AUTH_SALT', '');
define('LOGGED_IN_SALT',   '');
define('NONCE_SALT',       '');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'nswp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * Increase memory limit. 
 */
define('WP_MEMORY_LIMIT', '64M');

/**
 * Disable all automatic updates.
 */
define('AUTOMATIC_UPDATER_DISABLED', true);

/**
 * Enable/Disable all core updates. Note, you can also set the second parameter to 
 * the String `'minor'` to allow updates for only minor releases.
 */
// define('WP_AUTO_UPDATE_CORE', false);

/**
 * Enable/Disable Post Revisions feature.
 */
define('WP_POST_REVISIONS', false);

/**
 * Enable/Disable display of Admin Bar. Please note, `false` shows the admin bar 
 * and `true` will hide it.
 */
define('WP_TURN_OFF_ADMIN_BAR', false);

/**
 * Enable/Disable WordPress CRON jobs – which handles tasks such as publishing 
 * scheduled posts. Please note, WordPress CRON jobs are not "real" CRON jobs and 
 * are based/triggered by user visits.
 */
define('DISABLE_WP_CRON', false);

/**
 * Set the Autosave time in seconds. Default is 60 seconds.
 */
define('AUTOSAVE_INTERVAL', 120);

/**
 * Set the days before emptying the trash instead of keeping junk long term.
 */
define('EMPTY_TRASH_DAYS', 3);

/**
 * Enabled/Disable the file editor for plugin and theme files directly in the 
 * Admin Area.
 */
define('DISALLOW_FILE_EDIT', true);















