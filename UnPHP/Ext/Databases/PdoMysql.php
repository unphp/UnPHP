<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PdoMySQL
 *
 * @author Administrator
 */
class PdoMysql {

    private $pool = null;

    public function __construct($pool) {
        $this->pool = $pool;
    }

    public function get_table_filed_type($collectionName) {
        $data = array();
        $sql = "SHOW COLUMNS FROM {$collectionName} ";
        $sth = $this->pool->prepare($sql);
        $sth->execute();
        $rs = $sth->fetchAll(PDO::FETCH_ASSOC);
        
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
        return $data;
    }

    private function str_to_type($str) {
        $rs = array();
        $temp = array();
        preg_match('/([a-zA-Z]+)\(([0-9]+)\)/', $str,$temp);
        if(count($temp)==3){
            switch ($temp[1]) {
                case 'int':
                    $type = 'int';
                    break;
                case 'varchar':
                default:
                    $type = 'string';
            }
            $rs['type'] = $type;
            $rs['length'] = $temp[2];
        }else{
            $rs['type'] = $str;
        }
        return $rs;
    }

}
