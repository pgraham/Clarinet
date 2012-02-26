<?php
namespace zeptech\dynamic\orm\transformer;

use \zeptech\orm\runtime\ActorFactory;
use \zeptech\orm\runtime\Criteria;
use \DateTime;
use \DateTimeZone;
use \Exception;

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
class ${actor} {

  /**
   * Transform the given model object into an array.
   *
   * @param ${class} $model The model instance to convert.
   */
  public function asArray(\${class} $model) {
    $a = array();

    $a['${idIdx}'] = $model->get${id}();

    ${each:properties AS property}
      $a['${property[idx]}'] = $model->get${property[id]}();
    ${done}

    ${each:relationships AS relationship}
      $relVal = $model->get${relationship[name]}();
      if ($relVal === null) {
        $a['${relationship[idx]}'] = null;
      } else {

        ${if:relationship[type] = many-to-many or relationship[type] = one-to-many }

          $relIds = array();
          foreach ($relVal AS $rel) {
            $relIds[] = $rel->get${relationship[rhsIdProperty]}();
          }
          $a['${relationship[idx]}'] = $relIds;

        ${elseif:relationship[type] = many-to-one}

          $relId = $relVal->get${relationship[rhsIdProperty]}();
          $a['${relationship[idx]}'] = $relId;

        ${fi}

      }

    ${done}

    return $a;
  }

  /**
   * Transform an array of ${class} instances into an array.
   *
   * @param ${class}[] $models
   */
  public function asCollection(array $models) {
    $a = array();
    foreach ($models AS $model) {
      if (!($model instanceof \${class})) {
        throw new Exception("Cannot transform " . print_r($model, true));
      }
      $a[] = $this->asArray($model);
    }
    return $a;
  }

  /**
   * Transform the given array into a model.
   *
   * @param array $a
   */
  public function fromArray(array $a) {
    if (isset($a['${idIdx}'])) {
      $persister = ActorFactory::getActor('persister', '${class}');
      $model = $persister->getById($a['${idIdx}']);
    } else {
      $model = new \${class}();
    }

    ${each:properties AS property}
      if (isset($a['${property[idx]}'])) {
        $val = $a['${property[idx]}'];

        ${if:property[type] = timestamp}
          // Ensure proper format for timestamps
          $val = date('Y-m-d H:i:s', strtotime($val));
        ${fi}
        $model->set${property[id]}($val);
      }
    ${done}

    ${each:relationships AS relationship}
    
      ${if:relationship[type] = many-to-many or relationship[type] = one-to-many}

        if (isset($a['${relationship[idx]}'])) {
          $relIds = $a['${relationship[idx]}'];

          $c = new Criteria();
          $c->addIn('${relationship[rhsIdProperty]}', $relIds);

          $persister = ActorFactory::getActor('persister',
            '${relationship[rhs]}');
          $relVal = $persister->retrieve($c);
          $model->set${relationship[name]}($relVal);
        }

      ${elseif:relationship[type] = many-to-one}

        if (isset($a['${relationship[idx]}'])) {
          $relId = $a['${relationship[idx]}'];

          if ($relId !== null) {
            $persister = ActorFactory::getActor('persister',
              '${relationship[rhs]}');
            $relVal = $persister->getById($relId);
            $model->set${relationship[name]}($relVal);
          }
        }

      ${fi}

    ${done}

    return $model;
  }

  /**
   * Transform a collection of array representations into an array of models.
   *
   * @param array $a
   */
  public function fromCollection(array $a) {
    $models = array();
    foreach ($a AS $modelArray) {
      $models[] = $this->fromArray($modelArray);
    }
    return $models;
  }

  /**
   * Normalized method for retrieving an entity's id.
   *
   * @param entity $entity
   * @return mixed The entity's id.
   */
  public function getId($entity) {
    return $entity->get${id}();
  }

  /**
   * Transform the value returned by the DB into the appropriate type for
   * the model.  This is needed since PDO seems to only return string values.
   *
   * @param string $dbVal The value from the PDO result set to convert.
   */
  public function idFromDb($dbVal) {
    return ${from_db_id_cast}$dbVal;
  }
}
