<?php

/**
 * 框架扩展：数据库操作类库
 * 数据表模型
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author Xiao Tangren  <unphp@qq.com>
 * @data 2014-03-27
 * */
class Ext_Databases_Model
{

        protected $_tableName = null;
        protected $_attributes = array();
        protected $_new = array();
        protected $_isNew = TRUE;
        protected static $poolConf = array();
        protected $_error = null;
        protected $_validation = array();
        protected $_validaObj = null;
        protected $_pk = null;

        const ACTION_READ = 1;
        const ACTION_WRITE = 2;
        const ACTION_BOTH = 0;

        protected static function getReadEngine($tableName)
        {
                if (isset(static::$poolConf[$tableName][static::ACTION_WRITE]))
                {
                        $conf = static::$poolConf[$tableName][static::ACTION_READ];
                        return Ext_Databases_Pool::getWritePool($tableName, $conf[0], $conf[1], $conf[2]);
                }
                throw new DatabasesException('The table of "' . $tableName . '" not config for Read Databases of Engine!');
        }

        protected static function getWriteEngine($tableName)
        {
                if (isset(static::$poolConf[$tableName][static::ACTION_WRITE]))
                {
                        $conf = static::$poolConf[$tableName][static::ACTION_WRITE];
                        return Ext_Databases_Pool::getWritePool($tableName, $conf[0], $conf[1], $conf[2]);
                }

                throw new DatabasesException('The table of "' . $tableName . '" not config for Write Databases of Engine!');
        }

        public static function regionPool($table, $dsn, $user, $password, $action = self::ACTION_BOTH)
        {
                if (!isset(static::$poolConf[$table]))
                {
                        switch ($action)
                        {
                                case static::ACTION_READ:
                                        $temp = array();
                                        $temp[] = $dsn;
                                        $temp[] = $user;
                                        $temp[] = $password;
                                        static::$poolConf[$table][self::ACTION_READ] = $temp;
                                        break;
                                case static::ACTION_WRITE:
                                        $temp = array();
                                        $temp[] = $dsn;
                                        $temp[] = $user;
                                        $temp[] = $password;
                                        static::$poolConf[$table][self::ACTION_WRITE] = $temp;
                                        break;
                                case self::ACTION_BOTH:
                                default:
                                        $temp = array();
                                        $temp[] = $dsn;
                                        $temp[] = $user;
                                        $temp[] = $password;
                                        static::$poolConf[$table][self::ACTION_READ] = $temp;
                                        static::$poolConf[$table][self::ACTION_WRITE] = $temp;
                                        break;
                        }
                }
        }

        /**
         * 
         * @param type $attributes
         * @param type $isNew   该参数是针对 save（）的：当用findOne或findAll时，返回了查询结果并实例来对象本身，此时这个参数为False。
         *                       即，当save操作受到isNew值影响：isNew为TRUE时，save实际是insert；isNew为False时，save实际是update。
         */
        public function __construct($attributes = array(), $isNew = TRUE)
        {
                $this->_attributes = $attributes;
                $this->_isNew = $isNew;
                $this->_error = array();
                $this->_validaObj = new Ext_Databases_Validation($this);
        }

        public function getTableName()
        {
                return $this->_tableName;
        }

        public function fileds()
        {
                return array();
        }

        public function rules()
        {
                return array(
                );
        }

        public function validation($data)
        {
                $rs = true;
                $rules = $this->rules();
                if (!empty($rules))
                {
                        foreach ($rules as $rule)
                        {
                                if (is_array($rule) && count($rule) > 1)
                                {
                                        if (isset($this->_validation[$rule[1]]))
                                        {
                                                $valida = $this->_validation[$rule[1]]($data, $rule);
                                                if (true !== $valida)
                                                {
                                                        $this->_error[] = $valida;
                                                        $rs = false;
                                                }
                                        }
                                        else
                                        {
                                                $valida = $this->_validaObj->$rule[1]($data, $rule);
                                                if (true !== $valida)
                                                {
                                                        $rs = false;
                                                }
                                        }
                                }
                        }
                }
                return $rs;
        }

        public function addError($err)
        {
                $this->_error[] = $err;
        }

        public function getError()
        {
                return $this->_error;
        }

        public function getPk()
        {
                return static::getWriteEngine($this->_tableName)->conn()->getPk($this->_tableName);
        }

        public function setAttributes($attributes)
        {
                if ($attributes && is_array($attributes))
                {
                        foreach ($attributes as $key => $value)
                        {
                                $this->setAttribute($key, $value);
                        }
                }
        }

        public function setAttribute($key, $value, $type = null)
        {
                if ($this->_isNew)
                {
                        $this->_attributes[$key] = $value;
                }
                else
                {
                        if ($type == 'inc')
                        {
                                $this->_new['$inc'][$key] = $value;
                        }
                        else
                        {
                                $this->_new['$set'][$key] = $value;
                        }
                }
        }

        public function getAttributes($key = null)
        {
                if ($key)
                {
                        return $this->_attributes[$key];
                }
                return $this->_attributes;
        }

        public function findOne($condition = array(), $options = array())
        {
                $data = static::getReadEngine($this->_tableName)->conn()->findOne($this->_tableName, $condition, $options);
                if ($data)
                {
                        $this->_attributes = $data;
                        $this->_isNew = $this->_attributes ? FALSE : TRUE;
                        return $this;
                }
                else
                {
                        return null;
                }
        }

        public function findAll($condition = array(), $options = array())
        {
                $rs = array();
                $data = static::getReadEngine($this->_tableName)->conn()->findAll($this->_tableName, $condition, $options);
                if ($data)
                {
                        $className = get_called_class();

                        foreach ($data as $value)
                        {
                                $rs[] = new $className($value, FALSE);
                        }
                }
                return $rs;
        }

        public function count($condition = array())
        {
                return static::getReadEngine($this->_tableName)->conn()->count($this->_tableName, $condition);
        }

        public function remove($condition, $options = array())
        {
                return static::getWriteEngine($this->_tableName)->conn()->remove($this->_tableName, $condition, $options);
        }

        public function insert($new, $options = array())
        {
                $data = false;
                if ($this->validation($new))
                {
                        $this->_pk = static::getWriteEngine($this->_tableName)->conn()->insert($this->_tableName, $new, $options);
                        if ($this->_pk)
                        {
                                $condition = array();
                                $condition[$this->getPk()] = $this->_pk;
                                $this->_attributes = static::getReadEngine($this->_tableName)->conn()->findOne($this->_tableName, $condition, $options);
                                $this->_isNew = $this->_attributes ? FALSE : TRUE;
                                return $this;
                        }
                }
                return $data;
        }

        public function batchInsert($new, $options = array())
        {
                return static::getWriteEngine($this->_tableName)->conn()->batchInsert($this->_tableName, $new, $options);
        }

        public function update($condition, $new, $options = array())
        {
                return static::getWriteEngine($this->_tableName)->conn()->update($this->_tableName, $condition, $new, $options);
        }

        public function save()
        {
                if ($this->_attributes)
                {
                        if ($this->_isNew)
                        {
                                echo 'sss';
                                exit;
                                return $this->insert($this->_attributes);
                        }
                        else
                        {

                                $condition = array();
                                $condition[$this->getPk()] = $this->_attributes[$this->getPk()];
                                return $this->update($condition, $this->_new);
                        }
                }
        }

}
