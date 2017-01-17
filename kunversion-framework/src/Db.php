<?php
    /**
     * Created by PhpStorm.
     * User: skrelo
     * Date: 1/11/17
     * Time: 5:13 PM
     */

    namespace Kunversion;


    class Db
    {
        private $config = array();
        private $overrideDriver = false;
        private $driver = 'mysql'; // Default driver
        private $connection;

        public function __construct($config=false)
        {
            if ( !$config) {
                $this->config[ 'host' ] = $_ENV[ 'ACCOUNT_HOST' ];
                $this->config[ 'port' ] = isset($_ENV[ 'ACCOUNT_PORT' ]) ? $_ENV[ 'ACCOUNT_PORT' ] : '';
                $this->config[ 'username' ] = $_ENV[ 'ACCOUNT_DB_USER' ];
                $this->config[ 'password' ] = $_ENV[ 'ACCOUNT_DB_PASS' ];
            }else {
                $this->config = $config;
            }

            $this->setConnection();
            return $this->connection;
        }

        private function setDriverFromEnv()
        {
            if ( isset($this->config['db_driver'] ) && !empty($this->config['db_driver']) && false=== $this->overrideDriver) {
                $this->driver = $this->config[ 'db_driver' ];
            }elseif (isset($_ENV[ 'ACCOUNT_DB_DRIVER' ]) && !empty($_ENV[ 'ACCOUNT_DB_DRIVER' ]) && false === $this->overrideDriver) {
                $this->driver = $_ENV[ 'ACCOUNT_DB_DRIVER' ];
            }
        }

        private function setConnection()
        {
            $this->setDriverFromEnv();

            $driverClass = 'Kunversion\Db\\' . ucwords($this->driver) . "Connection";
            $this->connection = new $driverClass($this->config);
        }

        public function setDefaultDriver($driver)
        {
            $this->driver = $driver;
            $this->overrideDriver = true;
        }

        public static function getInstance()
        {
            return new Db();
        }
    }