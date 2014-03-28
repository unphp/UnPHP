<?php
/**
 * 框架“应用配置”读取类
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author Xiao Tangren  <unphp@qq.com>
 * @data 2014-03-05
 * */
class Unphp_ReadConf
{
        protected $iniFile = null;
        protected $conf = array();

        public function __construct($iniFile)
        {
                $this->iniFile = $iniFile;
        }

        public function get()
        {
                if (file_exists($this->iniFile))
                {
                        return $this->read();
                }
        }

        protected function read()
        {
                $rs = array();
                $contents = file_get_contents($this->iniFile);
                $list = explode("\n", $contents);
                $temp_key = "";
                foreach ($list as $v)
                {
                        if(!empty($temp_key) && preg_match('/([\.a-zA-Z0-9\_\-]+)[\s]+=[\s]+"([^"]+)"/',$v,$arr))
                        {
                                $value = preg_replace_callback('/{([a-zA-Z0-9\_]+)}/', 'self::callbackFun', $arr[2]);
                                $this->getKey($rs[$temp_key], $arr[1], $value);
                        }
                        else{
                                $pos = strpos($v, ";");
                                if (false !== $pos)
                                {
                                        $v = trim(substr($v, 0, $pos));
                                }
                                if (!empty($v))
                                {
                                        $arr1 = array();
                                        $arr2 = array();
                                        if (preg_match('/\[([a-zA-Z0-9\_\-]+)\]/i', $v, $arr1))
                                        {
                                                $temp_key = $arr1[1];
                                                continue;
                                        }
                                        if (!empty($temp_key) && preg_match('/([\.a-zA-Z0-9\_\-]+)[\s]+=[\s]+(.*)/i', $v, $arr2))
                                        {
                                                $value = preg_replace_callback('/{([a-zA-Z0-9\_]+)}/', 'self::callbackFun', $arr2[2]);
                                                $this->getKey($rs[$temp_key], $arr2[1], $value);
                                        }
                                }
                        }
                }
                return $rs;
        }

        protected function callbackFun($macth)
        {
                $rs = "";
                eval('?>' . '<?php $rs= ' . $macth[1] . ';?>');
                return $rs;
        }
        
        protected function getKey(&$rs, $keystring, $v) {
        $newarr = explode(".", $keystring);
        if (count($newarr) > 1) {
            $k = $newarr[0];
            unset($newarr[0]);
            $this->getKey($rs[$k], implode(".", $newarr),$v);
        } else {
            $rs[$keystring] = $v;
        }
    }

}
