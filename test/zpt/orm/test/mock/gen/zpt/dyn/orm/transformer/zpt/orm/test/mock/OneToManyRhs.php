<?php
namespace zpt\dyn\orm\transformer;

use \zeptech\orm\runtime\Persister;
use \zeptech\orm\runtime\Transformer;
use \zpt\orm\Criteria;
use \DateTime;
use \DateTimeZone;
use \Exception;
use \StdClass;

/**
* This is a transformer class generate by Clarinet.  Do NOT modify this file.
* Instead, modify the model class of this transformer, then run the clarinet
* generator to re-generate this file.
*
* Notes about array -> model transformations:
*
* - All array indexes are the lcfirst version of the property/relationship
* - Entities involved in a relationship with the entity being transformed are
*   always represented by IDs, not models, in the array representation.
*/
class zpt_orm_test_mock_OneToManyRhs {

  /**
  * Transform the given model object into an array.
  *
  * @param zpt\orm\test\mock\OneToManyRhs $model The model instance to convert.
  */
  public function asArray(\zpt\orm\test\mock\OneToManyRhs $model = null) {
    if ($model === null) {
      return null;
    }

    $a = array();

    $a['id'] = $model->getid();

    $a['name'] = $model->getname();
    $a['oneToManyEntityId'] = $model->getoneToManyEntityId();

    #{ each: collections as col
    #} each


    return $a;
  }

  /**
  * Transform an array of zpt\orm\test\mock\OneToManyRhs instances into an array.
  *
  * @param zpt\orm\test\mock\OneToManyRhs[] $models
  */
  public function asCollection(array $models) {
    $a = array();
    foreach ($models AS $model) {
      if (!($model instanceof \zpt\orm\test\mock\OneToManyRhs)) {
        throw new Exception("Cannot transform " . print_r($model, true));
      }
      $a[] = $this->asArray($model);
    }
    return $a;
  }

  /**
  * Transform the given array into a model.
  * If a model instance is given, the contents of the given array will be
  * merged into the entity.
  *
  * @param array $a
  * @param entity $model
  * @param array $whiteList List of parameters to set in the model. Only useful
  *   if a model is given or an ID is specified in $a.
  */
  public function fromArray(array $a, $model = null, array $whiteList = null) {
    if ($model === null) {
      if (isset($a['id'])) {
        $persister = Persister::get('zpt\orm\test\mock\OneToManyRhs');
        $model = $persister->getById($a['id']);
      } else {
        $model = new \zpt\orm\test\mock\OneToManyRhs();
      }
    }

    if ($whiteList === null || in_array('name', $whiteList)) {
      if (array_key_exists('name', $a)) {
        $val = $a['name'];


        $model->setname($val);
      }
    }

    if ($whiteList === null || in_array('oneToManyEntityId', $whiteList)) {
      if (array_key_exists('oneToManyEntityId', $a)) {
        $val = $a['oneToManyEntityId'];


        $model->setoneToManyEntityId($val);
      }
    }


    #-- Add each collection into the model
    #{ each: collections as col
    #} each


    return $model;
  }

  /**
  * Transform a collection of array representations into an array of models.
  *
  * @param array $a
  */
  public function fromCollection(array $a, array $whiteList = null) {
    $models = array();
    foreach ($a AS $model) {
      if ($model instanceof StdClass) {
        $models[] = $this->fromObject($model, $whiteList);
      } else if (is_array($model)) {
        $models[] = $this->fromArray($model, $whiteList);
      }
    }
    return $models;
  }

  /**
  * Transform a StdClass instance into a model.
  *
  * @param StdClass $obj
  */
  public function fromObject(StdClass $obj = null, array $whiteList = null) {
    if ($obj === null) {
      return null;
    }
    return $this->fromArray((array) $obj, $whiteList);
  }

  /**
  * Normalized method for retrieving an entity's id.
  *
  * @param entity $entity
  * @return mixed The entity's id.
  */
  public function getId($entity) {
    return $entity->getid();
  }

  /**
  * Transform the value returned by the DB into the appropriate type for
  * the model.  This is needed since PDO seems to only return string values.
  *
  * @param string $dbVal The value from the PDO result set to convert.
  */
  public function idFromDb($dbVal) {
    return (int) $dbVal;
  }
}