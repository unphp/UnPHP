<?php

/**
 * 默认插件---初始化模块/控制器/方法等。
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Core_Plugins extends Yaf_Plugin_Abstract
{

        /**
         * 路由解析前
         * 最早的一个. 但是一些全局自定的工作, 还是应该放在Bootstrap中去完成
         * @author xiaotangren  <unphp@qq.com>
         * @param Yaf_Request_Abstract $request
         * @param Yaf_Response_Abstract $response
         */
        public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
        {
                
        }

        /**
         * 路由解析后
         * 此时路由一定正确完成, 否则这个事件不会触发
         * @author xiaotangren  <unphp@qq.com>
         * @param Yaf_Request_Abstract $request
         * @param Yaf_Response_Abstract $response
         */
        public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
        {
                
        }

        /**
         * 分发循环开始之前
         * @author xiaotangren  <unphp@qq.com>
         * @param Yaf_Request_Abstract $request
         * @param Yaf_Response_Abstract $response
         */
        public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
        {
                
        }

        /**
         * 分发之前
         * 如果在一个请求处理过程中, 发生了forward, 则这个事件会被触发多次
         * @author xiaotangren  <unphp@qq.com>
         * @param Yaf_Request_Abstract $request
         * @param Yaf_Response_Abstract $response
         */
        public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
        {
                
                //$this->initModule($module_name, $request, $response);
                if (Pub_Url::$custom_route)
                {
                        $data = Pub_Url::$custom_route;
                        if ($data['type'] == 'p')
                        {
                                $request->module = 'Goods';
                                $request->controller = 'Index';
                                $request->action = 'index';
                                $request->method = 'GET';
                                $request->setParam('id',$data['id']);

                        } else
                        {
                                $request->module = 'Category';
                                $request->controller = 'Index';
                                $request->action = 'index';
                                $request->method = 'GET';
                                $request->setParam('ocid',$data['id']);
                        }
                }
                $module_name = $request->getModuleName();
                //var_dump($module_name);exit;
                $this->initModule($module_name, $request, $response);
        }

        /**
         * 分发之后
         * 此时动作已经执行结束, 视图也已经渲染完成. 和preDispatch类似, 此事件也可能触发多次
         * @param Yaf_Request_Abstract $request
         * @param Yaf_Response_Abstract $response
         */
        public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
        {
                
        }

        /**
         * 分发循环结束之后
         * 此时表示所有的业务逻辑都已经运行完成, 但是响应还没有发送
         * @author xiaotangren  <unphp@qq.com>
         * @param Yaf_Request_Abstract $request
         * @param Yaf_Response_Abstract $response
         */
        public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
        {
                if (DEBUG_AECMP)
                {
                        echo '<hr style="border-top: 1px solid #ff0000;" />';
                        echo '以下为调试的debug变量数据：<br>';
                        if (empty($GLOBALS['debug']) && is_array($GLOBALS['debug']))
                        {
                                foreach ($GLOBALS['debug'] as $v)
                                {
                                        var_dump($v);
                                        echo '<br>';
                                }
                        }
                        else{
                                var_dump($GLOBALS['debug']);
                        }
                        echo '<br><br><br><br>';
                }
        }

        /**
         * 初始化模块配置
         * 不可重载
         * @author xiaotangren  <unphp@qq.com>
         */
        public function initModule($module_name, $request, $response)
        {
                // 调入模块初始化对象
                $module_file = Yaf_Application::app()->getConfig()->application->directory . "/modules/" . $module_name . "/" . $module_name . "Module.php";
                if (file_exists($module_file))
                {
                        Yaf_Loader::import($module_file);
                        $module_obj = $module_name . "Module";
                        $this->module = new $module_obj($request, $response);
                        Yaf_Registry::set($module_obj, $this->module);
                }
                // 根据模块名称，加载不同的语言包
                
        }

}

?>
