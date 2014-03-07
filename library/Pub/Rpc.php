<?php

/**
 * WebSerivice客户端核心类---发送RPC请求。
 * 该类统一了发送RPC请求的方法，统一了参数等。
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Pub_Rpc
{

        public $site_id = null;
        
        public $site_code = null;
        
        public $language_id = null;
        
        public $language_code = null;
        
        protected $_items = array();
        protected $_adapter = 'adapter';
        protected $_client = null;
        private static $_instance;
        protected $_username;
        protected $_password;
        protected $_api;
        

        public static function getInstance($rpc_cfg)
        {
                if (!(self::$_instance instanceof self))
                {
                        self::$_instance = new self($rpc_cfg);
                }
                return self::$_instance;
        }

        public function __construct($rpc_cfg)
        {
                $this->_username = $rpc_cfg['username'];
                $this->_password = $rpc_cfg['password'];
                $this->_api = $rpc_cfg['api'];
        }

        public static function model(pub_service $model_name)
        {
                
        }

        public function set_site_id($site_id)
        {
                $this->site_id = $site_id;
        }

        public function set_site_code($site_code)
        {
                $this->site_code = $site_code;
        }

        public function set_lan_id($lan_id)
        {
                $this->language_id = $lan_id;
        }

        public function set_lan_code($lan_code)
        {
                $this->language_code = $lan_code;
        }

        /**
         * 发送一个Rpc请求，返回yar对象
         * @param string $rpc “目录名（.目录名）.文件类名” 或 “文件类名”
         * @return obj
         */
        public final function c($rpc)
        {
                try
                {
                        if (!preg_match('/^([\da-zA-Z\_]+?)(\.[\d\w\_]+){0,}$/', $rpc))
                                $this->errfun(101);
                        $api = $this->_api . '/?&rpc=' . $rpc;
                        if (isset($this->_items[$rpc]) && is_object($this->_items[$rpc]))
                        {
                                return $this->_items[$rpc];
                        } else
                        {
                                $Yar_Client = new Yar_Client('http://' . $this->_username . ':' . $this->_password . '@' . $api);
                                $this->_items[$rpc] = new RpcCall(
                                        $Yar_Client, 
                                        $this->site_id, 
                                        $this->language_id,
                                        $this->site_code,
                                        $this->language_code
                                        );
                                return $this->_items[$rpc];
                        }
                } catch (Exception $exc)
                {
                        echo $exc->getMessage();
                }
        }

        /**
         * 发送单次请求
         * @param string $ac  目录名.文件类名.方法名
         * @param type $parameters 该方法接受的参数
         * @param string $type 返回的数据格式类型
         * @return type
         * @throws Exception
         */
        public function send($ac, $parameters = null, $type = 'php')
        {
                try
                {
                        if ($parameters !== null && !is_array($parameters))
                        {
                                throw new Exception('Yar请求参数必须为数组！');
                        }
                        if (!isset($ac))
                        {
                                throw new Exception('Yar请求参数中必须包含ac元素！');
                        }
                        if (!isset($type))
                        {
                                $type = 'json';
                        } else
                        {
                                if (!in_array($type, array('json', 'xml', 'php')))
                                {
                                        throw new Exception('Yar请求参数中必须包含type元素错误！');
                                }
                        }
                        $cfg = array(
                            'type' => $type,
                            'ac' => $ac,
                            'data' => $parameters,
                        );
                        if ($this->_client == null)
                        {
                                $this->_client = new Yar_Client('http://' . $this->_username . ':' . $this->_password . '@' . $this->_api);
                        }
                        $_adapter = $this->_adapter;
                        $data = $this->_client->$_adapter($cfg);
                        return $this->data_transform($data, $type, $ac);
                } catch (Exception $exc)
                {
                        echo $exc->getMessage();
                }
        }

        protected function data_transform($data, $type, $ac)
        {
                try
                {
                        switch ($type)
                        {
                                case 'json':
                                        return $data;
                                        break;
                                case 'xml':
                                        return $data;
                                        break;
                                case 'php':
                                        $rs = unserialize($data);
                                        if ($rs['code'] != 100)
                                        {
                                                throw new Exception(
                                                        '<br>' .
                                                        '【Code】 : <font style="color:red;font-weight:bold;">' . $rs['code'] . '</font>' .
                                                        '  【WebSerivice Api】 ：<font style="color:blue;font-weight:bold;">' . $ac . '</font>' .
                                                        '  【Msg】 : <font style="color:green;font-weight:bold;">' . $rs['msg'] . '</font>' .
                                                        '<br>'
                                                );
                                        }
                                        return $rs['data'];
                                        break;
                        }
                } catch (Exception $exc)
                {
                        echo $exc->getMessage();
                }
        }

        protected function errfun($code)
        {
                $err = array(
                    '101' => 'Models::r()第一个参数格式正确！'
                );
                throw new Exception($err[$code]);
        }

}

/**
 * 魔术裂变类
 * @system AECMPS <傲基电子商务系统>
 * @version aecmps 2.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-01-01
 */
class RpcCall
{

        public $Yar_Client;
        public $site_id = null;
        public $language_id = null;
        public $site_code = null;
        public $language_code = null;

        public function __construct(Yar_Client $obj, $site_id, $language_id,$site_code,$language_code)
        {
                $this->Yar_Client = $obj;
                $this->site_id = $site_id;
                $this->language_id = $language_id;
                $this->site_code = $site_code;
                $this->language_code = $language_code;
        }

        public function __call($name, $arguments = array())
        {
                try
                {
                        $bac = 'beforeYar';
                        $afac = 'afterYar';
                        $ac = $name . 'Yar';
                        $rs = null;
                        $cfg = array();
                        $cfg['type'] = 'php';
                        $num = count($arguments);
                        if ($num > 2)
                                throw new Exception('WebSerivice调用方法' . $name . '的参数错误！参数超过了规定个数！');
                        $cfg['type'] = $num == 2 ? $arguments[1] : 'php';
                        $cfg['data'] = $num == 1 ? $arguments[0] : array();
                        /* 自动传递的参数 */
                        $auto_params = array(
                            'site_id' => $this->site_id,
                            'site_code' => $this->site_code,
                            'language_id' => $this->language_id,
                            'language_code' => $this->language_code
                        );
                        if (is_array($cfg['data']))
                        {
                                $cfg['data'] = @array_merge($auto_params, $cfg['data']);
                        }
                        else{
                                $cfg['data'] = $auto_params;
                        }
                        $data = $this->Yar_Client->$bac($ac, $cfg);
                        if ($data !== false){
                                $rs = $this->Yar_Client->$ac($data);
                                $rs = $this->Yar_Client->$afac($cfg['type'], $rs);
                        }
                        else
                                throw new Exception('WebSerivice调用方法' . $name . '的参数错误！,第二个参数错误，必须为json或xml或php！');
                        return $this->data_transform($rs, $cfg['type'], $ac);
                } catch (Exception $exc)
                {
                        echo $exc->getMessage();
                }
        }
 
        protected function data_transform($data, $type, $ac)
        {
                try
                {
                        switch ($type)
                        {
                                case 'json':
                                        return $data;
                                        break;
                                case 'xml':
                                        return $data;
                                        break;
                                case 'php':
                                        $rs = unserialize($data);
                                        if ($rs['code'] != 100)
                                        {
                                                throw new Exception(
                                                        '<br>' .
                                                        '【Code】 : <font style="color:red;font-weight:bold;">' . $rs['code'] . '</font>' .
                                                        '  【WebSerivice Api】 ：<font style="color:blue;font-weight:bold;">' . $ac . '</font>' .
                                                        '  【Msg】 : <font style="color:green;font-weight:bold;">' . $rs['msg'] . '</font>' .
                                                        '<br>'
                                                );
                                        }
                                        return $rs['data'];
                                        break;
                        }
                } catch (Exception $exc)
                {
                        echo $exc->getMessage();
                }
        }

}

?>
