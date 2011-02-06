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
 * @package clarinet
 */
namespace clarinet;

use \Exception;
use \PDO;

use \clarinet\model\ConfigValue;

/**
 * This class provides a static interface to most (if not all) of Clarinet's
 * CRUD and transformation capabilities.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet
 */
class Clarinet {

  /** PDO Connection object. */
  public static $pdo = null;

  /** Path to generated class files. */
  public static $outputPath = null;

  /* Whether or not clarinet has been initialized. */
  private static $_initialized = false;

  /**
   * Uses the transformation API to create an array representation of the given
   * model object.
   *
   * @param object $obj The model object to transform
   */
  public static function asArray($obj) {
    self::_ensureInitialized();

    $modelClass = get_class($obj);
    $transformer = ActorAbstractFactory::getActor('transformer', $modelClass);
    return $transformer->asArray($obj);
  }

  /**
   * Delete the given object.
   */
  public static function delete($obj) {
    self::_ensureInitialized();

    $modelClass = get_class($obj);
    $persister = ActorAbstractFactory::getAction('persister', $modelClass);

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
   *     returned objects.
   * @return {array} List of objects of the given type that match the given
   *     criteria.
   */
  public static function get($modelClass, Criteria $c = null) {
    self::_ensureInitialized();

    $persister = ActorAbstractFactory::getActor('persister', $modelClass);

    $rows = $persister->retrieve($c);
    return $rows;
  }

  /**
   * Retrieve all instances of a given entity and return this in an array
   * indexed by the given column.
   *
   * @param string $entity The type of entity to load.
   * @param string $property The property to index by.
   */
  public static function getAll($entity, $property) {
    self::_ensureInitialized();

    $rows = self::get($entity);

    $getter = 'get' . ucfirst($property);
    
    $indexed = Array();
    foreach ($rows AS $row) {
      $idx = $row->$getter();
      $indexed[$idx] = $row;
    }
    return $indexed;
  }

  /**
   * Retrieve the configuration value with the given name.  In order for this to
   * work the database must be setup to handler configuration values.
   *
   * @param {string} $name The name of the configuration value to retrieve.
   */
  public static function getConfigValue($name) {
    $c = new Criteria();
    $c->addEquals('name', $name);

    $rows = self::get('clarinet\model\ConfigValue', $c);
    if (count($rows) == 0) {
      return null;
    }

    $obj = $rows[0];
    return $obj->getValue();
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
   */
  public static function init($config = array()) {
    if (!isset($config['pdo']) || !is_object($config['pdo'])) {
      throw new Exception('Clarinet needs a PDO connection in order to be able'
        . ' to do anything.');
    }
    self::$pdo = $config['pdo'];

    // Turn on exceptions for the PDO connection
    self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($config['outputPath'])) {
      throw new Exception('Clarinet needs to know where generated classes are'
        . ' found in order to be able to do anything.');
    }

    // Store the output path for the generators and set the path in the
    // autoloader so that generated classes are loaded properly
    self::$outputPath = $config['outputPath'];
    Autoloader::$genBasePath = $config['outputPath'] . '/clarinet';

    self::$_initialized = true;
  }

  /**
   * Saves the state of the given object.
   *
   * @param object $obj The object to save.
   */
  public static function save($obj) {
    self::_ensureInitialized();

    $modelClass = get_class($obj);
    $persister = ActorAbstractFactory::getAction('persister', $modelClass);

    // TODO - Determine if the obj has an id or not and take the necessary
    //        action
  }

  /**
   * Validates the given object.
   *
   * @param object $obj The object to validate.
   * @return null if the object is valid or a ValidationException that contains
   *   a list of messages for why the object did not validate.
   */
  public static function validate($obj) {
    self::_ensureInitialized();

    $modelClass = get_class($obj);
    $validator = ActorAbstractFactory::getActor('validator', $modelClass);

    $e = $validator->validate($obj);
    return $e;
  }

  /* Throws an exception if the class has not been initialized */
  private static function _ensureInitialized() {
    if (!self::$_initialized) {
      throw new Exception('Clarinet must be initialized with a PDO connection'
        . ' and a path for generated persister classes before it can perform'
        . ' any operations.');
    }
  }
}
