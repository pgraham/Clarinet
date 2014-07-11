<?php
/**
 * =============================================================================
 * Copyright (c) 2011, Philip Graham
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
namespace zpt\orm;

use \zpt\opal\CompanionLoader;
use \Exception;
use \PDO;

/**
 * This class provides a static interface to most (if not all) of Clarinet's
 * CRUD and transformation capabilities.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Clarinet {

	/* Whether or not clarinet has been initialized. */
	private static $initialized = false;

	/* CompanionLoader - Injectable as argument to init method */
	private static $companionLoader;

	/**
	 * Uses the transformation API to create an array representation of the given
	 * model object.
	 *
	 * @param object $obj The model object to transform
	 */
	public static function asArray($obj) {
		self::ensureInitialized();

		$modelClass = get_class($obj);
		$transformer = self::getTransformer($modelClass);
		return $transformer->asArray($obj);
	}

	/**
	 * Delete the given object.
	 */
	public static function delete($obj) {
		self::ensureInitialized();

		$modelClass = get_class($obj);
		$persister = self::getPersister($modelClass);

		$rows = $persister->delete($obj);
		if ($rows != 1) {
			throw new Exception("Unable to delete $className with id "
				. $obj->getId());
		}
	}

	/**
	 * Retrieve instances of the given class that satisfy the given
	 * criteria.
	 *
	 * @param {string} $modelClass The name of the class to retrieve.
	 * @param {Criteria} $c An optional criteria object for filtering the
	 *   returned objects.
	 * @return {array} List of objects of the given type that match the given
	 *   criteria.
	 */
	public static function get($modelClass, Criteria $c = null) {
		self::ensureInitialized();

		$persister = self::getPersister($modelClass);

		$rows = $persister->retrieve($c);
		return $rows;
	}

	/**
	 * Retrieve all instances of a given entity and return this in an array
	 * indexed by the given column.
	 *
	 * @param string $entity The type of model to load.
	 * @param string $property The property to index by. Can be a function.
	 * @param Criteria $c Optional criteria used to filter the returned set
	 */
	public static function getAll($model, $property, Criteria $c = null) {
		if (is_string($property)) {
			$getter = 'get' . ucfirst($property);
			$fn = function ($entity) use ($getter) {
				return $entity->$getter();
			};
		} else {
			$fn = $property;
		}

		$entities = self::get($model, $c);
		
		$indexed = array();
		foreach ($entities AS $entity) {
			$idx = $fn($entity);
			$indexed[$idx] = $entity;
		}
		return $indexed;
	}

	/**
	 * Return the first instance returned by retrieving objects of the given type
	 * for the given criteria.
	 *
	 * @param {string} $className The name of the class of object to retrieve.
	 * @param {Criteria} $c Criteria to use to filter the list of candidates
	 * @return a single instance of the given model class that satisfies the given
	 *   criteria or null if none.
	 */
	public static function getOne($className, Criteria $c = null) {
		if ($c === null) {
			$c = new Criteria();
		}
		$c->setLimit(1);

		$rows = self::get($className, $c);
		if (count($rows) > 0) {
			return $rows[0];
		} else {
			return null;
		}
	}

	/**
	 * Initialize clarinet with a PDO connection and a path to generated
	 * output (or to where they can be generated if DEV mode).
	 *
	 * TODO Document what the configuration options are.
	 *
	 * @param array $config Array of configuration object
	 * @param CompanionLoader $companionLoader [Optional] If none is provided 
	 *   a new instance will be created.
	 */
	public static function init(
		PDO $pdo,
		CompanionLoader $companionLoader = null
	) {

		if (self::$initialized) {
			return;
		}
		self::$initialized = true;

		// Turn on exceptions for the PDO connection
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		PdoWrapper::set($pdo);

		if ($companionLoader === null) {
			$companionLoader = new CompanionLoader();
		}
		self::$companionLoader = $companionLoader;
	}

	/**
	 * De-initialize clarinet.  This is useful for testing or for reconnected to 
	 * a different database.
	 */
	public static function reset() {
		PdoWrapper::get()->close();
		self::$companionLoader = null;
		self::$initialized = false;
	}

	/**
	 * Saves the state of the given object.
	 *
	 * @param object $obj The object to save.
	 */
	public static function save($obj) {
		self::ensureInitialized();

		$modelClass = get_class($obj);
		$persister = self::getPersister($modelClass);
		$persister->save($obj);
	}

	/**
	 * Validates the given object.
	 *
	 * @param object $obj The object to validate.
	 * @return ValidationException
	 *   Null if the object is valid or a
	 *   {@link zpt\orm\runtime\ValidationException} that contains a list of
	 *   messages for why the object did not validate.
	 */
	public static function validate($obj) {
		self::ensureInitialized();

		$modelClass = get_class($obj);
		$persister = Validator::get($modelClass);

		$e = $validator->validate($obj);
		return $e;
	}

	/* Throws an exception if the class has not been initialized */
	private static function ensureInitialized() {
		if (!self::$initialized) {
			throw new Exception('Clarinet must be initialized with a PDO connection'
				. ' and a path for generated persister classes before it can perform'
				. ' any operations.');
		}
	}

	private static function getPersister($model) {
		return self::$companionLoader->get(
			'zpt\dyn\orm\persister',
			$model
		);
	}

	private static function getTransformer($model) {
		return self::$companionLoader->get(
			'zpt\dyn\orm\transformer',
			$model
		);
	}

	private static function getValidator($model) {
		return self::$companionLoader->get(
			'zpt\dyn\orm\validator',
			$model
		);
	}
}
