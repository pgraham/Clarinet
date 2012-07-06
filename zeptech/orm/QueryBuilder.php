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
namespace zeptech\orm;

use \zeptech\orm\generator\model\ManyToMany;
use \zeptech\orm\generator\model\ManyToOne;
use \zeptech\orm\generator\model\Model;
use \zeptech\orm\generator\model\OnceToMany;
use \zeptech\orm\generator\AbstractModelGenerator;
use \zpt\pct\CodeTemplateParser;

/**
 * This class generates a query builder class which can be used to create
 * criteria objects for retrieving object's of a specific model class.  The
 * query builder is aware of a model's relationships and can be used to create
 * criteria which sort and filter on the properties of a related entity.
 *
 * This class can also be used statically at runtime as a factory for
 * QueryBuilder instances.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class QueryBuilder extends AbstractModelGenerator {

  protected static $actorNamespace = 'zeptech\dynamic\orm\querybuilder';

  /**
   * Override the static get method to not use a cache.  A new querybuilder is
   * returned everytime.
   *
   * @override
   */
  public static function get($modelName) {
    $actor = str_replace('\\', '_', $modelName);
    $fq = static::$actorNamespace . "\\$actor";
    return new $fq();
  }

  /*
   * ===========================================================================
   * Instance
   * ===========================================================================
   */

  protected function getTemplatePath() {
    return __DIR__ . '/queryBuilder.tmpl.php';
  }

  /**
   * Generates the PHP Code for a query builder actor for the given model
   * structure.
   *
   * @param Model $model
   * @return string The PHP code for a query builder.
   */
  protected function getValuesForModel(Model $model) {
    $tmplValues = array(
      'actor' => $model->getActor()
    );

    $props = array();
    foreach ($model->getProperties() as $prop) {
      $props[] = array(
        'name' => $prop->getName(),
        'col' => $prop->getColumn()
      );
    }

    $rels = array();
    foreach ($model->getRelationships() as $rel) {
      $relName = $rel->getLhsProperty();
      $rhs = $rel->getRhs();

      $relVals = array(
        'name' => $relName,
        'tbl' => $rhs->getTable(),
        'props' => array()
      );

      foreach ($rhs->getProperties() as $relProp) {
        $relVals['props'][$relProp->getName()] = $relProp->getColumn();
      }

      if ($rel instanceof ManyToOne) {
        $lhsCol = $rel->getLhsColumn();

        $relVals['type'] = 'many-to-one';
        $relVals['lhs_col'] = $lhsCol;
        $relVals['rhs_id_col'] = $rhs->getId()->getColumn();

        $props[] = array(
          'name' => $relName,
          'col' => $lhsCol,
        );

      } else if ($rel instanceof OneToMany) {
        $relVals['type'] = 'one-to-many';
        $relVals['lhs_id_col'] = $model->getId()->getColumn();
        $relVals['rhs_col'] = $rel->getRhsColumn();

      } else if ($rel instanceof ManyToMany) {
        $relVals['type'] = 'many-to-many';
        $relVals['link'] = $rel->getLinkTable();
        $relVals['lhs_id_col'] = $model->getId()->getColumn();
        $relVals['link_lhs'] = $rel->getLinkLhsId();
        $relVals['link_rhs'] = $rel->getLinkRhsId();
        $relVals['rhs_id_col'] = $rhs->getId()->getColumn();

      }

      $rels[] = $relVals;
    }

    $tmplValues['properties'] = $props;
    $tmplValues['relationships'] = $rels;

    return $tmplValues;
  }
}
