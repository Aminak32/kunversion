<?php

    namespace Kunversion;

    /**
     * Class Cache
     * @package Kunversion
     *
     * @todo Do we update the expire when pulling a cache value?
     */

    class Cache
    {
        private $driver = 'memcache'; // Default driver
        private $connection;
        private $config;
        public function __construct()
        {
            $this->driver = isset($_ENV[ 'CACHE_DRIVER' ]) ? $_ENV[ 'CACHE_DRIVER' ] : 'memcache';
            if ( isset( $_ENV['CACHE_HOST'] ) ) {
                $this->config[ 'host' ] = $_ENV[ 'CACHE_HOST' ];
            }
            if ( isset( $_ENV['CACHE_PORT'] ) ) {
                $this->config[ 'port' ] = $_ENV[ 'CACHE_PORT' ];
            }
            $this->createConnection();
            return $this->connection;
        }

        private function createConnection()
        {
            $className = 'Kunversion\Cache\\' . ucwords($this->driver);
            $this->connection = new $className($this->config);
        }

        public static function instance()
        {
            return new Cache();
        }

    }