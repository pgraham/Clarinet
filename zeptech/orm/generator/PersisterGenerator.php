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
use \ReflectionClass;

/**
 * Generator for model persisters.  Generated code is output into a file at the
 * path given to the Clarinet::init(...) method.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class PersisterGenerator extends AbstractModelGenerator {

  protected static $actorNamespace = 'zeptech\dynamic\orm\persister';

  protected function getTemplatePath() {
    return __DIR__ . '/persister/persister.php';
  }

  /**
   * Generates the PHP Code for a persister actor for the given model
   * structure.
   *
   * @param Model $model Information about the model for which a persister is
   *   to be generated.
   * @return string The PHP code for a persister.
   */
  protected function getValuesForModel(Model $model) {
    $className = $model->getClass();
    $persisterName = str_replace('\\', '_', $className);

    $columnNames = Array();
    $valueNames = Array();
    $sqlSetters = Array();
    $properties = array();
    foreach ($model->getProperties() AS $property) {
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
    }

    $collections = array();
    foreach ($model->getCollections() as $col) {
      $collections[] = $col->asArray();
    }

    $relationships = array();
    foreach ($model->getRelationships() AS $rel) {
      $lhs = $rel->getLhs();
      $rhs = $rel->getRhs();
      $vals = array(
        'type'          => $rel->getType(),
        'lhs'           => $lhs->getClass(),
        'lhsProperty'   => $rel->getLhsProperty(),
        'lhsIdProperty' => $lhs->getId()->getName(),
        'rhs'           => $rhs->getClass(),
        'rhsStr'        => str_replace('\\', '\\\\', $rhs->getClass()),
        'rhsIdProperty' => $rhs->getId()->getName(),
      );

      switch ($rel->getType()) {
        case 'one-to-many':
        $vals['rhsColumn'] = $rel->getRhsColumn();
        $vals['rhsIdColumn'] = $rhs->getId()->getColumn();
        $vals['deleteOrphan'] = $rel->deleteOrphans();

        // Retrieve params
        $orderBy = $rel->getOrderBy();
        if ($orderBy !== null) {
          $vals['orderByCol'] = $orderBy['col'];
          $vals['orderByDir'] = $orderBy['dir'];
        }

        // Save params
        $mirrored = $rhs->hasRelationship('many-to-one', $lhs->getClass());
        $vals['mirrored'] = $mirrored;
        if ($mirrored) {
          $mirrorRel = $rhs->getRelationship('many-to-one', $lhs->getClass());
          $vals['rhsProperty'] = $mirrorRel->getLhsProperty();
        } else {
          $vals['rhsTable'] = $rhs->getTable();
        }
        break;

        case 'many-to-one':
        $lhsCol = $rel->getLhsColumn();
        $vals['lhsColumn'] = $lhsCol;
        $columnNames[] = "`$lhsCol`";
        $valueNames[] = ":$lhsCol";
        $sqlSetters[] = "`$lhsCol` = :$lhsCol";
        break;

        case 'many-to-many':
        $vals['linkTable'] = $relationship->getLinkTable();
        $vals['lhsLinkColumn'] = $relationship->getLinkLhsId();
        $vals['rhsLinkColumn'] = $relationship->getLinkRhsId();
        $vals['rhsTable'] = $relationship->getRhs()->getTable();
        $vals['rhsIdColumn'] = $relationship->getRhs()->getId()->getColumn();

        $orderBy = $relationship->getOrderBy();
        if ($orderBy !== null) {
          $vals['orderByCol'] = $orderBy['col'];
          $vals['orderByDir'] = $orderBy['dir'];
        }
        break;
      }

      $relationships[] = $vals;
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
      'collections'            => $collections,
      'relationships'          => $relationships,

      'column_names'           => $columnNames,
      'value_names'            => $valueNames,
      'sql_setters'            => $sqlSetters,
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
