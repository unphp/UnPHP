<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * “增删改”操作
 * 数据库“写入”操作类---WebService(MySQL)写入操作类
 *
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Core_MysqlWr extends Core_DBWr
{

        protected function init()
        {
                parent::init();
        }

        
        public function lastInsertId(){
                return $this->collection->lastInsertId();
        }


        /**
         * 数据表“更新”操作接口---WebService
         * 
         * @author xiaotangren  <unphp@qq.com>
         * @param array $condition
         * @param array $new
         * @param array $options
         * @return type
         * @throws Exception
         */
        public function update(array $condition, array $new, array $options = array())
        {
                try
                {
                        $sql = "";
                        $where = "";
                        $set = "";
                        $key_str = "mongo_";
                        $i = 0;
                        $bind_params = array();
                        if ($condition)
                        {
                                $condition = array_merge($this->getAttributes(), $condition);
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
                                                                        $where .= $k . " in:" . $temp_key . " AND ";
                                                                        foreach ($v2 as $v_in)
                                                                        {
                                                                                $bind_params[$temp_key][] = $v_in;
                                                                        }
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
                                                                $where .= $k . $sign . ":" . $temp_key . " AND ";
                                                                $bind_params[$temp_key] = $v2;
                                                        }
                                                }
                                        }
                                        else
                                        {
                                                $temp_key = $key_str . $i++;
                                                $where .= $k . "=:" . $temp_key . " AND ";
                                                $bind_params[$temp_key] = $v;
                                        }
                                }
                        }
                        else
                        {
                                throw new Exception($this->models_name . '::update() the first params must array and not empty!');
                        }
                        if ($new)
                        {
                                foreach ($new as $k => $v)
                                {
                                        if ($k == '$set')
                                        {
                                                /* 验证各字段 */
                                                $this->new_attributes = $v;
                                                if (false == $this->validate())
                                                {
                                                        return false;
                                                }
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
                                                        //$temp_key = $key_str . $i++;
                                                        $set .= $k2 . "=" . $k2 . "+" . $v2 . ",";
                                                        //$bind_params[$temp_key] = $k2 . "+" . $v2;
                                                }
                                        }
                                        else
                                        {
                                                throw new Exception($this->models_name . '::update() the second params is error!');
                                        }
                                }
                        }
                        else
                        {
                                throw new Exception($this->models_name . '::update() the second params must array and not empty!');
                        }
                        $where = substr($where, 0, -4);
                        $set = substr($set, 0, -1);
                        $sql = "UPDATE " . $this->tables . " SET " . $set . " WHERE " . $where;
                        //var_dump($sql);exit;
                        //return $this->getWebService()->execute($sql, $bind_params,$options);
                        $sth = $this->collection->prepare($sql);
                        $sth->execute($bind_params);
                        return $sth->rowCount();
                }
                catch (Exception $exc)
                {
                        echo $exc->getMessage();
                }
        }

        /**
         * 数据表“删除”操作接口---WebService
         * 
         * @author xiaotangren  <unphp@qq.com>
         * @param array $condition
         * @param array $options
         * @return type
         * @throws Exception
         */
        public function remove(array $condition, array $options = array())
        {
                try
                {
                        $sql = "";
                        $where = "";
                        $key_str = "mongo_";
                        $i = 0;
                        $bind_params = array();
                        if ($condition)
                        {
                                $condition = array_merge($this->getAttributes(), $condition);
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
                                                                        $where .= $k . " in:" . $temp_key . " AND ";
                                                                        foreach ($v2 as $v_in)
                                                                        {
                                                                                $bind_params[$temp_key][] = $v_in;
                                                                        }
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
                                                                $where .= $k . $sign . ":" . $temp_key . " AND ";
                                                                $bind_params[$temp_key] = $v2;
                                                        }
                                                }
                                        }
                                        else
                                        {
                                                $temp_key = $key_str . $i++;
                                                $where .= $k . "=:" . $temp_key . " AND ";
                                                $bind_params[$temp_key] = $v;
                                        }
                                }
                        }
                        else
                        {
                                throw new Exception($this->models_name . '::remove() the first params must array and not empty!');
                        }
                        $where = substr($where, 0, -4);
                        $sql = "DELETE FROM " . $this->tables . " WHERE " . $where;
                        //return $this->getWebService()->execute($sql, $bind_params,$options);
                        $sth = $this->collection->prepare($sql);
                        $sth->execute($bind_params);
                        return $sth->rowCount();
                }
                catch (Exception $exc)
                {
                        echo $exc->getMessage();
                }
        }

        /**
         * 数据表“插入”操作接口---WebService
         * 
         * @author xiaotangren  <unphp@qq.com>
         * @param array $new
         * @param array $options
         * @return type
         * @throws Exception
         */
        public function insert(array $new = array(), array $options = array())
        {
                try
                {
                        /* 验证各字段 */
                        $this->new_attributes = $new;
                        if (false == $this->validate())
                        {
                                return false;
                        }
                        $key_str = "mongo_";
                        $i = 0;
                        $insert_field = "";
                        $bind_params = array();
                        $attributes = array_merge($this->models->getAttributes(), $new);
                        $mongo_filed = $this->models->fieldsValues();
                        $mysql_field_data = $this->models->getTableFiledType();
                        //var_dump($mysql_field_data);exit;
                        $must_field = $mysql_field_data['field'];
                        $auto_key_id = $mysql_field_data['auto_key_id'];
                        foreach ($must_field as $k => $v)
                        {
                                if ($k === $auto_key_id)
                                        continue;
                                if (!isset($attributes[$k]))
                                {
                                        if ($mongo_filed && is_array($mongo_filed))
                                        {
                                                if (isset($mongo_filed[$k]))
                                                {
                                                        $temp_key = $key_str . $i++;
                                                        $insert_field .= $k . ',';
                                                        $insert_value .= ':' . $temp_key . ',';
                                                        $bind_params[$temp_key] = $mongo_filed[$k];
                                                }
                                                else
                                                {
                                                        $temp_key = $key_str . $i++;
                                                        $insert_field .= $k . ',';
                                                        $insert_value .= ':' . $temp_key . ',';
                                                        $bind_params[$temp_key] = $this->get_field_value($k, $v['default']);
                                                }
                                        }
                                }
                                else
                                {
                                        $temp_key = $key_str . $i++;
                                        $insert_field .= $k . ',';
                                        $insert_value .= ':' . $temp_key . ',';
                                        $bind_params[$temp_key] = $attributes[$k];
                                }
                        }
                        $insert_field = substr($insert_field, 0, -1);
                        $insert_value = substr($insert_value, 0, -1);
                        $sql = 'INSERT INTO ' . $this->tables . ' (' . $insert_field . ') VALUES (' . $insert_value . ')';
                        //$rs = $this->getWebService()->execute($sql, $bind_params, $options);
                        //var_dump($rs);exit;
                        //var_dump($bind_params);exit;
                        //return $rs;
                        $sth = $this->collection->prepare($sql);
                        return $sth->execute($bind_params);
                }
                catch (Exception $exc)
                {
                        echo $exc->getMessage();
                }
        }

        public function query(array $condition, array $options = array())
        {
                try
                {
                        $sql = "";
                        $where = "";
                        $key_str = "mongo_";
                        $i = 0;
                        $bind_params = array();
                        if ($condition)
                        {
                                $condition = array_merge($this->getAttributes(), $condition);
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
                                                                        $where .= $k . " in:" . $temp_key . " AND ";
                                                                        foreach ($v2 as $v_in)
                                                                        {
                                                                                $bind_params[$temp_key][] = $v_in;
                                                                        }
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
                                                                $where .= $k . $sign . ":" . $temp_key . " AND ";
                                                                $bind_params[$temp_key] = $v2;
                                                        }
                                                }
                                        }
                                        else
                                        {
                                                $temp_key = $key_str . $i++;
                                                $where .= $k . "=:" . $temp_key . " AND ";
                                                $bind_params[$temp_key] = $v;
                                        }
                                }
                        }
                        else
                        {
                                throw new Exception($this->models_name . '::remove() the first params must array and not empty!');
                        }
                        $where = substr($where, 0, -4);
                        $type = isset($options['type']) ? $options['type'] : 'one';
                        if ($type === 'count')
                        {
                                $filed = 'count(*) as num';
                                $sql = "select " . $filed . " FROM " . $this->tables . " WHERE " . $where;
                                $rs = $this->getWebService()->query($sql, $bind_params, 'one');
                                $rs = $rs['num'];
                        }
                        else
                        {
                                $filed = isset($options['filed']) && is_array($options['filed']) ? implode(',', $options['filed']) : '*';
                                $sql = "select " . $filed . " FROM " . $this->tables . " WHERE " . $where;
                                $rs = $this->getWebService()->query($sql, $bind_params, $type);
                        }
                        return $rs;
                }
                catch (Exception $exc)
                {
                        echo $exc->getMessage();
                }
        }

        protected function sign_to_sql($str)
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
        
        

}

?>
