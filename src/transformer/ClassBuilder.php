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
 */
namespace clarinet\transformer;

use \clarinet\model\Model;
use \reed\generator\CodeTemplateLoader;

/**
 * This class generates the PHP code for a transformer class for model classes.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ClassBuilder {

  /**
   * Generate a transformer class for the given model information.
   *
   * @param Model $model The model's structure information as parsed by
   *   clarinet\model\Parser::getModel(...).
   * @return string The model's transformer's class body.
   */
  public static function build(Model $model) {
    $templateValues = self::_buildTemplateValues($model);

    // Load templates
    $templateLoader = CodeTemplateLoader::get(__DIR__);
    $asArray   = $templateLoader->load('asArray',   $templateValues);
    $fromArray = $templateLoader->load('fromArray', $templateValues);

    // Put it all together
    $templateValues['${asArray}'] = $asArray;
    $templateValues['${fromArray}'] = $fromArray;

    $body = $templateLoader->load('class', $templateValues);
    return $body;
  }

  /*
   * This method uses a parsed model info array structure to create the values
   * to insert into a transformer template.
   */
  private static function _buildTemplateValues(Model $model) {
    $properties  = array();
    $propertyMap = array();

    $id = $model->getId()->getName();;
    $idIdx = strtolower($id);
    $properties[] = $id;
    $propertyMap[] = "'$id' => '$idIdx'";

    foreach ($model->getProperties() AS $property) {
      $prop = $property->getName();
      $idx = strtolower($prop);

      $properties[] = $prop;
      $propertyMap[] = "'$prop' => '$idx'";
    }

    $templateValues = Array
    (
      'class'        => $model->getClass(),
      'actor'        => $model->getActor(),
      'properties'   => $properties,
      'property_map' => $propertyMap
    );
    return $templateValues;
  }
}
