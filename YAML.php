<?php
/**
 * This library is based on old libraries of sfYaml and tries to enhance the readability and customizability of the generated code
 * It still relies on these sfYaml libraries
 * @author ToBe
 *
 * sfYaml sources:
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    sfYamlDumper.class.php 10575 2008-08-01 13:08:42Z nicolas
 */

namespace Topolis\FunctionLibrary;

require_once "sfYaml/sfYamlInline.php";
use \sfYamlInline;

class YAML{

    /**
   * Dumps a PHP value to YAML.
   *
   * @param  mixed   $input  The PHP value
   * @param  integer $indentation The level where you switch to inline YAML
   * @param  integer $inline The level where you switch to inline YAML
   * @param  integer $level The level o indentation indentation (used internally)
   *
   * @return string  The YAML representation of the PHP value
   */
  public static function encode($input, $indentation = 4, $keyspace = 30, $returnLevel = 1, $inline = 10, $level = 0)
  {
    $output = "";  
    $prefix = str_repeat(' ', $indentation * $level);

    // Inline JSON values
    if ($inline <= 0 || !is_array($input) || empty($input)) {
      $output .= $prefix.sfYamlInline::dump($input);
    }
    // Normal Yaml values
    else {
      $isAHash = array_keys($input) !== range(0, count($input) - 1);

      foreach ($input as $key => $value) {
        $willBeInlined = $level > $inline || !is_array($value) || empty($value);

        $keycolumn = $prefix
                   . ($isAHash ? sfYamlInline::dump($key).':' : '-');
        $keycolumn = str_pad($keycolumn, $keyspace, ' ')
                   . ($willBeInlined ? '' : "\n");
        $valcolumn = self::encode($value, $indentation, $keyspace, $returnLevel, $inline - 1, $willBeInlined ? 0 : $level + 1)
                   . ($willBeInlined ? "\n" : '');
        
        $output .= $keycolumn
                 . $valcolumn
                 . ($level < $returnLevel ? "\n" : '');
      }
    }

    return $output;
  }
}
