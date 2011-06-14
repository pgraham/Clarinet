<?php
namespace clarinet\persister;

use \PDO;
use \PDOException;

use \clarinet\ActorFactory;
use \clarinet\Criteria;
use \clarinet\Exception;
use \clarinet\PdoWrapper;

use \clarinet\validator\${actor} as Validator;
use \clarinet\transformer\${actor} as Transformer;

/**
 * This is a persister class generated by Clarinet.  Do NOT modify this file.
 * Instead, modify the model class of this persister, then run the clarinet
 * generator to re-generate this file.
 */
class ${actor} {

  /**
   * Entities that in the process of being created are marked with this id so
   * that any right side relationships won't call the create method for the
   * entity a second time.  In particular this will help prevent double creation
   * when saving a mirrored ManyToOneEntity.  This happens because the left side
   * (many side) of the relationship will attempt to create the right side, if
   * it doesn't have an id, before the left side entity has been assigned an id.
   * So the right side persister will see that the left side has a null id and
   * will create it a second time.  So setting an entities id to this marker
   * will let a right side persister know not to re-create or update the entity.
   */
  const CREATE_MARKER = 0;

  /*
   * Delete entities will have an index in the cache but the value will be
   * null.
   */
  private $_cache = Array();

  /* PDO Connection to the database in which entities are to be persisted. */
  private $_pdo = null;

  /* PDOStatement for inserting new entities into the database */
  private $_create = null;

  /* PDOStatement for updating existing entities. */
  private $_update = null;

  /* PDOStatement for deleting existing entities. */
  private $_delete = null;

  /**
   * Create a new persister for ${class} entities.
   */
  public function __construct() {
    $this->_pdo = PdoWrapper::get();

    $this->_create = $this->_pdo->prepare(
      "INSERT INTO ${table} (${join:column_names:,}) VALUES (${join:value_names:,})");

    ${if:has_update}
      $this->_update = $this->_pdo->prepare(
        "UPDATE ${table} SET ${join:sql_setters:,} WHERE ${id_column} = :id");
    ${else}
      $this->_update = null;
    ${fi}

    $this->_delete = $this->_pdo->prepare(
      "DELETE FROM ${table} WHERE ${id_column} = :id");
  }

  /**
   * Clear the cache.  This is generally only used for testing.
   */
  public function clearCache() {
    $this->_cache = Array();
  }

  /**
   * Insert the given entity into the database.
   *
   * @param ${class} $model
   */
  public function create(\${class} $model) {
    $validator = new Validator();
    $e = $validator->validate($model);
    if ($e !== null) {
      throw $e;
    }

    if ($model->get${id_property}() !== null) {
      return $model->get${id_property}();
    }

    ${if:beforeCreate}
      $model->beforeCreate();
    ${fi}

    try {
      $startTransaction = $this->_pdo->beginTransaction();

      $model->set${id_property}(self::CREATE_MARKER);

      $params = Array();
      ${each:populate_parameters AS populate_param}
        ${populate_param}
      ${done}

      $this->_create->execute($params);
      $id = $this->_pdo->lastInsertId();
      $model->set${id_property}($id);
      $this->_cache[$id] = $model;

      $saveLock = SaveLock::acquire();
      $saveLock->lock($model);

      ${each:save_relationships as save}
        ${save}
      ${done}

      if ($startTransaction) {
        $this->_pdo->commit();
      }

      $saveLock->release();

      ${if:onCreate}
        $model->onCreate();
      ${fi}

      return $id;
    } catch (PDOException $e) {
      $this->_pdo->rollback();
      $model->set${id_property}(null);

      if (isset($saveLock)) {
        $saveLock->forceRelease();
      }

      throw new Exception('Error creating ${class_str}: ' . $e->getMessage(), $e);
    }
  }

  /**
   * Delete the given entity.
   *
   * @param ${class} $model
   */
  public function delete(\${class} $model) {
    $id = $model->get${id_property}();
    if ($id === null) {
      throw new Exception("Can't delete ${class_str} because it does not have an id");
    }

    $params = Array();
    $params[':id'] = $id;

    ${if:beforeDelete}
      $model->beforeDelete();
    ${fi}

    try {
      $startTransaction = $this->_pdo->beginTransaction();

      $this->_delete->execute($params);
      $rowCount = $this->_delete->rowCount();

      ${each:delete_relationships as delete}
        ${delete}
      ${done}

      if ($startTransaction) {
        $this->_pdo->commit();
      }

      $this->_cache[$id] = null;
      $model->set${id_property}(null);

      ${if:onDelete}
        $model->onDelete();
      ${fi}

      return $rowCount;
    } catch (PDOException $e) {
      $this->_pdo->rollback();

      throw new Exception("Error deleting ${class_str}: {$e->getMessage()}", $e);
    }
  }

  /**
   * Get the entity with the given id.  If no entity with the given id exists
   * then null is returned.
   *
   * @param integer $id
   * @return ${class}
   */
  public function getById($id) {
    if (!isset($this->_cache[$id])) {
      $c = new Criteria();
      $c->addEquals('${id_column}', $id);

      // We don't care about the result since the retrieve method will
      // populate the cache
      $this->retrieve($c);
      
      if (!isset($this->_cache[$id])) {
        return null;
      }
    }
    return $this->_cache[$id];
  }

  /**
   * Retrieve a single entity that matches the given criteria.  If the criteria
   * results in more than one entity being retrieved then an exception is
   * thrown.
   *
   * @param Criteria $c Criteria that will result in a single entity
   * @return ${class}
   * @throws Exception if the criteria results in more than one entity.
   */
  public function retrieveOne(Criteria $c) {
    $entities = $this->retrieve($c);

    $num = count($entities);
    if ($num !== 1) {
      throw new Exception("Criteria maps to $num entities, expected 1");
    }

    return $entities[0];
  }

  /**
   * Retrieve entities that match the given criteria.  If no criteria then all
   * entities are returned.  If no entities exist or match the criteria then an
   * empty array is returned.
   *
   * @param Criteria $c
   */
  public function retrieve(Criteria $c = null) {
    if ($c === null) {
      $c = new Criteria();
    }

    if ($c->getTable() === null) {
      $c->setTable('${table}');
    }

    $sql = $c->__toString();
    try {

      $stmt = $this->_pdo->prepare($sql);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      $params = ($c !== null) ? $c->getParameters() : null;
      $stmt->execute($params);

      $transformer = new Transformer();
      $result = Array();
      foreach ($stmt AS $row) {
        $id = $transformer->idFromDb($row['${id_column}']);

        // Don't allow two instances for the same id to be created
        if (isset($this->_cache[$id])) {
          $result[] = $this->_cache[$id];
          continue;
        }

        $model = new \${class}();
        $model->set${id_property}($id);

        // Cache the instance before populating any relationships in order to
        // prevent inifinite loops when loading models that have a
        // relationship that is declared on both sides
        $this->_cache[$id] = $model;

        // Populate the model's properties
        ${each:populate_properties as populate_prop}
          ${populate_prop}
        ${done}

        // Populate any relationships
        ${each:populate_relationships as populate_rel}
          ${populate_rel}
        ${done}

        $result[] = $model;
      }

      return $result;
    } catch (PDOException $e) {
      throw new Exception("Error retrieving ${class_str} instances: {$e->getMessage()}\n\n$sql\n", $e);
    }
  }

  /**
   * Saves the given instance by either creating it if it does not have an ID or
   * updating if it does.
   *
   * @param ${class} $model
   */
  public function save(\${class} $model) {
    $id = $model->get${id_property}();
    if ($id === null) {
      $this->create($model);
    } else {
      $this->update($model);
    }
  }

  /**
   * Update the given entity.
   *
   * @param ${class} $model
   */
  public function update(\${class} $model) {
    $id = $model->get${id_property}();
    if ($id === null) {
      throw new Exception("Can't update ${class_str} because it does not have an id");
    }

    if (SaveLock::isLocked($model)) {
      return;
    }

    $validator = new Validator();
    $e = $validator->validate($model);
    if ($e !== null) {
      throw $e;
    }

    ${if:beforeUpdate}
      $model->beforeUpdate();
    ${fi}

    try {
      $startTransaction = $this->_pdo->beginTransaction();

      $saveLock = SaveLock::acquire();
      $saveLock->lock($model);

      $params = Array();
      $params[':id'] = $id;
      ${each:populate_parameters as populate_param}
        ${populate_param}
      ${done}

      ${if:has_update}
        $this->_update->execute($params);
        $rowCount = $this->_update->rowCount();
      ${fi}

      ${each:save_relationships as save_rel}
        ${save_rel}
      ${done}

      $saveLock->release();

      if ($startTransaction) {
        $this->_pdo->commit();
      }

      ${if:onUpdate}
        $model->onUpdate();
      ${fi}

      ${if:has_update}
        return $rowCount;
      ${else}
        return 1;
      ${fi}
      return $rowCount;
    } catch (PDOException $e) {
      $this->_pdo->rollback();
      $saveLock->forceRelease();

      throw new Exception("Error updating ${class_str}: {$e->getMessage()}", $e);
    }
  }
}
