<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Category
 *
 * @author xiao
 */
class Mod_Cate extends Core_MysqlRecord
{

        protected static $collectionName = 'cate';

        public static function getRootAutoValue()
        {
                $root = 0;
                $condition = array();
                $condition['level'] = 0;
                $options['sort']['root'] = -1;
                $rs = self::getOne($condition, $options);
                if ($rs)
                {
                        $root = (int) $rs['root'] + 1;
                }
                return $root;
        }

        public static function getRoot()
        {
                $arr_list = Pub_Cache::get_temp_cache('cate', 'root');
                if (empty($arr_list))
                {
                        $arr_list = self::getAll(array('level' => 0), array('sort' => array('root' => 1)));
                        Pub_Cache::set_temp_cache('cate', 'root', $arr_list);
                }
                return $arr_list;
        }

        public static function getChildren($cate_id, $is_data = 1)
        {
                $rs = array();
                $root_list = self::getRoot();
                foreach ($root_list as $root_data)
                {
                        $cate_cache = self::getCache($root_data['root']);
                        if (isset($cate_cache[$cate_id]))
                        {
                                $children_list = $cate_cache[$cate_id]['children_id'];
                                if ($is_data)
                                {
                                        foreach ($children_list as $id)
                                        {
                                                $rs[] = $cate_cache[$id];
                                        }
                                }
                                else
                                {
                                        $rs = $children_list;
                                }
                                break;
                        }
                        unset($cate_cache);
                }
                return $rs;
        }

        /**
         * 接口：取得所有的产品（目录）分类
         * @author Xiao Tangren <unphp@qq.com>
         * @date 2013-10-31
         * @param int $cat_id 分类ID
         * @return array
         */
        public static function getCache($root)
        {
                $arr_list = Pub_Cache::get_temp_cache('cate', 'all_root_' . $root);
                if (empty($arr_list))
                {
                        $arr_tree = self::getAll(array('root' => (int) $root));
                        $all_arr_temp = array();
                        $level_arr_temp = array();
                        foreach ($arr_tree as $v)
                        {
                                $all_arr_temp[$v['cate_id']] = array(
                                    'cate_id' => $v['cate_id'],
                                    'name' => $v['name'],
                                    'root' => $v['root'],
                                    'is_show' => $v['is_show'],
                                    'level' => $v['level'],
                                    'lft' => $v['lft'],
                                    'rgt' => $v['rgt'],
                                );
                                $level_arr_temp[$v['level']][$v['cate_id']] = array('lft' => $v['lft'], 'rgt' => $v['rgt']);
                        }
                        self::get_child_list($all_arr_temp, $level_arr_temp);
                        $arr_list = $all_arr_temp;
                        Pub_Cache::set_temp_cache('cate', 'all_root_' . $root, $arr_list);
                }
                return $arr_list;
        }

        /**
         * 私有方法
         * 递归分类，产生一个三维的父子级结构的数组。
         * @author Xiao Tangren <unphp@qq.com>
         * @date 2013-10-31
         * @param array $all 引用（分类数组）
         * @param array $level_arr_temp（分类层级索引数组）
         */
        protected static function get_child_list(&$all, $level_arr_temp)
        {
                foreach ($all as $cat)
                {
                        $level_arr = $level_arr_temp[$cat['level'] + 1];
                        if ($level_arr)
                        {
                                foreach ($level_arr as $cat_id => $value)
                                {
                                        if ($cat['lft'] < $value['lft'] && $cat['rgt'] > $value['rgt'])
                                        {
                                                $all[$cat['cate_id']]['children_id'][] = $cat_id;
                                                $all[$cat_id]['rs_parent_id'] = $cat['cat_id'];
                                        }
                                }
                        }
                }
        }

}
