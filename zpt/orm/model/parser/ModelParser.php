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

use \zeptech\anno\AnnotationFactory;
use \zeptech\orm\generator\model\Model;
use \zpt\orm\model\ModelCache;
use \Exception;
use \ReflectionClass;

/**
 * This class will parse the ORM information from a given model class into a
 * structure which can then be used to generate various actors for retrieving
 * and manipulating persisted data.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ModelParser
{

  // Injected properties
  private $modelCache;
  private $namingStrategy;
  private $annotationFactory;

  // Composite association properties
  private $idParser;
  private $columnParser;
  private $collectionParser;
  private $relationshipParser;

  /**
   * Initialize the parser once its dependencies has been set.
   */
  public function init()
  {
    $this->idParser = new IdParser(
      $this->annotationFactory,
      $this->namingStrategy,
      $this->modelCache
    );
    $this->columnParser = new ColumnParser(
      $this->annotationFactory,
      $this->namingStrategy,
      $this->modelCache
    );
    $this->collectionParser = new CollectionParser(
      $this->annotationFactory,
      $this->namingStrategy,
      $this->modelCache
    );
    $this->relationshipParser = new RelationshipParser(
      $this->annotationFactory,
      $this->namingStrategy,
      $this->modelCache
    );
  }

  public function parse($className)
  {
    // Don't reparse a model that is already in the cache
    if ($this->modelCache->isCached($className)) {
      return $this->modelCache->get($className);
    }

    $class = $this->reflect($className);

    // TODO Create an AnnotationFactory class and use an injected instance to
    //      create the annotations instance.
    $classAnnos = $this->annotationFactory->get($class);

    if (!$classAnnos->isAnnotatedWith('Entity')) {
      // TODO Use a status code in the exception to make localization easier
      throw new Exception("$className is not an entity");
    }

    $methods = $class->getMethods();

    $table = $classAnnos['entity']['table'];
    if ($table === null) {
      $table = $this->namingStrategy->getTableName($className);
    }

    $model = new Model($className, $classAnnos);
    $model->setTable($table);

    // Cache the model before parsing in order to avoid infinite
    // recursions when delegates attempt to retrieve the model
    $this->modelCache->set($className, $model);

    // Find the id column.  This is done before parsing other column types since
    // some default values rely on the id.
    $id = $this->idParser->parse($class);
    if ($id === null) {
      throw new Exception("{$class->getName()} does not define an ID column.");
    }

    // TODO Is this parent relationship necessary?
    $id->setModel($model);
    $model->setId($id);

    // Parse Column mappings
    $columns = $this->columnParser->parse($class);
    foreach ($columns as $column) {
      $column->setModel($model);
      $model->addProperty($column);
    }

    // Parse collection mappings.
    $collections = $this->collectionParser->parse($class);
    foreach ($collections as $collection) {
      $model->addCollection($collection);
    }

    $relationships = $this->relationshipParser->parse($class);
    foreach ($relationships as $relationship) {
      $model->addRelationship($relationship);
    }

    return $model;
  }

  /* ===========================================================================
   * Dependency setters.
   * ------------------------------------------------------------------------ */

  public function setAnnotationFactory(AnnotationFactory $annotationFactory) {
    $this->annotationFactory = $annotationFactory;
  }

  public function setModelCache(ModelCache $modelCache) {
    $this->modelCache = $modelCache;
  }

  public function setNamingStrategy(NamingStrategy $namingStrategy) {
    $this->namingStrategy = $namingStrategy;
  }

  /* ===========================================================================
   * Private helpers.
   * ------------------------------------------------------------------------ */


  /* Attempt to reflect the specified class */
  private function reflect($className) {
    try {
      $class = new ReflectionClass($className);
      return $class;
    } catch (ReflectionException $e) {
      throw new Exception(
        "Unabled to reflect $className: {$e->getMessage()}",
        $e->getCode(),
        $e
      );
    }
  }
}

