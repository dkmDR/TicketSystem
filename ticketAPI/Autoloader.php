<?php

//define routes
define('_ROUTE_LIB_VENDOR_', 'lib\\vendor\\');

function Loader( $class )
{
    //search configurations
    $jsonConfiguration = file_get_contents("lib/Configuration.json");
    $decode = json_decode( $jsonConfiguration );    
    
    $modules = ( isset( $decode -> dirModules ) ) ? $decode -> dirModules : "";
    
    $includeFile = $modules . _DS_ . $class . '.php';
    
    if( is_readable( $includeFile ) ) { 
        $class = $modules . _DS_ . $class;         
    }

    if( is_readable( $class . '.php' ) ) { 
        require_once $class . '.php';         
    }
    else {
        $class = str_replace("\\", "/", $class); 
        if( is_readable( $class . '.php' ) ){            
            require_once $class . '.php'; 
        }
        else{
            $path   =   _ROUTE_LIB_VENDOR_ . $class;
            $path = str_replace("\\", "/", $path);
            if( is_readable( $path . '.php' ) ) {
                require_once $path . '.php';
            }
            else{
                $new_route = str_replace("//", "/", $class);
                $new_path = _DIR_MODULE_ . "/" . $new_route . ".php";
                if( is_readable( $new_path ) ) {
                    require_once $new_path;
                } else {
					$last_change = _BASE_ . _DS_ . $class . ".php";
					if( is_readable( $last_change ) ) {
						require_once $last_change;
					}
				}
            }
        }
    }
}

spl_autoload_register('Loader');

function handleErrors(){
        
    $array_errors = error_get_last();
    if( count($array_errors) > 0 )
    {
        $message    =   $array_errors["message"] . " in line " . $array_errors["line"] . " on file " . $array_errors["file"];
        $loggerMsg  =   $message;
        
        if ( lib\Config::$_DEVELOPING_ == FALSE ){            
            $message    =   "
                                <p>Sorry, we found the internal error.</p> 
                                <p>Please refresh the browser or exit of system.</p> 
                                <p>If the problem persist, please call the administrator or check the logs</p>
                                
                            ";        
        }    
        
        spl_autoload_register('fatalErrorHandle');
        \Factory::loggerDebug( $loggerMsg );
        echo $message;
    }
}

register_shutdown_function('handleErrors');

function fatalErrorHandle( $class ){    

    $path   =  __DIR__ . _DS_ . _ROUTE_LIB_VENDOR_ . $class . '.php';
    
    if( is_readable( $path ) ) { 

        require_once $path;
    }
}

