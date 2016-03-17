<?php

namespace Topolis\FunctionLibrary;

/**
 * Binary type
 * @package Binary
 * @author tbulla
 */

/**
 * store unlimited number of bits and perform binary operations on them
 * @package Binary
 * @author tbulla
 *
 */
class Bitmask
{
   protected $data = array();
   
   /**
    * create new binary either from cloning or from hexadecimal string
    * @param mixed $input      Binary or high endian hexadecimal string
    */
   public function __construct($input="")
   {
      if($input instanceof Bitmask)
         $this->data = $input->get_raw();
      elseif(strlen($input) > 0)
         $this->set($input);
   }
   
   /**
    * set bit at position $idx to true
    * @param int $idx
    */
   public function bit_set($idx) // Set some bit
   {
      $byte = (int)floor($idx/8);
      $bit = $idx % 8;
      $this->data[$byte] = $this->data[$byte] | pow(2,$bit);
   }
   
   /**
    * set bit at position $idx to false
    * @param int $idx
    */
   public function bit_remove($idx) // Remove some bit
   {
      $byte = (int)floor($idx/8);
      $bit = $idx % 8;
      $this->data[$byte] = $this->data[$byte] &~ pow(2,$bit);
   }
   
   /**
    * invert value of bit at position $idx
    * @param int $idx
    */
   public function bit_toggle($idx) // Toggle some bit
   {
      $byte = (int)floor($idx/8);
      $bit = $idx % 8;
      $this->data[$byte] = $this->data[$byte] ^ pow(2,$bit);
   }
   
   /**
    * get value of bit at position $idx
    * @param int $idx
    * @return boolean
    */
   public function bit_get($idx) // Read some bit
   {
      $byte = (int)floor($idx/8);
      $bit = $idx % 8;
      return (($this->data[$byte] & pow(2,$bit)) > 0)+0;
   }

   /**
    * purge binary data and reset all values
    */
   public function clear()
   {
      $this->data = array();
   }
   
   /**
    * perform and operation with another Binary object
    * @param Bitmask $Binary
    */
   public function set_and(Bitmask $Binary)
   {
      if(!($Binary instanceof Bitmask)) return;
      $data = $Binary->get_raw();

      foreach($data as $Pos => $Byte)
         $this->data[$Pos] = @$this->data[$Pos] & $Byte;
   }

   /**
    * Perform or operation with another Binary object
    * @param Bitmask $Binary
    */
   public function set_or(Bitmask $Binary)
   {
      if(!($Binary instanceof Bitmask)) return;
      $data = $Binary->get_raw();

      foreach($data as $Pos => $Byte)
         $this->data[$Pos] = @$this->data[$Pos] | $Byte;
   }

   /**
    * Perform xor operation with another Binary object
    * @param $Binary
    */
   public function set_xor(Bitmask $Binary)
   {
      if(!($Binary instanceof Bitmask)) return;
      $data = $Binary->get_raw();

      foreach($data as $Pos => $Byte)
         $this->data[$Pos] = @$this->data[$Pos] ^ $Byte;
   }

   /**
    * Perform nand operation with another Binary object
    * @param Bitmask $Binary
    */
   public function set_nand(Bitmask $Binary)
   {
      if(!($Binary instanceof Bitmask)) return;
      $data = $Binary->get_raw();

      foreach($data as $Pos => $Byte)
         $this->data[$Pos] = @$this->data[$Pos] &~ $Byte;
   }

   /**
    * set value by high endian hexadecimal string
    * @param string $input
    */
   public function set($input)
   {
      $this->data = $this->hex2data($input);
   }
   
   /**
    * return value as high endian hexadecimal string
    * @return string
    */
   public function get()
   {
      $temp = $this->data2hex($this->data); 
      return $temp;
   }
   
   /**
    * return internal data array
    * @return array
    */
   public function get_raw()
   {
      return $this->data;
   }   
   
   /**
    * return value as binary string
    * @return string
    */
   public function to_bin()
   {
         $out = "";
      for ($i=0; $i<$this->dataLength($this->data); $i++)
         $out = sprintf("%08b",$this->data[$i]).$out;
      return $out;      
   }

   /**
    * return value as integer (might cause integer size violations!)
    * @return int
    */
   public function to_dec()
   {
         $out = 0;
      for ($i=0; $i<$this->dataLength($this->data); $i++)
         $out += $this->data[$i];
      return $out;        
   }   
   
   /**
    * return value as string in binary form
    * @return string
    */
   public function __toString()
   {
      return $this->to_bin();
   }
   
   /**
    * convert array of two byte integers to high endian hexadecimal string
    * @param array $temp
    * @return string
    */
   protected function data2hex ($temp)
   {
      $data = "";
      for ($i=0; $i<$this->dataLength($temp); $i++)
         $data.=sprintf("%02X",$temp[$i]);
      return $data;
   }
   
   /**
    * convert high endian hexadecimal string to array of two byte integers
    * @param string $temp
    * @return array
    */
   protected function hex2data($temp)
   {
      $data = array();
      $len = strlen($temp);
      for ($i=0;$i<$len;$i+=2)
         $data[(int)($i/2)]= hexdec(substr($temp,$i,2));
      return $data;
   }
   
   /**
    * return index of highest used byte.
    * (A plain count would wrongly return 2 if we only used byte 0 and byte 4)
    * @param array $temp
    * @return int
    */
   protected function dataLength($temp)
   {
      if(count($temp) == 0) return 0;
      
      $arKeys = array_keys($temp);
      sort($arKeys);
      $dataLength = $arKeys[count($arKeys)-1];
      return $dataLength+1;
   }
}
