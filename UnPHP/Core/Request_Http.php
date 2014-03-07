<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 请求默认处理类
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author Xiao Tangren  <unphp@qq.com>
 * @data 2014-03-05
 * */
class UnPHP_Request_Http extends UnPHP_Request_Abstract
{

        public function getLanguage()
        {
                
        }

        public function getQuery($name = NULL)
        {
                if (empty($name))
                {
                        return $_GET;
                }
                else
                {
                        return isset($_GET[$name]) ? $_GET[$name] : null;
                }
        }

        public function getPost($name = NULL)
        {
                if (empty($name))
                {
                        return $_POST;
                }
                else
                {
                        return $_POST[$name];
                }
        }

        public function getEnv($name = NULL)
        {
                
        }

        public function getServer($name = NULL)
        {
                if (empty($name))
                {
                        return $_SERVER;
                }
                else
                {
                        return $_SERVER[$name];
                }
        }

        public function getCookie($name = NULL)
        {
                if (empty($name))
                {
                        return $_COOKIE;
                }
                else
                {
                        return $_COOKIE[$name];
                }
        }

        public function getFiles($name = NULL)
        {
                if (empty($name))
                {
                        return $_FILES;
                }
                else
                {
                        return $_FILES[$name];
                }
        }

        public function isGet()
        {
                $rs = 'GET' === $this->_method ? true : false;
                return $rs;
        }

        public function isPost()
        {
                $rs = 'POST' === $this->_method ? true : false;
                return $rs;
        }

        public function isHead()
        {
                
        }

        public function isXmlHttpRequest()
        {
                
        }

        public function isPut()
        {
                
        }

        public function isDelete()
        {
                
        }

        public function isOption()
        {
                
        }

        public function isCli()
        {
                
        }

}
