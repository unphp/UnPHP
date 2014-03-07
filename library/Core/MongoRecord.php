<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * MongoDB数据表模型基类
 * 改造自：https://github.com/lunaru/mongorecord
 * 说明：本类基于“多主一从模式”
 * 即多个“写入操作”数据库，一个“读取操作”数据库。
 * 本类拟定“读取操作”数据库为MongoDB，“写入操作”数据库不限制（如可以通过webservice写入其他类型的数据库）
 * 
 * “策略模式”将多主一丛（多写一读）的读写进行简单分离
 * 
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 *
 */
abstract class Core_MongoRecord extends Core_DBRecord
{

    public static $findTimeout = 20000;        // mongoDB超时时间

    /**
     * 创建索引
     * @param array $keys
     * @param array $options
     * @return type
     */

    public function ensureIndex(array $keys, array $options = array())
    {
        return self::getCollection()->ensureIndex($keys, $options);
    }

    /**
     * 删除索引
     * @param type $keys
     * @return boolean
     * @throws Exception
     */
    public function deleteIndex($keys)
    {
        return self::getCollection()->deleteIndex($keys);
    }

    /**
     * 返回所有满足条件的表集合
     * @param type $condition
     * @param type $options
     * @return type
     */
    public static function findAll($condition = array(), $options = array())
    {

        $documents = static::_find($condition, $options);
        $ret = array();
        while ($documents->hasNext())
        {
            $document = $documents->getNext();
            $ret[] = self::instantiate($document);
        }
        return $ret;
    }

    public static function getAll($condition = array(), $options = array())
    {
        $documents = static::_find($condition, $options);
        $ret = array();
        while ($documents->hasNext())
        {
            $document = $documents->getNext();
            $ret[] = self::instantiate($document)->getAttributes();
        }
        return $ret;
    }

    /**
     * 返回一个原生的MongoCollection::find句柄资源
     * @param type $condition
     * @param type $options
     * @return Core_MongoRecordIterator
     */
    public static function find($condition = array(), $options = array())
    {
        $documents = static::_find($condition, $options);
        $className = get_called_class();
        return new Core_MongoRecordIterator($documents, $className);
    }

    /**
     * 返回满足条件的表集合的第一条记录对象
     * @param type $condition
     * @param array $options
     * @return object|null
     */
    public static function findOne($condition = array(), $options = array())
    {
        $collection = self::getCollection();
        $fields = isset($options['fields']) ? $options['fields'] : array();
        $rs = $collection->findOne($condition, $fields);
        if ($rs)
        {
            $className = get_called_class();
            return new $className($rs, false);
        }
        else
            return null;
    }

    /**
     * 返回满足条件的表集合的第一条记录数组
     * @param type $condition
     * @param type $options
     * @return array
     */
    public static function getOne($condition = array(), $options = array())
    {
        $collection = self::getCollection();
        $fields = isset($options['fields']) ? $options['fields'] : array();
        return $collection->findOne($condition, $fields);
    }

    /**
     * 返回满足条件的表集合的总条数
     * @param type $condition
     * @return int
     */
    public static function count($condition = array())
    {
        $collection = self::getCollection();
        $documents = $collection->count($condition);
        return $documents;
    }

    /**
     * 超时时间
     * @param type $timeout
     */
    public static function setFindTimeout($timeout)
    {
        $className = get_called_class();
        $className::$findTimeout = $timeout;
    }

    /**
     * 获取表集合链接
     * @author xiaotangren  <unphp@qq.com>
     * @author luhui  <luhui@aukeys.com>
     * @return type
     * @throws Exception
     */
    public static function getCollection()
    {
        $className = get_called_class();
        if (null == static::$collectionName)
        {
            throw new Exception($className . "::collectionName must be initialized to a proper tables string");
        }
        if (null === static::$adapter)
        {
            $dblist = Pub_Init::$common_ini->databases;
            $tablelist = Pub_Init::$common_ini->tables;
            static::$adapter = new Core_Adapter($dblist, $tablelist);
        }
        return static::$adapter->getConnection(static::$collectionName);
    }

    public static function creatTable($name)
    {
        self::$database->createCollection($name);
    }

    /**
     * 获取表集合名称
     * @return string
     */
    public static function getCollectionName()
    {
        return static::$collectionName;
    }

    //---------------------------------------------------------------------------//
    // 以下为私有（包括严格私有）的方法
    //---------------------------------------------------------------------------//

    /**
     * MongoDB查询返回的对象
     * @author xiaotangren  <unphp@qq.com>
     * @param type $condition
     * @param type $options
     * @return type
     */
    private static function _find($condition = array(), $options = array())
    {
        $collection = self::getCollection();
        if (isset($options['fields']))
        {
            $documents = $collection->find($condition, $options['fields']);
        }
        else
        {
            $documents = $collection->find($condition);
        }
        $className = get_called_class();
        if (isset($options['sort']))
        {
            $documents->sort($options['sort']);
        }

        if (isset($options['offset']))
        {
            $documents->skip($options['offset']);
        }

        if (isset($options['limit']))
        {
            $documents->limit($options['limit']);
        }
        $documents->timeout($className::$findTimeout);
        return $documents;
    }

    /**
     * 返回数据表模型集合---用于查询后返回的表记录集合
     * （注意集合都为单条记录集合）
     * @author xiaotangren  <unphp@qq.com>
     * @param type $document
     * @return \className|null
     */
    private static function instantiate($document)
    {
        if ($document)
        {
            $className = get_called_class();
            return new $className($document, false);
        }
        else
        {
            return null;
        }
    }

}

?>
