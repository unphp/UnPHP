<?php
/**
 * 框架扩展：数据库操作类库
 * Mongo数据库操作接口类
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author Xiao Tangren  <unphp@qq.com>
 * @data 2014-03-27
 * */
class Ext_Databases_Mongo {

    private $pool = null;
    private $collectionMap = array();

    public function __construct($pool) {
        $this->pool = $pool;
    }

    private function getCollection($collectionName) {
        $database = $this->pool->conn();
        if (!($database->connected)) {
            $database->connect();
        }
        if (!isset($this->collectionMap[$collectionName])) {
            $this->collectionMap[$collectionName] = $database->selectCollection($database, $collectionName);
        }
        return $this->collectionMap[$collectionName];
    }

    private function _find($collectionName, $condition = array(), $options = array()) {
        $collection = $this->getCollection($collectionName);
        if (isset($options['fields'])) {
            $documents = $collection->find($condition, $options['fields']);
        } else {
            $documents = $collection->find($condition);
        }
        if (isset($options['sort']))
            $documents->sort($options['sort']);
        if (isset($options['offset']))
            $documents->skip($options['offset']);
        if (isset($options['limit']))
            $documents->limit($options['limit']);
        return $documents;
    }

    public function findOne($collectionName, $condition = array(),$options = array()) {
        $collection = $this->getCollection($collectionName);
        $fields = isset($options['fields']) ? $options['fields'] : array();
        return $collection->findOne($condition, $fields);
    }

    public function findAll($collectionName, $condition = array(), $options = array()) {
        $documents = static::_find($collectionName, $condition, $options);
        $ret = array();
        while ($documents->hasNext()) {
            $document = $documents->getNext();
            $ret[] = $document;
        }
        return $ret;
    }

    public function count($collectionName, $condition = array()) {
        $collection = $this->getCollection($collectionName);
        $documents = $collection->count($condition);
        return $documents;
    }

    public function insert() {
        
    }

    public function remove() {
        
    }

    public function update() {
        
    }

}

class RecordIterator implements Iterator, Countable {

    protected $current; // a PHP5.3 pointer hack to make current() work
    protected $cursor;
    protected $className;

    public function __construct($cursor, $className) {
        $this->cursor = $cursor;
        $this->className = $className;
        $this->cursor->rewind();
        $this->current = $this->current();
    }

    public function cursor() {
        return $this->cursor;
    }

    public function current() {
        $this->current = $this->instantiate($this->cursor->current());
        return $this->current;
    }

    public function count() {
        return $this->cursor->count();
    }

    public function key() {
        return $this->cursor->key();
    }

    public function next() {
        $this->cursor->next();
    }

    public function rewind() {
        $this->cursor->rewind();
    }

    public function valid() {
        return $this->cursor->valid();
    }

    private function instantiate($document) {
        if ($document) {
            $className = $this->className;
            return new $className($document, false);
        } else {
            return null;
        }
    }

}
