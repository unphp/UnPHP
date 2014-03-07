<?php

/**
 * 加密、解密组件（DES加密算法）
 * @system UNPHP 
 * @version UNPHP 1.0
 * @author xiaotangren  <unphp@qq.com>
 * @data 2013-10-28
 */
class Pub_Des
{

        private $key;
        private $iv;
        private static $_instance;

        /**
         * 
         * @param type $key
         * @param type $iv
         * @return type
         */
        public static function getInstance($key = "82543243", $iv = 0)
        {
                if (!(self::$_instance instanceof self))
                {
                        self::$_instance = new self($key, $iv);
                }
                return self::$_instance;
        }

        /**
         * 
         * @param type $key
         * @param type $iv
         */
        private function __construct($key, $iv)
        {
                $this->key = $key;
                if ($iv == 0)
                {
                        $this->iv = $key;
                }
                else
                {
                        $this->iv = $iv;
                }
        }

        /**
         * 
         * @param type $key
         * @param type $iv
         */
        public function setKey($key = "82543243", $iv = 0)
        {
                $this->key = $key;
                if ($iv == 0)
                {
                        $this->iv = $key;
                }
                else
                {
                        $this->iv = $iv;
                }
        }

        /**
         * 
         * @param type $str
         * @return type
         */
        public function encrypt($str)
        {
                $size = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC);
                $str = $this->pkcs5Pad($str, $size);
                return strtoupper(bin2hex(mcrypt_cbc(MCRYPT_DES, $this->key, $str, MCRYPT_ENCRYPT, $this->iv)));
        }

        /**
         * 
         * @param type $str
         * @return type
         */
        public function decrypt($str)
        {
                $strBin = $this->hex2bin(strtolower($str));
                $str = mcrypt_cbc(MCRYPT_DES, $this->key, $strBin, MCRYPT_DECRYPT, $this->iv);
                return $this->pkcs5Unpad($str);;
        }

        /**
         * 
         * @param type $hexData
         * @return type
         */
        private function hex2bin($hexData)
        {
                $binData = "";
                for ($i = 0; $i < strlen($hexData); $i += 2)
                {
                        $binData .= chr(hexdec(substr($hexData, $i, 2)));
                }
                return $binData;
        }

        /**
         * 
         * @param type $text
         * @param type $blocksize
         * @return type
         */
        private function pkcs5Pad($text, $blocksize)
        {
                $pad = $blocksize - (strlen($text) % $blocksize);
                return $text . str_repeat(chr($pad), $pad);
        }

        /**
         * 
         * @param type $text
         * @return boolean
         */
        private function pkcs5Unpad($text)
        {
                $pad = ord($text {strlen($text) - 1});
                if ($pad > strlen($text))
                        return false;
                if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)
                        return false;
                return substr($text, 0, - 1 * $pad);
        }

}

?>