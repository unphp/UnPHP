<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * "观察者模式"接口
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Core_Subject implements Subject
{

        protected $_observers;
        public $err = null;
        public $UC = null;
        public $_LANG = null;

        public function __construct()
        {
                $this->UC = Yaf_Registry::get('UC');
                $this->err = Pub_Error::mode();
                $this->_LANG = $GLOBALS['_LANG'];
        }

        public function attach($observer)
        {
                $this->_observers[] = $observer;
        }

        public function detach($observer)
        {
                $observer_key = array_search($observer, $this->_observers);
                if ($observer_key !== false)
                {
                        unset($this->_observers[$observer_key]);
                }
        }

        public function notify()
        {
                
        }

}

interface Subject
{

        public function attach($observer);

        public function detach($observer);
}

?>
