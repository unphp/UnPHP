<?php

/**
 * 邮件发送公共类
 * 基于 webservice
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Pub_Mail
{

        static private $_mode;
        static public $clientService;

        public function __construct($clientService = null)
        {
                if ($clientService)
                {
                        self::$clientService = $clientService;
                } 
                else
                {
                        self::$clientService = Core_Webservice::Yar('MailRpc');
                }
        }
        
        public static function mode(){
                if (!(self::$_mode instanceof self))
                {
                        self::$_mode = new self();
                }
                return self::$_mode;
        }

        public function sendMail($name, $email, $title, $body,$type=0)
        {
                $site_id = Yaf_Registry::get("site_id");
                $language_id = Yaf_Registry::get("language_id");
                return self::$clientService->MailYar($site_id,$language_id,$name,$email, $title, $body,$type);
        }

        
        public function sendMailList($name, $email, $title, $body,$type=0,$pri=1){
                $new = array();
                $new['user_name'] = $name;
                $new['email'] = $email;
                $new['email_subject'] = $title;
                $new['email_content'] = $body;
                $new['type'] = $type;
                $new['pri'] = $pri;
                $new['last_send'] = time();
                M_EmailSendlist::mode()->insert($new);
                return true;
        }
        public function sendwebpowerMailList($user_id='',$name='', $email, $content,$type,$status=0){
                $new = array();
                $new['uset_id'] = $user_id;
                $new['user_name'] = $name;
                $new['email'] = $email;
                $new['email_content'] = $content;
                $new['type'] = $type;
                $new['status'] = $status;
                $new['add_date'] = time();
                M_EmailWebpowerlist::mode()->insert($new);
                return true;
        }
}

?>
