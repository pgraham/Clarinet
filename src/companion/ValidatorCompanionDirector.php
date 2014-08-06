<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Clarinet. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\orm\companion;

use \zpt\orm\model\Model;
use \zpt\orm\model\ModelFactory;
use \zpt\orm\BaseModelCompanionDirector;

/**
 * Generator for model validator classes.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ValidatorCompanionDirector extends BaseModelCompanionDirector
{

  public function __construct(ModelFactory $modelFactory = null) {
    parent::__construct('validator', $modelFactory);
  }

  public function getTemplatePath() {
    return __DIR__ . '/validator.tmpl.php';
  }

  /**
   * Generates the PHP Code for a validator actor for the given model
   * structure.
   *
   * @param Model $model Information about the model for which a validator
   *   is to be generated.
   * @return string The PHP code for a validator.
   */
  public function getValuesForModel(Model $model) {
    $properties = array();

    foreach ($model->getProperties() AS $property) {
      $prop = array();
      $prop['name'] = $property->getName();
      $prop['type'] = $property->getType();
      if ($property->isEnumerated()) {
        $prop['values'] = $property->getValues();
      }

      $prop['notNull'] = $property->notNull();

      $properties[] = $prop;
    }

    return array(
      'class'     => $model->getName(),
      'properties' => $properties
    );
  }
}
