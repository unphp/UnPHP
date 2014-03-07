<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 路由匹配分发类
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author Xiao Tangren  <unphp@qq.com>
 * @data 2014-03-05
 * */
class UnPHP_Router
{

        private $_routes = array();
        private $_current_route = null;

        /**
         * 注册路由规则
         * @author Xiao Tangren  <unphp@qq.com>
         * @data 2014-03-05
         */
        public function addRoute($name, UnPHP_Route_Interface $route)
        {
                $this->_routes[$name] = $route;
        }

        /**
         * 开始路由匹配
         * @author Xiao Tangren  <unphp@qq.com>
         * @data 2014-03-05
         * @param UnPHP_Request_Abstract $request
         */
        public function route(UnPHP_Request_Abstract $request)
        {
                $rs = FALSE;
                if (!empty($this->_routes))
                {
                        // 遍历理由协议
                        foreach ($this->_routes as $name => $route)
                        {
                                if ($route->route($request))
                                {
                                        $this->_current_route = $name;
                                        $rs = TRUE;
                                        break;
                                }
                        }
                        if (FALSE === $rs && '/' !== $request->getServer('REQUEST_URI'))
                        {
                                $rs = $this->defaultRoute($request);
                        }
                }
                if ($request->getServer('REQUEST_URI') == '/')
                {
                        $request->setModuleName($request->getDefaultModule());
                        $request->setControllerName($request->getDefaultController());
                        $request->setActionName($request->getDefaultAction());
                        $rs = TRUE;
                }
                return $rs;
        }

        /**
         * 默认路由： /m/c/a/params1/1/params2/2/...
         * @author Xiao Tangren  <unphp@qq.com>
         * @data 2014-03-05
         * @param type $request
         * @return boolean
         */
        private function defaultRoute($request)
        {
                $rs = FALSE;
                $match = array();
                $base_url = $request->getServer('REQUEST_URI');
                $selfPramas = stripos($base_url,'?');
                $base_url = $selfPramas ? substr($base_url,0,$selfPramas) : $base_url;
                $match = explode("/", trim($base_url, '/'));
                $n = count($match);
                switch ($n)
                {
                        case 0:
                                $request->setModuleName($request->getDefaultModule());
                                $request->setControllerName($request->getDefaultController());
                                $request->setActionName($request->getDefaultAction());
                                $rs = TRUE;
                                break;
                        case 1:
                                if (in_array($match[0], UnPHP::app()->getModules()))
                                {
                                        $request->setModuleName($match[0]);
                                        $request->setControllerName($request->getDefaultController());
                                        $request->setActionName($request->getDefaultAction());
                                }
                                else
                                {
                                        $request->setModuleName($request->getDefaultModule());
                                        $request->setControllerName($match[0]);
                                        $request->setActionName($request->getDefaultAction());
                                }
                                $rs = TRUE;
                                break;
                        case 2:
                                if (in_array($match[0], UnPHP::app()->getModules()))
                                {
                                        $request->setModuleName($match[0]);
                                        $request->setControllerName($match[1]);
                                        $request->setActionName($request->getDefaultAction());
                                        $rs = TRUE;
                                        break;
                                }
                                else
                                {
                                        $request->setModuleName($request->getDefaultModule());
                                        $request->setControllerName($match[0]);
                                        $request->setActionName($match[1]);
                                        $rs = TRUE;
                                        break;
                                }
                        default:
                                if (in_array($match[0], UnPHP::app()->getModules()))
                                {
                                        $request->setModuleName($match[0]);
                                        $request->setControllerName($match[1]);
                                        $request->setActionName($match[2]);
                                        for ($i = 4; $i < $n; $i+=2)
                                        {
                                                $request->setParam($match[$i - 1], $match[$i]);
                                        }
                                }
                                else
                                {
                                        $request->setModuleName($request->getDefaultModule());
                                        $request->setControllerName($match[0]);
                                        $request->setActionName($match[1]);
                                        for ($i = 3; $i < $n; $i+=2)
                                        {
                                                $request->setParam($match[$i - 1], $match[$i]);
                                        }
                                }
                                $rs = TRUE;
                                break;
                }
                return $rs;
        }

}

/**
 * “路由协议”接口
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author Xiao Tangren  <unphp@qq.com>
 * @data 2014-03-05
 * */
Interface UnPHP_Route_Interface
{

        /**
         * 分析请求url，匹配路由规则。
         * @author Xiao Tangren  <unphp@qq.com>
         * @data 2014-03-05
         * return bool
         */
        public function route(UnPHP_Request_Abstract $request);
}
