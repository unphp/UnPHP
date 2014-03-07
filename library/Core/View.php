<?php

/**
 * 模板引擎调解器
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */

/**
 * 加载Smarty模板引擎
 */
//require_once APPLICATION_PATH."/vendor/smarty/SmartyBC.class.php";

class Core_View implements Yaf_View_Interface
{

        /**
         * 
         * Smarty object
         * @var Smarty
         */
        public $_smarty;

        /**
         * Constructor
         *
         * @param string $tmplPath
         * @param array $extraParams
         * @return void
         */
        public function __construct($template_dir, $methods = null, $attribute = null)
        {
                //$this->_smarty = new SmartyBC();
                //$this->_smarty->setTemplateDir($template_dir);
                $redis_cacheing = Pub_Redis::getConfkey('smarty_cache', 'cacheing');
                $this->_smarty = $redis_cacheing && Pub_Redis::$redis_no_err ? new Ext_View_SmartyRedis($template_dir, $themes) : new Ext_View_Smarty($template_dir, $themes);
                if (null !== $methods)
                {
                        foreach ($methods as $key => $value)
                        {
                                $this->_smarty->$key($value);
                        }
                }
                if (null !== $attribute)
                {
                        foreach ($attribute as $key => $value)
                        {
                                $this->_smarty->$key = $value;
                        }
                }
        }

        /**
         * Return the template engine object
         *
         * @return Smarty
         */
        public function getEngine()
        {
                return $this->_smarty;
        }

        public function setTemplateDir($template_dir)
        {
                $this->_smarty->setTemplateDir($template_dir);
        }

        public function setCompileDir($compile_dir)
        {
                $this->_smarty->setCompileDir($compile_dir);
        }

        public function setCacheDir($cache_dir)
        {
                $this->_smarty->setCacheDir($cache_dir);
        }

        /**
         * Set the path to the templates
         *
         * @param string $path The directory to set as the path.
         * @return void
         */
        public function setScriptPath($path)
        {
                if (is_readable($path))
                {
                        $this->_smarty->template_dir = $path;
                        return;
                }

                throw new Exception('Invalid path provided');
        }

        /**
         * Retrieve the current template directory
         *
         * @return string
         */
        public function getScriptPath()
        {
                return $this->_smarty->template_dir;
        }

        /**
         * Alias for setScriptPath
         *
         * @param string $path
         * @param string $prefix Unused
         * @return void
         */
        public function setBasePath($path, $prefix = 'Zend_View')
        {
                return $this->setScriptPath($path);
        }

        /**
         * Alias for setScriptPath
         *
         * @param string $path
         * @param string $prefix Unused
         * @return void
         */
        public function addBasePath($path, $prefix = 'Zend_View')
        {
                return $this->setScriptPath($path);
        }

        /**
         * 魔法方法，用于快速赋模板变量的值
         * @author Xiao Tangren <unphp@qq.com>
         * @param string $key The variable name.
         * @param mixed $val The variable value.
         * @return void
         */
        public function __set($key, $val)
        {
                $this->_smarty->$key = $val;
                // 排除设置缓存
                if (in_array($key, array('force_compile', 'caching')))
                {
                        $this->_smarty->$key = $val;
                }
                else
                        $this->_smarty->assign($key, $val);
        }

        /**
         * 魔术方法，获取smarty属性
         * @author Xiao Tangren <unphp@qq.com>
         * @param type $name
         * @return type
         */
        public function __get($name)
        {
                return $this->_smarty->$name;
        }

        /**
         * 开启smarty缓存
         * @author Xiao Tangren <unphp@qq.com>
         * @date 2013-08-06
         * @return type
         */
        public function setCache()
        {
                if (Pub_Url::is_https() || DEBUG_AECMP)
                        return;
                $this->_smarty->caching = 1;
                $this->_smarty->force_compile = 1;
        }

        /**
         * 关闭smarty缓存
         * @author Xiao Tangren <unphp@qq.com>
         * @date 2013-08-06
         * @return type
         */
        public function unCache()
        {
                $this->_smarty->caching = 1;
                $this->_smarty->force_compile = 1;
        }

        /**
         * Allows testing with empty() and isset() to work
         *
         * @param string $key
         * @return boolean
         */
        public function __isset($key)
        {
                return (null !== $this->_smarty->get_template_vars($key));
        }

        /**
         * Allows unset() on object properties to work
         *
         * @param string $key
         * @return void
         */
        public function __unset($key)
        {
                $this->_smarty->clear_assign($key);
        }

        /**
         * Assign variables to the template
         *
         * Allows setting a specific key to the specified value, OR passing
         * an array of key => value pairs to set en masse.
         *
         * @see __set()
         * @param string|array $spec The assignment strategy to use (key or
         * array of key => value pairs)
         * @param mixed $value (Optional) If assigning a named variable,
         * use this as the value.
         * @return void
         */
        public function assign($spec, $value = null)
        {
                if (is_array($spec))
                {
                        $this->_smarty->assign($spec);
                        return;
                }

                $this->_smarty->assign($spec, $value);
        }

        /**
         * Clear all assigned variables
         *
         * Clears all variables assigned to Zend_View either via
         * {@link assign()} or property overloading
         * ({@link __get()}/{@link __set()}).
         *
         * @return void
         */
        public function clearVars()
        {
                $this->_smarty->clear_all_assign();
        }

        /**
         * 判断变量是否被注册并返回值
         *
         * @access  public
         * @param   string     $name
         *
         * @return  mix
         */
        function get_template_vars($name = null)
        {
                return $this->_smarty->get_template_vars($name);
        }

        /**
         * 增加模板中使用的CSS
         */
        public function addCss($mixed)
        {
                $css_code = '<link rel="stylesheet" type="text/css" href="%css_path%" />';
                $add_css = "";
                if (!empty($mixed))
                {
                        if (is_array($mixed))
                        {
                                foreach ($mixed as $value)
                                {
                                        $css_path_all = empty($value) ? "" : '/' . ltrim($value, "\//");
                                        if (!empty($css_path_all) && file_exists(APPLICATION_PATH . $css_path_all))
                                        {
                                                $add_css .= str_replace("%css_path%", Pub_Url::get_home_url() . $css_path_all, $css_code);
                                                $add_css .= "\n";
                                        }
                                }
                        }
                        else
                        {
                                $css_path_all = empty($mixed) ? "" : '/' . ltrim($mixed, "\//");
                                if (!empty($css_path_all) && file_exists(APPLICATION_PATH . $css_path_all))
                                {
                                        $add_css = str_replace("%css_path%", Pub_Url::get_home_url() . $css_path_all, $css_code);
                                }
                        }
                }
                if (!empty($add_css))
                {
                        if ((null !== $this->_smarty->get_template_vars("add_css")))
                        {
                                self::assign("add_css", $add_css);
                        }
                        else
                        {
                                // 合并，加入css
                                $first_add_css = $this->_smarty->get_template_vars("add_css");
                                $add_css = $first_add_css . $add_css;
                                self::assign("add_css", $add_css);
                        }
                }
        }

        /**
         * 增加模板中使用的JAVASCRIPT
         * @author kevinG
         */
        public function addJs($mixed)
        {
                $URL_PUBLIC = '/themes/' . THEMES . '/public';
                $js_code = '<script type="text/javascript" src="%js_path%"></script>';
                $js_path = $URL_PUBLIC . "/js/";
                $add_js = "";
                if (!empty($mixed))
                {
                        if (is_array($mixed))
                        {
                                foreach ($mixed as $value)
                                {
                                        $js_path_all = empty($value) ? "" : '/' . ltrim($value, "\//");
                                        if (!empty($js_path_all) && file_exists(APPLICATION_PATH . $js_path_all))
                                        {
                                                $add_js .= str_replace("%js_path%", Pub_Url::get_home_url() . $js_path_all, $js_code);
                                                $add_js .= "\n";
                                        }
                                }
                        }
                        else
                        {
                                $js_path_all = empty($mixed) ? "" : '/' . ltrim($mixed, "\//");
                                if (!empty($js_path_all) && file_exists(APPLICATION_PATH . $js_path_all))
                                {
                                        $add_js = str_replace("%js_path%", Pub_Url::get_home_url() . $js_path_all, $js_code);
                                }
                        }
                }
                if ((null !== $this->_smarty->get_template_vars("add_js")))
                {
                        self::assign("add_js", $add_js);
                }
                else
                {
                        // 合并，加入css
                        $first_add_js = $this->_smarty->get_template_vars("add_js");
                        $add_js = $first_add_js . $add_js;
                        self::assign("add_js", $add_js);
                }
        }

        /**
         * Processes a template and returns the output.
         *
         * @param string $name The template to process.
         * @return string The output.
         */
        public function render($name, $value = NULL)
        {
                return $this->_smarty->fetch($name, $value);
        }

        public function display($name, $value = NULL)
        {
                $html = $this->_smarty->display($name, $value);
                return $html;
        }

        public function fetch($name, $value = NULL)
        {
                return $this->_smarty->fetch($name, $value);
        }

        public function is_cached($name, $value = NULL)
        {
                return $this->_smarty->is_cached($name, $value);
        }

}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
?>
