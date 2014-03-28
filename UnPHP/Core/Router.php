<?php
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
         * 注册路由规则
         * @author Xiao Tangren  <unphp@qq.com>
         * @data 2014-03-05
         */
        public function getRegisteredRoute($name) 
        {
                return isset($this->_routes[$name]) ? $this->_routes[$name] : $this->_routes['default'];
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
                $base_url = $request->getServer('REQUEST_URI');
                $selfPramas = stripos($base_url, '?');
                $base_url = $selfPramas ? substr($base_url, 0, $selfPramas) : $base_url;
                if ($base_url == '/')
                {
                        $request->setModuleName($request->getDefaultModule());
                        $request->setControllerName($request->getDefaultController());
                        $request->setActionName($request->getDefaultAction());
                        $rs = TRUE;
                }
                $this->_routes['default'] = new UnPHP_Route_Default();
                if (FALSE === $rs && !empty($this->_routes))
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
