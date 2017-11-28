<?php

namespace Topolis\FunctionLibrary;

use \Exception;

class Collection {

    /**
     * get a value from a multi dimensional tree-like array structure via a path string (ex.: "folder.folder.key")
     * @param array $array array to search
     * @param string $path path to traverse
     * @param string $separator (Optional) separator in path. Default: "."
     * @return array|mixed
     * @throws Exception         if a node from $path is not found in $array
     */
	public static function getFromPath($array, $path, $separator = "."){
		
        $nodes = explode($separator, $path);
        while($path && count($nodes) > 0){
            $node = array_shift($nodes);
            
            if(!is_array($array) || !isset($array[$node])){
                throw new Exception(__METHOD__." - Not an array or node '$node' from path '$path' not found");
            }
            
            $array = $array[$node];
        }
        
        return $array;
	}

    /**
     * set a value from a multi dimensional tree-like array structure via a path string (ex.: "folder.folder.key")
     * creating any needed array elements on the way.
     * @param array $array       array to search
     * @param string $path       path to traverse
     * @param mixed $value       value to set. (if you set an array, it will be traversable via path too)
     * @param string $separator  (Optional) separator in path. Default: "."
     */
	public static function setFromPath(&$array, $path, $value, $separator = "."){
        $nodes = explode($separator, $path);
        $array = self::setFromPath_r($array, $nodes, $value);
    }
    protected static function setFromPath_r($array, &$nodes, $value){
    	if(!is_array($array))
    	    $array = array();
    	    
    	$node = array_shift($nodes);
    	
    	if(count($nodes) < 1)
    	    $array[$node] = $value;
    	else{
    		if(!isset($array[$node]))
    		    $array[$node] = array();
    	    $array[$node] = self::setFromPath_r($array[$node], $nodes, $value);
    	}
    	
    	return $array;
    }

    /**
     * add a value (optionally with non-numeric key) to array at path
     * @param array  $array       array to search
     * @param string $path        path to traverse to target array
     * @param mixed  $value       value to set. (if you set an array, it will be traversable via path too)
     * @param mixed  $key         (Optional) key to use instead of auto incremented numeric key. Default: null
     * @param string $separator   (Optional) separator in path. Default: "."
     * @throws \Exception
     */
	public static function addFromPath(&$array, $path, $value, $key = null, $separator = "."){
        
	    try{
	        $target = self::getFromPath($array, $path, $separator);
	    } catch(Exception $e) {
	        $target = array();
	    }
        
        if(!is_array($target))
            throw new Exception(__METHOD__." - element at path '$path' is no array");
        
        if($key !== null)
            $target[$key] = $value;
        else
            $target[] = $value;
            
        self::setFromPath($array, $path, $target, $separator);
    }
    
    /**
     * union two multidimensional tree-like arrays. Anything in $defaults will be added to $array 
     * @param array $array           base array
     * @param array $defaults        array with additional values
     */
    public static function unionTree(&$array, $defaults){
    	$array = self::unionTree_r($array, $defaults);
    }
    protected static function unionTree_r($array, $defaults){
        if(!is_array($array))
            $array = array();
        
        foreach($defaults as $key => $value){
            if(!isset($array[$key]))
                $array[$key] = $value;
            if(is_array($value))
                $array[$key] = self::unionTree_r($array[$key], $value);
        }
        
        return $array;
    }

    /**
     * sort an multidimensional array by any of it's fields and return sorted array
     * ex.: $sorted = Utility::multisort($data, 'volume', SORT_DESC, 'edition', SORT_ASC);
     * IMPORTANT: This function uses mutlisort and will reindex numeric keys !
     * @return array
     * @internal param array $data array to sort
     * @internal param string $field name of field to sort by
     * @internal param int $direction SORT_DESC or SORT_ASC constant
     */
    public static function multisort(){
        $args = func_get_args();
        $data = array_shift($args);
        
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
            }
        }
        
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);        
    }

    /**
     * get an element from the array (or path) and return $default if not set
     * @param $array
     * @param string $path
     * @param mixed $default default value
     * @return array|mixed|null
     */
    public static function get($array, $path = null, $default = null){
    	try{
    		return Collection::getFromPath($array, $path);
    	}
    	catch(Exception $e) {
    		return $default;
    	}    	
    }

    /**
     * set an element from the array (or path)
     * @param $array
     * @param string $path
     * @param $value
     * @return mixed
     */
    public static function set(&$array, $path, $value){
        Collection::setFromPath($array, $path, $value);
        return $array;
    }

    /**
     * delete an element from the array (or path)
     * @param $array
     * @param string $path
     * @param string $separator (Optional)
     * @return mixed
     */
    public static function remove(&$array, $path, $separator = "."){
        $nodes = explode($separator, $path);
        $key = array_pop($nodes);
        $parent = implode($separator, $nodes);

        $siblings = self::getFromPath($array, $parent, $separator);
        unset($siblings[$key]);
        self::setFromPath($array, $parent, $siblings, $separator);

        return $array;
    }

    public static function add(&$array, $path, $value) {

        Collection::addFromPath($array, $path, $value, $key = null);
        return $array;
    }
    
    public static function unshift(&$array, $value, $key = null){
        $array = array_reverse($array, true);
        if($key)
            $array[$key] = $value;
        else
            $array[] = $value;
        $array = array_reverse($array, true);
    }
    
    public static function ksortTree(&$array, $sortLevel = 0, $thisLevel = 0){
        
        if(!is_array($array))
            return;
        
        if($sortLevel <= $thisLevel)
            ksort($array);
        
        foreach($array as $key => &$value)
            if(is_array($value))
                self::ksortTree($value, $sortLevel, $thisLevel +1);
    }

    /**
     * search for first sub array in $array that has a matching value either in any field or in a field named $field
     * eg: items:
     *         munich:
     *             country: germany
     *             size: 1.3M
     *         london:
     *             country: great britain
     *             size: 2.9M
     * find($items, "germany", "country") returns the array munich
     *
     * @static
     * @param array $array      array to search in
     * @param string $path      path to start search in $array
     * @param mixed $search     value to match
     * @param bool $field       (Optional) match $value only in items named $field
     * @param mixed $default    (Optional) return this value if nothing found
     * @return bool
     */
    public static function find($array, $path, $search, $field = false, $default = false){

        $data = self::get($array, $path, array());

        foreach($data as $item)
            foreach($item as $key => $value)
                if( ($field == false || $field = $key) && $value == $search)
                    return $item;

        return $default;
    }

    /**
     * Return first element of an array at $path
     * @static
     * @param array $array
     * @param string $path
     * @param bool $default
     * @return bool|mixed
     */
    public static function first($array, $path, $default = false){
        $data = self::get($array, $path, array());
        if(count($data) > 0)
            return reset($data);

        return $default;
    }

    /**
     * Implode array like implode() but also display keys.
     * @param array $array
     * @param string $separatorAssign         (Optional) separator between key and value. Default: ": "
     * @param string $separatorElement        (Optional) separator between elements. Default: ", "
     * @param string $encapsulation           (Optional) Value encapsulation. Default: ""
     * @return string
     */
    public static function implodeMap($array, $separatorAssign = ": ", $separatorElement = ", ", $encapsulation = ""){
        $out = array();
        foreach($array as $key => $value){
            $out[] = $key.$separatorAssign.$encapsulation.$value.$encapsulation;
        }
        return implode($separatorElement, $out);
    }
}
