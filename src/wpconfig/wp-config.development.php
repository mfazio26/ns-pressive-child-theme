<?php
/**
 * Development environment config settings
 *
 * Enter any WordPress config settings that are specific to this environment 
 * in this file.
 * 
 * @package    Studio 24 WordPress Multi-Environment Config
 * @version    1.0
 * @author     Studio 24 Ltd  <info@studio24.net>
 */
  

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'notsalmon_dev_db');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

// define('WP_HOME', "localhost") // homepage
// define('WP_SITEURL', 'localhost') // path

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', true);

/**
 * For developers: WordPress debug logging.
 *
 * Change this to true causes all errors to also be saved to a `debug.log` 
 * file inside the `wp-content/` directory. This is also useful in debugging 
 * AJAX requests where errors otherwise might not be displayed in full.
 */
define('WP_DEBUG_LOG', true );

/**
 * For developers: WordPress debugging scripts.
 *
 * Change this to true to use the "dev" versions of core CSS and JavaScript 
 * files rather than the minified versions versions that are normally loaded.
 */
define('SCRIPT_DEBUG', true);

/**
 * For developers: WordPress debugging admin scripts.
 *
 * Change this to true to use the "dev" versions of core CSS and JavaScript 
 * files rather than the minified versions versions that are normally loaded.
 * Please note, this only effecs the Admin Area (backend)
 */
define('CONCATENATE_SCRIPTS', false );