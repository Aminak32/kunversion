<?php

    namespace Kunversion\Db;

    use Kunversion\DbInterface;
    use PDO,PDOStatement;

    class MysqlConnection implements DbInterface
    {
        private $config,
            $defaultFetchMode,
            $connection;
        public function __construct(array $config)
        {
            $this->config = $config;
            $this->createConnection();
        }

        private function getDsn()
        {

            // Socket DSN
            if (isset($this->config[ 'unix_socket' ]) && !empty($this->config[ 'unix_socket' ])) {
                return "mysql:unix_socket={$this->config['unix_socket']};dbname={$this->config['database']}";
            } else {
                if (isset($this->config[ 'port' ])) {
                    return "mysql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['database']}";
                } else {
                    return "mysql:host={$this->config['host']};dbname={$this->config['database']}";
                }
            }
        }

        private function createConnection()
        {
            $dsn = $this->getDsn();
            $this->config[ 'connection_options' ] = isset($this->config[ 'connection_options' ]) ? $this->config[ 'connection_options' ] : array();
            $this->connection = new PDO($dsn, $this->config[ 'username' ], $this->config[ 'password' ],
                $this->config[ 'coinnection_options' ]);
        }

        public function setDefaultFetchMode( $fetch_mode='object' )
        {
            $this->defaultFetchMode = $this->setFetchMode($fetch_mode);
        }

        private function setFetchMode($fetch_mode)
        {
            if ( !empty($this->defaultFetchMode ) ) {
                return $this->defaultFetchMode;
            }
            switch ($fetch_mode) {
                case 'object':
                    return PDO::FETCH_OBJ;
                    break;
                case 'array':
                    return PDO::FETCH_ASSOC;
                    break;
            }
        }

        public function getResults($query, $params=false, $fetch_mode='object')
        {
            $results = $this->connection->prepare($query);
            if ( !is_array($params)) {
                $params = (array)$params;
            }
            $results->execute($params);
            return $results->fetchAll($this->setFetchMode($fetch_mode));
        }

        public function getRow( $query, $params=false, $fetch_mode='object' )
        {
            if ( !is_array($params)) {
                $params = (array)$params;
            }
            $results = $this->connection->prepare($query);
            $results->execute($params);
            return $results->fetch($this->setFetchMode($fetch_mode));
        }

        public function getColumn( $query,$params=false, $fetch_mode='object' )
        {
            if ( !is_array($params)) {
                $params = (array)$params;
            }
            $results = $this->connection->prepare($query);
            $results->execute($params);
            return $results->fetchColumn($this->setFetchMode($fetch_mode));
        }

        public function getVal( $query,$params=false )
        {
            if ( !is_array($params)) {
                $params = (array)$params;
            }
            $results = $this->connection->prepare($query);
            $results->execute($params);
            $row = $results->fetch(PDO::FETCH_BOTH);
            if (!empty($row)) {
                return $row[ 0 ];
            }
        }

        public function query($query, $params=false)
        {
            if (!is_array($params)) {
                $params = (array)$params;
            }
            $result = $this->connection->prepare($query);
            $result->execute($params);
        }
    }