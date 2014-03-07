<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MysqlRecord
 *
 * @author xiao
 */
class Core_MysqlRecord extends Core_DBRecord
{
    
    /**
     * 获取表集合链接
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

    /**
     * 获取表集合链接
     * @return type
     * @throws Exception
     */
    public static function getCollectionName()
    {
        return static::$collectionName;
    }

    /**
     * 取得满足条件的记录总条数
     * @param type $condition
     * @return type
     */
    public static function count($condition = array())
    {
        $dbh = static::getCollection();
        $sql = "";
        // 条件
        $condition = self::setCondition($condition);
        $where = $condition[0];
        $params = $condition[1];
        $sql .= 'SELECT count(*) as total FROM ' . static::getCollectionName() . ' ' . $where;
        $sth = $dbh->prepare($sql);
        $sth->execute($params);
        $rs = $sth->fetch();
        return empty($rs) ? 0 : $rs['total'];
    }

    public static function findAll($condition = array(), $options = array())
    {
        $records = self::getAll($condition, $options);
        $ret = array();
        if ($records)
        {
            foreach ($records as $value)
            {
                $ret[] = self::instantiate($value);
            }
        }
        return $ret;
    }

    /**
     * 取得满足条件的记录总数
     * @param type $condition
     * @param type $options
     * @return type
     */
    public static function getAll($condition = array(), $options = array())
    {
        $dbh = static::getCollection();
        //----------------------------------------------------
        // 条件
        $condition = self::setCondition($condition);
        $where = $condition[0];
        $params = $condition[1];
        //----------------------------------------------------
        // 获取字段
        $fields = isset($options['fields']) ? implode(',', array_keys($options['fields'])) : '*';
        //----------------------------------------------------
        // 排序
        $order = self::setSort($options['sort']);
        //----------------------------------------------------
        // 条数
        $limit = self::setLimit($options['limit'],$options['offset']);
        //----------------------------------------------------
        $sql = "";
        $sql .= 'SELECT ' . $fields . ' FROM ' . static::getCollectionName() . ' ' . $where . $order . $limit;
        $sth = $dbh->prepare($sql);
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        $sth->execute($params);
        $result = $sth->fetchAll();
        return $result;
    }

    /**
     * 取得一条记录
     * @param type $condition
     * @param type $options
     * @return \className|null
     */
    public static function findOne($condition = array(), $options = array())
    {
        $dbh = static::getCollection();
        $table_fileds = self::getTableFiledType();
        //var_dump($table_fileds);exit;
        //----------------------------------------------------
        // 条件
        $condition = self::setCondition($condition);
        $where = $condition[0];
        $params = $condition[1];
        //----------------------------------------------------
        // 获取字段
        $fields = isset($options['fields']) ? implode(',', array_keys($options['fields'])) : '*';
        //----------------------------------------------------
        // 排序
        $order = self::setSort($options['sort']);
        //----------------------------------------------------
        // 条数
        $limit = self::setLimit($options['limit'],$options['offset']);
        //----------------------------------------------------
        $sql = "";
        $sql .= 'SELECT ' . $fields . ' FROM ' . static::getCollectionName() . ' ' . $where . $order . $limit;
        $sth = $dbh->prepare($sql);
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        $sth->execute($params);
        $result = $sth->fetch();
        if ($result)
        {
            $className = get_called_class();
            return new $className($result, false);
        }
        return null;
    }

    /**
     * 取得一条记录
     * @param type $condition
     * @param type $options
     * @return \className|null
     */
    public static function getOne($condition = array(), $options = array())
    {
        $dbh = static::getCollection();
        //----------------------------------------------------
        // 条件
        $condition = self::setCondition($condition);
        $where = $condition[0];
        $params = $condition[1];
        //----------------------------------------------------
        // 获取字段
        $fields = isset($options['fields']) ? implode(',', array_keys($options['fields'])) : '*';
        //----------------------------------------------------
        // 排序
        $order = self::setSort($options['sort']);
        //----------------------------------------------------
        // 条数
        $limit = self::setLimit($options['limit'],$options['offset']);
        //----------------------------------------------------
        $sql = "";
        $sql .= 'SELECT ' . $fields . ' FROM ' . static::getCollectionName() . ' ' . $where . $order . $limit;
        $sth = $dbh->prepare($sql);
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        $sth->execute($params);
        $result = $sth->fetch();
        return $result;
    }

    public static function setFindTimeout($timeout)
    {
        
    }

    public function getTableFiledType()
    {
        $table_name = static::getCollectionName();
        $cache_name = 'table_filed_datatype_' . $table_name;
        $data = Pub_Cache::get_temp_cache('tables_filed', $cache_name);
        if (empty($data))
        {
            $dbh = static::getCollection();
            $data = array();
            $show_table_sql = "SHOW COLUMNS FROM {$table_name}";
            $sth = $dbh->prepare($show_table_sql);
            $sth->execute();
            $rs = $sth->fetchAll();
            foreach ($rs as $v => $k)
            {
                $field = $k['Field'];
                $type = self::str_to_type($k['Type']);
                $data['field'][$field]['type'] = $type['type'];
                $data['field'][$field]['length'] = $type['length'];
                $data['field'][$field]['null'] = $k['Null'];
                $data['field'][$field]['key'] = $k['Key'];
                $data['field'][$field]['default'] = $k['Default'];
                $data['field'][$field]['extra'] = $k['Extra'];
                if ($k['Extra'] == "auto_increment")
                {
                    $data['auto_key_id'] = $field;
                }
            }
            Pub_Cache::set_temp_cache('tables_filed', $cache_name, $data);
            //Yii::app()->filecache->add($cache_name,$data,18600);
        }
        return $data;
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
    
    // 条件
    private static function setCondition($condition){
        $where = '';
        $params = array();
        if ($condition)
        {
            foreach ($condition as $key => $value)
            {
                $where .= "AND {$key} =:{$key} ";
                $params[':' . $key] = $value;
            }
            $where = 'WHERE' . substr($where, 3);
        }
        return array($where,$params);
    }
    
    // 排序
    private static function setSort($sort){
        $order = '';
        if (isset($sort))
        {
            $order .= 'ORDER BY ';
            foreach ($sort as $k => $v)
            {
                $v = $v == 1 ? ' ASC ' : ' DESC ';
                $order .= $k . $v;
            }
        }
        return $order;
    }
    
    private static function setLimit($limit,$offset){
        $limit_sql = '';
        if (isset($limit))
        {
            $offset = isset($offset) ? ',' . $offset : '';
            $limit_sql .= 'LIMIT ' . $limit . $offset;
        }
        else
        {
            $limit_sql .= isset($offset) ? 'LIMIT 0,' . $offset : '';
        }
        return $limit_sql;
    }

    protected static function str_to_type($str){
                $rs = array();
                if(preg_match('/int\(([\d]+)\).*/ie', $str,$arr)){
                        $rs['length'] = intval($arr[1]);
                        $rs['type'] = 'int';
                        return $rs;
                }
                if(preg_match('/bigint\(([\d]+)\).*/ie', $str,$arr)){
                        $rs['length'] = intval($arr[1]);
                        $rs['type'] = 'int';
                        return $rs;
                }
                if(preg_match('/decimal\(10\,([\d])\).*/ie', $str,$arr)){
                        $rs['length'] = intval($arr[1]);
                        $rs['type'] = 'float';
                        return $rs;
                }
                if(preg_match('/tinyint\(([\d]+)\).*/ie', $str,$arr)){
                        $rs['length'] = intval($arr[1]);
                        $rs['type'] = 'int';
                        return $rs;
                }
                if(preg_match('/smallint\(([\d]+)\).*/ie', $str,$arr)){
                        $rs['length'] = intval($arr[1]);
                        $rs['type'] = 'int';
                        return $rs;
                }
                if (preg_match('/mediumint\(([\d]+)\).*/ie', $str, $arr))
                {
                        $rs['length'] = intval($arr[1]);
                        $rs['type'] = 'int';
                        return $rs;
                }
                $rs['length'] = 0;
                $rs['type'] = 'string';
                return $rs;
        }
    
}
