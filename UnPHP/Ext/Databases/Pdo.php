<?php

/**
 * 框架扩展：数据库操作类库
 * Pdo类型（数据库）操作接口类
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author Xiao Tangren  <unphp@qq.com>
 * @data 2014-03-27
 * */
class Ext_Databases_Pdo
{

        private $pool = null;
        private $engineCustom = null;
        private $filed_type = array(
            'int' => 'int',
        );

        public function __construct($pool, $engineType)
        {
                $this->pool = $pool;
                $engine_class = 'Pdo' . ucfirst($engineType);
                $engine_file = __DIR__ . '/' . $engine_class . '.php';
                if (file_exists($engine_file))
                {
                        require $engine_file;
                        $this->engineCustom = new $engine_class($pool);
                }
                else
                {
                        $this->engineCustom = new PdoEngine($pool);
                }
        }

        public function getPk($collectionName)
        {
                $fileds = $this->engineCustom->get_table_filed_type($collectionName);
                return $fileds['auto_key_id'];
        }

        public function findOne($collectionName, $condition = array(), $options = array())
        {
                $bind_params = array();
                $where = $this->_querySql($condition, $bind_params);
                $filed = $this->getFileds($options);
                $order = $this->getOrder($options);
                $limit = $this->getLimit($options);
                $sql = "select " . $filed . " FROM " . $collectionName . $where . $order . $limit;
                $sth = $this->pool->prepare($sql);
                foreach ($bind_params as $key => $v)
                {
                        $value = $v;
                        if (is_array($v))
                        {
                                $value = "(" . implode(",", $v) . ")";
                        }
                        $sth->bindValue($key, $value);
                }
                $sth->execute();
                $rs = $sth->fetch(PDO::FETCH_ASSOC);
                return $rs;
        }

        public function findAll($collectionName, $condition = array(), $options = array())
        {
                $bind_params = array();
                $where = $this->_querySql($condition, $bind_params);
                $filed = $this->getFileds($options);
                $order = $this->getOrder($options);
                $limit = $this->getLimit($options);
                $sql = "select " . $filed . " FROM " . $collectionName . $where . $order . $limit;
                $sth = $this->pool->prepare($sql);
                foreach ($bind_params as $key => $v)
                {
                        $value = $v;
                        if (is_array($v))
                        {
                                $value = "(" . implode(",", $v) . ")";
                        }
                        $sth->bindValue($key, $value);
                }
                $sth->execute();
                $rs = $sth->fetchAll(PDO::FETCH_ASSOC);
                return $rs;
        }

        public function count($collectionName, $condition = array())
        {
                $bind_params = array();
                $where = $this->_querySql($condition, $bind_params);
                $filed = 'count(*) as num';
                $sql = "select " . $filed . " FROM " . $collectionName . $where;
                $sth = $this->pool->prepare($sql);
                foreach ($bind_params as $key => $v)
                {
                        $value = $v;
                        if (is_array($v))
                        {
                                $value = "(" . implode(",", $v) . ")";
                        }
                        $sth->bindValue($key, $value);
                }
                $sth->execute();
                $rs = $sth->fetch(PDO::FETCH_ASSOC);
                return $rs['num'];
        }

        public function insert($collectionName, $new, $options = array())
        {
                if (is_array($new) && !empty($new))
                {
                        $fileds = $this->engineCustom->get_table_filed_type($collectionName);
                        $sth = $this->pool->prepare($this->getInsertSql($collectionName, $fileds));
                        $data = array();
                        $rs = array();
                        foreach ($fileds['field'] as $filed => $v)
                        {
                                if ($fileds['auto_key_id'] == $filed)
                                {
                                        continue;
                                }
                                $data[':' . $filed] = $this->get_field_value($v['type'], isset($new[$filed]) ? $new[$filed] : $v['default']);
                                $rs[$filed] = $this->get_field_value($v['type'], isset($new[$filed]) ? $new[$filed] : $v['default']);
                        }
                        //var_dump($this->getInsertSql($collectionName, $fileds),$data);exit;
                        if ($sth->execute($data))
                        {
                                return $this->pool->lastInsertId();
                        }
                        else
                        {
                                return FALSE;
                        }
                }
                else
                {
                        return FALSE;
                }
        }

        public function batchInsert($collectionName, $new, $options = array())
        {
                $fileds = $this->engineCustom->get_table_filed_type($collectionName);
                $sth = $this->pool->prepare($this->getInsertSql($collectionName, $fileds));
                foreach ($new as $value)
                {
                        $data = array();
                        foreach ($fileds['field'] as $filed => $v)
                        {
                                if ($fileds['auto_key_id'] == $filed)
                                {
                                        continue;
                                }
                                $data[':' . $filed] = $this->get_field_value($v['type'], isset($value[$filed]) ? $value[$filed] : $v['default']);
                        }
                        $sth->execute($data);
                }
        }

        public function remove($collectionName, $condition = array(), $options = array())
        {
                $bind_params = array();
                $where = $this->_querySql($condition, $bind_params);
                $sql = "DELETE FROM " . $collectionName . $where;
                $sth = $this->pool->prepare($sql);
                foreach ($bind_params as $key => $value)
                {
                        $sth->bindValue($key, $value);
                }
                return $sth->execute();
        }

        public function update($collectionName, $condition, $new, $options = array())
        {
                if ($new)
                {
                        $bind_params = array();
                        $where = $this->_querySql($condition, $bind_params);
                        $set = "";
                        $key_str = "set_";
                        $i = 0;
                        foreach ($new as $k => $v)
                        {
                                if ($k == '$set')
                                {
                                        foreach ($v as $k2 => $v2)
                                        {
                                                $temp_key = $key_str . $i++;
                                                $set .= $k2 . "=:" . $temp_key . ",";
                                                $bind_params[$temp_key] = $v2;
                                        }
                                }
                                elseif ($k == '$inc')
                                {
                                        foreach ($v as $k2 => $v2)
                                        {
                                                $set .= $k2 . "=" . $k2 . "+" . $v2 . ",";
                                        }
                                }
                                else
                                {
                                        throw new DatabasesException('The table of ' . $collectionName . ' do update() error --- in the "new" params is unable to identify!');
                                }
                        }
                        $set = substr($set, 0, -1);
                        $sql = "UPDATE " . $collectionName . " SET " . $set . $where;
                        $sth = $this->pool->prepare($sql);
                        foreach ($bind_params as $key => $value)
                        {
                                $sth->bindValue($key, $value);
                        }
                        return $sth->execute();
                }
        }

        private function getFileds($options)
        {
                $fileds = "*";
                if (isset($options['fields']) && is_array($options['fields']))
                {
                        $fileds = "";
                        foreach ($options['fields'] as $key => $value)
                        {
                                if (1 === $value)
                                {
                                        $fileds .= $key . ",";
                                }
                        }
                        $fileds = substr($fileds, 0, -1);
                }
                return $fileds;
        }

        private function getOrder($options)
        {
                $order = "";
                if (isset($options['sort']) && is_array($options['sort']) && !empty($options['sort']))
                {
                        $order = " Order by ";
                        foreach ($options['sort'] as $key => $value)
                        {
                                if ($value == 1)
                                {
                                        $order .= $key . " ASC,";
                                }
                                else
                                {
                                        $order .= $key . " DESC,";
                                }
                        }
                        $order = substr($order, 0, -1) . " ";
                }
                return $order;
        }

        private function getLimit($options)
        {
                $limits = "";
                if (isset($options['offset']) || isset($options['limit']))
                {
                        $limits = " Limit ";
                        if (isset($options['offset']))
                        {
                                $limits .= intval($options['offset']) . " ";
                        }
                        else
                        {
                                $limits .= " 0 ";
                        }
                        if (isset($options['limit']) && $options['limit'] > 0)
                        {
                                $limits .= "," . intval($options['limit']) . " ";
                        }
                }
                return $limits;
        }

        private function _querySql($condition = array(), &$bind_params = array())
        {
                try
                {
                        $sql = "";
                        $where = "";
                        $key_str = "where_";
                        $i = 0;
                        if ($condition)
                        {
                                foreach ($condition as $k => $v)
                                {
                                        if (is_array($v))
                                        {
                                                foreach ($v as $k2 => $v2)
                                                {
                                                        if ($k2 === '$in')
                                                        {
                                                                if ($v2 && is_array($v2))
                                                                {
                                                                        $temp_key = $key_str . $i++;
                                                                        $where .= $k . ' in (';
                                                                        foreach ($v2 as $v_in)
                                                                        {
                                                                                $temp_key = $key_str . $i++;
                                                                                $bind_params[$temp_key] = $v_in;
                                                                                $where .= ':' . $temp_key . ',';
                                                                        }
                                                                        $where = substr($where, 0, -1) . ") AND ";
                                                                }
                                                                else
                                                                {
                                                                        throw new Exception('$in=>array() must not empty!');
                                                                }
                                                        }
                                                        $sign = $this->sign_to_sql($k2);
                                                        if ($sign)
                                                        {
                                                                $temp_key = $key_str . $i++;
                                                                $where .= $k . $sign . ':' . $temp_key . ' AND ';
                                                                $bind_params[$temp_key] = $v2;
                                                        }
                                                }
                                        }
                                        else
                                        {
                                                $temp_key = $key_str . $i++;
                                                $where .= $k . '=:' . $temp_key . ' AND ';
                                                $bind_params[$temp_key] = $v;
                                        }
                                }
                                return ' WHERE ' . substr($where, 0, -4);
                        }
                        else
                        {
                                return $where;
                        }
                }
                catch (Exception $exc)
                {
                        echo $exc->getTraceAsString();
                }
        }

        private function _updateSet($new, &$set = array())
        {
                
        }

        private function sign_to_sql($str)
        {
                $rs = '';
                switch ($str)
                {
                        case '$gt':
                                $rs = '>';
                                break;
                        case '$lt':
                                $rs = '<';
                                break;
                        case '$gt':
                                $rs = '>';
                                break;
                        case '$gte':
                                $rs = '>=';
                                break;
                        case '$lte':
                                $rs = '<=';
                                break;
                        case '$ne':
                                $rs = '<>';
                                break;
                }
                if (empty($rs))
                        return false;
                return $rs;
        }

        private function get_field_value($type, $value)
        {
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

        private function getInsertSql($collectionName, $fileds)
        {
                $insert_files = "(";
                $insert_values = "(";
                foreach ($fileds['field'] as $k => $v)
                {
                        if ($fileds['auto_key_id'] == $k)
                        {
                                continue;
                        }
                        $insert_files .= $k . ',';
                        $insert_values .= ':' . $k . ',';
                }
                $insert_files = substr($insert_files, 0, -1) . ') ';
                $insert_values = substr($insert_values, 0, -1) . ')';
                $sql = "INSERT INTO `{$collectionName}` " . $insert_files . 'value ' . $insert_values;
                return $sql;
        }

}

class PdoEngine
{
        
}
