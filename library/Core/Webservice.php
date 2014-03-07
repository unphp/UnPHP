<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Webservice(Yar)请求基类
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Core_Webservice
{
        /**
         * Webservice请求url地址
         * @var string
         */
        public static $webserviceApi = null;
        
        public static $auth_key = null;

        /**
         * Webservice请求返回的链接集合
         * @var array
         */
        protected static $clientService = array();
        
        protected $tableFieldList = array(); 


        /**
         * 链接句柄资源
         * @var type 
         */
        public $nowclient = null;

        public function __construct($clientService)
        {
                $this->nowclient = $clientService;
        }

        /**
         * “读取操作”公共接口
         * @author Xiao Tangren  <unphp@qq.com>
         * @param type $table_name
         * @return type
         */
        public function query($sql, array $bind_params = array(), $type = 'one')
        {
                try
                {
                        return $this->nowclient->queryYar($sql, $bind_params, $type);
                }
                catch (Exception $exc)
                {
                        throw new Core_Exception($exc->getMessage(), '1100000008');
                }
        }

        
        /**
         * “写入操作”公共接口
         * @author Xiao Tangren  <unphp@qq.com>
         * @param type $table_name
         * @return type
         */
        public function execute($sql, array $bind_params = array(), $options = array())
        {
                try
                {
                        //var_dump($this->nowclient);exit;
                        return $this->nowclient->executeYar($sql, $bind_params, $options);
                }
                catch (Exception $exc)
                {
//                        throw new Core_Exception($exc->getMessage(), '1100000007');
                        if (DEBUG_AECMP!=1)
                        {
                              throw new Core_Exception($exc->getMessage(), '1100000007');  
                        }
                }
        }

        /**
         * 获取“表”在mysql里的字段结构
         * @author Xiao Tangren  <unphp@qq.com>
         * @param type $table_name
         * @return type
         */
        public function getTableField($table_name)
        {
                try
                {
                        $key = $table_name . '_mysql_table_fields';
                        if (!isset($this->tableFieldList[$key]))
                        {
                                $table_field = Pub_Cache::get_data_cache('system', $key, false);
                                if (empty($table_field))
                                {
                                        $table_field = $this->nowclient->get_table_fieldYar($table_name);
                                        Pub_Cache::set_data_cache('system', $key, $table_field, 3600 * 24 * 30);
                                }
                                $this->tableFieldList[$key] = $table_field;
                        }
                        return $this->tableFieldList[$key];
                }
                catch (Exception $exc)
                {
                        throw new Core_Exception($exc->getMessage(), '1100000006');
                }
        }

        public static function getClient($rpc)
        {
                try
                {
                        if (self::$webserviceApi == null)
                        {
                                throw new Core_Exception('Webservice Api must set!','1100000004');
                                return false;
                        }
                        if (isset(self::$clientService[$rpc]))
                        {
                                return self::$clientService[$rpc];
                        }
                        else
                        {
                                $api = self::$webserviceApi . '&rpc=' . $rpc;
//                                $clientService = new Yar_Client($api);
                                $clientService = new SoapClient(null, array('uri' => self::$webserviceApi, 'location' => $api, 'trace' => true));
                                $h = new SoapHeader(self::$webserviceApi, 'doAuth', self::$auth_key, false, SOAP_ACTOR_NEXT);
                                $clientService->__setSoapHeaders(array($h));
                                $Webservice = new Core_Webservice($clientService);
                                self::$clientService[$rpc] = $Webservice;
                                return self::$clientService[$rpc];
                        }
                }
                catch (Exception $exc)
                {
                        throw new Core_Exception($exc->getMessage(),'1100000005');
                }
        }

        /**
         * 根据参数，返回一个Webservice的链接
         * @author xiaotangren  <unphp@qq.com>
         * @param type $rpc
         * @return RPC Yar_Client
         * @throws Exception
         */
        public static function Yar($rpc)
        {
                return self::getClient($rpc)->nowclient;
        }

        
}

?>
