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
namespace zeptech\orm\generator\validator;

use \pct\CodeTemplateParser;
use \pct\SubstitutionException;
use \pct\TemplateValues;
use \zeptech\orm\generator\model\Model;
use \Exception;

/**
 * This class generates the PHP code for a validator actor for a specific model
 * class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ClassBuilder {

  private static $_template;

  /**
   * Generate a validator actor for the given model information.
   *
   * @param Model $model The model's structure information as parsed by
   *   ModelParser::parse(...).
   * @return string The model's validator's class body.
   */
  public static function build(Model $model) {
    if (self::$_template === null) {
      $parser = new CodeTemplateParser();

      $tmpl = file_get_contents(__DIR__ . '/validator.tmpl.php');
      self::$_template = $parser->parse($tmpl);
    }
    
    $values = self::_buildTemplateValues($model);
    try {
      return self::$_template->forValues(new TemplateValues($values));
    } catch (SubstitutionException $e) {
      throw new Exception($e->getMessage() . ' at ' . __DIR__ . '/validator.tmpl.php:' . $e->getLineNum());
    }
  }

  /*
   * This method uses the parsed model info to create the values to insert into
   * the validator's template.
   */
  private static function _buildTemplateValues(Model $model) {
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
