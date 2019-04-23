<?php

/**
 * Show or Hide Errors
 */

ini_set('display_errors', 0);

use lib\Config;
use lib\SessionStorage;

/**
 * defined variables
 */

/**
 * base dir
 */
define('_BASE_', __DIR__);

/*
 * separator
 */
define('_DS_', DIRECTORY_SEPARATOR);

/*
 * directory all module
 */
define('_DIR_MODULE_', Config::$_MODULES_);

/*
 * directory all layout
 */
define('_LAYOUT_', Config::$_LAYOUTS_);

/*
 * charge styles, js, img
 */
define('_HOST_', Config::$_HOST_);

/*
 * application know which is the main directory (Try not use this variable)
 */
define('_DIRECTORY_', Config::$_MAIN_DIRECTORY);

/**
 * define variable for main directory
 */
define('_MAIN_DIRECTORY', Config::$_MAIN_DIRECTORY);

/*
 * application exec succesfully
 */
define('_EXEC_APP', TRUE);

/**
 * use session storage
 */

if (Config::$_USING_SESSION_STORAGE) {
    $SESSION_STORAGE = new SessionStorage();
}

/**
 * start session
 */
if (!session_id()) {
    session_start();
    Factory::setSession();
}

/*to charge all library Logger...*/
Factory::bootstrapLogger();