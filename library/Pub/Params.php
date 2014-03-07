<?php
/**
 * request参数过滤处理类
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Pub_Params extends ArrayObject
{
        
        private $data = "";
        private static $_instance;

        public function __construct()
        {
                parent::__construct(array(), ArrayObject::STD_PROP_LIST);
                foreach (array('_POST', '_GET') as $_request)
                {
                        if (!isset($GLOBALS[$_request]) || !is_array($GLOBALS[$_request]))
                                continue;
                        foreach ($GLOBALS[$_request] as $_key => $_value)
                        {
                                $_key = $this->mEncode($_key);
                                $_value = $this->daddslashes($_value);
                                $GLOBALS[$_request][$_key] = $this->data[$_key] = $_value;
                        }
                }
        }

        public static function getInstance()
        {
                if (!(self::$_instance instanceof self))
                {
                        self::$_instance = new self();
                }
                return self::$_instance;
        }

        public function pUrlArray($str)
        {//将URL路径转为数组/key/value
                if (!is_array($str))
                        $mArr = explode('/', $str);
                else
                        $mArr = $str;
                for ($i = 0; $i < count($mArr); $i+=2)
                {
                        $this->data[$mArr[$i]] = $this->daddslashes($mArr[$i + 1]);
                }
                unset($mArr);
        }

        //将URL请求数据转为数组     key=value&key1=value1
        public function pUrlToArr($str)
        {
                $mArr = explode('&', $str);
                foreach ($mArr as $v)
                {
                        $v = explode("=", $v);
                        $this->data[$this->mEncode($v[0])] = $this->mEncode($v[1]);
                }
        }

        public function pArrayToUrl($mArr)
        {//将URL路径转为数组/key/value
                $tmp = array();
                foreach ($mArr as $k => $v)
                {
                        $tmp[] = "$k/$v";
                }
                return implode('/', $tmp);
        }

        //数组数据安全处理
        public function pArray($mArr)
        {
                foreach ($mArr as $k => $v)
                {
                        $this->data[$k] = $this->daddslashes($v);
                }
                unset($mArr);
        }

        //所有数据
        public function getData()
        {
                return $this->data;
        }

        public function __get($key)
        {
                if (isset($this->data[$key]))
                {
                        return $this->data[$key];
                }
                else
                {
                        return NULL;
                }
        }

        public function __set($key, $val)
        {
                $this->data[$key] = $val;
        }

        public function __isset($key)
        {
                return isset($this->data[$key]);
        }

        public function __unset($key)
        {
                unset($this->data[$key]);
        }

        //转为安全代码
        public function mEncode($str,$p=true)
        {
                if (is_string($str))
                {
                        $str = stripslashes($str);
                        $st1 = array("#", "&", "/", '"', "'", "<", ">", "(", ")", "[", "]", "{", "}", "@", "`");
                        $st2 = array('&#035;', "&amp;", '&#047;', '&quot;', '&#039;', '&lt;', '&gt;', '&#040;', '&#041;', '&#091;', '&#093;', '&#123;', '&#125;', '&#064;', '&#096;');
                        if ($p)
                        {
                                $str = str_replace($st1, $st2, $str);
                        }
                        else
                        {
                                $str = str_replace($st2, $st1, $str);
                        }
                }
                return $str;
        }

        //转为安全代码
        public function daddslashes($string)
        {
                if (is_array($string))
                {
                        foreach ($string as $key => $val)
                                $string[$key] = $this->daddslashes($val);
                }
                else
                {
                        $string = $this->mEncode($string);
                }
                return $string;
        }
}

?>
