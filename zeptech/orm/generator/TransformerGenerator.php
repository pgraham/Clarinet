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
namespace zeptech\orm\generator;

use \zeptech\orm\generator\model\Model;
use \zeptech\orm\generator\model\Property;
use \zeptech\orm\generator\model\Relationship;
use \zpt\util\StringUtils;

/**
 * Generator for model transformer classes.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class TransformerGenerator extends AbstractModelGenerator {

  public static $actorNamespace = 'zpt\dyn\orm\transformer';

  protected function getTemplatePath() {
    return __DIR__ . '/transformer/transformer.php';
  }

  /**
   * Generates the PHP Code for a transformer actor for the given model
   * structure.
   *
   * @param Model $model Information about the model for which a transformer
   *   is to be generated.
   * @return string The PHP code for a transformer.
   */
  protected function getValuesForModel(Model $model) {
    $id = $model->getId()->getName();;

    $properties  = array();
    foreach ($model->getProperties() AS $property) {
      $propertyValues = array(
        'id'   => $property->getName(),
        'type' => $property->getType()
      );

      $default = $property->getDefault();
      if ($default !== null) {
        $propertyValues['default'] = $default;
      }

      $properties[] = $propertyValues;
    }

    $collections = array();
    foreach ($model->getCollections() as $col) {
      $collections[] = $col->asArray();
    }

    $relationships = array();
    foreach ($model->getRelationships() AS $relationship) {
      $lhsProp = $relationship->getLhsProperty();
      $rhs = $relationship->getRhs();
      $type = $relationship->getType();

      $rel = array(
        'type'          => $type,
        'name'          => $lhsProp,
        'idx'           => $lhsProp,
        'rhs'           => $rhs->getClass(),
        'rhsIdProperty' => $rhs->getId()->getName(),
      );

      $relationships[] = $rel;
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
      'idIdx'           => StringUtils::fromCamelCase($id),
      'properties'      => $properties,
      'collections'     => $collections,
      'relationships'   => $relationships,
      'from_db_id_cast' => $fromDbIdCast
    );
    return $templateValues;
  }
}
