<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Url相关操作类
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Pub_Url
{

        public static $no_301 = false;
        
        public static $domain_list = null;

                /**
         * 获取 代理服务器绑定的HOST域名
         * @author John Doe <john.doe@example.com>
         * @return type
         */
        public static function get_server_domain()
        {
                $domain = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
                return $domain;
        }

        /**
         * @ description get home page url 
         * @ author tanke <tanke@aukeys.com>
         * @ added 2013-03-26  
         * */
        public static function get_domain()
        {
                $domain = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
//                if(empty(self::$domain_list)){
//                        $conf = Pub_Aecmp::initGetSystem(ROOT_DOMAIN);
//                        self::$domain_list = $conf['domain_list'];
//                }
//                $domain = self::$domain_list[$GLOBALS['_CFG']['language_code']];
                return $domain;
        }

        public static function is_https(){
                $bool = false;
                if (isset($_SERVER['SERVER_PROTOCOL']) && preg_match('/^HTTPS/i', $_SERVER['SERVER_PROTOCOL']))
                {
                        $bool = true;
                }
                if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on')
                {
                        $bool = true;
                }
                return $bool;
                
        }

                /**
         * @ description get home page url 
         * @ author tanke <tanke@aukeys.com>
         * @ added 2013-03-26  
         * */
        public static function get_home_url($http = 1)
        {
                $protocol = self::is_https() ? 'https://' : 'http://'; 
                //$domain = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
                $domain = self::get_domain();
                if ($http)
                        return $protocol . $domain;
                else
                        return $domain;
        }

        /**
         * @ description get current url 
         * @ author tanke <tanke@aukeys.com>
         * @ added 2013-04-15
         * */
        public static function get_current_url($http = 1)
        {
                return self::get_home_url($http) . $_SERVER['REQUEST_URI'];
        }

        /**
         * @ description get current url 
         * @ author tanke <tanke@aukeys.com>
         * @ added 2013-04-15
         * */
        public static function get_request_url()
        {
                return $_SERVER['REQUEST_URI'];
        }

        /**
         * 获得当前环境的 URL 地址
         *
         * @access  public
         *
         * @return  void
         */
        public static function url()
        {
                return self::get_home_url();
        }

        /**
         * 判断中文浏览器
         * @return boolean
         */
        public static function get_browser_language()
        {
                $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 4);
                if (!preg_match("/zh-c/i", $lang) || !preg_match("/zh/i", $lang))
                {
                        return true;
                }
                return false;
        }

        public static function to_503($err='')
        {
                header('HTTP/1.1 503 Service Temporarily Unavailable');
                header('Status: 503 Service Temporarily Unavailable');
                header('Retry-After: 1800');
                echo '503 Service Unavailable! Error Code : '.$err;
                exit;
        }

        public static function to_301($link)
        {
                if (empty($link))
                        $link = self::get_home_url();
                Header("HTTP/1.1 301 Moved Permanently");
                Header("Location: $link");
        }
        
        public static function to_404($str=''){
                throw new Yaf_Exception_LoadFailed();//seo要求改为改到404页面
                exit;
        }

        /**
         * @author Xiao Tangren <unphp@qq.com>
         * @date 2013-07-25
         * @param type $url
         * @param type $params
         * @param type $value
         * @return string
         */
        public static function url_join_params($url, $params, $value = '')
        {
                if (is_array($params))
                {
                        foreach ($params as $k => $v)
                                if (!empty($v))
                                        $url .= Pub_Url::url_ask_and($url) . $k . '=' . $v;
                }
                else
                {
                        if (!empty($value))
                                $url .= Pub_Url::url_ask_and($url) . $params . '=' . $v;
                }
                return $url;
        }
        
        /**
         * 返回URL变量连接符,看有无问好,返回对应20121025 wushouhuan
         * @param type $url
         * @return string  & or ?
         */
        public static function url_ask_and($url)
        {
                $tag = preg_match('/\?/i', $url) ? '&' : '?';
                return $tag;
        }

}

?>
