<?php

/**
 * curl相关操作类
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Pub_Curl
{

        private $ch = null; //CURL句柄
        private $info = array(); //CURL执行前后所设置或服务器端返回的信息
        private $setopt = array(//CURL SETOPT 信息
            'port' => 80, //访问的端口,http默认是 80
            'userAgent' => '', //客户端 USERAGENT,如:"Mozilla/4.0",为空则使用用户的浏览器
            'timeOut' => 30, //连接超时时间
            'useCookie' => true, //是否使用 COOKIE 建议打开，因为一般网站都会用到
            'ssl' => false, //是否支持SSL
            'gzip' => true, //客户端是否支持 gzip压缩
            'proxy' => false, //是否使用代理
            'proxyType' => 'HTTP', //代理类型,可选择 HTTP 或 SOCKS5
            //代理的主机地址,如果是 HTTP 方式则要写成URL形式如:"http://www.proxy.com"
            //SOCKS方式则直接写主机域名为IP的形式，如:"192.168.1.1"
            'proxyHost' => 'http://www.proxy.com',
            'proxyPort' => 1234, //代理主机的端口
            'proxyAuth' => false, //代理是否要身份认证(HTTP方式时)
            'proxyAuthType' => 'BASIC', //认证的方式.可选择 BASIC 或 NTLM 方式
            'proxyAuthUser' => 'user', //认证的用户名和密码
            'proxyAuthPwd' => 'password',
        );

        public function __destruct()
        {//关闭CURL
                curl_close($this->ch);
        }

        public function __construct($setopt = array())
        {
                $this->setopt = array_merge($this->setopt, $setopt); //合并用户的设置和系统的默认设置
                function_exists('curl_init') || die('CURL Library Not Loaded'); //如果没有安装CURL则终止程序
                $this->ch = curl_init(); //初始化
                curl_setopt($this->ch, CURLOPT_PORT, $this->setopt['port']); //设置CURL连接的端口
                //使用代理
                if ($this->setopt['proxy'])
                {
                        $proxyType = $this->setopt['proxyType'] == 'HTTP' ? CURLPROXY_HTTP : CURLPROXY_SOCKS5;
                        curl_setopt($this->ch, CURLOPT_PROXYTYPE, $proxyType);
                        curl_setopt($this->ch, CURLOPT_PROXY, $this->setopt['proxyHost']);
                        curl_setopt($this->ch, CURLOPT_PROXYPORT, $this->setopt['proxyPort']);
                        //代理要认证
                        if ($this->setopt['proxyAuth'])
                        {
                                $proxyAuthType = $this->setopt['proxyAuthType'] == 'BASIC' ? CURLAUTH_BASIC : CURLAUTH_NTLM;
                                curl_setopt($this->ch, CURLOPT_PROXYAUTH, $proxyAuthType);
                                $user = "[{$this->setopt['proxyAuthUser']}]:[{$this->setopt['proxyAuthPwd']}]";
                                curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $user);
                        }
                }
                curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true); //启用时会将服务器服务器返回的“Location:”放在header中递归的返回给服务器
                if ($this->setopt['ssl'])
                {//打开的支持SSL
                        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false); //不对认证证书来源的检查
                        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, true); //从证书中检查SSL加密算法是否存在
                }
                $header[] = 'Expect:'; //设置http头,支持lighttpd服务器的访问
                curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);

                $userAgent = $this->setopt['userAgent'] ? $this->setopt['userAgent'] : $_SERVER['HTTP_USER_AGENT']; //设置 HTTP USERAGENT
                curl_setopt($this->ch, CURLOPT_USERAGENT, $userAgent);
                curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $this->setopt['timeOut']); //设置连接等待时间,0不等待
                curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->setopt['timeOut']); //设置curl允许执行的最长秒数
                if ($this->setopt['gzip'])
                {//设置客户端是否支持 gzip压缩
                        curl_setopt($this->ch, CURLOPT_ENCODING, 'gzip');
                }
                if ($this->setopt['useCookie'])
                {//是否使用到COOKIE
                        $cookfile = tempnam(sys_get_temp_dir(), 'cuk'); //生成存放临时COOKIE的文件(要绝对路径)
                        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $cookfile); //连接关闭以后，存放cookie信息
                        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $cookfile);
                }
                //是否将头文件的信息作为数据流输出(HEADER信息),这里保留报文
                curl_setopt($this->ch, CURLOPT_HEADER, true);
                //获取的信息以文件流的形式返回，而不是直接输出。
                curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($this->ch, CURLOPT_BINARYTRANSFER, true);
        }

        /**
         * 以 GET 方式执行请求
         * @param string $url :请求的URL
         * @param array $params ：请求的参数,格式如: array('id'=>10,'name'=>'yuanwei')
         * @param array $referer :引用页面,为空时自动设置,如果服务器有对这个控制的话则一定要设置的.
         * @return 错误返回:false 正确返回:结果内容
         */
        public function get($url, $params = array(), $referer = '')
        {
                return $this->_request('GET', $url, $params, array(), $referer);
        }

        /**
         * 以 POST 方式执行请求
         * @param string $url :请求的URL
         * @param array $params ：请求的参数,格式如: array('id'=>10,'name'=>'yuanwei')
         * @param array $uploadFile :上传的文件,支持相对路径,格式如下
         * 单个文件上传:array('img1'=>'./file/a.jpg')
         * 同字段多个文件上传:array('img'=>array('./file/a.jpg','./file/b.jpg'))
         * @param array $referer :引用页面,引用页面,为空时自动设置,如果服务器有对这个控制的话则一定要设置的.
         * @return 错误返回:false 正确返回:结果内容
         */
        public function post($url, $params = array(), $uploadFile = array(), $referer = '')
        {
                return $this->_request('POST', $url, $params, $uploadFile, $referer);
        }

        // 得到错误信息
        public function error()
        {
                return curl_error($this->ch);
        }

        //得到错误代码
        public function errno()
        {
                return curl_errno($this->ch);
        }

        /**
         * 得到发送请求前和请求后所有的服务器信息和服务器Header信息,其中
         * [before] ：请求前所设置的信息
         * [after] :请求后所有的服务器信息
         * [header] :服务器Header报文信息
         * @return array
         */
        public function getInfo()
        {
                return $this->info;
        }

        /**
         * 私有方法:执行最终请求
         * @param string $method :HTTP请求方式
         * @param string $url :请求的URL
         * @param array $params ：请求的参数
         * @param array $uploadFile :上传的文件(只有POST时才生效)
         * @param array $referer :引用页面
         * @return 错误返回:false 正确返回:结果内容
         */
        private function _request($method, $url, $params = array(), $uploadFile = array(), $referer = '')
        {
                if ($method == 'GET')
                {//如果是以GET方式请求则要连接到URL后面
                        $url = $this->_parseUrl($url, $params);
                }
                curl_setopt($this->ch, CURLOPT_URL, $url); //设置请求的URL
                if ($method == 'POST')
                {//如果是POST
                        curl_setopt($this->ch, CURLOPT_POST, true); //发送一个常规的POST请求，类型为：application/x-www-form-urlencoded
                        $postData = $this->_parsmEncode($params, false); //设置POST字段值
                        //如果有上传文件
                        if ($uploadFile)
                        {
                                foreach ($uploadFile as $key => $file)
                                {
                                        if (is_array($file))
                                        {
                                                $n = 0;
                                                foreach ($file as $f)
                                                {//文件必需是绝对路径
                                                        $postData[$key . '[' . $n++ . ']'] = '@' . realpath($f);
                                                }
                                        }
                                        else
                                        {
                                                $postData[$key] = '@' . realpath($file);
                                        }
                                }
                        }
                        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postData); //pr($postData); die;
                }
                //设置了引用页,否则自动设置
                if ($referer)
                        curl_setopt($this->ch, CURLOPT_REFERER, $referer);
                else
                        curl_setopt($this->ch, CURLOPT_AUTOREFERER, true);
                $this->info['before'] = curl_getinfo($this->ch); //得到所有设置的信息
                $result = curl_exec($this->ch); //开始执行请求

                $headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE); //得到报文头
                $this->info['header'] = substr($result, 0, $headerSize);
                $result = substr($result, $headerSize); //去掉报文头
                $this->info['after'] = curl_getinfo($this->ch); //得到所有包括服务器返回的信息
                //如果请求成功
                if ($this->errno() == 0)
                { //&& $this->info['after']['http_code'] == 200
                        return $result;
                }
                else
                {
                        return false;
                }
        }

        /**
         * 返回解析后的URL，GET方式时会用到
         * @param string $url :URL
         * @param array $params :加在URL后的参数
         * @return string
         */
        private function _parseUrl($url, $params)
        {
                $fieldStr = $this->_parsmEncode($params);
                if ($fieldStr)
                {
                        $url .= strstr($url, '?') === false ? '?' : '&';
                        $url .= $fieldStr;
                }
                return $url;
        }

        /**
         * 对参数进行ENCODE编码
         *
         * @param array $params :参数
         * @param bool $isRetStr : true：以字符串返回 false:以数组返回
         * @return string || array
         */
        private function _parsmEncode($params, $isRetStr = true)
        {
                $fieldStr = '';
                $spr = '';
                $result = array();
                foreach ($params as $key => $value)
                {
                        $value = urlencode($value);
                        $fieldStr .= $spr . $key . '=' . $value;
                        $spr = '&';
                        $result[$key] = $value;
                }
                return $isRetStr ? $fieldStr : $result;
        }

}

?>