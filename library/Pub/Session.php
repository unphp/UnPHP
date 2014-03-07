<?php

/**
 * Session类（借鉴改造自Ecshop）
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Pub_Session
{
        public $db = NULL;
        public $session_table = '';
        public $max_life_time = 3600; // SESSION 
        public $session_name = '';
        public $session_id = '';
        public $session_expiry = '';
        public $session_md5 = '';
        public $session_cookie_path = '/';
        public $session_cookie_domain = '';
        public $session_cookie_secure = false;
        public $_ip = '';
        public $_time = 0;

        public function __construct($session_name = 'AECMP_ID', $session_id = '')
        {
                $_SESSION = array();

                if (!empty($GLOBALS['cookie_path']))
                {
                        $this->session_cookie_path = $GLOBALS['cookie_path'];
                } else
                {
                        $this->session_cookie_path = '/';
                }

                if (!empty($GLOBALS['cookie_domain']))
                {
                        $this->session_cookie_domain = $GLOBALS['cookie_domain'];
                } else
                {
                        $this->session_cookie_domain = '';
                }

                if (!empty($GLOBALS['cookie_secure']))
                {
                        $this->session_cookie_secure = $GLOBALS['cookie_secure'];
                } else
                {
                        $this->session_cookie_secure = false;
                }
                /* 根据$_COOKIE[$this->session_name]，解析出session_id */
                //------------------------------------------------------------------------
                $this->session_name = $session_name;
                $this->_ip = real_ip();
                if ($session_id == '' && !empty($_COOKIE[$this->session_name]))
                {
                        $this->session_id = $_COOKIE[$this->session_name];
                } else
                {
                        $this->session_id = $session_id;
                }

                if ($this->session_id)
                {
                        $tmp_session_id = substr($this->session_id, 0, 32);
                        if ($this->gen_session_key($tmp_session_id) == substr($this->session_id, 32))
                        {
                                $this->session_id = $tmp_session_id;
                        } else
                        {
                                $this->session_id = '';
                        }
                }
                $this->_time = time();
                //--------------------------------------------------------------------------

                /* 如果session_id解析成功，则加载session */
                if ($this->session_id)
                {
                        $this->load_session();
                }
                /* 否则创建新的session，并插入数据库，并保存到cookie里去 */ 
                else
                {
                        $this->gen_session_id();
                        setcookie($this->session_name, $this->session_id . $this->gen_session_key($this->session_id), time()+3600*24, $this->session_cookie_path, $this->session_cookie_domain, $this->session_cookie_secure);
                }
                /* 关键的一步：注册php脚本执行结束的回调函数，定期销毁session */
                register_shutdown_function(array(&$this, 'close_session'));
                //var_dump($this->session_id);exit;
        }

        /**
         * 创建新的SESSION时，计算获得唯一的ID
         * @return type
         */
        public function gen_session_id()
        {
                $this->session_id = md5(uniqid(mt_rand(), true));
                return $this->insert_session();
        }

        /**
         * 验证ID的算法，防止Cookie造假
         * @staticvar string $ip
         * @param type $session_id
         * @return type
         */
        public function gen_session_key($session_id)
        {
                static $ip = '';
                if ($ip == '')
                {
                        $ip = substr($this->_ip, 0, strrpos($this->_ip, '.'));
                }
                return sprintf('%08x', crc32(APPLICATION_PATH . $ip . $session_id));
        }

        /**
         * 创建新的SESSION
         */
        public function insert_session()
        {
                $params = array();
                $params['sesskey'] = $this->session_id;
                $params['expiry'] = $this->_time;
                $params['ip'] = $this->_ip;
                $session_mode = new M_Session($params);
                $session_mode->save();
        }

        /**
         * 载入SESSION数据
         */
        public function load_session()
        {
                $M_Session = M_Session::findOne(array('sesskey'=>$this->session_id));
                $session = isset($M_Session) ? $M_Session->getAttributes() : null;
                if (empty($session))
                {
                        $this->insert_session();
                        $this->session_expiry = 0;
                        $this->session_md5 = '40cd750bba9870f18aada2478b24840a';
                        $_SESSION = array();
                } else
                {
                        if (!empty($session['data']) && $this->_time - $session['expiry'] <= $this->max_life_time)
                        {
                                $this->session_expiry = $session['expiry'];
                                $this->session_md5 = md5($session['data']);
                                $_SESSION = unserialize($session['data']);
                                $_SESSION['user_id'] = $session['userid'];
                                $_SESSION['admin_id'] = $session['adminid'];
                                $_SESSION['user_name'] = $session['user_name'];
                                $_SESSION['user_rank'] = $session['user_rank'];
                                $_SESSION['discount'] = $session['discount'];
                                $_SESSION['email'] = $session['email'];
                        } else
                        {
                                $M_SessionData = M_SessionData::findOne(array('sesskey'=>$this->session_id));
                                $session_data = isset($M_SessionData) ? $M_SessionData->getAttributes() : null;
                                if (!empty($session_data['data']) && $this->_time - $session_data['expiry'] <= $this->max_life_time)
                                {
                                        $this->session_expiry = $session_data['expiry'];
                                        $this->session_md5 = md5($session_data['data']);
                                        $_SESSION = unserialize($session_data['data']);
                                        $_SESSION['user_id'] = $session['userid'];
                                        $_SESSION['admin_id'] = $session['adminid'];
                                        $_SESSION['user_name'] = $session['user_name'];
                                        $_SESSION['user_rank'] = $session['user_rank'];
                                        $_SESSION['discount'] = $session['discount'];
                                        $_SESSION['email'] = $session['email'];
                                } else
                                {
                                        $this->session_expiry = 0;
                                        $this->session_md5 = '40cd750bba9870f18aada2478b24840a';
                                        $_SESSION = array();
                                }
                        }
                }
        }

        
        /**
         * 更新SESSION
         * @return boolean
         */
        public function update_session()
        {
                $adminid = !empty($_SESSION['admin_id']) ? intval($_SESSION['admin_id']) : 0;
                $userid = !empty($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
                $user_name = !empty($_SESSION['user_name']) ? trim($_SESSION['user_name']) : 0;
                $user_rank = !empty($_SESSION['user_rank']) ? intval($_SESSION['user_rank']) : 0;
                $discount = !empty($_SESSION['discount']) ? round($_SESSION['discount'], 2) : 0;
                $email = !empty($_SESSION['email']) ? trim($_SESSION['email']) : 0;
                unset($_SESSION['admin_id']);
                unset($_SESSION['user_id']);
                unset($_SESSION['user_name']);
                unset($_SESSION['user_rank']);
                unset($_SESSION['discount']);
                unset($_SESSION['email']);
                $data = serialize($_SESSION);
                $this->_time = time();
                /* 如果$data的md5值发生变化，则更新session */
                /* 如果相隔时间超过120秒，则更新session */
                if ($this->session_md5 == md5($data) && $this->_time < ($this->session_expiry + 120))
                {
                        return true;
                }
                //$data = addslashes($data);  // mongodb 不用转
                if (isset($data{255}))
                {
                        //$this->db->autoReplace($this->session_data_table, array('sesskey' => $this->session_id, 'expiry' => $this->_time, 'data' => $data), array('expiry' => $this->_time, 'data' => $data));
                        $M_SessionData = M_SessionData::findOne(array('sesskey'=>$this->session_id));
                        if($M_SessionData){
                                $M_SessionData->set('time',$this->_time);
                                $M_SessionData->set('data',$data);
                                $M_SessionData->save();
                        }
                        else{
                                $params = array();
                                $params['sesskey'] = $this->session_id;
                                $params['expiry'] = $this->_time;
                                $params['data'] = $data;
                                $M_SessionData = new M_SessionData($params);
                                $M_SessionData->save();
                        }
                        $data = '';
                }
                //return $this->db->query('UPDATE ' . $this->session_table . " SET expiry = '" . $this->_time . "', ip = '" . $this->_ip . "', userid = '" . $userid . "', adminid = '" . $adminid . "', user_name='" . $user_name . "', user_rank='" . $user_rank . "', discount='" . $discount . "', email='" . $email . "', data = '$data' WHERE sesskey = '" . $this->session_id . "' LIMIT 1");
                $M_Session = M_Session::findOne(array('sesskey' => $this->session_id));
                if ($M_Session)
                {
                        $M_Session->set('expiry',$this->_time);
                        $M_Session->set('ip',$this->_ip);
                        $M_Session->set('userid',$userid);
                        $M_Session->set('adminid',$adminid);
                        $M_Session->set('user_name',$user_name);
                        $M_Session->set('user_rank',$user_rank);
                        $M_Session->set('discount',$discount);
                        $M_Session->set('email',$email);
                        $M_Session->set('data',$data);
                        $rs = $M_Session->save();
                        return $rs;
                }
        }

        /**
         * 定期回收了session，避免了session表爆死
         * @author xiaotangren  <unphp@qq.com>
         * @return boolean
         */
        public function close_session()
        {
                $this->update_session();
                $time = $this->_time - $this->max_life_time;
                if (mt_rand(0, 2) == 2)
                {
                        M_SessionData::mode()->remove(array('expiry'=>array('$lt'=>$time)));
                }
                if ((time() % 2) == 0)
                {
                        return M_Session::mode()->remove(array('expiry'=>array('$lt'=>$time)));
                }

                return true;
        }


        /**
         * 退出登录时，销毁SESSION
         * @return type
         */
        public function destroy_session()
        {
                $_SESSION = array();
                setcookie($this->session_name, $this->session_id, 1, $this->session_cookie_path, $this->session_cookie_domain, $this->session_cookie_secure);
                //M_Cart::mode()->remove(array('session_id'=>$this->session_id));
                $M_SessionData = M_SessionData::findOne(array('sesskey'=>$this->session_id));
                if($M_SessionData) $M_SessionData->destroy();
                $M_Session = M_Session::findOne(array('sesskey'=>$this->session_id));
                if($M_Session) return $M_Session->destroy();
        }

        /**
         * 获取当前SESSION的ID
         * @return type
         */
        public function get_session_id()
        {
                return $this->session_id;
        }

        /**
         * 通过SESSION计算当前在线人数。
         * @return type
         */
        public function get_users_count()
        {
                return M_Session::count();
        }

}

?>