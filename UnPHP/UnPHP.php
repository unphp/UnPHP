<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 框架入口文件
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author Xiao Tangren  <unphp@qq.com>
 * @data 2014-03-05
 * */
class UnPHP
{
        private static $_app = null;
        private static $_global_library = array();
        private $_config = null;
        private $ds = null;
        private $_dispatcher = null;  // 应用调度分配器
        private $_modules = array();     // 应用“模块”列表---来之于配置app.modules

        /**
         * @author Xiao Tangren  <unphp@qq.com>
         * @data 2014-03-05
         * @param type $conf
         * @return \UnPHP
         */

        public function __construct($conf)
        {
                $this->ds = DIRECTORY_SEPARATOR;
                $this->init($conf);
                return $this;
        }

        /**
         * 获取应用的配置
         * @author Xiao Tangren  <unphp@qq.com>
         * @data 2014-03-05
         * @return array
         */
        public function getConfig()
        {
                return $this->_config;
        }

        /**
         * 获取应用配置中，声明的模块
         * @author Xiao Tangren  <unphp@qq.com>
         * @data 2014-03-05
         * @return array
         */
        public function getModules()
        {
                return $this->_modules;
        }

        /**
         * 初始化应用
         * @author Xiao Tangren  <unphp@qq.com>
         * @data 2014-03-05
         * @return UnPHP
         */
        public function bootstrap()
        {
                try
                {
                        if (isset($this->_config['app']['root']))
                        {
                                $bootstrapFile = $this->_config['app']['root'] . '/' . 'Bootstrap.php';
                                if (file_exists($bootstrapFile))
                                {
                                        $magicMethods = array(
                                            '__construct', '__destruct', '__call', '__callStatic',
                                            '__get', '__set', '__isset', '__unset',
                                            '__sleep', '__wakeup', '__toString', '__invoke',
                                            '__set_state', '__clone'
                                        );
                                        require $bootstrapFile;
                                        $bootstrap = new Bootstrap();
                                        $bootstrapRef = new ReflectionClass("Bootstrap");
                                        $methodList = $bootstrapRef->getMethods();
                                        foreach ($methodList as $methodTemp)
                                        {
                                                $f = substr($methodTemp->name, 0, 1);
                                                if ('_' === $f && !in_array($methodTemp->name, $magicMethods))
                                                {
                                                        $method = new ReflectionMethod($bootstrap, $methodTemp->name);
                                                        if (true === $method->isPublic())
                                                        {
                                                                if ($method->getParameters())
                                                                {
                                                                        $bootstrap->{$methodTemp->name}($this->_dispatcher);
                                                                }
                                                                else
                                                                {
                                                                        $bootstrap->{$methodTemp->name}();
                                                                }
                                                        }
                                                }
                                        }
                                }
                                else
                                {
                                        throw new UnPHP_Exception_StartupError('App\'s bootstrap(Bootstrap.php) not found, in the dir ' . $this->_config['app']['root'] . ' !');
                                }
                        }
                        else
                        {
                                throw new UnPHP_Exception_StartupError('App\'s config params, "root" must set!');
                        }
                }
                catch (UnPHP_Exception $exc)
                {
                        $err = new UnPHP_Controller_Error();
                        if (isset($this->_config['app']['debug']) && $this->_config['app']['debug'] == 1)
                        {
                                $err->debugAction($exc);
                        }
                        else
                        {
                                $err->error404Action();
                        }
                }
                return $this;
        }

        /**
         * 执行应用
         * @author Xiao Tangren  <unphp@qq.com>
         * @data 2014-03-05
         */
        public function run()
        {
                try
                {
                        if ($this->_dispatcher->getRouter()->route($this->_dispatcher->getRequest())) // 路由分发成功
                        {
                                $module_name = $this->_dispatcher->getRequest()->getModuleName();
                                $controller_name = $this->_dispatcher->getRequest()->getControllerName();
                                $action_name = $this->_dispatcher->getRequest()->getActionName();
                                // 判断模块开关
                                if (in_array($module_name, $this->_modules))
                                {
                                        $app_root = $this->_config['app']['root'];
                                        $controller_path = 'controllers/' . ucfirst($controller_name) . '.php';
                                        $controller_file = 'index' === $module_name ? $app_root . '/' . $controller_path : $app_root . '/' . $module_name . '/' . $controller_path;
                                        if (file_exists($controller_file))
                                        {
                                                include $controller_file;
                                                $controller = ucfirst($controller_name) . 'Controller';
                                                $action = $action_name . 'Action';
                                                $controller_obj = $this->checkClassMethod($controller, $action);
                                                if (is_object($controller_obj))
                                                {
                                                        $controller_obj->$action();
                                                }
                                        }
                                        else
                                        {
                                                throw new UnPHP_Exception_LoadFailed($controller_file . ', Not found this file!');
                                        }
                                }
                                else
                                {
                                        // 模块不存在（未在app.modules里配置）
                                        throw new UnPHP_Exception_LoadFailed_Module('App\'s config params, "module" not record now for you request module("' . $module_name . '") !');
                                }
                        }
                        else
                        {
                                // 路由分发失败
                                throw new UnPHP_Exception_RouterFailed('Router matching failed!');
                        }
                }
                catch (UnPHP_Exception $exc)
                {
                        $err = new UnPHP_Controller_Error($this->_dispatcher->getRequest());
                        if (isset($this->_config['app']['debug']) && $this->_config['app']['debug'] == 1)
                        {
                                $err->debugAction($exc);
                        }
                        else
                        {
                                $err->error404Action();
                        }
                }
        }

        /**
         * 初始化框架
         * @author Xiao Tangren  <unphp@qq.com>
         * @data 2014-03-05
         */
        private function init($confFile)
        {
                $this->iniLoadFile();
                $ReadConf = new Unphp_ReadConf($confFile);
                $this->_config = $ReadConf->get();
                $this->_modules = isset($this->_config['app']['modules']) ? explode(",", $this->_config['app']['modules']) : array();
                self::$_app = $this;
                $this->registerAutoLoad();
                $request = new UnPHP_Request_Http();
                // 设置默认模块/控制器/方法
                isset($this->_config['app']['default_module']) ? $request->setDefaultModule($this->_config['app']['default_module']) : $request->setDefaultModule('index');
                isset($this->_config['app']['default_controller']) ? $request->setDefaultController($this->_config['app']['default_controller']) : $request->setDefaultController('index');
                isset($this->_config['app']['default_action']) ? $request->setDefaultAction($this->_config['app']['default_action']) : $request->setDefaultModule('index');
                $this->_dispatcher = UnPHP_Dispatcher::getInstance();
                $this->_dispatcher->setRequest($request);
        }

        /**
         * 载入框架核心类文件
         * @author Xiao Tangren  <unphp@qq.com>
         * @data 2014-03-05
         */
        private function iniLoadFile()
        {
                $rootPath = __DIR__;
                require $rootPath . $this->ds . 'Lib' . $this->ds . 'ReadConf.php';
                require $rootPath . $this->ds . 'Lib' . $this->ds . 'AutoLoad.php';
                require $rootPath . $this->ds . 'Core' . $this->ds . 'Exception.php';
                require $rootPath . $this->ds . 'Core' . $this->ds . 'Request_Abstract.php';
                require $rootPath . $this->ds . 'Core' . $this->ds . 'Request_Http.php';
                require $rootPath . $this->ds . 'Core' . $this->ds . 'Router.php';
                require $rootPath . $this->ds . 'Core' . $this->ds . 'Route_Simple.php';
                require $rootPath . $this->ds . 'Core' . $this->ds . 'Dispatcher.php';
                require $rootPath . $this->ds . 'Core' . $this->ds . 'Bootstrap_Abstract.php';
                require $rootPath . $this->ds . 'Core' . $this->ds . 'Plugin_Abstract.php';
                require $rootPath . $this->ds . 'Core' . $this->ds . 'Controller_Abstract.php';
                require $rootPath . $this->ds . 'Core' . $this->ds . 'Controller_Error.php';
        }

        /**
         * 注册“自动加载接管”函数
         * @author Xiao Tangren  <unphp@qq.com>
         * @data 2014-03-05
         */
        private function registerAutoLoad()
        {
                //echo self::$app->getLibrary();exit;
                $auto = new Unphp_AutoLoad($this->_config["app"]["library"], self::$_global_library);
                spl_autoload_register(array($auto, 'defaultAutoLoad'));
        }

        private function checkClassMethod($class, $method)
        {
                $rs = null;
                $ref_class = new ReflectionClass($class);
                if ($ref_class->hasMethod($method))
                {
                        $obj = new $class($this->_dispatcher->getRequest());
                        $ref_method = new ReflectionMethod($obj, $method);
                        if ($ref_method->isPublic())
                        {
                                $rs = $obj;
                        }
                        else
                        {
                                throw new UnPHP_Exception_LoadFailed_Action('This action("' . $method . '") not open permissions!');
                        }
                }
                else
                {
                        throw new UnPHP_Exception_LoadFailed_Action('Not found this action("' . $method . '")!');
                }
                return $rs;
        }

        /**
         * 返回应用实例
         * @author Xiao Tangren  <unphp@qq.com>
         * @data 2014-03-05
         * @return type
         */
        public static function app()
        {
                return self::$_app;
        }

        /**
         * 设置系统全局库
         * @author Xiao Tangren  <unphp@qq.com>
         * @data 2014-03-05
         * @param type $path
         */
        public static function setLibrary($path)
        {
                self::$_global_library[] = $path;
        }

}
