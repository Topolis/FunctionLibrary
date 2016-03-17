<?php

namespace Topolis\FunctionLibrary;

class Path{

    const SEPARATOR_LINUX   = "/";
    const SEPARATOR_WINDOWS = "\\";
    const SEPARATOR_BOTH    = "/\\";

    const ALLOWED_CHARS = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890.-_:";

    /**
     * Resolve a path and return a cleaned real path without checking physical files on any device
     *
     * @static
     * @param        $input
     * @param string $separator
     * @return string
     * @throws \Exception
     */
    public static function real($input, $separator = self::SEPARATOR_BOTH){
        $parsed = self::parse($input, $separator);
        if(!self::validate($parsed["path"]))
            throw new \Exception("Path is invalid");

        $elements_in =  self::explode($parsed["path"], $separator);
        $elements_out = array();
        $absolute = self::isAbsolute($input);

        if(self::isAbsolute($input))
            array_push($elements_out, null);

        foreach($elements_in as $idx => $element){
            switch($element){
                case "":
                case ".":
                    break;
                case "..":
                    if(count($elements_out) > 0 && end($elements_out) != "..")
                        array_pop($elements_out);
                    elseif(!$absolute)
                        array_push($elements_out, "..");
                    else
                        throw new \Exception("Can not move to parent of root folder in an absolute path");
                    break;
                default:
                    array_push($elements_out, $element);
            }
        }

        return $parsed["device"].implode($separator==self::SEPARATOR_BOTH? self::SEPARATOR_LINUX : $separator, $elements_out);
    }

    public static function isAbsolute($input, $separator = self::SEPARATOR_BOTH){
        return preg_match("/^([A-Za-z]+:)?[".addcslashes($separator, "[].-_/\\")."]{1,1}/", $input) ? true : false;
    }

    /**
     * Check if a path contains valid characters
     *
     * @static
     * @param        $input
     * @param string $separator
     * @return bool
     */
    public static function validate($input, $separator = self::SEPARATOR_BOTH){

        $pattern = addcslashes(self::ALLOWED_CHARS . $separator, "[].-_/\\");
        $invalid = preg_match("/[^".$pattern."]+/", $input);

        return $invalid ? false : true;
    }

    /**
     * concatenate $input to $jail pat
     * Return false if evaluated path sits outside of jail (eg by containing to many "../" parts)
     *
     * @static
     * @param        $input
     * @param        $jail
     * @param string $separator
     * @return bool
     * @throws \Exception
     */
    public static function jail($input, $jail, $separator = self::SEPARATOR_BOTH){
        if(self::isAbsolute($input))
            return false;

        if(!self::isAbsolute($jail))
            throw new \Exception("Jail path needs to be absolute");

        $path = self::real($jail, $separator)
              . ($separator==self::SEPARATOR_BOTH? self::SEPARATOR_LINUX : $separator)
              . self::real($input, $separator);

        return self::in($path, $jail, $separator);
    }

    /**
     * Check if $path is inside $container
     *
     * @static
     * @param $path
     * @param $container
     * @param $separator
     * @return bool
     */
    public static function in($path, $container, $separator = self::SEPARATOR_BOTH){

        $path = self::real($path, $separator);
        $container  = self::real($container, $separator);
        return strpos($path, $container) === 0;
    }

    /**
     * Explode a path into it's components by specified separator
     * @param $path
     * @param $separator
     * @return array
     */
    public static function explode($path, $separator){
        return preg_split("/[".addcslashes($separator, "[].-_/\\")."]{1,1}/", $path);
    }

    public static function parse($path, $separator = self::SEPARATOR_LINUX){
        $valid = preg_match('/^((?:[A-Za-z0-9]+\:|))(['. addcslashes(self::ALLOWED_CHARS.$separator, "[].-_/\\") .']*)$/', $path, $matches);

        if(!$valid)
            return false;

        $result = array("device" => $matches[1],
                        "path" => $matches[2] );

        return $result;

    }

    /**
     * Return the extension of a file or false on invalid filenames
     * @static
     * @param string $file
     * @param string $separator
     * @return bool|string
     */
    public static function extension($file, $separator = self::SEPARATOR_BOTH){
        return self::validate($file, $separator) ? substr($file, strrpos($file, '.') + 1) : false;
    }
}