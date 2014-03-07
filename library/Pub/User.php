<?php

/**
 * 用户登录/注册类（借鉴改造自Ecshop）
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Pub_User
{
        /* 整合对象使用的cookie的domain */

        public $cookie_domain = '';

        /* 整合对象使用的cookie的path */
        public $cookie_path = '/';

        /* 是否需要同步数据到商城，开放平台时需要用到 */
        protected $need_sync = false;
        public $error = 0;

        public function __construct($cookie_domain = '', $cookie_path = '/')
        {
                $this->cookie_domain = $cookie_domain;
                $this->cookie_path = $cookie_path;
                $this->need_sync = false;
        }

        /**
         * 检验用户名和密码
         * @param type $username
         * @param type $password
         * @return type
         */
        public function check_user($username)
        {
                $condition = array();
                $condition['user_name'] = $username;
                $rs = M_Users::count($condition);
                if ($rs>0)
                        return true;
                else
                        return false;
        }

        public function login_check($email, $password)
        {
                $data = null;
                $condition = array();
                if (preg_match('/[\d\w\S]+(\.[\S]+)?@[\d\w\S]+(\.[\S]+)/ie', $email))
                {
                        $condition['email'] = $email;
                } else
                {
                        return null;
                        // 关闭用户名登录！ update by：xiaotangren 2013-07-20
                        // $condition['user_name'] = $username;
                }
                $rs = M_Users::getOne($condition);
                if ($rs)
                {
                        switch ($rs['ec_salt'])
                        {
                                case 'magento':
                                        if($this->magentoValidatePassword($password,$rs['password']))
                                                $data = $rs;
                                        else
                                                $data = null;
                                        break;
                                default:
                                        $psd = $this->compile_password($password);
                                        if ($rs['password'] == $psd)
                                                $data = $rs;
                                        else
                                                $data = null;
                                        break;
                        }
                        // 记录用户重复的帐号
                        $num = M_Users::count($condition);
                        if ($num>1)
                        {
                                $pwd_bool = null===$data ? 0:1;
                                Pub_Log::err_user_emailLog($num,$email,$rs['user_name'],$pwd_bool);
                        }
                }
                else{
//                        $mysql_count = M_Users::mode()->query($condition,array('type'=>'count'));
                        $data = null;
                }
                        return $data;
        }

        /**
         * 检验用户名和密码
         * @param type $username
         * @param type $password
         * @return type
         */
        public function check_user_password($user_id, $password = null)
        {
                $condition = array();
                $condition['user_id'] = $user_id;
                $rs = M_Users::getOne($condition);
                if (isset($password))
                {
                        switch ($rs['ec_salt'])
                        {
                                case 'magento':
                                        if($this->magentoValidatePassword($password,$rs['password']))
                                                return true;
                                        else
                                                return null;
                                        break;
                                default:
                                        $psd = $this->compile_password($password);
                                        if ($rs['password'] == $psd)
                                                return true;
                                        else
                                                return null;
                                        break;
                        }
                }
                else
                {
                        if (is_array($rs) && isset($rs['password']))
                                return true;
                        else
                                return false;
                }
        }

        /**
         *  设置指定用户SESSION
         * @access  public
         * @param
         * @return void
         */
        public function set_session($email = '')
        {
                //$sess = Yaf_Registry::get('SESS');
                if (empty($email))
                {
                        session_destroy();
                        //$sess->destroy_session();
                } else
                {
                        $condition = array();
                        $condition['email'] = $email;
                        $row = M_Users::getOne($condition);
                        if ($row)
                        {
                                $_SESSION['user_id'] = $row['user_id'];
                                $_SESSION['user_name'] = $row['user_name'];
                                $_SESSION['email'] = $row['email'];
                        }
                }
        }

        /**
         *  设置cookie
         * @access  public
         * @param
         * @return void
         */
        public function set_cookie($email = '', $remember = null)
        {
                if (empty($email))
                {
                        /* 摧毁cookie */
                        $time = time() - 3600;
                        setcookie("AECMP[user_id]", '', $time, $this->cookie_path);
                        setcookie("AECMP[password]", '', $time, $this->cookie_path);
                        setcookie("AECMP[username]", '', $time, $this->cookie_path);
                        setcookie("AECMP[email]", '', $time, $this->cookie_path);
                }
                /* 只有当设置了“记住帐号”才会setcookie */ elseif ($remember)
                {
                        /* 设置cookie */
                        $time = time() + 3600 * 24 * 15;
                        setcookie("AECMP[email]", $email, $time, $this->cookie_path, $this->cookie_domain);
                        $condition = array();
                        $condition['email'] = $email;
                        $row = M_Users::getOne($condition);
                        if ($row)
                        {
                                setcookie("AECMP[username]", $row['username'], $time, $this->cookie_path, $this->cookie_domain);
                                setcookie("AECMP[user_id]", $row['user_id'], $time, $this->cookie_path, $this->cookie_domain);
                                setcookie("AECMP[password]", $row['password'], $time, $this->cookie_path, $this->cookie_domain);
                        }
                }
        }

        /**
         *  编译密码函数
         * @access  public
         * @param   array   $cfg 包含参数为 $password, $md5password, $salt, $type
         * @return void
         */
        public function compile_password($password, $type = PWD_MD5)
        {
                $md5password = md5($password);
                switch ($type)
                {
                        case PWD_MD5 :
                                return $md5password;
                }
                return $md5password;
        }

        /**
         *
         * @access  public
         * @param
         * @return void
         */
        public function logout()
        {
                $this->set_cookie(); //清除cookie
                $this->set_session(); //清除session
        }

        /**
         *  根据登录状态设置cookie
         * @access  public
         * @param
         * @return void
         */
        public function get_cookie()
        {
                $id = $this->check_cookie();
                if ($id)
                {
                        if ($this->need_sync)
                        {
                                $this->sync($id);
                        }
                        $this->set_session($id);

                        return true;
                } else
                {
                        return false;
                }
        }

        /**
         *  检查cookie是正确，返回用户名
         * @access  public
         * @param
         * @return void
         */
        public function check_cookie()
        {
                return '';
        }

        /**
         *  检查指定邮箱是否存在
         * @access  public
         * @param   string  $email   用户邮箱
         * @return  boolean
         */
        public function check_email($email)
        {
                if (!empty($email))
                {
                        /* 检查email是否重复 */
                        $condition = array();
                        $condition['email'] = $email;
                        $check = M_Users::count($condition);
                        if ($check > 0)
                        {
                                $this->error = ERR_EMAIL_EXISTS;
                                return true;
                        }
                        return false;
                }
        }

        /**
         *  添加一个新用户
         * @access  public
         * @param
         * @return int
         */
        public function add_user($username, $password, $email)
        {
                /* 检查email是否重复 */
                $condition = array();
                $condition['email'] = $email;
                $check = M_Users::count($condition);
                $rpc_check = M_Users::checkEmail($email);
                $post_password = $this->compile_password($password);
                if ($check > 0)
                {
                        $this->error = ERR_EMAIL_EXISTS;
                        return false;
                }
                else{
                        /**
                        * 当不同步时，记录该注册的邮箱帐号
                        */
                        $mysql_count = M_Users::mode()->query($condition, array('type' => 'count'));
                        if ($mysql_count > 0)
                        {
                                $mysql_list = M_Users::mode()->query($condition, array('type' => 'all'));
                                foreach ($mysql_list as $sync_condition)
                                {
                                        $rs = M_Users::mode(array(), 'Mongo')->syncInsert($sync_condition);
                                        $lower_update_condition = array();
                                        $lower_update_condition['user_id'] = $sync_condition['user_id'];
                                        $lower_update_new = array();
                                        $lower_update_new['$set']['email'] = strtolower($sync_condition['email']);
                                        $lower_update_new['$set']['password'] = $post_password;
                                        $lower_update_new['$set']['user_name'] = $username;
                                        M_Users::mode()->update($lower_update_condition,$lower_update_new);
                                }
                                if (($mysql_count > 1))
                                {
                                        $data = M_Users::getAll($condition);
                                        foreach ($data as $key => $row)
                                        {
                                                if ($key == 0)
                                                {
                                                        //-------------------------------
                                                        // 保留的帐号。
                                                        //-------------------------------
                                                        $keep_user = $row;
                                                }
                                                else
                                                {
                                                        //-------------------------------
                                                        // 删除的帐号：删除前合并到保留帐号里去。
                                                        //-------------------------------
                                                        $merge_condition = array();
                                                        $merge_condition['user_id'] = $row['user_id'];
                                                        $merge_new = array();
                                                        $merge_new['$set']['user_id'] = $keep_user['user_id'];
                                                        // 合并用户的订单
                                                        M_OrderInfo::mode()->update($merge_condition, $merge_new);
                                                        // 合并地址
                                                        M_UserAddress::mode()->update($merge_condition, $merge_new);
                                                        // 合并红包积分
                                                        M_UserBonus::mode()->update($merge_condition, $merge_new);
                                                        // 合并购物车
                                                        M_Cart::mode()->update($merge_condition, $merge_new);
                                                        // 合并收藏的商品
                                                        M_CollectGoods::mode()->update($merge_condition, $merge_new);
                                                        // 合并商品标签
                                                        M_BookingGoods::mode()->update($merge_condition, $merge_new);
                                                        // 合并已付款发货订单
                                                        M_DeliveryOrder::mode()->update($merge_condition, $merge_new);
                                                        // 删除帐号
                                                        $delete_condition = array();
                                                        $delete_condition['user_id'] = $row['user_id'];
                                                        M_Users::mode()->remove($delete_condition);
                                                }
                                        }
                                }
                                return true;
                        }
                        
                }
                /* 插入数据 */
                $new = array();
                $new['user_id'] = Pub_Quid::getInstance()->getUserID();
                $new['user_name'] = $username;
                $new['email'] = $email;
                $new['password'] = $post_password;
                //var_dump($new);exit;
                $rs = M_Users::mode()->insert($new);
                if($rs==false)
                        return false;
                if ($this->need_sync)
                {
                        $this->sync($username, $password);
                }
                return true;
        }

        public function get_need_sync()
        {
                return $this->need_sync;
        }

        
        /**
         * magento 密码验证加入的方法
         * @param type $len
         * @param string $chars
         * @return string
         */
        public function magentoGetRandomString($len, $chars = null)
        {
                if (is_null($chars))
                {
                        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                }
                mt_srand(10000000 * (double) microtime());
                for ($i = 0, $str = '', $lc = strlen($chars) - 1; $i < $len; $i++)
                {
                        $str .= $chars[mt_rand(0, $lc)];
                }
                return $str;
        }

        /**
         * magento 密码验证加入的方法
         * @param type $password
         * @param type $salt
         * @return type
         */
        public function magentoGetHash($password, $salt = false)
        {
                if (is_integer($salt))
                {
                        $salt = $this->magentoGetRandomString($salt);
                }
                return $salt === false ? md5($password) : md5($salt . $password) . ':' . $salt;
        }

        /*
         * magento 密码验证加入的方法
          @param string $password
         * @param string $hash
         * @return bool
         */

        public function magentoValidatePassword($password, $hash)
        {
                $hashArr = explode(':', $hash);
                switch (count($hashArr))
                {
                        case 1:
                                return $this->magentoGetHash($password) === $hash;
                        case 2:
                                return $this->magentoGetHash($hashArr[1] . $password) === $hashArr[0];
                }
                return false;
        }

}

?>
