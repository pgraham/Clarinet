<?php
/**
 * =============================================================================
 * Copyright (c) 2013, Philip Graham
 * All rights reserved.
 *
 * This file is part of Clarinet and is licensed by the Copyright holder under
 * the 3-clause BSD License.	The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace zpt\orm\model\parser;

use zpt\orm\model\Model;
use zpt\anno\AnnotationFactory;
use zpt\orm\model\ModelCache;
use Exception;
use ReflectionClass;

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

	public function __construct(
		ModelCache $modelCache = null,
		AnnotationFactory $annotationFactory = null,
		NamingStrategy $namingStrategy = null
	) {

		if ($modelCache === null) {
			$modelCache = new ModelCache();
		}
		$this->modelCache = $modelCache;

		if ($annotationFactory === null) {
			$annotationFactory = new AnnotationFactory();
		}
		$this->annotationFactory = new AnnotationFactory();

		if ($namingStrategy === null) {
			$namingStrategy = new DefaultNamingStrategy($annotationFactory);
		}
		$this->namingStrategy = $namingStrategy;

		$this->init();
	}

	public function getAnnotationFactory() {
		return $this->annotationFactory;
	}

	public function getNamingStrategy() {
		return $this->namingStrategy;
	}

	public function parse($className) {
		// Don't reparse a model that is already in the cache
		if ($this->modelCache->isCached($className)) {
			return $this->modelCache->get($className);
		}

		$class = $this->reflect($className);
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
		// recursions when associated entities attempt to retrieve the model
		$this->modelCache->set($className, $model);

		// Find the id column. This is done before parsing other column types since
		// some default values rely on the id.
		$id = $this->idParser->parse($class);
		if ($id === null) {
			throw new Exception("{$class->getName()} does not define an ID column.");
		}
		$model->setId($id);

		// Parse Column mappings
		$columns = $this->columnParser->parse($class);
		foreach ($columns as $column) {
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

	/*
	 * ===========================================================================
	 * Private helpers.
	 * ===========================================================================
	 */

	/* Initialize the parser once its dependencies has been set. */
	private function init() {
		$this->idParser = new IdParser($this);
		$this->columnParser = new ColumnParser($this);
		$this->collectionParser = new CollectionParser($this);
		$this->relationshipParser = new RelationshipParser($this);
	}


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

