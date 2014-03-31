<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Index
 *
 * @author xiao
 */
class IndexController extends UnPHP_Controller_Abstract
{

        //put your code here
        public function indexAction()
        {
            $view = $this->getView();
            $view->assign('meta_link','test');
            $view->display('/index.tpl');
            
        }

        public function infoAction()
        {
                phpinfo();
        }

}
