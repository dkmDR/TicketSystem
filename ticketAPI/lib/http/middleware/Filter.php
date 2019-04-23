<?php
/**
 * Created by PhpStorm.
 * User: maperalta
 * Date: 7/16/2018
 * Time: 10:30 AM
 */

namespace lib\http\middleware;

abstract class Filter {

    /**
     * @var array
     */
    protected static $filters = array(

                            /*Route*/               /*classes and params*/
                            "auth"          =>      array(
                                                        "class" => array(
                                                                            "AuthBasic" => array(
                                                                                                    "host" => "localhost",
                                                                                                    "username" => "username",
                                                                                                    "password" => "password"
                                                                                                ),

                                                                            "Key" => array ("key" => "e10adc3949ba59abbe56e057f20f883e")

                                                                            )
                                                    )

                        );

}