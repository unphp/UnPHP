<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 
 * “数据表模型”字段（表单提交“插入/更新”）验证类
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Core_Validate
{

        private $attributes = array();
        private $attributeLabels = array();
        private $err = array();

        /**
         * @author Xiao Tangren  <unphp@qq.com>
         * @param type $attributes  插入/更新的字段值
         * @param type $attributeLabels   字段翻译名称
         */
        public function __construct($attributes, $attributeLabels)
        {
                $this->attributes = $attributes;
                $this->attributeLabels = $attributeLabels;
        }

        /**
         * 必填字段
         * @author Xiao Tangren  <unphp@qq.com>
         * @param type $rule
         */
        public function requiredValidate($rule)
        {
                $fileds = explode(',', $rule[0]);
                foreach ($fileds as $filed)
                {
                        if (!isset($this->attributes[$filed]))
                        {
                                $this->err[] = $this->getFieldName($filed) . ' must set!';
                        }
                }
        }

        /**
         * 是否为数字
         * @author Xiao Tangren  <unphp@qq.com>
         * @param type $rule
         */
        public function numericalValidate($rule)
        {
                $fileds = explode(',', $rule[0]);
                foreach ($fileds as $filed)
                {
                        $v = $this->attributes[$filed];
                        if (!isset($v) || is_numeric($v))
                        {
                                $this->err[] = $this->getFieldName($filed) . ' must numerical!';
                        }
                        if ($rule['integerOnly'] && !is_int($v))
                        {
                                $this->err[] = $filed . ' must int!';
                        }
                }
        }

        /**
         * 字符串长度限制
         * @author Xiao Tangren  <unphp@qq.com>
         * @param type $rule
         */
        public function lengthValidate($rule)
        {
                $fileds = explode(',', $rule[0]);
                $max = $rule['max'];
                foreach ($fileds as $filed)
                {
                        $v = $this->attributes[$filed];
                        if (!isset($v) || isset($v{$max}))
                        {
                                $this->err[] = $this->getFieldName($filed) . ' Length must be less than ' . $max . '!';
                        }
                }
        }

        /**
         * 邮箱验证
         * @author Xiao Tangren  <unphp@qq.com>
         * @param type $rule
         */
        public function emailValidate($rule)
        {
//                $fileds = explode(',', $rule[0]);
        }

        /**
         * 获取错误！
         * @author Xiao Tangren  <unphp@qq.com>
         * @return type
         */
        public function getErr()
        {
                return $this->err;
        }

        /**
         * 获取字段的翻译名称
         * @author Xiao Tangren  <unphp@qq.com>
         * @return type
         */
        private function getFieldName($filed)
        {
                $filed_name = isset($this->attributeLabels[$filed]) ? $this->attributeLabels[$filed] : $filed;
                return $filed_name;
        }

}

?>
