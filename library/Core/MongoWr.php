<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 数据库写入操作类---MongoDB数据库写入操作类
 *
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Core_MongoWr extends Core_DBWr implements DBWrite
{

        public function update(array $condition, array $new, array $options = array())
        {
                foreach ($new as $key => $value)
                {
                        if (is_int($value) && $value > 2147483647)
                        {
                                $new[$key] = new MongoInt64($value);
                        }
                }
                /* 验证各字段 */
                $this->new_attributes = $v;
                if (false == $this->validate())
                {
                        return false;
                }
                return $this->collection->update($condition, $new, $options);
        }

        public function insert(array $new = array(), array $options = array())
        {
                /* 验证各字段 */
                $this->new_attributes = $new;
                if (false == $this->validate())
                {
                        return false;
                }
                foreach ($new as $key => $value)
                {
                        if(is_int($value) && $value>2147483647){
                                $new[$key] = new MongoInt64($value);
                        }
                }
                return $this->collection->insert($new, $options);
        }

        /**
         * 删除某条件集合，谨慎使用此方法！
         * @author xiaotangren  <unphp@qq.com>
         * @return boolean
         * @throws Exception
         */
        public function remove(array $condition, array $options = array())
        {
                foreach ($condition as $key => $value)
                {
                        if(is_int($value) && $value>2147483647){
                                $condition[$key] = new MongoInt64($value);
                        }
                }
                return $this->collection->remove($condition, $options);
        }

        /**
         * 保存或更新一个文档到集合
         * @param array $options
         * @return boolean
         * @throws Exception
         * @author xiaotangren  <unphp@qq.com>
         */
        public function save(array $options = array())
        {
                $this->models->beforeSave($this);
                if ($this->is_new)
                {
                        $fileds = $this->models->fieldsValues();
                        if (empty($fileds) || !is_array($fileds))
                        {
                                throw new Exception($this->models_name . "::fieldsValues() must be return array()");
                                return false;
                        }
                        foreach ($fileds as $key => $value)
                        {
                                if (!isset($this->attributes[$key]))
                                        $this->attributes[$key] = $value;
                        }
                }
                $attributes = $this->attributes;
                foreach ($attributes as $key => $value)
                {
                        if(is_int($value) && $value>2147483647){
                                $attributes[$key] = new MongoInt64($value);
                        }
                }
                /* 验证各字段 */
                $this->new_attributes = $attributes;
                if (false == $this->validate())
                {
                        return false;
                }
                $this->collection->save($attributes, $options);
                $this->is_new = false;
                $this->models->afterSave($this);
                return true;
        }

        /**
         * 销毁已经查询出来的单条集合
         * @return boolean
         * @throws Exception
         * @author xiaotangren  <unphp@qq.com>
         */
        public function destroy()
        {
                $this->models->beforeDestroy($this);
                if (!$this->is_new)
                {
                        $this->collection->remove(array('_id' => $this->attributes['_id']));
                }
        }

        public function syncInsert(array $new = array(), array $options = array())
        {
                $new = $this->getTransformInsert($new);
                /* 验证各字段 */
                $this->new_attributes = $new;
                if (false == $this->validate())
                {
                        return false;
                }
                foreach ($new as $key => $value)
                {
                        if (is_int($value) && $value > 2147483647)
                        {
                                $new[$key] = new MongoInt64($value);
                        }
                }
                return $this->collection->insert($new, $options);
        }

}

?>
