<?php
    /**
     * Created by PhpStorm.
     * User: skrelo
     * Date: 1/16/17
     * Time: 5:48 PM
     */

    namespace Kunversion;


    class Session extends Model
    {

        public function __get($key)
        {
            if (isset($_SESSION[ $key ]) && !empty($_SESSION[ $key ])) {
                return $_SESSION[ $key ];
            }
            return false;
        }

        public function __set($key, $value )
        {
            $_SESSION[ $key ] = $value;
        }

        public function __isset( $key )
        {
            return (bool)isset($_SESSION[ $key ]);
        }


    }