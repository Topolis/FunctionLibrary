<?php

namespace Topolis\FunctionLibrary;

class String{
	
    // Constants for Byte precision in BtoStr
	const BP_B  = "0";
	const BP_KB = "1";
	const BP_MB = "2";
	const BP_GB = "3";
	const BP_TB = "4";
	
	/**
	 * convert a number of bytes to a rounded string representation ex: "1.23GB"
	 * @param integer $input    number of bytes
	 * @param integer $prec     precision constant. Default: self::BP_MB
	 * @return string
	 */
	public static function BtoStr($input, $prec = self::BP_MB){
		$units = array("", "K", "M", "G", "T");
		
		
		for($i=0; $i < $prec; $i++)
		    $input = round($input / 1024,2);
		return $input.$units[$i]."B";
	}
	
	/**
	 * return contents of string until a character specified in $match is no longer found, starting at optional $offset.
	 * @param string $input          source string
	 * @param string $match          string with allowed characters
	 * @param integer $offset        start at offset (Optional)
	 * @return string
	 */
    public static function substr_untilnot($input, $match, $offset = 0){
         if($offset > 0)
             $input = substr($input, $offset);
         $result = preg_split("/[^$match]+/", $input);
         return $result ? array_shift($result) : false;        
    }
    
    /**
     * return contents of string until one of the strings in $match is no longer found, starting at optional offset
     * @param string $input          source string
     * @param array $match           array with allowed strings
     * @param integer $offset        start at offset (Optional)
     * @return string
     */
    public static function substr_untilnotstr($input, $match, $offset = 0){
         if($offset > 0)
         $input = substr($input, $offset);
         
         $offset = 0;
         $found = "";         
         do{
             $offset += strlen($found);
             $pos = self::strpos($input, $match, $offset, $found);
         }while($pos === $offset);
         
         return substr($input, 0, $offset);
    }
    
	/**
	 * return contents of string until a character specified in $match is found, starting at optional $offset.
	 * @param string $input          source string
	 * @param string $match          string with stop characters
	 * @param integer $offset        start at offset (Optional)
	 * @return string
	 */
    public static function substr_until($input, $match, $offset = 0){
         if($offset > 0)
         $input = substr($input, $offset);
         $result = preg_split("/[$match]+/", $input);
         return $result ? array_shift($result) : false;
    }      
    
    /**
     * return anything within specified balanced brackets, including any nested brackets
     * @param string $input          source string
     * @param string $open           opening bracket. Default: '('
     * @param string $close          closing bracket. Default: ')'
     */
    public static function getNest($input, $open = "(", $close = ")"){
         
         $pattern = '#\\'.$open.'((?>[^\\'.$open.'[\\'.$close.']+)|(?R))*\\'.$close.'#';
         $count = preg_match_all($pattern, $input, $matches);
         
         if($count > 0)
                return $matches[0][0];
         
    return false;
    }
    
    /**
     * return position of first occurance of any of the specified $needles in $haystack or false.
     * @param string $haystack        string to search in
     * @param array $needles          array of strings to search for
     * @param integer $offset         start search at this offset. (Optional)
     * @param string $match           Set to found $needle on success. (Referenced)
     * @return booleany|integer
     */
    public static function strpos($haystack, $needles, $offset = 0, &$match = ""){
         if(!is_array($needles))
             $needles = array($needles);
         
         $found = false;
         foreach($needles as $needle){
                $pos = strpos($haystack, $needle, $offset);
                
                if($pos !== false && ($found === false || $pos < $found)){
                    $found = $pos;
                    $match = $needle;
                }
         }
         
         return $found;
    }	
	
}