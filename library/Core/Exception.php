<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 系统核心异常处理类
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Core_Exception extends Exception
{

        public function __construct($message, $code, $previous=NULL)
        {
                parent::__construct($message, $code, $previous);
                $this->getCodeConfig();
                $this->getAecmpMessage();
        }

        private $_errCode = array();


        private function getCodeConfig()
        {
                $this->_errCode = Pub_Init::$common_ini->errcode;
        }

        public function getAecmpMessage()
        {
                $err = parent::getMessage();
                $code = parent::getCode();
                $code_msg = empty($this->_errCode[$code]) ? 0 : $this->_errCode[$code];
                if (DEBUG_AECMP)
                {
                        header("Content-type: text/html; charset=utf-8");
                        $str = '';
                        $str .= $code . ':::' . $code_msg . ':::' . $err . '<br>';
                        $str .= '<span style="color:blue;">File:</span> <span style="color:red;">' . parent::getFile() . '</span><br>';
                        $str .= 'File line:' . parent::getLine() . '<br>';
                        $str .= parent::getTraceAsString();
                        echo $str;
                        exit;
                }
                else
                {
                        $url = Pub_Url::get_current_url();
                        Pub_Log::errLog($code . ':::' . $code_msg . ':::' . $err . ':::' . $url);
                        Pub_Url::to_503($code);
                }
        }

}

?>
