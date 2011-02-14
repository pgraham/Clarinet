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

use \clarinet\model\Info;
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
   * @param Info $modelInfo Structure information about the the entity for
   *     which a persister will be generated.
   * @return The persister's PHP code.
   */
  public static function build(Info $modelInfo) {
    $templateValues = self::_buildTemplateValues($modelInfo);

    $templateLoader = TemplateLoader::get(__DIR__);
    $body = $templateLoader->load('class', $templateValues);
    return $body;
  }

  /*
   * This method uses a parsed model info array structure to create the values
   * to insert into a persister template.
   */
  private static function _buildTemplateValues(Info $modelInfo) {
    $persisterName = str_replace('\\', '_', $modelInfo->getClass());

    $columnNames = Array();
    $valueNames = Array();
    $sqlSetters = Array();
    $populateParameters = Array();
    $populateProperties = Array();
    foreach ($modelInfo->getProperties() AS $property) {
      $prop = $property->getName();
      $col  = $property->getColumn();

      $columnNames[] = $col;
      $valueNames[] = ":$col";
      $sqlSetters[] = "$col = :$col";

      $populateParameters[] = "    \$params[':$col'] ="
        . " \$model->get$prop();";
      $populateProperties[] = "      \$model->set$prop("
        . "\$row['$col']);";
    }

    $populateRelationships = Array();
    $saveRelationships = Array();
    $deleteRelationships = Array();
    foreach ($modelInfo->getRelationships() AS $relationship) {
      $populateRelationship = $relationship->getPopulateModelCode();
      if ($populateRelationship !== null) {
        $populateRelationships[] = $populateRelationship;
      }

      $populateParameter = $relationship->getPopulateParameterCode();
      if ($populateParameter !== null) {
        $populateParameters[] = $populateParameter;
      }

      $saveRelationship = $relationship->getSaveCode();
      if ($saveRelationship !== null) {
        $saveRelationships[] = $saveRelationship;
      }

      $deleteRelationship = $relationship->getDeleteCode();
      if ($deleteRelationship !== null) {
        $deleteRelationships[] = $deleteRelationship;
      }

      $columnName = $relationship->getLhsColumnName();
      if ($columnName !== null) {
        $columnNames[] = $columnName;
        $valueNames[] = ":$columnName";
        $sqlSetters[] = "$columnName = :$columnName";
      }
    }

    $templateValues = Array
    (
      '${class}'                  => $modelInfo->getClass(),
      '${actor}'                  => $modelInfo->getActor(),
      '${table}'                  => $modelInfo->getTable(),

      '${id_property}'            => $modelInfo->getId()->getName(),
      '${id_column}'              => $modelInfo->getId()->getColumn(),

      '${column_names}'           => implode(',', $columnNames),
      '${value_names}'            => implode(',', $valueNames),
      '${sql_setters}'            => implode(',', $sqlSetters),
      '${populate_parameters}'    => implode("\n", $populateParameters),
      '${populate_properties}'    => implode("\n", $populateProperties),
      '${populate_relationships}' => implode("\n\n", $populateRelationships),
      '${save_relationships}'     => implode("\n\n", $saveRelationships),
      '${delete_relationships}'   => implode("\n\n", $deleteRelationships),

      '${class_str}'              => str_replace('\\', '\\\\',
                                       $modelInfo->getClass())
    );
    return $templateValues;
  }
}
