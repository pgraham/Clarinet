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
use \pct\CodeTemplateParser;
use \pct\TemplateValues;

/**
 * This class generates the PHP code for a persister class given the table
 * structure for the persisted class.
 *
 * @author Philip Graham
 */
class PersisterBuilder {

  private static $_template;

  /**
   * Generate a persister class given an entities table/class structure.
   *
   * @param Model $model Structure information about the the entity for
   *     which a persister will be generated.
   * @return The persister's PHP code.
   */
  public static function build(Model $model) {
    if (self::$_template === null) {
      $parser = new CodeTemplateParser();

      $tmpl = file_get_contents(__DIR__ . '/persister.php');
      self::$_template = $parser->parse($tmpl);
    }

    $values = self::_buildTemplateValues($model);
    return self::$_template->forValues(new TemplateValues($values));
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

      switch (get_class($rel)) {
        case 'clarinet\model\OneToMany':
        $vals['rhsColumn'] = $rel->getRhsColumn();
        $vals['rhsIdColumn'] = $rhs->getId()->getColumn();
        $vals['deleteOrphan'] = $rel->deleteOrphans();

        // Retrieve params
        $orderBy = $rel->getOrderBy();
        if ($orderBy !== null) {
          $rel['orderByCol'] = $orderBy['col'];
          $rel['orderByDir'] = $orderBy['dir'];
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

        case 'clarinet\model\ManyToOne':
        $vals['lhsColumn'] = $rel->getLhsColumn();
        break;

        case 'clarinet\model\ManyToMany':
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

      $columnName = $rel->getLhsColumn();
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
