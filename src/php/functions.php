<?php

/**
 * Thrive Themes Pressive Child Theme: notsalmon.com
 *
 * Please note, this file is only intended to create our class autoloader, and 
 * instantiate our main Theme bootstrap/entry-point file. This child-theme uses namespaced 
 * classes and OOP to organize and encapsulate all the required functionality into logical 
 * chunks. This makes it much easier to find, add, update – as well as ensuring that our 
 * variables and methods are never overwritten or conflict with another theme or plugin.
 *
 * It is acceptable to add code to this file for development purposes only. However, 
 * before committing any changes/updates to a live environment (production or staging) – 
 * all extra code should be removed entirely or refactored to use the existing class/OOP 
 * implementation. There is nothing you can do in this file that you can't do BETTER and 
 * CLEANER in a class! Save yourself some headaches...use a class.
 *
 * @since      1.0.0
 * @package    notsalmon
 * @author     Mike Fazio <mike@mfazio.com>
 */


// ======= DO NOT EDIT =================================================================
// Load the PS4 Autoloader file.
require_once(__DIR__ . "/notsalmon/utils/Psr4_Autoloader.php");
// Instantiate and configure the PS4 Autoloader and point it to our "notsalmon" pacakge.
$ns_loader = new notsalmon\utils\Psr4_Autoloader();
$ns_loader->register();
$ns_loader->addNamespace("notsalmon", __DIR__ . "/notsalmon");
// Instantiate the main child-theme's bootstrap/entry-point class. 
// Start it up! And away we go!
$ns_theme = new notsalmon\Theme();

// =====================================================================================
