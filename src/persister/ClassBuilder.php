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
namespace clarinet\persister;

use \clarinet\TemplateLoader;

/**
 * This class generates the PHP code for a persister class given the table
 * structure for the persisted class.
 *
 * @author Philip Graham
 * @package clarinet/generator
 */
class ClassBuilder {

  /**
   * Generate a persister class given an entities table/class structure.
   *
   * The structure of the given entity info array is as follows:
   *
   *  Array
   *  (
   *    [class] => The name of the model class
   *
   *    [table] => The name of the table in the database in which entities are
   *               persisted
   *
   *    [properties] => Array -- The entity's properties
   *      (
   *        [idx] => Array
   *          (
   *            [name]   => The camel case name of the property used in its
   *                        getters and setters.
   *
   *            [column] => The name of the column in the database table for the
   *                        property
   *          ), ...
   *      )
   *  )
   *
   * @param array $entityInfo Structure information about the the entity for
   *     which a persister will be generated.
   * @return The persister's PHP code.
   */
  public static function build(Array $modelInfo) {
    $templateValues = self::_buildTemplateValues($modelInfo);

    // Load templates
    $templateLoader = new TemplateLoader(__DIR__);
    $create   = $templateLoader->load('create',   $templateValues);
    $retrieve = $templateLoader->load('retrieve', $templateValues);
    $update   = $templateLoader->load('update',   $templateValues);
    $delete   = $templateLoader->load('delete',   $templateValues);

    // Put it all together
    $templateValues['${create}']   = $create;
    $templateValues['${retrieve}'] = $retrieve;
    $templateValues['${update}']   = $update;
    $templateValues['${delete}']   = $delete;

    $body = $templateLoader->load('class', $templateValues);
    return $body;
  }

  /*
   * This method uses a parsed model info array structure to create the values
   * to insert into a persister template.
   */
  private static function _buildTemplateValues(Array $modelInfo) {
    $persisterName = str_replace('\\', '_', $modelInfo['class']);

    $populateIdParameter = "    \$params[':id'] ="
      ." \$model->get{$modelInfo['id']['name']}();";
    $populateIdProperty = "      \$model->set{$modelInfo['id']['name']}("
      . "\$row['{$modelInfo['id']['column']}']);";

    $columnNames = Array();
    $valueNames = Array();
    $sqlSetters = Array();
    $populateParameters = Array();
    $populateProperties = Array();
    foreach ($modelInfo['properties'] AS $property) {
      $columnNames[] = $property['column'];
      $valueNames[] = ':' . $property['column'];
      $sqlSetters[] = $property['column'] . ' = :' . $property['column'];

      $populateParameters[] = "    \$params[':{$property['column']}'] ="
        . " \$model->get{$property['name']}();";
      $populateProperties[] = "      \$model->set{$property['name']}("
        . "\$row['{$property['column']}']);";
    }

    $templateValues = Array
    (
      '${class}'                 => $modelInfo['class'],
      '${persisterName}'         => $persisterName,
      '${table}'                 => $modelInfo['table'],

      '${id_column}'             => $modelInfo['id']['column'],
      '${populate_id_parameter}' => $populateIdParameter,
      '${populate_id_property}'  => $populateIdProperty,

      '${column_names}'          => implode(',', $columnNames),
      '${value_names}'           => implode(',', $valueNames),
      '${sql_setters}'           => implode(',', $sqlSetters),
      '${populate_parameters}'   => implode("\n", $populateParameters),
      '${populate_properties}'   => implode("\n", $populateProperties)
    );
    return $templateValues;
  }
}
