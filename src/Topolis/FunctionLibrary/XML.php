<?php

namespace Topolis\FunctionLibrary;

use \SimpleXMLElement;

class XML{
    
    public static function searchNodeAttributes($searched, SimpleXMLElement $xml){
        $result = array();

        //Children
        foreach($xml->children() as $child){
            if($child->getName() != $searched)
                continue;
            
            $result[] = self::exportAttributes($child);        
        }
        return $result;
    }
    
    public static function copy(SimpleXMLElement $element, SimpleXMLElement $target){
        $toDom = dom_import_simplexml($target);
        $fromDom = dom_import_simplexml($element);
        $copy = $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));      
        return simplexml_import_dom($copy);  
    }
    
    public static function copyChildren(SimpleXMLElement $source, SimpleXMLElement $target){
        foreach($source->children() as $child){
            self::copy($child, $target);
        }
    }   

    public static function mergeChildren(SimpleXMLElement $source, SimpleXMLElement $target){
        foreach($source->children() as $child){
            if(!self::exists($child, $target))
                self::copy($child, $target);
        }
    }

    // FIXME: Faster method?
    public static function exists(SimpleXMLElement $needle, SimpleXMLElement $haystack){
        foreach($haystack as $item){
            if($item->asXML() == $needle->asXML())
                return true;
        }
        return false;
    }
    
    public static function existsWithAttribute(SimpleXMLElement $needle, SimpleXMLElement $haystack, $attribute){
        foreach($haystack as $item){
            if((string)$item[$attribute] == (string)$needle[$attribute])
                return true;
        }
        return false;
    }

    public static function exportAttributes(SimpleXMLElement $node){
        $attributes = array();
        foreach($node->attributes() as $key => $value){
            $attributes[(string)$key] = self::xmlTypeDecode($value);
        }
        return $attributes;
    }    
    
    public static function importAttributes($attributes, SimpleXMLElement $node){
        foreach($attributes as $key => $value){
            $node[$key] = self::xmlTypeEncode($value);
        }
        return $node;
    }

    public static function arraysToNodes($arrays, $name, $target){
        foreach($arrays as $item){
            $node = $target->addChild($name);
            foreach($item as $key => $value){
                $node[$key] = self::xmlTypeEncode($value);
            }
        }
        return $target;
    }

    public static function xmlTypeEncode($value){
        switch(gettype($value)){
            case "boolean":
                return $value ? "true" : "false";
            default:
                return $value;
        }
    }
    
    public static function xmlTypeDecode($value){
        
        if($value == "true")
            return true;

        if($value == "false")
            return false;
        
        if(is_numeric($value))
            return $value+0;
        
        return (string)$value;
    }    
}
