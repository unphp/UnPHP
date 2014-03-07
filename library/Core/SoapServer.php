<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Webservice(Soap)服务基类
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Core_SoapServer
{

        /**
         * 身份验证密钥
         * @var type 
         */
        private $key = 'typioktiioasfhfermkahdfa';

        /**
         * 身份验证是否通过
         * @var type 
         */
        protected $auth = false;

        
        public function __construct($auth=false)
        {
                $this->auth = $auth;
        }


        /**
         * 强制身份验证
         * @param type $a
         * @throws SoapFault
         */
        public function doAuth($a)
        {
                if ($a != $this->key)
                {
                        throw new SoapFault('Server', '您无权访问');
                }
                else
                {
                        $this->auth = true;
                }
        }

}

?>
