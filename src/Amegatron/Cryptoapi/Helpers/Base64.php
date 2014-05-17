<?php namespace Amegatron\Cryptoapi\Helpers;

class Base64 {
    public static function UrlDecode($x)
    {
        return base64_decode(str_replace(array('_','-'), array('/','+'), $x));
    }

    public static function UrlEncode($x)
    {
        return str_replace(array('/','+'), array('_','-'), base64_encode($x));
    }
}
