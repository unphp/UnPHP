<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 递归方式的对变量中的特殊字符进行转义
 *
 * @access  public
 * @param   mix     $value
 *
 * @return  mix
 */
function addslashes_deep($value)
{
        if (empty($value))
        {
                return $value;
        }
        else
        {
                return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
        }
}

?>