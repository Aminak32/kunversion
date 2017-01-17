<?php
    /**
     * Created by PhpStorm.
     * User: skrelo
     * Date: 1/13/17
     * Time: 1:28 PM
     */

    namespace Kunversion\Cache;

    use \Memcached as MemcachedCore;


    class Memcached implements CacheInterface
    {
        private $config;
        private $memcached;
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
            if ( extension_loaded( 'Memcached' ) ) {
                if (!$this->memcached instanceof MemcachedCore) {
                    $this->memcached = new MemcachedCore();
                    $this->memcached->addserver($this->config[ 'host' ], $this->config[ 'port' ]);
                }
            }
        }

        public function setDefaultCompressed( $flag)
        {
            $this->defaultCompressed = $flag;
        }

        public function get( $key )
        {
            return $this->memcached instanceof MemcachedCore
                ? $this->memcached->get($key)
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
            if ( $this->memcached instanceof MemcachedCore ) {
                $this->memcached->setOption(MemcachedCore::OPT_COMPRESSION, $compress);
                return $this->memcached->set($key, $value, $expire);
            }
        }

        public function delete( $key )
        {
            return $this->memcached instanceof MemcachedCore
                ? $this->memcached->delete($key)
                : null;
        }

        public static function instance($config=false)
        {
            return new Memcached($config);
        }

    }