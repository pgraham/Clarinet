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
use \zpt\orm\ModelCompanionGenerator;

/**
 * Generator for model validator classes.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ValidatorGenerator extends ModelCompanionGenerator {

  public static $actorNamespace = 'zpt\dyn\orm\validator';

  protected function getTemplatePath() {
    return __DIR__ . '/validator/validator.tmpl.php';
  }

  /**
   * Generates the PHP Code for a validator actor for the given model
   * structure.
   *
   * @param Model $model Information about the model for which a validator
   *   is to be generated.
   * @return string The PHP code for a validator.
   */
  protected function getValuesForModel(Model $model) {
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
      'class'     => $model->getClass(),
      'actor'     => $model->getActor(),

      'properties' => $properties
    );
  }
}
