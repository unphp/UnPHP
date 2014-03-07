<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of test
 *
 * @author xiao
 */
class TestController extends UnPHP_Controller_Abstract
{
        //put your code here
        public function indexAction()
        {
                echo 'Hello,world! test---------';
                var_dump($this->getRequest()->getParams(),$_GET);
        }
}
