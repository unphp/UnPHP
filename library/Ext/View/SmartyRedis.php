<?php

/**
 * Redis结合版smarty
 * 缓存机制：静态缓存保存在redis中。
 * 
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Ext_View_SmartyRedis extends Ext_View_Smarty
{

        public $cachePower = '';
        public $hash_cache_key = '';
        public $hash_compile_key = '';
        
        public function __construct($template_dir)
        {
                parent::__construct($template_dir);
                // redis实例
                $this->cachePower = Pub_Redis::mode();
                // smarty静态缓存的hash值标识符
                $this->hash_cache_key = $this->cachePower->getConfkey('smarty_cache','hash');
                $this->hash_compile_key = $this->cachePower->getConfkey('smarty_compile','hash');
        }

        /**
         * 判断是否缓存---从redis中
         * @author xiaotangren  <unphp@qq.com>
         * @access  public
         * @param   string     $filename
         * @param   sting      $cache_id
         * @return  bool
         */
        public function is_cached($filename, $cache_id = '')
        {
                if ($this->caching == true && $this->direct_output == false)
                {
                        $this->cached = true;
                        $cachefile = $this->getCachefilepath($filename, $cache_id);
                        $data = $this->cachePower->hGet($this->hash_cache_key,$cachefile);
                        if ($data)
                        {
                                $data = substr($data, 13);
                                $pos = strpos($data, '<');
                                $paradata = substr($data, 0, $pos);
                                $para = @unserialize($paradata);
                                // 如果 “现在的时间”>缓存“过期时间” ，那么，缓存失效！
                                if ($para === false || $this->_nowtime > $para['expires'])
                                {
                                        // 及时删除单个静态缓存
                                        $this->cachePower->hDel($this->hash_cache_key,$cachefile);
                                        $this->cached = false;
                                        return false;
                                }
                                $this->_expires = $para['expires'];
                                $this->template_out = substr($data, $pos);
                                // 遍历加载进来的每个“子模板”，对比“子模板”修改时间与缓存创建时间。
                                foreach ($para['template'] AS $val)
                                {
                                        $stat = @stat($val);
                                        // 如果“子模板”修改时间 大于 “缓存创建时间”，那么，缓存失效！
                                        if ($para['maketime'] < $stat['mtime'])
                                        {
                                                // 及时删除单个静态缓存
                                                $this->cachePower->hDel($this->hash_cache_key,$cachefile);
                                                $this->cached = false;
                                                return false;
                                        }
                                }
                        }
                        else
                        {
                                $this->cached = false;
                                return false;
                        }
                        return true;
                }
                else
                {
                        return false;
                }
        }

        /**
         * 编译模板函数
         * @author xiaotangren  <unphp@qq.com>
         * @access  public
         * @param   string      $filename
         * @return  sring        编译后文件内容
         */
        public function make_compiled($filename)
        {
                if ($this->force_compile == false)
                {
                        $this->_current_file = $filename;
                        $source = $this->_eval($this->fetch_str(file_get_contents($filename)));
                }
                else
                {
                        $compile_dir_file = $this->filename[$filename];
                        $name = $this->compile_dir . $compile_dir_file . '.php';
                        $redis_key = PROXY_DOMAIN.$name;
                        $expires = 0;
                        if (file_exists($name))
                        {
                                $filestat = @stat($name);
                                $expires = $filestat['mtime'];
                        }
                        
                        
                        // 取得 编译缓存在redis保存的过期时间
                        $overdue = false;
                        $redis_data = $this->cachePower->hGet($this->hash_compile_key,$redis_key);
                        if($expires>0 && $redis_data && intval($redis_data)>$this->_nowtime){
                                $overdue = true;
                        }
    
                        //-----------------------------------
                        // 模板文件日期等信息
                        $filestat = @stat($filename);
                        // 判断模板是否更新：对比“缓存生成时间”和“模板修改时间”
                        if ($filestat['mtime'] <= $expires && $overdue)
                        {
                                $source = $this->_require($name);
                        }
                        // 生成（或覆盖）编译缓存。
                        else
                        {
                                if (!file_exists($filename))
                                {
                                        throw new Core_Exception('模板文件不存在，路径：'.$filename,'1100000014');
                                }
                                $this->cachePower->hDel($this->hash_compile_key,$redis_key);
                                $this->_current_file = $filename;
                                $fetch_str = $this->fetch_str(file_get_contents($filename));
                                $this->autoMkdir($name);
                                if (file_put_contents($name, $fetch_str, LOCK_EX) === false)
                                {
                                        trigger_error('can\'t write:' . $name);
                                }
                                $this->cachePower->hSet($this->hash_compile_key,$redis_key,$this->_nowtime + $this->cache_lifetime);
                                $source = $this->_eval($fetch_str);
                        }
                }
                // 返回编译内容
                return $source;
        }

        /**
         * 生成缓存---保存到redis中
         * @author xiaotangren  <unphp@qq.com>
         * @param type $filename
         * @param type $cache_id
         * @param type $out
         * @return type
         */
        protected function doCreateCache($filename, $cache_id,$out)
        {
                $cachefile = $this->getCachefilepath($filename, $cache_id);
                $data = serialize(
                        array(
                            'template' => $this->template,
                            'expires' => $this->_nowtime + $this->cache_lifetime,
                            'maketime' => $this->_nowtime
                        )
                );
                while (strpos($out, "\n\n") !== false)
                {
                        $out = str_replace("\n\n", "\n", $out);
                }
                $data = '<?php exit;?>' . $data . $out;
                $this->cachePower->hSet($this->hash_cache_key,$cachefile,$data);
                return $out;
        }

}

?>
