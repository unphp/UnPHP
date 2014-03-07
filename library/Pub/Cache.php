<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 缓存组件
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Pub_Cache
{
        public static $temp_cache_path;
        public static $data_cache_path;
        public static $redis = 0;
        public static $get_data_cache_list = array();
        public static $get_temp_cache_list = array();
        
        private static $cache_lifetime = 86400;

        public static function get_data_path($key)
        {
                $cache_dir = self::$data_cache_path;
                return $cache_dir->$key;
        }

        public static function get_data_cache($prefix, $key, $check_time = true)
        {
                $rs = false;
                $self_attr_key = $key . '_' . $prefix;
                if (!isset(self::$get_data_cache_list[$self_attr_key]))
                {
                        $prefix = self::get_data_path($prefix);
                        $key = self::create_key($key);
                        /* read conf */
                        $result = @file_get_contents($prefix . "/" . $key);
                        if ($result === false)
                        {
                                $rs = false;
                        }
                        else
                        {
                                $result = @unserialize($result);
                                if ($check_time === true && (empty($result['timeout']) || $result['timeout'] < time()))
                                {

                                        $rs = false;
                                }
                                else
                                {
                                        $rs = $result['data'];
                                        self::$get_data_cache_list[$self_attr_key] = $result['data'];
                                }
                        }
                }
                else
                {
                        $rs = self::$get_data_cache_list[$self_attr_key];
                }
                return $rs;
        }

        public static function set_data_cache($prefix, $key, $value, $timeout = '')
        {
                $timeout = empty($timeout) ? self::$cache_lifetime : $timeout;
                $prefix = self::get_data_path($prefix);
                $key = self::create_key($key);
                $tmp['data'] = $value;
                $tmp['timeout'] = time() + (int) $timeout;
                return @self::put_file($prefix . "/" . $key, @serialize($tmp));
        }

        public static function del_data_cache($prefix, $key = null)
        {
                if (empty($key))
                {
                        return self::delDir($prefix);
                }
                $key = $key = self::create_key($key);
                return @unlink($prefix . "/" . $key);
        }

        /**
         * 返回“数组缓存文件”（非序列化文件缓存）的路径
         */
        public static function get_temp_path($key = 'category')
        {
                $cache_dir = self::$temp_cache_path;
                return $cache_dir[$key];
        }

        public static function get_temp_cache($prefix, $key, $check_time = true)
        {
                $rs = false;
                $self_attr_key = $key . '_' . $prefix;
                if (!isset(self::$get_temp_cache_list[$self_attr_key]))
                {
                        $redis_hash = $prefix;
                        $prefix = self::get_temp_path($prefix);
                        $key = self::create_key($key);
                        //-----------------------------------------------------------------------
                        // 优先从redis中取值
                        if (self::$redis)
                        {
                                $result = Pub_Redis::mode()->getHashCache($redis_hash, $prefix . "/" . $key);
                        }
                        else
                        {
                                $result = @file_get_contents($prefix . "/" . $key);
                        }
                        //-----------------------------------------------------------------------
                        if ($result === false)
                        {
                                $rs = false;
                        }
                        else
                        {
                                $result = @unserialize($result);
                                if ($check_time === true && (empty($result['timeout']) || $result['timeout'] < time()))
                                {
                                        $rs = false;
                                }
                                else
                                {
                                        $rs = $result['data'];
                                        self::$get_temp_cache_list[$self_attr_key] = $rs;
                                }
                        }
                }
                else
                {
                        $rs = self::$get_temp_cache_list[$self_attr_key];
                }
                return $rs;
        }

        public static function set_temp_cache($prefix, $key, $value, $timeout = '')
        {
                $timeout = empty($timeout) ? self::$cache_lifetime : $timeout;
                $redis_hash = $prefix;
                $prefix = self::get_temp_path($prefix);
                $key = $key = self::create_key($key);
                $tmp['data'] = $value;
                $tmp['timeout'] = time() + (int) $timeout;
                if (self::$redis)
                {
                        Pub_Redis::mode()->delHashCache($redis_hash,$prefix . "/" . $key);
                        return Pub_Redis::mode()->setHashCache($redis_hash, $prefix . "/" . $key, @serialize($tmp));
                }
                else
                {
                        return @self::put_file($prefix . "/" . $key, @serialize($tmp));
                }
        }

        public static function del_temp_cache($prefix, $key = null)
        {
                if (empty($key))
                {
                        if (self::$redis)
                        {
                                return Pub_Redis::mode()->delHashCache($prefix);
                        }
                        return self::delDir(self::get_temp_path($prefix));
                }
                $key = $key = self::create_key($key);
                // 删除redis中的缓存
                if (self::$redis)
                {
                        return Pub_Redis::mode()->delHashCache($prefix, $prefix . "/" . $key);
                }
                // 删除本地缓存
                return @unlink(self::get_temp_path($prefix) . "/" . $key);
        }

       
        public static function create_key($key)
        {
                return $key . '-' . substr(md5($key), 0, 10);
        }

        
        public static function put_file($file, $content, $flag = 0)
        {
                $pathinfo = pathinfo($file);
                if (!empty($pathinfo['dirname']))
                {
                        if (file_exists($pathinfo['dirname']) === false)
                        {
                                if (@mkdir($pathinfo['dirname'], 0777, true) === false)
                                {
                                        return false;
                                }
                                chmod($pathinfo['dirname'], 0777);
                        }
                }
                if ($flag === FILE_APPEND)
                {
                        return @file_put_contents($file, $content, FILE_APPEND);
                }
                else
                {
                        return @file_put_contents($file, $content, LOCK_EX);
                }
        }

        public static function fopen($file, $mode = 'w')
        {
                $pathinfo = pathinfo($file);
                if (!empty($pathinfo['dirname']))
                {
                        if (file_exists($pathinfo['dirname']) === false)
                        {
                                if (@mkdir($pathinfo['dirname'], 0777, true) === false)
                                {
                                        return false;
                                }
                                chmod($pathinfo['dirname'], 0777);
                        }
                }
                return fopen($file, $mode);
        }

        //删除当前文件夹及文件夹下所有的文件
        protected static function delDir($dir)
        {
                //先删除目录下的文件：
                self::truncateDir($dir);
                //删除当前文件夹：
                if (@rmdir($dir))
                {
                        return true;
                }
                else
                {
                        return false;
                }
        }

        protected static function truncateDir($dir)
        {
                $dh = @opendir($dir);
                while ($file = @readdir($dh))
                {
                        if ($file != "." && $file != "..")
                        {
                                $fullpath = $dir . "/" . $file;
                                if (!is_dir($fullpath))
                                {
                                        @unlink($fullpath);
                                }
                                else
                                {
                                        self::deldir($fullpath);
                                }
                        }
                }
                @closedir($dh);
        }

}

?>
