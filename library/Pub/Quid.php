<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 唯一ID算法组件
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Pub_Quid
{

        protected $site_id = null;
        protected $language_id = null;
        private static $_instance;

        public static function getInstance()
        {
                if (!(self::$_instance instanceof self))
                {
                        self::$_instance = new self();
                }
                return self::$_instance;
        }

        private function __construct()
        {
                $this->site_id = Yaf_Registry::get('site_id');
                $this->language_id = Yaf_Registry::get('language_id');
        }

        /**
         * 注册新用户时，获取系统分配的用户ID
         */
        public function getUserID()
        {
//                $nowtime = explode('-', date("Y-m-d"));
//                $year = substr($nowtime[0], -2);
//                $month = $nowtime[1];
                $new_auto_id = $this->getID('user_id',10000000);
                $site_id = sprintf("%03d", $this->site_id);
//                $language_id = sprintf("%02d", $this->language_id);
//                $new_user_id = intval($year . $month . $site_id . $language_id .$new_auto_id.rand(100, 999));
                $new_user_id = intval($site_id .$new_auto_id.rand(100, 999));
                return $new_user_id;
        }

        /**
         * 发布新闻时，计算新闻的唯一ID
         * @return type
         */
        public function getNewID(){
                $new_auto_id = $this->getID('news_id',100000);
                $site_id = sprintf("%03d", $this->site_id);
                $new_user_id = intval($site_id .$new_auto_id.rand(10, 99));
                return $new_user_id;
        }
        
        /**
         * 
         * @param type $name 表ID名称
         * @param type $base_num 自增基数
         * @return int
         * @throws Exception
         */
        protected function getID($name,$base_num)
        {
                try
                {
                        $condition = array();
                        $condition['name'] = $name;
                        $rs = M_Quid::getOne($condition);
                        if(empty($rs)){
                                M_Quid::mode()->insert(array('name'=>$name,'id'=>$base_num));
                        }
                        if(isset($rs['id']) && $rs['id']<$base_num){
                                M_Quid::mode()->update(array('name'=>$name),array('$set'=>array('id'=>$base_num)));
                        }
                        $new = array();
                        $new['$inc']['id'] = 1;
                        if (M_Quid::mode()->update($condition, $new))
                        {
                                $quid_obj = M_Quid::findOne($condition);
                                if (isset($quid_obj))
                                {
                                        $attributes = $quid_obj->getAttributes();
                                        if (isset($attributes['id']) && $attributes['id'] > $base_num)
                                        {
                                                return $attributes['id'];
                                        } else
                                        {
                                                throw new Exception("Table quid get id can't empty!");
                                        }
                                } else
                                {
                                        throw new Exception("Table quid get err!");
                                        return null;
                                }
                        } else
                        {
                                throw new Exception("Table quid save err!");
                                return null;
                        }
                } catch (Exception $exc)
                {
                        echo $exc->getMessage();
                }
        }
        
        //将IP转换为数字
        protected function ipton($ip)
        {
                $ip_arr = explode('.', $ip); //分隔ip段
                foreach ($ip_arr as $value)
                {
                        $iphex = dechex($value); //将每段ip转换成16进制
                        if (strlen($iphex) < 2)
                        {//255的16进制表示是ff，所以每段ip的16进制长度不会超过2
                                $iphex = '0' . $iphex; //如果转换后的16进制数长度小于2，在其前面加一个0
                                //没有长度为2，且第一位是0的16进制表示，这是为了在将数字转换成ip时，好处理
                        }
                        $ipstr.=$iphex; //将四段IP的16进制数连接起来，得到一个16进制字符串，长度为8
                }
                return hexdec($ipstr); //将16进制字符串转换成10进制，得到ip的数字表示
        }

        
        
        //将数字转换为IP，进行上面函数的逆向过程
        protected function ntoip($n)
        {
                $iphex = dechex($n); //将10进制数字转换成16进制
                $len = strlen($iphex); //得到16进制字符串的长度
                if (strlen($iphex) < 8)
                {
                        $iphex = '0' . $iphex; //如果长度小于8，在最前面加0
                        $len = strlen($iphex); //重新得到16进制字符串的长度
                }
                //这是因为ipton函数得到的16进制字符串，如果第一位为0，在转换成数字后，是不会显示的
                //所以，如果长度小于8，肯定要把第一位的0加上去
                //为什么一定是第一位的0呢，因为在ipton函数中，后面各段加的'0'都在中间，转换成数字后，不会消失
                for ($i = 0, $j = 0; $j < $len; $i = $i + 1, $j = $j + 2)
                {//循环截取16进制字符串，每次截取2个长度
                        $ippart = substr($iphex, $j, 2); //得到每段IP所对应的16进制数
                        $fipart = substr($ippart, 0, 1); //截取16进制数的第一位
                        if ($fipart == '0')
                        {//如果第一位为0，说明原数只有1位
                                $ippart = substr($ippart, 1, 1); //将0截取掉
                        }
                        $ip[] = hexdec($ippart); //将每段16进制数转换成对应的10进制数，即IP各段的值
                }
                return implode('.', $ip); //连接各段，返回原IP值
        }

        /**
         * 获得用户的真实IP地址
         *
         * @access  public
         * @return  string
         */
        public function real_ip()
        {
                static $realip = NULL;
                if ($realip !== NULL)
                {
                        return $realip;
                }
                if (isset($_SERVER))
                {
                        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
                        {
                                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

                                /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
                                foreach ($arr AS $ip)
                                {
                                        $ip = trim($ip);

                                        if ($ip != 'unknown')
                                        {
                                                $realip = $ip;

                                                break;
                                        }
                                }
                        } elseif (isset($_SERVER['HTTP_CLIENT_IP']))
                        {
                                $realip = $_SERVER['HTTP_CLIENT_IP'];
                        } else
                        {
                                if (isset($_SERVER['REMOTE_ADDR']))
                                {
                                        $realip = $_SERVER['REMOTE_ADDR'];
                                } else
                                {
                                        $realip = '0.0.0.0';
                                }
                        }
                } else
                {
                        if (getenv('HTTP_X_FORWARDED_FOR'))
                        {
                                $realip = getenv('HTTP_X_FORWARDED_FOR');
                        } elseif (getenv('HTTP_CLIENT_IP'))
                        {
                                $realip = getenv('HTTP_CLIENT_IP');
                        } else
                        {
                                $realip = getenv('REMOTE_ADDR');
                        }
                }

                preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
                $realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
                return $realip;
        }

}

?>
