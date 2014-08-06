<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Clarinet. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\orm\companion;

use \zpt\orm\BaseModelCompanionDirector;
use \zpt\orm\model\Model;
use \zpt\orm\model\ModelFactory;
use \zpt\orm\model\Property;
use \zpt\util\StringUtils;

/**
 * Generator for model transformer classes.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class TransformerCompanionDirector extends BaseModelCompanionDirector
{

  public function __construct(ModelFactory $modelFactory = null) {
    parent::__construct('transformer', $modelFactory);
  }

  public function getTemplatePath() {
    return __DIR__ . '/transformer.tmpl.php';
  }

  /**
   * Generates the PHP Code for a transformer companion for the given
   * {@link Model}.
   *
   * @param Model $model Information about the model for which a transformer
   *   is to be generated.
   * @return string The PHP code for a transformer.
   */
  public function getValuesForModel(Model $model) {
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
        'rhs'           => $rhs->getName(),
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
      'class'           => $model->getName(),
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
