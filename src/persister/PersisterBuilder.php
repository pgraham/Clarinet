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

use \ReflectionClass;

use \clarinet\model\Model;
use \reed\generator\CodeTemplateLoader;

/**
 * This class generates the PHP code for a persister class given the table
 * structure for the persisted class.
 *
 * @author Philip Graham
 */
class PersisterBuilder {

  private $_templateLoader;

  public function __construct() {
    $this->_templateLoader = CodeTemplateLoader::get(__DIR__);
  }

  /**
   * Generate a persister class given an entities table/class structure.
   *
   * @param Model $model Structure information about the the entity for
   *     which a persister will be generated.
   * @return The persister's PHP code.
   */
  public function build(Model $model) {
    $templateValues = $this->_buildTemplateValues($model);

    $body = $this->_templateLoader->load('persister.php', $templateValues);
    return $body;
  }

  /*
   * This method uses a parsed model info array structure to create the values
   * to insert into a persister template.
   */
  private function _buildTemplateValues(Model $model) {
    $className = $model->getClass();
    $persisterName = str_replace('\\', '_', $className);

    $columnNames = Array();
    $valueNames = Array();
    $sqlSetters = Array();
    $properties = array();
    $populateProperties = Array();
    foreach ($model->getProperties() AS $property) {
      $propBuilder = new PropertyBuilder($property);

      $name = $property->getName();
      $type = $property->getType();
      $col  = $property->getColumn();

      $properties[] = array(
        'type' => $type,
        'name' => $name,
        'col'  => $col
      );

      $columnNames[] = "`$col`";
      $valueNames[] = ":$col";
      $sqlSetters[] = "`$col` = :$col";

      $populateProperties[] = $propBuilder->populateFromDb('model', 'row');
    }

    $populateRelationships = Array();
    $saveRelationships = Array();
    $deleteRelationships = Array();
    $relationships = array();
    foreach ($model->getRelationships() AS $relationship) {
      $relationships[] = array(
        'type'          => $relationship->getType(),
        'lhs'           => $relationship->getLhs()->getClass(),
        'lhsProperty'   => $relationship->getLhsProperty(),
        'lhsColumn'     => $relationship->getLhsColumn(),
        'rhs'           => $relationship->getRhs()->getClass(),
        'rhsIdProperty' => $relationship->getRhs()->getId()->getName(),
      );

      $relationshipBuilder = new RelationshipBuilder($relationship);
      $populateRelationship = $relationshipBuilder->getRetrieveCode();
      if ($populateRelationship !== null) {
        $populateRelationships[] = $populateRelationship;
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
        $columnNames[] = "`$columnName`";
        $valueNames[] = ":$columnName";
        $sqlSetters[] = "`$columnName` = :$columnName";
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

      'properties'             => $properties,
      'relationships'          => $relationships,

      'column_names'           => $columnNames,
      'value_names'            => $valueNames,
      'sql_setters'            => $sqlSetters,
      'populate_properties'    => $populateProperties,
      'populate_relationships' => $populateRelationships,
      'save_relationships'     => $saveRelationships,
      'delete_relationships'   => $deleteRelationships
    );

    // Add booleans for callbacks
    $modelClass = new ReflectionClass($model->getClass());

    if ($modelClass->hasMethod('beforeCreate')) {
      $templateValues['beforeCreate'] = true;
    }
    if ($modelClass->hasMethod('onCreate')) {
      $templateValues['onCreate'] = true;
    }

    if ($modelClass->hasMethod('beforeUpdate')) {
      $templateValues['beforeUpdate'] = true;
    }
    if ($modelClass->hasMethod('onUpdate')) {
      $templateValues['onUpdate'] = true;
    }

    if ($modelClass->hasMethod('beforeDelete')) {
      $templateValues['beforeDelete'] = true;
    }
    if ($modelClass->hasMethod('onDelete')) {
      $templateValues['onDelete'] = true;
    }

    // If the model doesn't define any columns (only relationships) then don't
    // generate an UPDATE statement as it will result in an SQL error
    if (count($sqlSetters) > 0) {
      $templateValues['has_update'] = true;
    }
    return $templateValues;
  }
}
