<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 系统日志类
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Pub_Log
{

        /**
         * 记录访问者访问的页面（首页，列表页，详细页）
         * @author Xiao Tangren <unphp@qq.com>
         * @param type $type
         * @param type $id
         * @return boolean
         */
        public static function visitorLog($type = 'index', $id = 0)
        {
                $uid = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
                $ip = real_ip();
                $data = $ip . ':' . $uid;
                $log_dir = Pub_Cache::get_data_path('log');
                $log_file = $log_dir . '/visitor/' . date("Ymd", time()) . '.log';
                switch ($type)
                {
                        case 'cat':
                                $data .= ':' . $id . ':0';
                                break;
                        case 'goods':
                                $data .= ':0:' . $id;
                                if (Pub_Redis::$redis_no_err)
                                {
                                        self::record_visitor_googs_redis($id);
                                }
                                break;
                        case 'index':
                                $data .= ':0:0';
                                break;
                        default:
                                $data .= ':0:0';
                                break;
                }
                self::write($log_file, $data);
                return true;
        }
        
        /**
         * 新用户注册日志
         * @author Xiao Tangren <unphp@qq.com>
         * @param type $email
         * @return boolean
         */
        public static function registerLog($email){
                $log_dir = Pub_Cache::get_data_path('log');
                $log_file = $log_dir . '/register/' . date("Ym", time()) . '.log';
                $data = date("Y-m-d h:m:s A").' | '.$email;
                self::write($log_file, $data);
                return true;
        }
        
        /**
         * 记录用户帐号（邮箱）重复的
         * @author Xiao Tangren <unphp@qq.com>
         * @param type $email
         * @return boolean
         */
        public static function err_user_emailLog($num,$email,$user_name,$pwd_bool){
                $log_dir = Pub_Cache::get_data_path('log');
                $log_file = $log_dir . '/err/err_user_email_' . date("Ym", time()) . '.log';
                $data = date("Y-m-d h:m:s A").' | '.$num.' | '.$email.' | '.$user_name.' | '.$pwd_bool;
                self::write($log_file, $data);
                return true;
        }
        
        /**
         * @author Xiao Tangren <unphp@qq.com>
         * 系统错误异常日志
         * @param type $err
         */
        public static function errLog($err){
                $log_dir = Pub_Cache::get_data_path('log');
                $log_file = $log_dir . '/err/' . date("Ymd", time()) . '.log';
                $data = date("Y-m-d h:m:s A").' | '.$err;
                self::write($log_file, $data);
        }
        /**
         * 调试日志
         */
        public static function debugLog($err){
                $log_dir = Pub_Cache::get_data_path('log');
                $log_file = $log_dir . '/debug/' . date("Ymd", time()) . '.log';
                $data = date("Y-m-d h:m:s A").' | '.$err;
                self::write($log_file, $data);
        }
        
        /**
         * 记录订单行为日志
         * @param type $type
         * @param type $id
         * @return boolean
         */
        public static function payLog($data)
        {
                $log_dir = Pub_Cache::get_data_path('log');
                $log_file = $log_dir . '/pay/' . date("Ymd", time()) . '.log';
                
                //userid:123456|setp:create_order|payment:paypal|time:201309021232105|orderid:2012356465###
                self::write($log_file, $data);
                return true;
        }
        
        
        protected static function write($log_file,$data){
                $f = Pub_Cache::fopen($log_file, 'a+');
                if (filesize($log_file) > 0)
                {
                        fwrite($f, "\r\n" . $data);
                } else
                {
                        fwrite($f, $data);
                }
                fclose($f);
        }
        
        
        /**
         * redis
         * 记录单个商品（详细页），每天的访问次数（不限重复IP）。
         * @author Xiao Tangren <unphp@qq.com>
         * @param type $id
         */
        protected static function record_visitor_googs_redis($id){
                $hash = 'click_count_'.date("Ymd", time());
                $goods_id = $GLOBALS['_CFG']['site_code'].'_'.$GLOBALS['_CFG']['language_code'].'_goods:'.$id;
                if(Pub_Redis::mode()->hExists($hash, $goods_id)){
                        Pub_Redis::mode()->hIncrBy($hash, $goods_id, 1);
                }
                else{
                        Pub_Redis::mode()->hSet($hash, $goods_id, 1);
                }
        }

}

?>
