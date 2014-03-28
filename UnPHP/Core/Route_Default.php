<?php

/**
 * 默认的“路由协议”
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author Xiao Tangren  <unphp@qq.com>
 * @data 2014-03-05
 * */
class UnPHP_Route_Default implements UnPHP_Route_Interface {

        /**
         * 默认路由： /m/c/a/params1/1/params2/2/...
         * @author Xiao Tangren  <unphp@qq.com>
         * @data 2014-03-05
         * @param type $request
         * @return boolean
         */
        public function route(UnPHP_Request_Abstract $request) {
                $rs = FALSE;
                $match = array();
                $base_url = $request->getServer('REQUEST_URI');
                $selfPramas = stripos($base_url, '?');
                $base_url = $selfPramas ? substr($base_url, 0, $selfPramas) : $base_url;
                $match = explode("/", trim($base_url, '/\\'));
                $n = count($match);
                switch ($n) {
                        case 0:
                                $request->setModuleName($request->getDefaultModule());
                                $request->setControllerName($request->getDefaultController());
                                $request->setActionName($request->getDefaultAction());
                                $rs = TRUE;
                                
                                break;
                        case 1:
                                
                                if (in_array($match[0], UnPHP::app()->getModules())) {
                                        $request->setModuleName($match[0]);
                                        $request->setControllerName($request->getDefaultController());
                                        $request->setActionName($request->getDefaultAction());
                                }
                                else {
                                        $request->setModuleName($request->getDefaultModule());
                                        $request->setControllerName($match[0]);
                                        $request->setActionName($request->getDefaultAction());
                                }
                                $rs = TRUE;
                                break;
                        case 2:
                                if (in_array($match[0], UnPHP::app()->getModules())) {
                                        $request->setModuleName($match[0]);
                                        $request->setControllerName($match[1]);
                                        $request->setActionName($request->getDefaultAction());
                                        $rs = TRUE;
                                        break;
                                }
                                else {
                                        $request->setModuleName($request->getDefaultModule());
                                        $request->setControllerName($match[0]);
                                        $request->setActionName($match[1]);
                                        $rs = TRUE;
                                        break;
                                }
                        default:
                                if (in_array($match[0], UnPHP::app()->getModules())) {
                                        $request->setModuleName($match[0]);
                                        $request->setControllerName($match[1]);
                                        $request->setActionName($match[2]);
                                        for ($i = 4; $i < $n; $i+=2) {
                                                $request->setParam($match[$i - 1], $match[$i]);
                                        }
                                }
                                else {
                                        $request->setModuleName($request->getDefaultModule());
                                        $request->setControllerName($match[0]);
                                        $request->setActionName($match[1]);
                                        for ($i = 3; $i < $n; $i+=2) {
                                                $request->setParam($match[$i - 1], $match[$i]);
                                        }
                                }
                                $rs = TRUE;
                                break;
                }
                
                return $rs;
        }

        public function createUrl($module_controllers_action, $params = array()) {
                $url = "";
                $module_controllers_action = trim($module_controllers_action);
                if (!empty($module_controllers_action)) {
                        $temp = explode("/", trim($module_controllers_action, '/\\'));
                        switch (count($temp)) {
                                case 1:
                                        $url = $this->urlParams($temp[0], $params);
                                        break;
                                case 2:
                                        $url = $this->urlParams($temp[0] . '/' . $temp[1], $params);
                                        break;
                                case 3:
                                default:
                                        $url = $this->urlParams($temp[0] . '/' . $temp[1] . '/' . $temp[2], $params, TRUE);
                                        break;
                        }
                }
                else {
                        $url = $this->urlParams($url, $params);
                }
                return $url;
        }

        private function urlParams($url, $params, $b = false) {
                if ($b) {
                        $url .= '/';
                        if (!empty($params)) {
                                foreach ($params as $key => $value) {
                                        $url .= $key . '/' . $value . '/';
                                }
                        }
                }
                else {
                        $url .= '?';
                        if (!empty($params)) {
                                foreach ($params as $key => $value) {
                                        $url .= $key . '=' . $value . '&';
                                }
                        }
                }
                $url = substr($url, 0, -1);
                return $url;
        }

}
