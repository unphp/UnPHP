<?php

/**
 * 策略模式：
 * 对WebService客户端mod逻辑层进行封装。
 * 统一了对WebService服务端的请求方式。
 * 该类用于控制器调用，注册mod逻辑层对象。
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Pub_Mod
{

        /**
         * 保存了models对象的数组。
         * @var type 
         */
        private $_items = array();
        private static $_instance;

        /**
         * RPC类对象实例
         * @var type 
         */
        private static $_Rpc;
        public static $site_id = null;
        public static $language_id = null;
        public static $site_code = null;
        public static $language_code = null;

        /**
         * 构造方法，不对外！
         * 对外只能调用getInstance（静态）方法，
         * 这样就保证了该类只能以静态方式调用。
         * @param type $Rpc
         */
        private function __construct($Rpc = NULL)
        {
                if (NULL !== $Rpc)
                        self::$_Rpc = $Rpc;
        }

        /**
         * 静态实例方法
         * @param type $auth
         * @return type
         */
        public static function getInstance($auth = null)
        {
                if (!(self::$_instance instanceof self))
                {
                        if (null !== $auth)
                                self::$_Rpc = new Pub_Rpc($auth);
                        self::$_instance = new self();
                }
                return self::$_instance;
        }

        /**
         * 注册一个model对象
         * @param type $model
         * @return \Pub_Call
         */
        public function reg($model)
        {
                if (!isset($this->$model))
                {
                        $this->$model = new $model(self::$_Rpc);
                }
                return new ModCall($this->_items[$model]);
        }

        public static function set_site_id($site_id)
        {
                if (!(self::$_instance instanceof self))
                        self::$_instance = new self();
                self::$_Rpc->set_site_id($site_id);
        }

        public static function set_site_code($site_code)
        {
                if (!(self::$_instance instanceof self))
                        self::$_instance = new self();
                self::$_Rpc->set_site_code($site_code);
        }

        public static function set_lan_id($lan_id)
        {
                if (!(self::$_instance instanceof self))
                        self::$_instance = new self();
                self::$_Rpc->set_lan_id($lan_id);
        }

        public static function set_lan_code($lan_code)
        {
                if (!(self::$_instance instanceof self))
                        self::$_instance = new self();
                self::$_Rpc->set_lan_code($lan_code);
        }

        /**
         * 注册一个model对象
         * @param type $model
         * @return \Pub_Call
         */
        public static function r($m)
        {
                try
                {
                        if (!is_string($m))
                                throw new Exception('Pub_Mod::r()参数必须为字符串！');
                        $model = preg_match('/^Mod\_/', $m) ? $m : 'Mod_'.$m;
                        if (!(self::$_instance instanceof self))
                                self::$_instance = new self();
                        if (!isset(self::$_instance->$model))
                        {
                                self::$_instance->$model = new $model(self::$_Rpc);
                        }
                        return new ModCall(self::$_instance->_items[$model]);
                } catch (Exception $exc)
                {
                        echo $exc->getMessage();
                }
        }

        /**
         * 魔术方法
         * @param type $name
         * @param Core_Models $value
         * @throws Exception
         */
        public final function __set($name, $value)
        {
                try
                {
                        if (is_object($value))
                        {
                                if ($value instanceof Core_Models)
                                {
                                        $this->_items[$name] = $value;
                                }
                                else
                                        throw new Exception('请定义一个Models对象！');
                        }
                        else
                                throw new Exception('请定义一个对象！');
                } catch (Exception $exc)
                {
                        echo $exc->getTraceAsString();
                }
        }

        /**
         * 魔术方法
         * @param type $name
         * @return \Pub_Call
         */
        public final function __get($name)
        {
                if (isset($this->_items[$name]))
                        return new Pub_Call($this->_items[$name]);
        }

}



/**
 * 采用魔术方法---call，对mod层方法进行统一封装。
 * @system AECMPS <傲基电子商务系统>
 * @version aecmps 2.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-01-15
 */
class ModCall
{

        protected $_models;


        public function __construct(Core_Models $api)
        {
                $this->_models = $api;
                $this->init();
        }

        public function init(){

        }

        public function __call($name, $arguments=array())
        {
                $check_site_lan = '_checkSiteidLanidYar';
                $bac = 'beforeYar';
                $afac = 'afterYar';
                $ac = $name.'Yar';
                $rs = null;
                // 排除获取“站点基本信息”和“语言列表信息”需要检测
                $beforYar = in_array($name, array('get_site_info','get_lan_list')) ? true : $this->_models->$check_site_lan($ac);
                if($beforYar && $this->_models->$bac($ac)){
                        $num = count($arguments);
                        switch ($num)
                        {
                                case 1:
                                        $rs = $this->_models->$ac($arguments[0]);
                                        break;
                                case 2:
                                        $rs = $this->_models->$ac($arguments[0],$arguments[1]);
                                        break;
                                case 3:
                                        $rs = $this->_models->$ac($arguments[0],$arguments[1],$arguments[2]);
                                        break;
                                case 4:
                                        $rs = $this->_models->$ac($arguments[0],$arguments[1],$arguments[2],$arguments[3]);
                                        break;
                                case 5:
                                        $rs = $this->_models->$ac($arguments[0],$arguments[1],$arguments[2],$arguments[3],$arguments[4]);
                                        break;
                                case 6:
                                        $rs = $this->_models->$ac($arguments[0],$arguments[1],$arguments[2],$arguments[3],$arguments[4],$arguments[5]);
                                        break;
                                default:
                                        $rs = $this->_models->$ac();
                                        break;
                        }
                        $this->_models->$afac($name);
                }
                return $rs;
        }

}

?>
