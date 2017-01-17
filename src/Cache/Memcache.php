<?php
    /**
     * Created by PhpStorm.
     * User: skrelo
     * Date: 1/13/17
     * Time: 1:04 PM
     */

    namespace Kunversion\Cache;

    use \Memcache as MemcacheCore;


    class Memcache implements CacheInterface
    {
        private $config;
        private $memcache;
        private $loaded;
        private $defaultCompressed = false;
        public function __construct($config = array())
        {
            if (!isset($config[ 'host' ])) {
                $config[ 'host' ] = "localhost";
            }
            if (!isset($cofig[ 'port' ])) {
                $config[ 'port' ] = 11211;
            }
            $this->config = $config;
            $this->createConnection();
        }

        private function createConnection()
        {
            if ( extension_loaded( 'Memcache' ) ) {
                if (!$this->memcache instanceof MemcacheCore) {
                    $this->memcache = new MemcacheCore();
                    $this->memcache->pconnect($this->config[ 'host' ], $this->config[ 'port' ]);
                }
                $this->loaded = true;
            }
        }

        public function setDefaultCompressed( $flag)
        {
            $this->defaultCompressed = $flag;
        }

        public function get( $key )
        {
            return $this->memcache instanceof MemcacheCore
                ? $this->memcache->get($key)
                : null;
        }

        /**
         * @param $key
         * @param $value
         * @param int $expire - in minutes
         * @param bool $compress
         * @return mixed
         */
        public function set( $key, $value, $expire=60, $compress = false)
        {
            if (false === $compress && isset($this->defaultCompressed)) {
                $compress = $this->defaultCompressed;
            }
            $expire *= 60;
            return $this->memcache instanceof MemcacheCore
                ?$this->memcache->set($key, $value, $compress, $expire)
                : null;
        }

        public function delete( $key )
        {
            return $this->memcache instanceof MemcacheCore
                ?$this->memcache->delete($key)
                : null;
        }

        public static function instance($config=false)
        {
            return new Memcache($config);
        }

    }