<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * “增删改”操作
 * 数据库“写入”操作类---基础类
 *
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Core_DBWr
{
        protected $tables = null;
        protected $models = null;
        protected $models_name = null;
        protected $collection = null;
        protected $new_attributes = null;
        protected $validate_err = null;

        public function __construct(Core_DBRecord $mongo)
        {
                $this->models = $mongo;
                $models_name = get_class($mongo);
                $this->models_name = $models_name;
                $this->collection = $models_name::getCollection();
                $this->tables = $models_name::getCollectionName();
                $this->init();
        }

        protected function init()
        {
                
        }
        
        public function set($attribus, $value)
        {
                $this->attributes[$attribus] = $value;
                $this->models->setAttributes($this);
        }

        /**
         * 插入/更新时，验证的钩子
         * @author Xiao Tangren  <unphp@qq.com>
         * @date 2013-08-19
         * @return type
         */
        public function validate()
        {
                $this->models->beforeValidation($this);
                $data = $this->validateRule();
                if ($data)
                {
                        Pub_Error::mode()->add($data);
                        return false;
                }
                $retval = $this->isValid();
                $this->models->afterValidation($this);
                return $retval;
        }

        public final function getWebService($table = '')
        {
                $rpc = empty($table) ? $this->tables . 'Rpc' : $table . 'Rpc';
                return Core_Webservice::getClient($rpc);
        }

        public final function get_field_value($field_name, $value)
        {
                $fild_data = $this->getWebService()->getTableField($this->tables);
                $type = $fild_data['field'][$field_name]['type'];
                $rs = null;
                switch ($type)
                {
                        case 'int':
                                $rs = intval($value);
                                break;
                        case 'float':
                                $rs = floatval($value);
                                break;
                        case 'string':
                                $rs = strval($value);
                                break;
                        default:
                                $rs = strval($value);
                                break;
                }
                return $rs;
        }
        
        public final function getTransformInsert($arr)
        {
                $fild_data = $this->getWebService()->getTableField($this->tables);
                $rs = array();
                foreach ($arr as $key => $value)
                {
                        $type = $fild_data['field'][$key]['type'];
                        switch ($type)
                        {
                                case 'int':
                                        $rs[$key] = intval($value);
                                        break;
                                case 'float':
                                        $rs[$key] = floatval($value);
                                        break;
                                case 'string':
                                        $rs[$key] = strval($value);
                                        break;
                                default:
                                        $rs[$key] = strval($value);
                                        break;
                        }
                }
                return $rs;
        }

        /**
         * 根据“数据表模型”rules方法提供的规则，验证字段
         * @author Xiao Tangren  <unphp@qq.com>
         * @date 2013-08-19
         * @return type
         */
        protected function validateRule()
        {
                $err = null;
                $rules = $this->models->rules();
                $attributeLabels = $this->models->attributeLabels();
                if ($rules && is_array($rules))
                {
                        $validate = new Core_Validate($this->new_attributes, $attributeLabels);
                        foreach ($rules as $rule)
                        {
                                $methods_name = $rule[1] . 'Validate';
                                $validate->$methods_name($rule);
                        }
                        if ($validate->getErr())
                        {
                                $err = $validate->getErr();
                        }
                }
                return $err;
        }

        /**
         * 其他自定义验证方法
         * 自动执行验证方法，验证方法为：字段名+validates
         * @author Xiao Tangren  <unphp@qq.com>
         * @date 2013-08-19
         * @return boolean
         */
        protected function isValid()
        {
                $className = $this->models_name;
                $methods = get_class_methods($className);
                foreach ($methods as $method)
                {
                        if (substr($method, 0, 9) == 'validates')
                        {
                                $propertyCall = 'get' . substr($method, 9);
                                if (!$className::$method($this->$propertyCall()))
                                {
                                        return false;
                                }
                        }
                }
                return true;
        }

}

?>
