<?php

/**
 * 默认的“路由协议”
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author Xiao Tangren  <unphp@qq.com>
 * @data 2014-03-05
 * */
class UnPHP_Route_Simple implements UnPHP_Route_Interface {

        private $m;
        private $c;
        private $a;

        public function __construct($module, $controllers, $action) {
                $this->m = $module;
                $this->c = $controllers;
                $this->a = $action;
        }

        public function route(UnPHP_Request_Abstract $request) {
                $rs = false;
                $m = $request->getQuery($this->m);
                $c = $request->getQuery($this->c);
                $a = $request->getQuery($this->a);
                $c = !empty($m) && empty($c) ? $request->getDefaultController() : $c;
                $a = !empty($m) && !empty($c) && empty($a) ? $request->getDefaultAction() : $a;
                if (!empty($m) && !empty($c) && !empty($a)) {
                        $rs = true;
                        $request->setModuleName($m);
                        $request->setControllerName($c);
                        $request->setActionName($a);
                }
                return $rs;
        }

        public function createUrl($module_controllers_action, $params = array()) {
                $url = "?";
                $module_controllers_action = trim($module_controllers_action);
                if (!empty($module_controllers_action)) {
                        $temp = explode("/", trim($module_controllers_action, '/'));
                        switch (count($temp)) {
                                case 1:
                                        $url .= $this->m . '=' . $temp[0] . '&';
                                        break;
                                case 2:
                                        $url .= $this->m . '=' . $temp[0] . '&';
                                        $url .= $this->c . '=' . $temp[1] . '&';
                                        break;
                                case 3:
                                        $url .= $this->m . '=' . $temp[0] . '&';
                                        $url .= $this->c . '=' . $temp[1] . '&';
                                        $url .= $this->a . '=' . $temp[2] . '&';
                                        break;
                                default:
                                        break;
                        }
                }
                if (!empty($params)) {
                        foreach ($params as $key => $value) {
                                $url .= $key . '=' . $value . '&';
                        }
                }
                $url = substr($url, 0, -1);
                return $url;
        }

}
