<?php
    /**
     * Created by PhpStorm.
     * User: skrelo
     * Date: 1/13/17
     * Time: 1:09 PM
     */

    namespace Kunversion;


    interface DbInterface
    {
        public function getResults( $query, $params, $fetch_mode);

        public function getRow($query, $params, $fetch_mode);

        public function getColumn( $query, $params, $fetch_mode);

        public function getVal($query, $params );

        public function query($query, $params);

    }