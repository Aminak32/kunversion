<?php

    namespace Kunversion;

    use Kunversion\Db,
        Kunversion\Lead;

    defined( "KV_ROOT") || define('KV_ROOT', dirname(__FILE__));
    if ( !defined( 'KV_PROJECT_ROOT' ) ) {
        define("KV_PROJECT_ROOT", dirname(KV_ROOT . "/../../../"));
    }
    defined("KV_DATABASE_DRIVER_DIR") || define('KV_DATABASE_DRIVER_DIR', KV_ROOT . '/Db/');

    class Kunversion
    {
        private $lead;

        private static $salt = "kvsn111";
        const KV_SITE = "http://www.kunversion.com";
        public function __construct($verify_subdomain=false)
        {
            try {
                (new Dotenv\Dotenv(__DIR__ . "/../../"))->load();
            } catch (Dotenv\Exception\InvalidPathException $e) {
                var_dump($e);
            }


            if ( !empty($_REQUEST['key'] ) && isset($_SESSION['lead']) && $_REQUEST['key'] != '') {
                $email = self::decrypt($_REQUEST[ 'key' ], self::$salt);
                $l = new Lead;
                $lead = $l->getLead();

            }

        }

        public static function decrypt( $string, $salt)
        {
            $result = '';
            $string = base64_decode($string);

            for ($i = 0; $i < strlen($string); $i++) {
                $char = $string{$i};
                $keyChar = substr($salt, ($i % strlen($salt)) - 1, 1);
                $char = chr(ord($char) - ord($keyChar));
                $result .= $char;
            }
            return $result;
        }

        public static function encrypt($string, $salt)
        {
            $result = '';
            for ($i = 0; $i < strlen($string); $i++) {
                $char = $string{$i};
                $keyChar = substr($salt, ($i % strlen($salt)) - 1, 1);
                $char = chr(ord($char) - ord($keyChar));
                $result .= $char;
            }
            return base64_encode($result);
        }

        /**
         * @param $email
         * @return string
         *
         * @todo apply better logic (i.e. regular expressions) to validate the email address
         */
        public static function sanitizeEmail( $email)
        {
            $db = new Db();
            $email = trim($db->quote($email));
            if (empty($email) || false === strpos($email, '@') || false === strpos($email, '.')) {
                return '';
            }
            return $email;
        }

        public static function setSettingsBasedOnHost($verify_subdomain=false)
        {

        }

        //public static function getHost

    }