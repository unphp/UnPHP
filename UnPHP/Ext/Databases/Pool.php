<?php

/**
 * 框架扩展：数据库操作类库
 * 数据库资源池
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author Xiao Tangren  <unphp@qq.com>
 * @data 2014-03-27
 * */
class Ext_Databases_Pool
{

        private static $dbPool = array();
        private static $modelReadPool = array();
        private static $modelWritePool = array();

        /**
         * @param string $dsn         如 'mysql:dbname=testdb;host=127.0.0.1'
         * @param string $user        用户
         * @param string $password    密码
         */
        public static function addPool($dsn, $user, $password)
        {
                if (!isset(self::$dbPool[$dsn . $user . $password]))
                {
                        self::$dbPool[$dsn . $user . $password] = new PoolClient($dsn, $user, $password);
                }
        }

        public static function getPool($dsn, $user, $password)
        {
                if (!isset(self::$dbPool[$dsn . $user . $password]))
                {
                        self::$dbPool[$dsn . $user . $password] = new PoolClient($dsn, $user, $password);
                }
                return self::$dbPool[$dsn . $user . $password];
        }

        public static function getReadPool($table, $dsn, $user, $password)
        {
                if (!isset(self::$modelReadPool[$table]))
                {
                        self::$modelReadPool[$table] = self::getPool($dsn, $user, $password);
                }
                return self::$modelReadPool[$table];
        }

        public static function getWritePool($table, $dsn, $user, $password)
        {
                if (!isset(self::$modelWritePool[$table]))
                {
                        self::$modelWritePool[$table] = self::getPool($dsn, $user, $password);
                }
                return self::$modelWritePool[$table];
        }

}

class PoolClient
{

        protected $engine = null;
        protected $host = null;
        protected $port = null;
        protected $dbname = null;
        protected $user = null;
        protected $password = null;
        protected $client = null;

        public function __construct($dsn, $user, $password)
        {
                $temp = explode(':', $dsn);
                $this->engine = $temp[0];
                $db_base_params = explode(';', $temp[1]);
                foreach ($db_base_params as $params)
                {
                        $p = explode('=', $params);
                        switch ($p[0])
                        {
                                case 'host':
                                        $this->host = $p[1];
                                        break;
                                case 'port':
                                        $this->port = $p[1];
                                        break;
                                case 'dbname':
                                        $this->dbname = $p[1];
                                        break;
                        }
                }
                $this->user = $user;
                $this->password = $password;
        }

        public function conn()
        {
                try
                {
                        if (null == $this->client)
                        {
                                switch ($this->engine)
                                {
                                        case 'mysql':
                                        case 'mssql':
                                                $dsn = $this->engine . ":dbname=" . $this->dbname . ";host=" . $this->host;
                                                if (null != $this->port)
                                                {
                                                        $dsn .=";port=" . $this->port;
                                                }
                                                $this->client = new Ext_Databases_Pdo(new PDO($dsn, $this->user, $this->password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'")), $this->engine);
                                                break;
                                        case 'mongodb':
                                                $dsn = 'mongodb://' . $this->user . ':password@' . $this->host . ':' . $this->port;
                                                $options = array();
                                                $options['db'] = $this->dbname;
                                                $options['password'] = $this->password;
                                                $client = new MongoClient($dsn, $options);
                                                $client->selectDB();
                                                $this->client = new Ext_Databases_Mongo();
                                }
                        }
                        return $this->client;
                }
                catch (Exception $exc)
                {
                        throw new DatabasesException($exc->getMessage());
                }
        }

}
