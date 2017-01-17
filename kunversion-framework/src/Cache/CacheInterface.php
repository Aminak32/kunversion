<?php
    /**
     * Created by PhpStorm.
     * User: skrelo
     * Date: 1/13/17
     * Time: 1:13 PM
     */

    namespace Kunversion\Cache;


    interface CacheInterface
    {
        public function get( $key );

        public function set( $key, $value, $expire); // expire in minutes

        public function delete( $key );

        public static function instance($config);
    }