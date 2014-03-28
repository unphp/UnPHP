<?php
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
        private $_dispatcher = null;     // 应用调度分配器
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
         * 获取应用配置中，声明的模块
         * @author Xiao Tangren  <unphp@qq.com>
         * @data 2014-03-05
         * @return array
         */
        public function createURL($m_c_a,$params=array(),$route_name='default')
        {
                return $this->_dispatcher->getRouter()->getRegisteredRoute($route_name)->createUrl($m_c_a,$params);
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
                        $this->checkException(!isset($this->_config['app']['root']), 'UnPHP_Exception_StartupError', 'App\'s config params, "root" must set!');
                        $bootstrapFile = $this->_config['app']['root'] . '/' . 'Bootstrap.php';
                        $this->checkException(!file_exists($bootstrapFile), 'UnPHP_Exception_StartupError', 'App\'s bootstrap(Bootstrap.php) not found, in the dir ' . $this->_config['app']['root'] . ' !');
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
                catch (UnPHP_Exception $exc)
                {
                        $exc->getMsg($this->_config['app']['debug'], $this->_dispatcher->getErrorController());
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
                        // 判断路由是否成功
                        $this->checkException(!$this->_dispatcher->getRouter()->route($this->_dispatcher->getRequest()), 'UnPHP_Exception_RouterFailed', 'Router matching failed!');
                        $module_name = $this->_dispatcher->getRequest()->getModuleName();
                        $controller_name = $this->_dispatcher->getRequest()->getControllerName();
                        $action_name = $this->_dispatcher->getRequest()->getActionName();
                        // 判断模块开关
                        $this->checkException(!in_array($module_name, $this->_modules), 'UnPHP_Exception_LoadFailed_Module', 'App\'s config params, "module" not record now for you request module("' . $module_name . '") !');
                        $app_root = $this->_config['app']['root'];
                        $controller_path = 'controllers/' . ucfirst($controller_name) . '.php';
                        $controller_file = 'index' === $module_name ? $app_root . '/' . $controller_path : $app_root . '/modules/' . ucfirst($module_name) . '/' . $controller_path;
                        $this->checkException(!file_exists($controller_file), 'UnPHP_Exception_LoadFailed', $controller_file . ', Not found this file!');
                        include $controller_file;
                        $controller = ucfirst($controller_name) . 'Controller';
                        $action = $action_name . 'Action';
                        $controller_obj = $this->checkClassMethod($controller, $action);
                        if (is_object($controller_obj))
                        {
                                $controller_obj->init();
                                $controller_obj->$action();
                        }
                }
                catch (UnPHP_Exception $exc)
                {
                        $exc->getMsg($this->_config['app']['debug'], $this->_dispatcher->getErrorController());
                }
        }

        /**
         * 初始化框架
         * @author Xiao Tangren  <unphp@qq.com>
         * @data 2014-03-05
         */
        private function init($confFile)
        {
                error_reporting(0);
                $this->iniLoadFile();
                $this->_dispatcher = UnPHP_Dispatcher::getInstance();
                try
                {       
                        $ReadConf = new Unphp_ReadConf($confFile);
                        $this->_config = $ReadConf->get();
                        if($this->_config['app']['debug']){
                            error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR| E_COMPILE_ERROR | E_RECOVERABLE_ERROR);
                        }
                        $this->_modules = isset($this->_config['app']['modules']) ? explode(",", $this->_config['app']['modules']) : array();
                        self::$_app = $this;
<<<<<<< HEAD
                        // 注册“自动加载”接管函数
                        $this->registerAutoLoad();
                        $request = new UnPHP_Request_Http();
=======

                        // 注册“自动加载”接管函数
                        $this->registerAutoLoad();
                        $request = new UnPHP_Request_Http();

>>>>>>> be3ceb0d38a20221c5cabaf67a91af80f7ce34cf
                        // 设置默认模块/控制器/方法
                        isset($this->_config['app']['default_module']) ? $request->setDefaultModule($this->_config['app']['default_module']) : $request->setDefaultModule('index');
                        isset($this->_config['app']['default_controller']) ? $request->setDefaultController($this->_config['app']['default_controller']) : $request->setDefaultController('index');
                        isset($this->_config['app']['default_action']) ? $request->setDefaultAction($this->_config['app']['default_action']) : $request->setDefaultModule('index');
<<<<<<< HEAD
                        $this->_dispatcher->setRequest($request);
                        // 设置框架404异常页面
                        $this->_dispatcher->setErrorController(new UnPHP_Error($request));
                        // 加载框架扩展库
                        if (isset($this->_config['ext']))
                        {
                            foreach ($this->_config['ext'] as $k=>$v){
                                $init = isset($this->_config[$k]['path']) ? $this->_config[$k]['path'].$this->ds.'init.php':__DIR__ . $this->ds . 'Ext' . $this->ds .ucfirst($k).$this->ds. 'init.php';
                                require $init;
                                $initFunc = 'ext_'.$k.'_init';
                                $initFunc($this,$this->_dispatcher);
                            }
                        }
=======

                        $this->_dispatcher->setRequest($request);
                        $this->_dispatcher->setErrorController(new UnPHP_Error($request));

                        // 视图渲染
                        $this->checkException(!isset($this->_config['app']['cache_dir']), 'UnPHP_Exception_StartupError', 'App\'s config "cache_dir" must set! ');
                        $view_conf = array();
                        $view = $this->_config['view']['cache_mode'] == 'redis' ? new UnPHP_View_Smarty() : new UnPHP_View_Smarty();
                        $this->_dispatcher->setView($view);

                        // 
>>>>>>> be3ceb0d38a20221c5cabaf67a91af80f7ce34cf
                }
                catch (Exception $exc)
                {
                        $exc->getMsg($this->_config['app']['debug'], $this->_dispatcher->getErrorController());
                }
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
                require $rootPath . $this->ds . 'Core' . $this->ds . 'Route_Default.php';
                require $rootPath . $this->ds . 'Core' . $this->ds . 'Dispatcher.php';
                require $rootPath . $this->ds . 'Core' . $this->ds . 'Bootstrap_Abstract.php';
                require $rootPath . $this->ds . 'Core' . $this->ds . 'Plugin_Abstract.php';
                require $rootPath . $this->ds . 'Core' . $this->ds . 'Controller_Abstract.php';
                require $rootPath . $this->ds . 'Core' . $this->ds . 'View_Abstract.php';
                require $rootPath . $this->ds . 'Core' . $this->ds . 'Error.php';
<<<<<<< HEAD
=======
                require $rootPath . $this->ds . 'Ext' . $this->ds . 'View_Smarty.php';
>>>>>>> be3ceb0d38a20221c5cabaf67a91af80f7ce34cf
        }

        /**
         * 注册“自动加载接管”函数
         * @author Xiao Tangren  <unphp@qq.com>
         * @data 2014-03-05
         */
        private function registerAutoLoad()
        {
<<<<<<< HEAD
=======
                //var_dump(self::$_global_library);
>>>>>>> be3ceb0d38a20221c5cabaf67a91af80f7ce34cf
                $auto = new Unphp_AutoLoad($this->_config["app"]["library"], self::$_global_library);
                spl_autoload_register(array($auto, 'defaultAutoLoad'));
        }

        /**
         * 检查，是否需要抛出异常。
         * @author Xiao Tangren  <unphp@qq.com>
         * @data 2014-03-05
         * @param type $bool
         * @param type $exception_name
         * @param type $message
         * @throws type
         */
        private function checkException($bool, $exception_name, $message)
        {
                if ($bool)
                {
                        throw new $exception_name($message);
                }
        }

        /**
         * 检查“类/方法”是否存在，返回类实例对象。
         * @author Xiao Tangren  <unphp@qq.com>
         * @data 2014-03-05
         * @param type $class
         * @param type $method
         * @return \class
         */
        private function checkClassMethod($class, $method)
        {
                $rs = null;
<<<<<<< HEAD
                try
                {
                        $ref_class = new ReflectionClass($class);
                }
                catch (Exception $exc)
                {
                        throw new UnPHP_Exception_LoadFailed_Controller($exc->getMessage() . ' Not found this action("' . $class . '")');
                }
=======
                $ref_class = new ReflectionClass($class);
>>>>>>> be3ceb0d38a20221c5cabaf67a91af80f7ce34cf
                $this->checkException(!$ref_class->hasMethod($method), 'UnPHP_Exception_LoadFailed_Action', 'Not found this action("' . $method . '")!');
                $obj = new $class($this->_dispatcher->getRequest(), $this->_dispatcher->getView());
                $ref_method = new ReflectionMethod($obj, $method);
                $this->checkException(!$ref_method->isPublic(), 'UnPHP_Exception_LoadFailed_Action', 'This action("' . $method . '") not open permissions!');
                $rs = $obj;
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
