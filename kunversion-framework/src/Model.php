<?php

    namespace Kunversion;

    use Kunversion\Db;

    class Model
    {

        public static function instance()
        {
            var_dump(get_called_class());
        }

        public static function getDb($config=false)
        {
            $db = new Db($config);
            return $db;
        }
    }