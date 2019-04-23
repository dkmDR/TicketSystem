<?php

namespace lib;

/**
 * PHP version 5.4
 *
 * LICENSE: This source file is subject to the MIT license
 * that is available through the world-wide-web at the following URI:
 * https://opensource.org/licenses/MIT.
 *
 * @category   Configuration
 * @package    lib
 * @author     Miguel Peralta <mcalderon0329@gmail.com>
 * @license    https://opensource.org/licenses/MIT  MIT license
 * @since      File available since Release 1.0
 */
class Config
{
    /**
     * @var bool define if application will connect to database ( TRUE OR FALSE )
     */
    public static $_USE_DB,
        /**
         *
         * @var bool define if application is in development, present defference errors
         */
        $_DEVELOPING_,
        /**
         * set database to application
         * @var string
         */
        $_DATABASE_,
        /**
         * directory all modules
         * @var string
         */
        $_MODULES_,
        /**
         * directory all layouts
         * @var string
         */
        $_LAYOUTS_,
        /**
         * HOST APPLICATION
         * @var string
         */
        $_HOST_,
        /**
         * MAIN DIRECTORY APPLICATION
         * @var string
         */
        $_MAIN_DIRECTORY,
        /**
         * DIRECTORY LOGS
         * @var string
         */
        $_LOGS,
        /**
         * root directory to js
         * @var string
         */
        $_ROOT_JS,
        /**
         * root directory to css
         * @var string
         */
        $_ROOT_CSS,
        /**
         * LAYOUT ERROR
         * @var string
         */
        $_ERROR_LAYOUT,
        /**
         * define route root key
         * @var string
         */
        $_ROUTES_ROOT_KEY,
        /**
         * TITLE APPLICATION
         * @var string
         */
        $_TITLE_APP,
        /**
         * Specify if the application will use session storage
         * @var boolean
         */
        $_USING_SESSION_STORAGE,
        /**
         * Table data to sessions
         * @var string
         */
        $_DATA_SESSION,
        /**
         * Field STORAGE session id
         * @var string
         */
        $_SESSION_ID,
        /**
         * FIELD STORAGE THE DATA
         * @var string MUST BE NULL
         */
        $_SESSION_DATA,
        /**
         * AUTOINCREMENT FIELD
         * @var string
         */
        $_SERIAL_ID,
        /**
         * @var string
         */
        $_LOCAL_SERVERS,
        /**
         * @var bool
         */
        $_CORS,
        /**
         * @var string
         */
        $_MAIN_HEADER,
        /**
         * @var string
         */
        $_MAIN_FOOTER
        ;


    public static function constructor()
    {
        //search configurations
        $jsonConfiguration = file_get_contents("lib/Configuration.json");
        $decode = json_decode($jsonConfiguration);

        self::$_USE_DB = $decode->useDatabase;
        self::$_DEVELOPING_ = $decode->appInDeveloping;
        self::$_DATABASE_ = $decode->instanceDatabase;
        self::$_MODULES_ = $decode->dirModules;
        self::$_LAYOUTS_ = $decode->dirLayouts;
        self::$_HOST_ = $decode->server;
        self::$_MAIN_DIRECTORY = $decode->dirProject;
        self::$_LOGS = $decode->dirLogs;
        self::$_ROOT_JS = $decode->dirMainFileJs;
        self::$_ROOT_CSS = $decode->dirMainFileCss;
        self::$_ERROR_LAYOUT = $decode->fileLayoutError;
        self::$_ROUTES_ROOT_KEY = $decode->defaultKeyRouting;
        self::$_TITLE_APP = $decode->title;
        self::$_USING_SESSION_STORAGE = $decode->useStorageSession;
        self::$_DATA_SESSION = $decode->storageTable;
        self::$_SESSION_ID = $decode->storageIdField;
        self::$_SESSION_DATA = $decode->StorageDataField;
        self::$_SERIAL_ID = $decode->primaryKeyField;
        //define local servers
        self::$_LOCAL_SERVERS = explode(",", $decode->localservers);
        //define is will be use cors
        self::$_CORS = $decode->cors;
        self::$_MAIN_HEADER = $decode->mainHeaderLayout;
        self::$_MAIN_FOOTER = $decode->mainFooterLayout;

    }

}

Config::constructor();