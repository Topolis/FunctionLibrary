<?php

namespace Topolis\FunctionLibrary;

class Math{

    const HEX_CHARS = '0123456789abcdef';
    const BASE36_CHARS = '0123456789abcdefghijklmnopqrstuvwxyz';
    const BASE62_CHARS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * convert character string with arbitrary base to decimal string
     * @param string $input       arbitrary base string
     * @param string $Chars       list of letters in arbitrary base
     * @return string             decimal base string
     */
    public static function baseToDec($input, $Chars)
    {
        if (preg_match('/^[' . $Chars . ']+$/', $input)){
            $Result = (string)'0';

            for ($i=0; $i<strlen($input); $i++){
                if ($i != 0) $Result = bcmul($Result, strlen($Chars),0);
                $Result = bcadd($Result, strpos($Chars, $input[$i]),0);
            }
            return $Result;
        }
        return false;
    }

    /**
     * convert character string with decimal base to arbitrary base
     * @param string $input        decimal string
     * @param string $Chars
     * @return string|boolean
     */
    public static function baseFromDec($input, $Chars)
    {
        if (preg_match('/^[0-9]+$/', $input))
        {
            $Result = '';
            do
            {
                $Result .= $Chars[bcmod($input, strlen($Chars))];
                $input = bcdiv($input, strlen($Chars), 0);
            }
            while (bccomp($input, '0') != 0);

            return strrev($Result);
        }
        return false;
    }
    
    /**
     * cut of (round down) decimals with optional precision.
     * Replacement for floor with higher precision to avoid round errors in standard floor
     * 
     * <code>
     * $x = Math::floor(123.2345344);    // $x will be 123
     * $x = Math::floor(2823.787214, 2); // $x will be 2823.28
     * </code>
     * 
     * @param int|float $number        input number
     * @param int $prec                     (Optional) number of decimals in result. Default: 0
     * @return int;
     */
    static function floor($number, $prec = 0) {
        $number = $number * pow(10,$prec);
        $number = bcdiv($number, pow(10,$prec), $prec);
        return $number;
    }
    
}

?>