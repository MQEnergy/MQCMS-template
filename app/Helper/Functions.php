<?php
/**
 * 助手函数
 */

/**
 * 随机字符串
 */
if (!function_exists('generate_random_string')) {
    function generate_random_string($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}

/**
 * Generate random decimals
 */
if (!function_exists('rand_float')) {
    function rand_float($min = 0, $max = 1) {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }
}

/**
 * 调用文件夹所有的php文件
 */
if (!function_exists('require_dir_script')) {
    function require_dir_script($dir, $filename='') {
        if (is_dir($dir)) {
            $handler = opendir($dir);
            //遍历脚本文件夹下的所有文件
            while (false !== ($file = readdir($handler))) {
                if ($file != "." && $file != "..") {
                    $fullpath = $dir . "/" . $file;
                    if (!is_dir($fullpath) && substr($file,-4) == '.php') {
                        if ($filename !== '' && basename($fullpath, '.php') === $filename) {
                            require_once($fullpath);
                        } else {
                            require_once($fullpath);
                        }
                    } else {
                        require_dir_script($fullpath);
                    }
                }
            }
            //关闭文件夹
            closedir($handler);
        }
    }
}

/**
 * copy
 */
if (!function_exists('recurse_copy')) {
    function recurse_copy($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                if (is_dir($src . '/' . $file)) {
                    recurse_copy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}


if (!function_exists('key_ed')) {
    function key_ed($txt, $encrypt_key)
    {
        $encrypt_key = md5($encrypt_key);
        $ctr = 0;
        $tmp = "";
        for ($i = 0; $i < strlen($txt); $i++) {
            if ($ctr == strlen($encrypt_key))
                $ctr = 0;
            $tmp .= substr($txt, $i, 1) ^ substr($encrypt_key, $ctr, 1);
            $ctr++;
        }
        return $tmp;
    }
}

/**
 * 加密
 * @param $txt
 * @param $key
 * @return string
 */
if (!function_exists('random_encrypt')) {
    function random_encrypt($txt, $key = '')
    {
        $encrypt_key = md5(mt_rand(0, 100));
        $ctr = 0;
        $tmp = "";
        for ($i = 0; $i < strlen($txt); $i++) {
            if ($ctr == strlen($encrypt_key))
                $ctr = 0;
            $tmp .= substr($encrypt_key, $ctr, 1) . (substr($txt, $i, 1) ^ substr($encrypt_key, $ctr, 1));
            $ctr++;
        }
        return key_ed($tmp, $key);
    }
}

/**
 * 解密
 * @param $txt
 * @param $key
 * @return string
 */
if (!function_exists('random_decrypt')) {
    function random_decrypt($txt, $key = '')
    {
        $txt = key_ed($txt, $key);
        $tmp = "";
        for ($i = 0; $i < strlen($txt); $i++) {
            $md5 = substr($txt, $i, 1);
            $i++;
            $tmp .= (substr($txt, $i, 1) ^ $md5);
        }
        return $tmp;
    }
}

/**
 * url加密
 * @param $url
 * @param string $key
 * @return string
 */
if (!function_exists('encrypt_url')) {
    function encrypt_url($url, $key = '')
    {
        return rawurlencode(base64_encode(random_decrypt($url, $key)));
    }
}

/**
 * url解密
 * @param $url
 * @param string $key
 * @return string
 */
if (!function_exists('decrypt_url')) {
    function decrypt_url($url, $key = '')
    {
        return random_decrypt(base64_decode(rawurldecode($url)), $key);
    }
}

/**
 * 获取连接
 * @param $str
 * @param $key
 * @return mixed
 */
if (!function_exists('geturl')) {
    function geturl($str, $key = '')
    {
        $str = decrypt_url($str, $key);
        $url_array = explode('&', $str);
        if (is_array($url_array)) {
            foreach ($url_array as $var) {
                $var_array = explode("=", $var);
                $vars[$var_array[0]] = $var_array[1];
            }
        }
        return $vars;
    }
}

/**
 * 字符串加密
 */
if (!function_exists('params_encrypt')) {
    function params_encrypt($data, $key)
    {
        $key = md5($key);
        $x = 0;
        $len = strlen($data);
        $l = strlen($key);
        $char = $str = '';
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= $key[$x];
            $x++;
        }
        for ($i = 0; $i < $len; $i++) {
            $str .= chr(ord($data[$i]) + (ord($char[$i])) % 256);
        }
        return base64_encode($str);
    }
}

if (!function_exists('params_decrypt')) {
    //解密
    function params_decrypt($data, $key)
    {
        $key = md5($key);
        $x = 0;
        $data = base64_decode($data);
        $len = strlen($data);
        $l = strlen($key);
        $char = $str = '';
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= substr($key, $x, 1);
            $x++;
        }
        for ($i = 0; $i < $len; $i++) {
            if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            } else {
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }
        return $str;
    }

}