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
use \clarinet\model\Property;

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
    $body = $templateLoader->load('transformer.php', $templateValues);
    return $body;
  }

  /*
   * This method uses a parsed model info array structure to create the values
   * to insert into a transformer template.
   */
  private static function _buildTemplateValues(Model $model) {
    $propertyMap = array();

    $properties  = array();
    $relationshipsToArray = array();
    $relationshipsFromArray = array();

    $id = $model->getId()->getName();;
    $idIdx = strtolower($id);
    $fromDbIdCast = '';
    if ($model->getId()->getType() == Property::TYPE_INTEGER) {
      $fromDbIdCast = '(int) ';
    }
    $propertyMap[] = "'$id' => '$idIdx'";

    foreach ($model->getProperties() AS $property) {
      $prop = $property->getName();
      $idx = strtolower($prop);

      $properties[] = $prop;
      $propertyMap[] = "'$prop' => '$idx'";
    }

    $relationshipBuilder = new RelationshipBuilder();
    foreach ($model->getRelationships() AS $relationship) {
      $relationshipsToArray[] = $relationshipBuilder->buildToArray(
        $relationship);
      $relationshipsFromArray[] = $relationshipBuilder->buildFromArray(
        $relationship);

      $rel = $relationship->getLhsProperty();
      $idx = strtolower($rel);
      $propertyMap[] = "'$rel' => '$idx'";
    }

    $templateValues = Array
    (
      'class'                  => $model->getClass(),
      'actor'                  => $model->getActor(),
      'id'                     => $id,
      'properties'             => $properties,
      'relationshipsToArray'   => $relationshipsToArray,
      'relationshipsFromArray' => $relationshipsFromArray,
      'property_map'           => $propertyMap,
      'from_db_id_cast'        => $fromDbIdCast
    );
    return $templateValues;
  }
}
