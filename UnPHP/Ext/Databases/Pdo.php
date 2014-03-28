<?php
/**
 * 框架扩展：数据库操作类库
 * Pdo类型（数据库）操作接口类
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author Xiao Tangren  <unphp@qq.com>
 * @data 2014-03-27
 * */
class Ext_Databases_Pdo {

    private $pool = null;

    public function __construct($pool) {
        $this->pool = $pool;
    }

    public function findOne($collectionName, $condition = array(), $options = array()) {
        $bind_params = array();
        $where = $this->_querySql($condition, $bind_params);
        $filed =  $this->getFileds($options);
        $order = $this->getOrder($options);
        $limit = $this->getLimit($options);
        $sql = "select " . $filed . " FROM " . $collectionName . $where . $order . $limit;
        $sth = $this->pool->prepare($sql);
        foreach ($bind_params as $key => $v) {
            $value = $v;
            if (is_array($v)) {
                $value = "(" . implode(",", $v) . ")";
            }
            $sth->bindValue($key, $value);
        }
        $sth->execute();
        $rs = $sth->fetch(PDO::FETCH_ASSOC);
        return $rs;
    }

    public function findAll($collectionName, $condition = array(), $options = array()) {
        $bind_params = array();
        $where = $this->_querySql($condition, $bind_params);
        $filed =  $this->getFileds($options);
        $order = $this->getOrder($options);
        $limit = $this->getLimit($options);
        $sql = "select " . $filed . " FROM " . $collectionName . $where . $order . $limit;   
        $sth = $this->pool->prepare($sql);
        foreach ($bind_params as $key => $v) {
            $value = $v;
            if (is_array($v)) {
                $value = "(" . implode(",", $v) . ")";
            }
            $sth->bindValue($key, $value);
        }
        $sth->execute();
        $rs = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $rs;
    }

    public function count($collectionName, $condition = array()) {
        $bind_params = array();
        $where = $this->_querySql($condition, $bind_params);
        $filed = 'count(*) as num';
        $sql = "select " . $filed . " FROM " . $collectionName . $where;
        $sth = $this->pool->prepare($sql);
        foreach ($bind_params as $key => $v) {
            $value = $v;
            if (is_array($v)) {
                $value = "(" . implode(",", $v) . ")";
            }
            $sth->bindValue($key, $value);
        }
        $sth->execute();
        $rs = $sth->fetch(PDO::FETCH_ASSOC);
        return $rs['num'];
    }

    public function insert() {
        
    }

    public function remove() {
        
    }

    public function update() {
        
    }

    private function getFileds($options) {
        $fileds = "*";
        if (isset($options['fields']) && is_array($options['fields'])) {
            $fileds = "";
            foreach ($options['fields'] as $key => $value) {
                if (1 === $value) {
                    $fileds .= $key . ",";
                }
            }
            $fileds = substr($fileds, 0, -1);
        }
        return $fileds;
    }

    private function getOrder($options) {
        $order = "";
        if (isset($options['sort']) && is_array($options['sort']) && !empty($options['sort'])) {
            $order = " Order by ";
            foreach ($options['sort'] as $key => $value) {
                if ($value==1){
                    $order .= $key." ASC,";
                }else{
                    $order .= $key." DESC,";
                }
            }
            $order = substr($order, 0, -1)." ";
        }
        return $order;
    }

    private function getLimit($options) {
        $limits = "";
        if (isset($options['offset']) || isset($options['limit'])) {
            $limits = " Limit ";
            if (isset($options['offset'])) {
                $limits .= intval($options['offset']) . " ";
            }else{
                $limits .= " 0 ";
            }
            if (isset($options['limit']) && $options['limit'] > 0) {
                $limits .= "," . intval($options['limit']) . " ";
            }
        }
        return $limits;
    }

    private function _querySql($condition=array(), &$bind_params=array()) {
        try {
            $sql = "";
            $where = "";
            $key_str = "mongo_";
            $i = 0;
            if ($condition) {
                foreach ($condition as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $k2 => $v2) {
                            if ($k2 === '$in') {
                                if ($v2 && is_array($v2)) {
                                    $temp_key = $key_str . $i++;
                                    $where .= $k . ' in (';
                                    foreach ($v2 as $v_in) {
                                        $temp_key = $key_str . $i++;
                                        $bind_params[$temp_key] = $v_in;
                                        $where .= ':'.$temp_key.',';
                                    }
                                    $where = substr($where, 0, -1). ") AND ";
                                } else {
                                    throw new Exception('$in=>array() must not empty!');
                                }
                            }
                            $sign = $this->sign_to_sql($k2);
                            if ($sign) {
                                $temp_key = $key_str . $i++;
                                $where .= $k . $sign . ':' . $temp_key . ' AND ';
                                $bind_params[$temp_key] = $v2;
                            }
                        }
                    } else {
                        $temp_key = $key_str . $i++;
                        $where .= $k . '=:' . $temp_key . ' AND ';
                        $bind_params[$temp_key] = $v;
                    }
                }
                return ' WHERE '.substr($where, 0, -4);
            } else {
                return $where; 
            }
            
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    private function sign_to_sql($str) {
        $rs = '';
        switch ($str) {
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

    private function get_field_value($field_name, $value) {
        $fild_data = self::getConn()->getTableField($this->tables);
        $type = $fild_data['field'][$field_name]['type'];
        $rs = null;
        switch ($type) {
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

    private function get_table_filed_type($collectionName) {
        $cache_name = 'table_filed_datatype_' . $collectionName;
        $data = Yii::app()->filecache->get($cache_name);
        if (empty($data)) {
            $data = array();
            $show_table_sql = "SHOW COLUMNS FROM {$collectionName}";
            $command = $this->createCommand($show_table_sql);
            $rs = $command->queryAll();
            foreach ($rs as $v => $k) {
                $field = $k['Field'];
                $type = $this->str_to_type($k['Type']);
                $data['field'][$field]['type'] = $type['type'];
                $data['field'][$field]['length'] = $type['length'];
                $data['field'][$field]['null'] = $k['Null'];
                $data['field'][$field]['key'] = $k['Key'];
                $data['field'][$field]['default'] = $k['Default'];
                $data['field'][$field]['extra'] = $k['Extra'];
                if ($k['Extra'] == "auto_increment") {
                    $data['auto_key_id'] = $field;
                }
            }
            //Yii::app()->filecache->set($cache_name,$data,18600);
        }
        return $data;
    }

}
