<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of View_Interface
 *
 * @author xiao
 */
abstract class UnPHP_View_Abstract implements UnPHP_View_Interface
{
        protected $_tpl_vars = array();
        protected $_script_path = null;
}

Interface UnPHP_View_Interface{
        
        public function init($conf = array());

        public function render($view_path, $tpl_vars = NULL);

        public function display($view_path, $tpl_vars = NULL);

        public function assign($name, $value);

        public function setScriptPath($view_directory);

        public function getScriptPath();
}
