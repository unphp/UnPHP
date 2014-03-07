<?php

/**
 * Redis配置类
 * 
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Pub_Redis
{

        public static $redis_no_err = 1;
        public static $host_ip;
        public static $host_port;
        public static $conf;
        private static $redis_obj = null;
        private static $redis_client = null;
        private $masters_methods = array(
            'hSet',
            'hDel',
            'hIncrBy',
            'hMset',
            'delete',
        );
        private $masters = null;
        private $slaves = null;

        public function __construct($masters, $slaves)
        {
                $this->masters = $masters;
                $this->slaves = $slaves;
        }

        public static function getConfkey($key, $type = 'hash')
        {
                $hash_list = self::$conf;
                return $hash_list[$type][$key];
        }

        public static function setConfig($host, $port)
        {
                self::$host_ip = $host;
                self::$host_port = $port;
        }

        public static function mode()
        {
                if (null == self::$redis_client)
                {
                        $className = get_called_class();
                        $redis_client = new Pub_RedisClient(self::$host_ip, self::$host_port);
                        $masters_pool = $redis_client->masters();
                        $rand_masters = rand(0, count($masters_pool) - 1);
                        $masters = new Redis();
                        $masters->connect($masters_pool[$rand_masters]['ip'], $masters_pool[$rand_masters]['port'], 0.3);
                        $slaves_pool = $redis_client->slaves($masters_pool[$rand_masters]['name']);
                        $rand_slaves = rand(0, count($slaves_pool) - 1);
                        $slaves = new Redis();
                        $slaves->connect($slaves_pool[$rand_slaves]['ip'], $slaves_pool[$rand_slaves]['port'], 0.3);
                        self::$redis_client = new $className($masters, $slaves);
                }
                return self::$redis_client;
        }

        public static function setHashCache($h, $k, $v)
        {
                if (self::getConfkey($h, 'cacheing'))
                {
                        $hash = self::getConfkey($h, 'hash');
                        self::mode()->hSet($hash, $k, $v);
                        return true;
                }
                else
                {
                        return false;
                }
        }

        public static function getHashCache($h, $k)
        {
                if (self::getConfkey($h, 'cacheing'))
                {
                        $hash = self::getConfkey($h, 'hash');
                        return self::mode()->hGet($hash, $k);
                }
                else
                {
                        return false;
                }
        }

        public static function delHashCache($h, $k = null)
        {
                $hash = self::getConfkey($h, 'hash');
                if (self::mode()->hLen($hash) > 0)
                {
                        if (empty($k))
                        {
                                self::mode()->delete($hash);
                        }
                        else
                        {
                                self::mode()->hDel($hash, $k);
                        }
                        return true;
                }
                return false;
        }

        public static function getHashAll($h, $type = '')
        {
                $hash = self::getConfkey($h, 'hash');
                $data = self::mode()->hGetAll($hash);
                return !empty($type) ? array_keys($data) : $data;
        }

        public function __call($name, $arguments)
        {
                $rs = NULL;
                if (in_array($name, $this->masters_methods))
                {
                        switch (count($arguments))
                        {
                                case 1:
                                        $rs = $this->masters->$name($arguments[0]);
                                        break;
                                case 2:
                                        $rs = $this->masters->$name($arguments[0], $arguments[1]);
                                        break;
                                case 3:
                                        $rs = $this->masters->$name($arguments[0], $arguments[1], $arguments[2]);
                                        break;
                                case 4:
                                        $rs = $this->masters->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
                                        break;
                                default:
                                        $rs = $this->masters->$name();
                                        break;
                        }
                }
                else
                {
                        switch (count($arguments))
                        {
                                case 1:
                                        $rs = $this->slaves->$name($arguments[0]);
                                        break;
                                case 2:
                                        $rs = $this->slaves->$name($arguments[0], $arguments[1]);
                                        break;
                                case 3:
                                        $rs = $this->slaves->$name($arguments[0], $arguments[1], $arguments[2]);
                                        break;
                                case 4:
                                        $rs = $this->slaves->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
                                        break;
                                default:
                                        $rs = $this->slaves->$name();
                                        break;
                        }
                }
                return $rs;
        }

}

?>
