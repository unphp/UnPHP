<?php

/**
 * 该类是mod逻辑层基类。
 * 统一mod逻辑层子类方法命名规范，总控子类。
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
abstract class Core_Models
{

        public $yar;

        public function __construct(Pub_Rpc $yar)
        {
                $this->yar = $yar;
                $this->init();
        }

        protected function init()
        {
                
        }

        protected function c($class)
        {
                return $this->yar->c($class);
        }

        protected final function send($ac, $parameters = array(), $type = 'php')
        {
                try
                {
                        //$model_name = get_called_class();
                        if (!is_array($parameters))
                                throw new Exception("参数必须为数组格式！");
                        $parameters = @array_merge(array('site_id' => $this->site_id, 'language_id' => $this->language_id), $parameters);
                        return $this->yar->send($ac, $parameters, $type = 'php');
                } catch (Exception $exc)
                {
                        echo $exc->getMessage();
                }
        }

        /**
         * Yar前置方法
         * @return boolean
         * @throws Exception
         */
        public final function _checkSiteidLanidYar($ac)
        {
//                try
//                {
//                        if ($this->site_id == null || $this->language_id == null)
//                        {
//                                $child_class_name = get_called_class();
//                                throw new Exception($child_class_name . '::' . $ac . '( ) 被调用时，站点ID或语言ID未初始化！');
//                        }
//                } catch (Exception $exc)
//                {
//                        echo $exc->getMessage();
//                }
                return TRUE;
        }

        public function beforeYar($ac)
        {
                return TRUE;
        }

        public function afterYar($ac)
        {
                return TRUE;
        }

}

?>
