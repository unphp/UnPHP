<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * “自动加载”调控器
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author Xiao Tangren  <unphp@qq.com>
 * @data 2014-03-05
 * */
class Unphp_AutoLoad
{

        //put your code here
        private $common_library = null;
        private $app_library = null;

        public function __construct($appLibrary, $commonLibrary = null)
        {
                $this->common_library = $commonLibrary;
                $this->app_library = $appLibrary;
        }

        public function defaultAutoLoad($className)
        {
                $temp_list = explode("_", $className);
                $after = "";
                for ($i = 0; $i < count($temp_list) - 1; $i++)
                {
                        $after .= '/' . $temp_list[$i];
                }
                $className = $temp_list[count($temp_list) - 1];
                // 优先加载应用库文件
                $appClass = $this->app_library . $after . '/' . $className . '.php';
                if (file_exists($appClass))
                {
                        include $appClass;
                        return;
                }
                // 加载公共库文件
                if (!empty($this->common_library))
                {
                        foreach ($this->common_library as $library)
                        {
                                $commonClass = $library . $after . '/' . $className . '.php';
                                if (file_exists($commonClass))
                                {
                                        include $commonClass;
                                        return;
                                }
                        }
                }
        }

}
