<?php
namespace zpt\dyn\orm\persister;

use \zeptech\orm\runtime\ActorFactory;
use \zeptech\orm\runtime\Persister;
use \zeptech\orm\runtime\PdoExceptionWrapper;
use \zeptech\orm\runtime\PdoWrapper;
use \zeptech\orm\runtime\SaveLock;
use \zeptech\orm\QueryBuilder;
use \zpt\orm\Criteria;
use \Exception;
use \PDO;
use \PDOException;

/**
* This is a persister class generated by Clarinet.  Do NOT modify this file.
* Instead, modify the model class of this persister, then run the clarinet
* generator to re-generate this file.
*/
class zpt_orm_test_mock_OneToManyEntity {

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
  private $_cache = array();

  /* PDO Connection to the database in which entities are to be persisted. */
  private $_pdo = null;

  /* PDOStatement for inserting new entities into the database */
  private $createSql;
  private $_create = null;

  /* PDOStatement for updating existing entities. */
  private $updateSql;
  private $_update = null;

  /* PDOStatement for deleting existing entities. */
  private $deleteSql;
  private $_delete = null;

  /**
  * Create a new persister for ${class} entities.
  */
  public function __construct() {
    $this->_pdo = PdoWrapper::get();

    $this->createSql =
      "INSERT INTO one_to_many_entity
      (`name`) VALUES (:name)";
    $this->_create = $this->_pdo->prepare($this->createSql);

    $this->updateSql =
      "UPDATE one_to_many_entity
      SET `name` = :name
      WHERE id = :id";
    $this->_update = $this->_pdo->prepare($this->updateSql);

    $this->deleteSql = "DELETE FROM one_to_many_entity WHERE id = :id";
    $this->_delete = $this->_pdo->prepare($this->deleteSql);
  }

  /**
  * Clear the cache. If an id is provided, only the entity with the given id
  * is cleared.  This will happen when an entity at the many side of an
  * un-mirrored one-to-many relationship is updated to ensure that it does not
  * have a stale id for the one side of the relationship.  The entire cache is
  * generally only cleared during testing.
  *
  * @param mixed $id
  */
  public function clearCache($id = null) {
    if ($id === null) {
      $this->_cache = array();
    } else {
      unset($this->_cache[$id]);
    }
  }

  /**
  * Return a count of all entities that match the given criteria.
  *
  * @param Criteria $c
  * @return integer
  */
  public function count(Criteria $c) {
    if ($c === null) {
      $c = new Criteria();
    }

    if ($c->getTable() === null) {
      $c->setTable('one_to_many_entity');
    }


    $c->setDistinct(true) // Ensure each entity is only counted once
      ->selectCount()     // Setup the select list to only select COUNT(*)
      ->setLimit(null);   // Remove any limit on the criteria
    $sql = $c->__toString();
    $params = $c->getParameters();

    try {

      $stmt = $this->_pdo->prepare($sql);
      $stmt->execute($params);

      return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
      // TODO - Create a PDOExceptionWrapper that parses the error message in
      //        order to present an error suitable for users
      $e = new PdoExceptionWrapper($e, 'zpt\orm\test\mock\OneToManyEntity');
      $e->setSql($sql, $params);
      throw $e;
    }
  }

  /**
  * Insert the given entity into the database.
  *
  * @param ${class} $model
  */
  public function create(\zpt\orm\test\mock\OneToManyEntity $model) {
    $validator = ActorFactory::getActor('validator', 'zpt\orm\test\mock\OneToManyEntity');
    if (!$validator->validate($model, $e)) {
      throw $e;
    }

    if ($model->getid() !== null) {
      return $model->getid();
    }


    try {
      $startTransaction = $this->_pdo->beginTransaction();

      $model->setid(self::CREATE_MARKER);

      $params = Array();
      $params['name'] = $model->getname();



      $sql = $this->createSql; // If there is an exception this is handy to know
      $this->_create->execute($params);

      $transformer = ActorFactory::getActor('transformer', 'zpt\orm\test\mock\OneToManyEntity');
      $id = $transformer->idFromDb($this->_pdo->lastInsertId());
      $model->setid($id);
      $this->_cache[$id] = $model;

      // TODO Figure out a way of getting sql into any exceptions
      $sql = null;
      $params = null;

      $saveLock = SaveLock::acquire();
      $saveLock->lock($model);

      // Save related zpt\orm\test\mock\OneToManyRhs entities -------------------------------
      $persister = Persister::get('zpt\orm\test\mock\OneToManyRhs');
      $related = $model->getmany();
      if ($related === null) {
        $related = array();
      }

      // Update or save the collection
      foreach ($related AS $rel) {
        if ($rel->getid() === null) {
          $persister->create($rel);
        }
      }

      $sql = "UPDATE one_to_many_rhs
              SET one_to_many_entity_id = :id
              WHERE id = :relId";
      $updateStmt = $this->_pdo->prepare($sql);

      foreach ($related AS $rel) {
        $params = array(
          'id' => $id,
          'relId' => $rel->getid()
        );
        $updateStmt->execute($params);

        // Clear the cache of the RHS entity as it may contain a stale id
        $persister->clearCache($rel->getid());
      }
      // ---------------------------------------------------------------------

      if ($startTransaction) {
        $this->_pdo->commit();
      }

      $saveLock->release();


      return $id;
    } catch (PDOException $e) {
      $this->_pdo->rollback();
      $model->setid(null);

      if (isset($saveLock)) {
        $saveLock->forceRelease();
      }

      $e = new PdoExceptionWrapper($e, 'zpt\orm\test\mock\OneToManyEntity');
      $e->setSql($sql, $params);
      throw $e;
    }
  }

  /**
  * Delete the given entity.
  *
  * @param ${class} $model
  */
  public function delete(\zpt\orm\test\mock\OneToManyEntity $model) {
    $id = $model->getid();
    if ($id === null) {
      throw new Exception("Can't delete zpt\\orm\\test\\mock\\OneToManyEntity because it does not have an id");
    }

    $params = array();
    $params[':id'] = $id;


    try {
      $startTransaction = $this->_pdo->beginTransaction();

      $sql = $this->deleteSql; // Set SQL in case there is an exception
      $this->_delete->execute($params);
      $rowCount = $this->_delete->rowCount();


      // ---------------------------------------------------------------------
      // Delete related zpt\orm\test\mock\OneToManyRhs entities
      $persister = Persister::get('zpt\orm\test\mock\OneToManyRhs');
      $related = $model->getmany();
      foreach ($related AS $rel) {
        $persister->delete($rel);
      }

      // ---------------------------------------------------------------------


      $this->_cache[$id] = null;
      $model->setid(null);

      if ($startTransaction) {
        $this->_pdo->commit();
      }

      return $rowCount;
    } catch (PDOException $e) {
      $this->_pdo->rollback();

      $e = new PdoExceptionWrapper($e, 'zpt\orm\test\mock\OneToManyEntity');
      $e->setSql($sql, $params);
      throw $e;
    }
  }

  /**
  * Get the entity with the given id.  If no entity with the given id exists
  * then null is returned.
  *
  * @param integer $id
  * @return zpt\orm\test\mock\OneToManyEntity
  */
  public function getById($id) {
    if (!isset($this->_cache[$id])) {
      $c = new Criteria();
      $c->addEquals('id', $id);

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
  * Get a new query builder instance for the model handled by this persister.
  *
  * @return QueryBuilder
  */
  public function getQueryBuilder() {
    return QueryBuilder::get('zpt\orm\test\mock\OneToManyEntity');
  }

  /**
  * Retrieve a single entity that matches the given criteria.  If the criteria
  * results in more than one entity being retrieved then an exception is
  * thrown.  If the criteria results in no entities being retrieved then NULL
  * is returned
  *
  * @param Criteria $c Criteria that will result in a single entity
  * @return zpt\orm\test\mock\OneToManyEntity
  * @throws Exception if the criteria results in more than one entity.
  */
  public function retrieveOne(Criteria $c) {
    $entities = $this->retrieve($c);

    $num = count($entities);
    if ($num > 1) {
      throw new Exception("Criteria maps to $num entities, expected 1");
    } else if ($num === 0) {
      return null;
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
      $c->setTable('one_to_many_entity');
    }


    // Clear the selects so that the default of all columns for the FROM table
    // are selected and ensure that only distinct entities are returned
    $c->clearSelects()
      ->setDistinct(true);
    $sql = $c->__toString();
    $params = $c->getParameters();

    try {
      $stmt = $this->_pdo->prepare($sql);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);
      $stmt->execute($params);

      $transformer = ActorFactory::getActor('transformer', 'zpt\orm\test\mock\OneToManyEntity');
      $result = Array();
      foreach ($stmt AS $row) {
        $id = $transformer->idFromDb($row['id']);

        // Don't allow two instances for the same id to be created
        if (isset($this->_cache[$id])) {
          $result[] = $this->_cache[$id];
          continue;
        }

        $model = new \zpt\orm\test\mock\OneToManyEntity();
        $model->setid($id);

        // Cache the instance before populating any relationships in order to
        // prevent inifinite loops when loading models that have a
        // relationship that is declared on both sides
        $this->_cache[$id] = $model;

        // Populate the model's properties
        $model->setname($row['name']);


        // Populate collections
        $sql = null;
        $params = null;

        // Populate any relationships
        // -------------------------------------------------------------------
        // Populate the zpt\orm\test\mock\OneToManyRhs
        $c = new Criteria();
        $c->addEquals('one_to_many_entity_id', $id);
        $c->addSort('id', 'asc');

        $persister = Persister::get('zpt\orm\test\mock\OneToManyRhs');
        $related = $persister->retrieve($c);
        $model->setmany($related);

        // -------------------------------------------------------------------

        $result[] = $model;
      }

      return $result;
    } catch (PDOException $e) {
      $e = new PdoExceptionWrapper($e, 'zpt\orm\test\mock\OneToManyEntity');
      $e->setSql($sql, $params);
      throw $e;
    }
  }

  /**
  * Saves the given instance by either creating it if it does not have an ID or
  * updating if it does.
  *
  * @param ${class} $model
  */
  public function save(\zpt\orm\test\mock\OneToManyEntity $model) {
    $id = $model->getid();
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
  public function update(\zpt\orm\test\mock\OneToManyEntity $model) {
    $id = $model->getid();
    if ($id === null) {
      throw new Exception("Can't update zpt\\orm\\test\\mock\\OneToManyEntity because it does not have an id");
    }

    if (SaveLock::isLocked($model)) {
      return;
    }

    $validator = ActorFactory::getActor('validator', 'zpt\orm\test\mock\OneToManyEntity');
    if (!$validator->validate($model, $e)) {
      throw $e;
    }


    try {
      $startTransaction = $this->_pdo->beginTransaction();

      $saveLock = SaveLock::acquire();
      $saveLock->lock($model);

      $params = Array();
      $params[':id'] = $id;
      $params[':name'] = $model->getname();




      $sql = $this->updateSql;
      $this->_update->execute($params);
      $rowCount = $this->_update->rowCount();

      #-- Update each of the model's collections by first removing the existing
      #-- persisted collection and replacing it with what is in the model
      // TODO Figure out a way of getting the SQL and params into any exception
      $sql = null;
      $params = null;


      // ---------------------------------------------------------------------
      // Save related zpt\orm\test\mock\OneToManyRhs entities
      $persister = Persister::get('zpt\orm\test\mock\OneToManyRhs');
      $related = $model->getmany();
      if ($related === null) {
        $related = array();
      }

      $relIds = array();
      foreach ($related AS $rel) {
        $relIds[] = $rel->getid();
      }

      $c = new Criteria();
      $c->addEquals('one_to_many_entity_id', $id);
      $current = $persister->retrieve($c);

      // Update or save the collection
      foreach ($related AS $rel) {
        if ($rel->getid() === null) {
          $persister->create($rel);
        }
      }

      $sql = "UPDATE one_to_many_rhs SET one_to_many_entity_id = :id WHERE id = :relId";
      $updateStmt = $this->_pdo->prepare($sql);
      foreach ($related AS $rel) {
        $params = array(
          'id' => $id,
          'relId' => $rel->getid()
        );
        $updateStmt->execute($params);

        // Clear the cache of the RHS entity as it may contain a stale id
        $persister->clearCache($rel->getid());
      }

      $sql = "UPDATE one_to_many_rhs SET one_to_many_entity_id = null WHERE id = :relId";
      $orphanStmt = $this->_pdo->prepare($sql);
      foreach ($current AS $cur) {
        if (!in_array($cur->getid(), $relIds)) {
          $params = array('relId' => $cur->getid());
          $orphanStmt->execute($params);
        }

        $persister->clearCache($rel->getid());
      }
      // ---------------------------------------------------------------------

      $saveLock->release();

      if ($startTransaction) {
        $this->_pdo->commit();
      }


      return $rowCount;
      return $rowCount;
    } catch (PDOException $e) {
      $this->_pdo->rollback();
      $saveLock->forceRelease();

      $e = new PdoExceptionWrapper($e, 'zpt\orm\test\mock\OneToManyEntity');
      $e->setSql($sql, $params);
      throw $e;
    }
  }

  #-- Create methods for removing each of the model's collections

  #-- Create methods for persisting each of the model's collections

  #-- Create methods for retrieve each of the model's collections
}
