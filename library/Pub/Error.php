<?php
/**
 * 公共错误消息类（借鉴改造自Ecshop）
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Pub_Error
{
        public $_smarty = null;
        public $_message = array();
        public $_template = '';
        public $error_no = 0;
        public static $modes = null;

        /**
         * 构造函数
         *
         * @access  public
         * @param   string  $tpl
         * @return  void
         */
        public function __construct($smarty,$tpl)
        {
                $this->_smarty = $smarty;
                $this->_template = $tpl;
        }
        
        public static function mode($view_adpter = '', $tpl = 'msg.tpl')
        {
                $smarty = empty($view_adpter) ? Yaf_Registry::get('viewAdapter') : $view_adpter;
                if (null === self::$modes)
                        self::$modes = new Pub_Error($smarty, $tpl);
                return self::$modes;
        }

        /**
         * 添加一条错误信息
         *
         * @access  public
         * @param   string  $msg
         * @param   integer $errno
         * @return  void
         */
        public function add($msg, $errno = 1)
        {
                if (is_array($msg))
                {
                        $this->_message = array_merge($this->_message, $msg);
                } else
                {
                        $this->_message[] = $msg;
                }
                $this->error_no = $errno;
        }

        /**
         * 清空错误信息
         *
         * @access  public
         * @return  void
         */
        public function clean()
        {
                $this->_message = array();
                $this->error_no = 0;
        }

        /**
         * 返回所有的错误信息的数组
         *
         * @access  public
         * @return  array
         */
        public function get_all()
        {
                return $this->_message;
        }

        /**
         * 返回最后一条错误信息
         *
         * @access  public
         * @return  void
         */
        public function last_message()
        {
                return array_slice($this->_message, -1);
        }

        /**
         * 显示错误信息
         *
         * @access  public
         * @param   string  $err_msg
         * @param   string  $href
         * @return  void
         */
        public function show($err_msg = '', $href = '',$type='html')
        {       
                if ($this->error_no > 0)
                {      
                        $message = array();
                        $href = (empty($href)) ? 'javascript:history.back();' : $href;
                        $message['info'] = $GLOBALS['_LANG']['url_auto_jump'];
                        $message['back_url'] = $href;
                        if(!empty($err_msg))
                        $message['content'] = '<div>' . ($type=='html' ? htmlspecialchars($err_msg) : $err_msg) . '</div>';
                        foreach ($this->_message AS $msg)
                        {
                                $message['content'] .= '<div>' . ($type=='html' ? htmlspecialchars($msg) : $msg) . '</div>';
                        }
                        if (isset($this->_smarty))
                        {
                                assign_template();
                                $this->_smarty->assign('auto_redirect', true);
                                $this->_smarty->assign('message', $message);
                                $this->_smarty->display($this->_template);
                        } else
                        {
                                die($message['content']);
                        }
                        exit;
                }
        }

}

?>
