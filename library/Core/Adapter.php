<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * “数据库链接”适配器
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Core_Adapter
{

    private $_connection = array();
    private $_tablecConnection = array();
    private $db_list;
    private $table_list;

    public function __construct($db_list, $table_list)
    {
        $this->db_list = $db_list;
        $this->table_list = $table_list;
        $this->_connection = array();
        $this->_tablecConnection = array();
    }

    /**
     * 检测某表是否已经配置过。
     * @author Xiao Tangren <unphp@qq.com>
     * @date 2013-08-12
     * @param type $table
     * @return type
     */
    public function checkTable($table)
    {
        try
        {
            $collectionName = Pub_Aecmp::table($table);
            $db_id = $this->table_list[$collectionName];
            $rs = empty($db_id) ? false : true;
            return $rs;
        }
        catch (Exception $exc)
        {
            echo $exc->getMessage();
            exit;
        }
    }

    /**
     * 通过webservice获取每个表的（分库）数据库配置信息
     * @author Xiao Tangren <unphp@qq.com>
     * @date 2013-08-12
     * @param type $table
     * @return type
     * @throws Exception
     */
    public function getConfig($table)
    {
        $db_id = $this->table_list[$table];

        if (empty($db_id))
        {
            throw new Core_Exception($table, '1100000002');
        }
        $conf = $this->db_list[$db_id];
        return $conf;
    }

    /**
     * 返回某表的mongodb链接句柄资源。
     * @author Xiao Tangren <unphp@qq.com>
     * @date 2013-08-12
     * @param type $table
     * @return type
     */
    public function getConnection($table)
    {
        if (!isset($this->_tablecConnection[$table]))
        {
            $conf = $this->getConfig($table);
            $conf_md5 = md5(implode('|', $conf));
            try
            {
                $conf_md5 = md5(implode('|', $conf));
                if (!isset($this->_connection[$conf_md5]))
                {
                    switch ($conf['engine'])
                    {
                        case 'mongodb':
                            $server = 'mongodb://' . $conf['user_name'] . ':password@' . $conf['host'] . ':' . $conf['port'];
                            $option = array();
                            $option['db'] = $conf['db_name'];
                            $option['password'] = $conf['password'];
                            $this->_connection[$conf_md5] = new MongoClient($server, $option); // 连接
                            break;
                        case 'mysql':
                            $dsn = $conf['engine'] . ':dbname=' . $conf['dbname'] . ';host=' . $conf['host'] . ';port=' . $conf['port'];
                            $this->_connection[$conf_md5] = new PDO($dsn, $conf['user'], $conf['password']);
                            break;
                        default:
                            break;
                    }
                }
                switch ($conf['engine'])
                {
                    case 'mongodb':
                        $dbh = $this->_connection[$conf_md5]->selectDB($conf['db_name']);
                        if (!($this->_connection[$conf_md5]->connected))
                        {
                            $this->_connection[$conf_md5]->connect();
                        }
                        $this->_tablecConnection[$table] = $this->_connection[$conf_md5]->selectCollection($this->_connection[$conf_md5], $table);
                        break;
                    default:
                        $this->_tablecConnection[$table] = $this->_connection[$conf_md5];
                        break;
                }
            }
            catch (Exception $exc)
            {
                throw new Core_Exception($exc->getMessage(), '1100000001');
            }
        }
        return $this->_tablecConnection[$table];
    }

}

?>
