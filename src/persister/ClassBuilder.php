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
namespace clarinet\persister;

use \clarinet\model\Model;
use \reed\generator\CodeTemplateLoader;

/**
 * This class generates the PHP code for a persister class given the table
 * structure for the persisted class.
 *
 * @author Philip Graham
 */
class ClassBuilder {

  /**
   * Generate a persister class given an entities table/class structure.
   *
   * @param Model $model Structure information about the the entity for
   *     which a persister will be generated.
   * @return The persister's PHP code.
   */
  public static function build(Model $model) {
    $templateValues = self::_buildTemplateValues($model);

    $templateLoader = CodeTemplateLoader::get(__DIR__);
    $body = $templateLoader->load('class', $templateValues);
    return $body;
  }

  /*
   * This method uses a parsed model info array structure to create the values
   * to insert into a persister template.
   */
  private static function _buildTemplateValues(Model $model) {
    $className = $model->getClass();
    $persisterName = str_replace('\\', '_', $className);

    $columnNames = Array();
    $valueNames = Array();
    $sqlSetters = Array();
    $populateParameters = Array();
    $populateProperties = Array();
    foreach ($model->getProperties() AS $property) {
      $propBuilder = new PropertyBuilder($property);

      $prop = $property->getName();
      $col  = $property->getColumn();

      $columnNames[] = $col;
      $valueNames[] = ":$col";
      $sqlSetters[] = "$col = :$col";

      $populateParameters[] = $propBuilder->populateIntoDb('params', 'model');
      $populateProperties[] = $propBuilder->populateFromDb('model', 'row');
    }

    $populateRelationships = Array();
    $saveRelationships = Array();
    $deleteRelationships = Array();
    foreach ($model->getRelationships() AS $relationship) {
      $relationshipBuilder = new RelationshipBuilder($relationship);
      $populateRelationship = $relationshipBuilder->getRetrieveCode();
      if ($populateRelationship !== null) {
        $populateRelationships[] = $populateRelationship;
      }

      $populateParameter = $relationshipBuilder->getSaveLhsCode();
      if ($populateParameter !== null) {
        $populateParameters[] = $populateParameter;
      }

      $saveRelationship = $relationshipBuilder->getSaveRhsCode();
      if ($saveRelationship !== null) {
        $saveRelationships[] = $saveRelationship;
      }

      $deleteRelationship = $relationshipBuilder->getDeleteCode();
      if ($deleteRelationship !== null) {
        $deleteRelationships[] = $deleteRelationship;
      }

      $columnName = $relationship->getLhsColumn();
      if ($columnName !== null) {
        $columnNames[] = $columnName;
        $valueNames[] = ":$columnName";
        $sqlSetters[] = "$columnName = :$columnName";
      }
    }

    $templateValues = Array
    (
      'class'                  => $className,
      'class_str'              => str_replace('\\', '\\\\', $className),

      'actor'                  => $model->getActor(),
      'table'                  => $model->getTable(),

      'id_property'            => $model->getId()->getName(),
      'id_column'              => $model->getId()->getColumn(),

      'column_names'           => $columnNames,
      'value_names'            => $valueNames,
      'sql_setters'            => $sqlSetters,
      'populate_parameters'    => $populateParameters,
      'populate_properties'    => $populateProperties,
      'populate_relationships' => $populateRelationships,
      'save_relationships'     => $saveRelationships,
      'delete_relationships'   => $deleteRelationships
    );
    return $templateValues;
  }
}
