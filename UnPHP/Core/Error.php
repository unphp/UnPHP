<?php
/**
 * 框架默认的“异常/错误”等处理控制器
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author Xiao Tangren  <unphp@qq.com>
 * @data 2014-03-05
 * */
class UnPHP_Error
{

        protected $_request;

        public function __construct($request)
        {
                $this->_request = $request;
        }

        public function getRequest()
        {
                return $this->_request;
        }

        public function error404Action()
        {
                header('Status: 404 Not fond this page!');
                $html = '<!DOCTYPE html>' . "\r\n";
                $html .= '<html>' . "\r\n";
                $html .= '<head>' . "\r\n";
                $html .= '<meta http-equiv=Content-Type content="text/html;charset=utf-8">' . "\r\n";
                $html .= '<title>404 Error</title>' . "\r\n";
                $html .= '<style>' . "\r\n";
                $html .= 'body {width: 960px;margin: 0 auto;font-family: Tahoma, Verdana, Arial, sans-serif;}' . "\r\n";
                $html .= 'div .main {margin: 0 auto;padding: 80px 30px 30px;}' . "\r\n";
                $html .= 'div .foot {margin: 0 auto;margin-top:50px;border-top: dimgray solid 1px;font-size: 14px; color: gray;  }' . "\r\n";
                $html .= 'div .foot a:link,a:visited,a:active,a:hover{color: #999;}' . "\r\n";
                $html .= 'div .foot #pp{color: #B6AEB6;}' . "\r\n";
                $html .= '</style>' . "\r\n";
                $html .= '</head>' . "\r\n";
                $html .= '<body>' . "\r\n";
                $html .= '<div class="main">' . "\r\n";
                $html .= '<div>' . "\r\n";
                $html .= '<h1>404</h1>' . "\r\n";
                $html .= '<p>404, Not found this page!</p>' . "\r\n";
                $html .= '</div>' . "\r\n";
                $html .= '<div class="foot">' . "\r\n";
                $html .= '<p>' . "\r\n";
                $html .= '<span>power by: </span><span id="pp"><a href="http://www.unphp.cn">UnPHP</a></span>&nbsp;&nbsp;&nbsp;&nbsp;' . "\r\n";
                $html .= '<span>Email: </span><span id="pp">unphp#qq.com(将#换成@)</span>&nbsp;&nbsp;&nbsp;&nbsp;' . "\r\n";
                $html .= '</p>' . "\r\n";
                $html .= '</div>' . "\r\n";
                $html .= '</div>' . "\r\n";
                $html .= '</body>' . "\r\n";
                $html .= '</html>';
                echo $html;
                exit();
        }

        public function debugAction($code, $msg, $file, $line, $trace_as_string)
        {
                header('Status: 503 Not fond this page!');
                $html = '<!DOCTYPE html>' . "\r\n";
                $html .= '<html>' . "\r\n";
                $html .= '<head>' . "\r\n";
                $html .= '<meta http-equiv=Content-Type content="text/html;charset=utf-8">' . "\r\n";
                $html .= '<title>503 Error</title>' . "\r\n";
                $html .= '<style>' . "\r\n";
                $html .= 'body {width: 960px;margin: 0 auto;font-family: Tahoma, Verdana, Arial, sans-serif;}' . "\r\n";
                $html .= 'div .main {margin: 0 auto;padding: 80px 30px 30px;}' . "\r\n";
                $html .= 'div .foot {margin: 0 auto;margin-top:50px;border-top: dimgray solid 1px;font-size: 14px; color: gray;  }' . "\r\n";
                $html .= 'div .foot a:link,a:visited,a:active,a:hover{color: #999;}' . "\r\n";
                $html .= 'div .foot #pp{color: #B6AEB6;}' . "\r\n";
                $html .= '</style>' . "\r\n";
                $html .= '</head>' . "\r\n";
                $html .= '<body>' . "\r\n";
                $html .= '<div class="main">' . "\r\n";
                $html .= '<div>' . "\r\n";
                $html .= "<h1>503</h1>" . "\r\n";
                $html .= '<p>Code::' . $code . '</p>' . "\r\n";
                $html .= '<p>Message::' . $msg . '</p>' . "\r\n";
                $html .= '<p>File::' . $file . '</p>' . "\r\n";
                $html .= '<p>Line::' . $line . '</p>' . "\r\n";
                $html .= '<p>Info::' . $trace_as_string . '</p>' . "\r\n";
                $html .= '</div>' . "\r\n";
                $html .= '<div class="foot">' . "\r\n";
                $html .= '<p>' . "\r\n";
                $html .= '<span>power by: </span><span id="pp"><a href="http://www.unphp.cn">UnPHP</a></span>&nbsp;&nbsp;&nbsp;&nbsp;' . "\r\n";
                $html .= '<span>Email: </span><span id="pp">unphp#qq.com(将#换成@)</span>&nbsp;&nbsp;&nbsp;&nbsp;' . "\r\n";
                $html .= '</p>' . "\r\n";
                $html .= '</div>' . "\r\n";
                $html .= '</div>' . "\r\n";
                $html .= '</body>' . "\r\n";
                $html .= '</html>';
                echo $html;
                exit();
        }

}

?>