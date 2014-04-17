<?php
/**
 * 数据表模型，字段验证过滤类
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author Xiao Tangren  <unphp@qq.com>
 * @data 2014-04-08
 * */
class Ext_Databases_Validation
{

        protected $_model;

        public function __construct($model)
        {
                $this->_model = $model;
        }

        /**
         * 必填字段验证。
         * @param type $data
         * @param type $rule
         * @return boolean
         */
        public function required($data, $rule)
        {
                $rs = true;
                $fileds = explode(",", $rule[0]);
                foreach ($fileds as $filed)
                {
                        if (!isset($data[$filed]) || $data[$filed]=="")
                        {
                                $rs = false;
                                $this->_model->addError('Filed\'s "' . $filed . '" must set value!');
                        }
                }
                return $rs;
        }

        /**
         * 整数验证。
         * @param type $data
         * @param type $rule
         * @return boolean
         */
        public function intcal($data, $rule)
        {
                $rs = true;
                $fileds = explode(",", $rule[0]);
                foreach ($fileds as $filed)
                {
                        if (empty($data[$filed]))
                        {
                                continue;
                        }
                        if (!preg_match('/^\s*[+-]?\d+\s*$/', "$data[$filed]"))
                        {
                                $rs = false;
                                $this->_model->addError('The Value of Filed\'s "' . $filed . '" must is Int !');
                        }
                }
                return $rs;
        }

        /**
         * 字符串长度过滤。
         * @param type $data
         * @param type $rule
         * @return boolean
         */
        public function length($data, $rule)
        {
                $rs = true;
                $fileds = explode(",", $rule[0]);
                foreach ($fileds as $filed)
                {
                        if (empty($data[$filed]))
                        {
                                continue;
                        }
                        $length = function_exists('mb_strlen') ? mb_strlen($data[$filed], 'utf-8') : strlen($data[$filed]);
                        if (!empty($rule['max']) && $length > $rule['max'])
                        {
                                $rs = false;
                                $this->_model->addError('The Value of Filed\'s "' . $filed . '" can not more than ' . $length . ' char !');
                        }
                        if (!empty($rule['min']) && $length < $rule['min'])
                        {
                                $rs = false;
                                $this->_model->addError('The Value of Filed\'s "' . $filed . '" must more than ' . $length . ' char !');
                        }
                }
                return $rs;
        }

}
