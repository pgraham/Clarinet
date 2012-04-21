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
namespace zeptech\orm\generator\transformer;

use \zpt\pct\CodeTemplateParser;
use \zeptech\orm\generator\model\Model;
use \zeptech\orm\generator\model\Property;

/**
 * This class generates the PHP code for a transformer class for model classes.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ClassBuilder {

  private static $_template;

  /**
   * Generate a transformer class for the given model information.
   *
   * @param Model $model The model's structure information as parsed by
   *   clarinet\model\Parser::getModel(...).
   * @return string The model's transformer's class body.
   */
  public static function build(Model $model) {
    if (self::$_template === null) {
      $parser = new CodeTemplateParser();

      $tmpl = file_get_contents(__DIR__ . '/transformer.php');
      self::$_template = $parser->parse($tmpl);
    }

    $values = self::_buildTemplateValues($model);
    return self::$_template->forValues($values);
  }

  /*
   * This method uses a parsed model info array structure to create the values
   * to insert into a transformer template.
   */
  private static function _buildTemplateValues(Model $model) {
    $id = $model->getId()->getName();;

    $properties  = array();
    foreach ($model->getProperties() AS $property) {
      $propertyValues = array(
        'id'   => $property->getIdentifier(),
        'idx'  => $property->getIdentifier(),
        'type' => $property->getType()
      );

      $default = $property->getDefault();
      if ($default !== null) {
        $propertyValues['default'] = $default;
      }

      $properties[] = $propertyValues;
    }

    $relationships = array();
    foreach ($model->getRelationships() AS $relationship) {
      $lhsProp = $relationship->getLhsProperty();
      $rhs = $relationship->getRhs();

      $relationships[] = array(
        'type'          => $relationship->getType(),
        'name'          => $lhsProp,
        'idx'           => $lhsProp,
        'rhs'           => $rhs->getClass(),
        'rhsIdProperty' => $rhs->getId()->getIdentifier()
      );
    }

    $fromDbIdCast = '';
    if ($model->getId()->getType() == Property::TYPE_INTEGER) {
      $fromDbIdCast = '(int) ';
    }

    $templateValues = Array
    (
      'class'           => $model->getClass(),
      'actor'           => $model->getActor(),
      'id'              => $id,
      'idIdx'           => self::_camelCaseToUnderscore($id),
      'properties'      => $properties,
      'relationships'   => $relationships,
      'from_db_id_cast' => $fromDbIdCast
    );
    return $templateValues;
  }

  private static function _camelCaseToUnderscore($s) {
    $s = lcfirst($s);
    return preg_replace_callback('/([A-Z]+)/', function ($matches) {
      // Handle strings in the form CamelABBRCase => camel_abbr_case
      $uc = $matches[1];
      $len = strlen($uc);
      if ($len > 1) {
        return strtolower('_' . substr($uc, 0, -1) . '_' . substr($uc, -1));
      }

      // Simple case CamelCase => camel_case
      return '_' . strtolower($uc);
    }, $s);
  }
}
