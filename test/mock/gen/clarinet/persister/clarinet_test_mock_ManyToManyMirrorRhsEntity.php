<?php
namespace clarinet\persister;

use \PDO;
use \PDOException;

use \clarinet\Clarinet;
use \clarinet\Criteria;
use \clarinet\Exception;

use \clarinet\validator\clarinet_test_mock_ManyToManyMirrorRhsEntity as Validator;

/**
 * This is a persister class generated by Clarinet.  Do NOT modify this file.
 * Instead, modify the model class of this persister, then run the clarinet
 * generator to re-generate this file.
 */
class clarinet_test_mock_ManyToManyMirrorRhsEntity {

  private $_cache = Array();

  private $_pdo = null;

  private $_create = null;
  private $_update = null;
  private $_delete = null;


  public function __construct(PDO $pdo) {
    $this->_pdo = $pdo;

    $this->_create = $this->_pdo->prepare(
      "INSERT INTO many_to_many_rhs_entity (name) VALUES (:name)");

    $this->_update = $this->_pdo->prepare(
      "UPDATE many_to_many_rhs_entity SET name = :name WHERE id = :id");

    $this->_delete = $this->_pdo->prepare(
      "DELETE FROM many_to_many_rhs_entity WHERE id = :id");
  }

  public function getById($id) {
    if (!isset($this->_cache[$id])) {
      $c = new Criteria();
      $c->addEquals('id', $id);

      // We don't care about the result since the retrieve method will
      // populate the cache
      $this->retrieve($c);
      
      if (!isset($this->_cache[$id])) {
        throw new Exception("No clarinet\test\mock\ManyToManyMirrorRhsEntity exists with id $id");
      }
    }
    return $this->_cache[$id];
  }

  public function create(\clarinet\test\mock\ManyToManyMirrorRhsEntity $model) {
    $validator = new Validator();
    $e = $validator->validate($model);
    if ($e !== null) {
      throw $e;
    }

    $params = Array();
    $params[':name'] = $model->getName();

    try {
      $this->_pdo->beginTransaction();
      $this->_create->execute($params);
      $id = $this->_pdo->lastInsertId();
      $this->_pdo->commit();
      return $id;
    } catch (PDOException $e) {
      $this->_pdo->rollback();

      throw new Exception('Error creating clarinet\test\mock\ManyToManyMirrorRhsEntity: ' . $e->getMessage(), $e);
    }
  }

  public function retrieve(Criteria $c = null) {
    if ($c->getTable() === null) {
      $c->setTable('many_to_many_rhs_entity');
    }

    $sql = $c->__toString();
    // TODO - Log the statement, this depends on a logging framework being added to reed
    $stmt = $this->_pdo->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    try {
      $params = ($c !== null) ? $c->getParameters() : null;
      $stmt->execute($params);
      
      $result = Array();
      foreach ($stmt AS $row) {
        $id = $row['id'];

        // Don't allow two instances for the same id to be created
        if (isset($this->_cache[$id])) {
          $result[] = $this->_cache[$id];
          continue;
        } 

        $model = new \clarinet\test\mock\ManyToManyMirrorRhsEntity();
        $model->setId($row['id']);
      $model->setName($row['name']);

        // Cache the instance before populating any relationships in order to
        // prevent inifinite loops when loading models that have a
        // relationship that is declared on both sides
        $this->_cache[$id] = $model;

        // Populate any relationships
// -------------------------------------------------------------------
// Populate the clarinet\test\mock\ManyToManyMirrorLhsEntity
$c = new Criteria();
$c->addJoin('many_to_many_lhs_entity_many_to_many_rhs_entity_link', 'id', 'many_to_many_lhs_entity_id');
$c->addEquals('many_to_many_rhs_entity_id', $id);

$persister = AbstractActorFactory::getActor('persister', 'clarinet\test\mock\ManyToManyMirrorLhsEntity');
$related = $persister->retrieve($c);
$model->setMany($related);
// -------------------------------------------------------------------


        $result[] = $model;
      }

      return $result;
    } catch (PDOException $e) {
      throw new Exception('Error retrieving clarinet\test\mock\ManyToManyMirrorRhsEntity instances', $e);
    }
  }

  public function update(\clarinet\test\mock\ManyToManyMirrorRhsEntity $model) {
    $e = Clarinet::validate($model);
    if ($e !== null) {
      throw $e;
    }

    $params = Array();
    $params[':id'] = $model->getId();
    $params[':name'] = $model->getName();

    try {
      $this->_pdo->beginTransaction();
      $this->_update->execute($params);
      $rowCount = $this->_update->rowCount();
      $this->_pdo->commit();

      return $rowCount;
    } catch (PDOException $e) {
      $this->_pdo->rollback();

      throw new Exception('Error updating clarinet\test\mock\ManyToManyMirrorRhsEntity', $e);
    }
  }

  public function delete(\clarinet\test\mock\ManyToManyMirrorRhsEntity $model) {
    $params = Array();
    $params[':id'] = $model->getId();

    try {
      $this->_pdo->beginTransaction();
      $this->_delete->execute($params);
      $rowCount = $this->_delete->rowCount();
      $this->_pdo->commit();

      return $rowCount;
    } catch (PDOException $e) {
      $this->_pdo->rollback();

      throw new Exception('Error updating clarinet\test\mock\ManyToManyMirrorRhsEntity', $e);
    }
  }

}
