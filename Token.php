<?php

namespace Topolis\FunctionLibrary;

use \Exception;

class Token{

    const UUID_V4 = 4;

    /**
     * Generate a uuid according to a specific UUID definition
     * @param integer $version        format/version of UUID to generate
     * @throws \Exception
     * @return string
     */
    public static function uuid($version = self::UUID_V4){

        switch($version){
            case self::UUID_V4:
                $data = openssl_random_pseudo_bytes(16);

                $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0010
                $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

                return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

            default:
                throw new Exception("Invalid or unknown uuid version specified");
        }
    }

    /**
     * Generate a unique id comparable to PHP::uniqid but with better randomness.
     * Uses openssl library
     * @param int $length
     * @return string
     */
    public static function uniqid($length = 12){
        $data = openssl_random_pseudo_bytes(ceil($length / 2));
        return bin2hex($data);
    }

}