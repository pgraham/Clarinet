<?php
/**
 * =============================================================================
 * Copyright (c) 2013, Philip Graham
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
namespace zpt\orm\model\parser;

use \zpt\anno\Annotations;
use \zpt\orm\model\ManyToMany;
use \zpt\orm\model\ManyToOne;
use \zpt\orm\model\OneToMany;
use \Exception;
use \ReflectionClass;
use \ReflectionMethod;

/**
 * This class parses the relationships mappings of a model class.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class RelationshipParser extends BaseMethodParser
{

  public function parse(ReflectionClass $class)
  {
    $methods = $class->getMethods();

    $relationships = array();
    foreach ($methods as $method) {
      $annos = $this->annotationFactory->get($method);

      $rel = null;
      if ($annos->isAnnotatedWith('onetomany')) {
        $rel = $this->parseOneToMany($method, $annos);

      } else if ($annos->isAnnotatedWith('manytoone')) {
        $rel = $this->parseManyToOne($method, $annos);
  
      } else if ($annos->isAnnotatedWith('manytomany')) {
        $rel = $this->parseManyToMany($method, $annos);
      }

      if ($rel !== null) {
        $relationships[] = $rel;
      }
    }
    return $relationships;
  }

  public function parseManyToMany(ReflectionMethod $method, Annotations $annos)
  {
    $propertyName = $this->getPropertyName($method);

    $anno = $annos['manytomany'];

    if (!isset($anno['entity'])) {
      throw $this->buildNoEntityException($method);
    }

    $lhs = $this->modelCache->get($method->getDeclaringClass()->getName());
    $rhs = $this->modelCache->get($anno['entity']);

    if (isset($anno['table'])) {
      $linkTable = $anno['table'];
    } else {
      $linkTable = $this->namingStrategy->getLinkTable($lhs, $rhs);
    }

    if (isset($anno['localId'])) {
      $lhsLinkCol = $anno['localId'];
    } else {
      $lhsLinkCol = $this->namingStrategy->getLinkColumn($lhs);
    }

    if (isset($anno['foreignId'])) {
      $rhsLinkCol = $anno['foreignId'];
    } else {
      $rhsLinkCol = $this->namingStrategy->getLinkColumn($rhs);
    }

    $rel = new ManyToMany(
      $lhs,
      $rhs,
      $propertyName,
      $linkTable,
      $lhsLinkCol,
      $rhsLinkCol
    );

    if (isset($anno['order'])) {
      $orderBy = $anno['order'];

      $dir = 'ASC';
      if (isset($anno['dir'])) {
        $dir = $anno['dir'];
      }

      $rel->setOrderBy($orderBy, $dir);
    }

    return $rel;
  }

  public function parseManyToOne(ReflectionMethod $method, Annotations $annos)
  {
    $propertyName = $this->getPropertyName($method);

    $anno = $annos['manytoone'];

    if (!isset($anno['entity'])) {
      throw $this->buildNoEntityException($method);
    }

    $lhs = $this->modelCache->get($method->getDeclaringClass()->getName());
    $rhs = $this->modelCache->get($anno['entity']);

    if (isset($anno['column'])) {
      $lhsColumn = $anno['column'];
    } else {
      $lhsColumn = $this->namingStrategy->getLinkColumn($rhs);
    }

    return new ManyToOne($lhs, $rhs, $propertyName, $lhsColumn);
  }

  public function parseOneToMany(ReflectionMethod $method, Annotations $annos)
  {
    $propertyName = $this->getPropertyName($method);

    $anno = $annos['onetomany'];

    if (!isset($anno['entity'])) {
      throw $this->buildNoEntityException($method);
    }

    $lhs = $this->modelCache->get($method->getDeclaringClass()->getName());
    $rhs = $this->modelCache->get($anno['entity']);

    if (isset($anno['column'])) {
      $rhsColumn = $anno['column'];
    } else {
      $rhsColumn = $this->namingStrategy->getLinkColumn($lhs);
    }

    $rel = new OneToMany($lhs, $rhs, $propertyName, $rhsColumn);

    if (isset($anno['order'])) {
      $orderBy = $anno['order'];

      $dir = 'ASC';
      if (isset($anno['dir'])) {
        $dir = $anno['dir'];
      }

      $rel->setOrderBy($orderBy, $dir);
    }

    if (isset($anno['deleteorphans'])) {
      $deleteOrphans = (bool) $anno['deleteorphans'];
      $rel->deleteOrphans($deleteOrphans);
    }

    return $rel;
  }

  private function buildNoEntityException($method) {
    return new Exception(
      "{$method->getDeclaringClass()->getName()}::{$method->getName()}: " .
      "Relationships must declare the related entity. " .
      "E.g. @OneToMany(entity = <...>).");
  }

}
