<?php
/**
 * 框架扩展：数据库操作类库
 * 数据表模型
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author Xiao Tangren  <unphp@qq.com>
 * @data 2014-03-27
 * */
class Ext_Databases_Model {

    protected static $tableName = null;
    protected static $poolConf = array();

    const ACTION_READ = 1;
    const ACTION_WRITE = 2;
    const ACTION_BOTH = 0;

    protected static function getReadEngine() {
        $conf = static::$poolConf[static::$tableName][static::ACTION_READ];
        return Ext_Databases_Pool::getWritePool(static::$tableName, $conf[0], $conf[1], $conf[2]);
    }

    protected static function getWriteEngine() {
        $conf = static::$poolConf[static::$tableName][static::ACTION_WRITE];
        return Ext_Databases_Pool::getWritePool(static::$tableName, $conf[0], $conf[1], $conf[2]);
    }

    public static function regionPool($table, $dsn, $user, $password, $action = self::ACTION_BOTH) {
        if (!isset(static::$poolConf[$table])) {
            switch ($action) {
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

    public static function findOne($condition = array(), $options = array()) {
        return static::getReadEngine()->conn()->findOne(static::$tableName, $condition, $options);
    }

    public static function findAll($condition = array(), $options = array()) {
        return static::getReadEngine()->conn()->findAll(static::$tableName, $condition, $options);
    }

    public static function count($condition = array()) {
        return static::getReadEngine()->conn()->count(static::$tableName, $condition);
    }

    public function insert($new, $options = array()) {
        $className = get_called_class();
        $pool = $className::getWriteEngine();
        return $pool->insert($new, $options);
    }

    public function remove($condition, $options = array()) {
        $className = get_called_class();
        $pool = $className::getWriteEngine();
        return $pool->remove($condition, $options);
    }

    public function update($condition, $new, $options = array()) {
        $className = get_called_class();
        $pool = $className::getWriteEngine();
        return $pool->update($condition, $new, $options);
    }

}
