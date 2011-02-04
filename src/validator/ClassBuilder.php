<?php
/**
 * =============================================================================
 * Copyright (c) 2011, Philip Graham
 * All rights reserved.
 *
 * This file is part of Clarinet and is licensed by the Copyright holder under
 * the 3-clause BSD License.  The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package clarinet/generator
 */
namespace clarinet\validator;

use \clarinet\TemplateLoader;

/**
 * This class generates the PHP code for a validator actor for a specific model
 * class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ClassBuilder {

  self::$_templateLoader = new TemplateLoader(__DIR__);

  /**
   * Generate a validator actor for the given model information.
   *
   * @param array $modelInfo The model's structure information as parsed by
   *   ModelParser::parse(...).
   * @return string The model's validator's class body.
   */
  public static function build(Array $modelInfo) {
    $templateValues = self::_buildTemplateValues($modelInfo);

    // Load templates
    $validate = self::$_templateLoader->load('validate', $templateValues);

    // Put it all together
    $templateValues['${validate}'] = $validate;

    $body = self::$_templateLoader->load('class', $templateValues);
    return $body;
  }

  /*
   * This method uses the parsed model info to create the values to insert into
   * the validator's template.
   */
  private static function _buildTemplateValues(Array $modelInfo) {
    $propertyGetters    = Array();
    $propertyCheckers   = Array();
    $propertyValidators = Array();

    foreach ($modelInfo['properties'] AS $property) {
      $prop = $property['name'];
      $var  = lcfirst($prop);

      // If the property is enumerated 
      if (isset($property['enumerated'])) {
        if (!isset($propertyGetters[$prop])) {
          $propertyGetters[$prop] = "    \$$var = \$model->get$prop();";
        }

        // Load the code fragment that calls the check method
        $values = Array
        (
          '${method_name}' => "_checkEnum$prop",
          '${var_name}'    => "\$$var"
        );
        $propertyCheckers[] = self::$_templateLoader::load('checker', $values);

        // Load the enum check method
        $values = Array
        (
          '${model}'    => $modelInfo['class'],
          '${property}' => $prop,
          '${var_name}' => "\$$var",
          '${values}'   => implode(',', $property['enumerated']['values'])
        );
        $propertyValidators[] = self::$_templateLoader::load('enum', $values);
      }
    }

    $templateValues = Array
    (
      '${class}'     => $modelInfo['class'],
      '${actor}'     => $modelInfo['actor'],

      '${property_getters}'    => implode("\n", $propertyGetters),
      '${property_checkers}'   => implode("\n", $propertyCheckers),
      '${property_validators}' => implode("\n", $propertyValidators)
    );
    return $templateValues;
  }
}
