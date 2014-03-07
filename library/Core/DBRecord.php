<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DBRecord
 *
 * @author xiao
 */
abstract class Core_DBRecord implements DBWrite
{

    public $attributes = array();
    
    public $primary = null;
    
    // “增删改”操作的类型，目前分“Mongo”和“Mysql”两种，以后可扩展，“多写一丛”模式。
    public $db_write_pool = 'Mysql';
    // 写入操作类实例对象
    
    protected $_db_write_obj = null;
    // 引入系统的错误类对象
    protected $_errors;
    
    // 数据库链接句柄实例
    public static $adapter = null;
    
    // MySQL数据表和MongoDB集合的名称
    protected static $collectionName = null;   

    /**
     * 初始化时，带入了attributes，此时为true
     * true---新创建的带有attributes对象，此时调用save()方法，是插入新的数据
     * false---查询返回的集合对象，此时调用save()方法，是更新此记录。
     * @var boole 
     */
    private $_is_new;

    /**
     * 创建新的记录集合，或查询返回的记录集合---构造方法
     * @param array $attributes
     * @param type $is_new
     */
    public function __construct($attributes = array(), $is_new = true, $cud_type = null)
    {
        $this->_is_new = $is_new;
        $this->attributes = $attributes;
        $this->_errors = array();
        /* 简易的“工厂模式”，实例不同的写入操作类 */
        if (!empty($cud_type))
            $this->db_write_pool = $cud_type;
    }

    /**
     * 当没有定义cud属性时，说明为写入MySQL方式。
     * @param type $new
     */
    public final function setDBWritePool()
    {
        if (null === $this->_db_write_obj)
        {
            $modewr = 'Core_' . $this->db_write_pool . 'Wr';
            $this->_db_write_obj = new $modewr($this);
        }
    }

    public function save(array $attributes, array $options = array())
    {
        if (FALSE === $this->_is_new)
        {
            $this->update($this->attributes, $attributes, $options);
        }
        else
        {
            $this->insert($attributes, $options);
        }
    }

    public function update(array $condition, array $new, array $options = array())
    {
        $this->setDBWritePool();
        return $this->_db_write_obj->update($condition, $new, $options);
    }

    public function insert(array $new = array(), array $options = array())
    {
        $this->setDBWritePool();
        return $this->_db_write_obj->insert($new, $options);
    }

    public function remove(array $condition, array $options = array())
    {
        $this->setDBWritePool();
        return $this->_db_write_obj->remove($condition, $options);
    }

    public function getID()
    {
        return $this->attributes['_id'];
    }

    /**
     * 获取记录集
     * @return type
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * 供写入类调用
     * @param type $modelsWr
     */
    public function setAttributes($modelsWr)
    {
        $this->attributes = $modelsWr->getAttributes();
    }

    //-----------------------------------------------------------------------------//
    // 以下为各流程节点上，预留的“钩子”---以便“子类”需要自定义扩展
    //-----------------------------------------------------------------------------//
    public function afterNew()
    {
        
    }

    public function beforeSave($cud_obj)
    {
        
    }

    // $cud_obj 为写操作类实例对象
    public function afterSave($cud_obj)
    {
        
    }

    // $cud_obj 为写操作类实例对象
    public function beforeValidation($cud_obj)
    {
        
    }

    // $cud_obj 为写操作类实例对象
    public function afterValidation($cud_obj)
    {
        
    }

    // $cud_obj 为写操作类实例对象
    public function beforeDestroy($cud_obj)
    {
        
    }

    /**
     * 快速创建表模型的写操作类对象
     * @param type $attributes
     * @return \className
     */
    public static function mode($attributes = array(), $cud_type = null)
    {
        $className = get_called_class();
        $mode = new $className($attributes, true, $cud_type);
        //$mode->getCUD(true);
        return $mode;
    }
    
    /**
         * 插入/更新时，字段验证规则
         * @return type
         */
        public function rules(){
                return array();
        }
        
        /**
         * 插入/更新时，字段默认值
         * @return type
         */
        public function fieldsValues(){
                return array();
        }
        

    /**
         * 
         * 数据表字段（翻译的名称）
         * @author xiaotangren  <unphp@qq.com>
         * @data 2013-03-18
         * @return array 例如array('字段名'=>'字段代表意思')
         */
    public function attributeLabels(){
            
    }
    
    /**
     * 获取表集合链接
     * @author xiaotangren  <unphp@qq.com>
     * @author luhui  <luhui@aukeys.com>
     * @return type
     * @throws Exception
     */
    abstract public static function getCollection();
    
    abstract public static function getCollectionName();
    
    abstract public static function count($condition);
    
    abstract public static function findAll($condition, $option);

    abstract public static function findOne($condition, $option);

    abstract public static function setFindTimeout($timeout);

}

/**
 * 接口：写入操作类接口
 * @author xiaotangren  <unphp@qq.com>
 */
interface DBWrite
{

    public function save(array $condition, array $options = array());

    public function update(array $condition, array $new, array $options = array());

    public function insert(array $new = array(), array $options = array());

    public function remove(array $condition, array $options = array());
}
