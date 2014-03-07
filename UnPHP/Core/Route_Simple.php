<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 默认的“路由协议”
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author Xiao Tangren  <unphp@qq.com>
 * @data 2014-03-05
 * */
class UnPHP_Route_Simple implements UnPHP_Route_Interface
{

        private $m;
        private $c;
        private $a;

        
        public function __construct($module, $controllers, $action)
        {
                $this->m = $module;
                $this->c = $controllers;
                $this->a = $action;
        }

        
        public function route(UnPHP_Request_Abstract $request)
        {
                $rs = false;
                $m = $request->getQuery($this->m);
                $c = $request->getQuery($this->c);
                $a = $request->getQuery($this->a);
                $c = !empty($m) && empty($c) ? $request->getDefaultController() : $c;
                $a = !empty($m) && !empty($c) && empty($a) ? $request->getDefaultAction() : $a;
                if (!empty($m) && !empty($c) && !empty($a))
                {
                        $rs = true;
                        $request->setModuleName($m);
                        $request->setControllerName($c);
                        $request->setActionName($a);
                }
                return $rs;
        }

}
