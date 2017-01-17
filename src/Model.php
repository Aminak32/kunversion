<?php

    namespace Kunversion;

    use Kunversion\Db;

    class Model
    {

        public static function instance()
        {
            $class = get_called_class();
            var_dump($class);
            return new $class();
        }

        public static function getDb($config=false)
        {
            $db = new Db($config);
            return $db;
        }
    }