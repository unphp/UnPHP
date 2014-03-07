<?php

/**
 * 语言包公共类
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Pub_Lan
{

        static public $_instance;
        static protected $lanuages_dir = null;
        static public $template;
        static public $lan_code;
        static public $site_code;
        
        

        protected function __construct($lanuages_dir=null)
        {
                if (NULL !== $lanuages_dir)
                self::$lanuages_dir = $lanuages_dir;
        }

        /**
         * 静态实例方法
         * @param type $auth
         * @return type
         */
        public static function getInstance($lanuages_dir = null)
        {
                if (!(self::$_instance instanceof self))
                {
                        if (null !== $lanuages_dir)
                                self::$lanuages_dir = $lanuages_dir;
                        self::$_instance = new self();
                }
                return self::$_instance;
        }

        /*
         * date：20131015
         * author：sunchangzhi
         * note：导入语言标签备份
         */
        public static function import_1($arr)
        {
                //echo $this->lanuages_dir;exit;
                global $_LANG;
                if(!is_array($_LANG)) $_LANG = array();
                $cache = isset($GLOBALS['_CFG']['lang_cache_status']) ? $GLOBALS['_CFG']['lang_cache_status'] : false;
                //var_dump($cache);exit;
                if (is_array($arr))
                {
                        foreach ($arr as $value)
                        {
                                $temp_arr = self::getData($value, $cache);
                                $_LANG = array_merge($_LANG,$temp_arr);
                                
                        }
                } else
                {
                        $temp_arr = self::getData($arr, $cache);
                        $_LANG = array_merge($_LANG, $temp_arr);
                }
                return $_LANG;
        }
        /*
         * date：20131015
         * 手机端兼容aecmp-001模板非common模块的语言标签
         */
        public static function import($arr)
        {
            //echo $this->lanuages_dir;exit;
            global $_LANG;
            if (!is_array($_LANG))
                $_LANG = array();
            $cache = isset($GLOBALS['_CFG']['lang_cache_status']) ? $GLOBALS['_CFG']['lang_cache_status'] : false;
            
            $tempTemplate=self::$template ; 
            //var_dump($cache);exit;
            if (is_array($arr))
            {
                foreach ($arr as $value)
                {
                    //手机模板标签兼容aecmp-001模板语言标签  sunchangzhi 20131015
                    if ($GLOBALS['_CFG']['template'] == 'tablet-pc')
                    { 
                        self::$template = 'aecmp-001';
                        if ($value == "common")
                        {     
                            self::$template = 'tablet-pc';
                        }
                    }
                    $temp_arr = self::getData($value, $cache);
                    $_LANG = array_merge($_LANG, $temp_arr);
                    
                }
            }
            else
            {
                //手机模板标签兼容aecmp-001模板标签  sunchangzhi 20131015
                if ($GLOBALS['_CFG']['template'] == 'tablet-pc')
                {
                    self::$template = 'aecmp-001';
                    if ($value == "common")
                    {
                        self::$template = 'tablet-pc';
                    }
                }
                $temp_arr = self::getData($arr, $cache);
                $_LANG = array_merge($_LANG, $temp_arr);
            }
                self::$template = $tempTemplate;
                return $_LANG;
          
        }
        
        protected static function getData($value, $cache = false)
        {
                $temp_lan_arr = array(
                    'aecmp-001' => 'aecmp-001',
                    'vessos' => 'aecmp-001',
                    'tablet-pc' => 'tablet-pc', //增加一列，手机模板 修改日期20130917 修改人：sunchangzhi
                    'firstfun' => 'aecmp-001',//
                );
                $template = $temp_lan_arr[self::$template];
                $key = self::$site_code . '_' . self::$lan_code . '_' . $template.'_'.$value;
                $temp_arr = array();
                if ($cache)
                {
                        $temp_arr = Pub_Cache::read_static_cache($key, 'lan');
                }
                if (empty($temp_arr))
                {
                        echo 'sss';exit;
                        $condition = array();
                        $condition['template'] = $template;
                        $condition['lan_code'] = self::$lan_code;
                        $condition['type'] = $value;
                        $options = array();
                        $options['fields']['first_key'] = 1;
                        $options['fields']['second_key'] = 1;
                        $options['fields']['content'] = 1;
                        $common_lan_data = M_LanPackage::getAll($condition, $options);
                        $custom_lan_data = M_LanPackageDif::getAll($condition, $options);
                        $temp_arr = array();
                        if (!empty($common_lan_data))
                        {
                                foreach ($common_lan_data as $row)
                                {
                                        if (empty($row['first_key']))
                                        {
                                                $temp_arr[$row['second_key']] = $row['content'];
                                        }
                                        else
                                        {
                                                $temp_arr[$row['first_key']][$row['second_key']] = $row['content'];
                                        }
                                }
                        }
                        if (!empty($custom_lan_data))
                        {
                                foreach ($custom_lan_data as $row)
                                {
                                        if (empty($row['first_key']))
                                        {
                                                $temp_arr[$row['second_key']] = $row['content'];
                                        }
                                        else
                                        {
                                                $temp_arr[$row['first_key']][$row['second_key']] = $row['content'];
                                        }
                                }
                        }
                        if ($cache)
                        {
                                Pub_Cache::write_static_cache($key, $temp_arr, 'lan');
                        }
                }
                return $temp_arr;
        }
        
        public static function temp_replace()
        {
                
                $key = array(
                    'user_index_welcome_personality',
                    'productstag_description',
                    'featured_products_description',
                    'specials_description',
                    'goods_index_one',
                    'library_goods_payment_three',
                    'library_goods_quality_two',
                    'copy_right_str',
                    'qs_rule4',
                    'productstag_title',
                    'featured_products_title',
                    'products_new_description',
                    'products_new_title',
                    'specials_title',
                    'library_goods_fag_die',
                    'library_goods_payment_einfache_bezahlung',
                    'library_goods_question_efox_reply',
                    'library_goods_quality_one',
                    'library_goods_quality_three',
                    'library_goods_fag_wenn',
                    'library_goods_shipping_one',
                    'goods_wholesaletpl_5'
                );
                $host = substr($GLOBALS['cookie_domain'], 1);
                if ($GLOBALS['cookie_domain'] == '.tabouf.com')
                {
                        foreach ($key as $k)
                        {
                                $GLOBALS['_LANG'][$k] = preg_replace('/myefox\.fr/', $host, $GLOBALS['_LANG'][$k]);
                        }
                }
                if ($GLOBALS['cookie_domain'] == '.myefox.fr')
                {
                        foreach ($key as $k)
                        {
                                $GLOBALS['_LANG'][$k] = preg_replace('/tabouf\.com/', $host, $GLOBALS['_LANG'][$k]);
                        }
                }
        }
        
        
        
        

}

?>
