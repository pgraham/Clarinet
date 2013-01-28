<?php
namespace zpt\dyn\orm\transformer;

use \zeptech\orm\runtime\Criteria;
use \zeptech\orm\runtime\Persister;
use \zeptech\orm\runtime\Transformer;
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
      $a['${property[id]}'] = $model->get${property[id]}();
    ${done}

    #{ each: collections as col
    ${each:collections as col}
      $a['${col[property]}'] = $model->get${col[property]}();
    ${done}
    #} each

    ${each:relationships AS relationship}
      $relVal = $model->get${relationship[name]}();
      if ($relVal === null) {
        $a['${relationship[idx]}'] = null;
      } else {
        $transformer = Transformer::get('${relationship[rhs]}');
        ${if:relationship[type] = many-to-many}
          $rels = array();
          foreach ($relVal as $rel) {
            $rels[] = $rel->get${relationship[rhsIdProperty]}();
          }
          $a['${relationship[idx]}'] = $rels;
        ${elseif:relationship[type] = one-to-many}
          $rels = array();
          foreach ($relVal as $rel) {
            $rels[] = $transformer->asArray($rel);
          }
          $a['${relationship[idx]}'] = $rels;
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
      if (isset($a['${idIdx}'])) {
        $persister = Persister::get('${class}');
        $model = $persister->getById($a['${idIdx}']);
      } else {
        $model = new \${class}();
      }
    }

    ${each:properties AS property}
      if ($whiteList === null || in_array('${property[id]}', $whiteList)) {
        if (array_key_exists('${property[id]}', $a)) {
          $val = $a['${property[id]}'];

          ${if:property[default] ISSET}
            if ($val === null) {
              ${if:property[type] = timestamp and property[default] = current_time}
                $val = date('Y-m-d H:i:s');
              ${elseif:property[type] = date and property[default] = current_date}
                $val = date('Y-m-d');
              ${else}
                $val = ${property[default]};
              ${fi}
            }
          ${fi}

          ${if:property[type] = timestamp or property[type] = date}
            try {
              $date = new DateTime($val, new DateTimeZone('UTC'));
              ${if:property[type] = timestamp}
                $val = $date->format('Y-m-d H:i:s');
              ${else}
                $val = $date->format('Y-m-d');
              ${fi}
            } catch (Exception $e) {
              // Swallow this exception and let the invalid value go through, it
              // will get handled during model validation.
            }
          ${fi}
          $model->set${property[id]}($val);
        }
      }

    ${done}

    #-- Add each collection into the model
    #{ each: collections as col
    ${each:collections as col}
      if ($whiteList === null || in_array('${col[property]}', $whiteList)) {
        if (array_key_exists('${col[property]}', $a)) {
          $model->set${col[property]}($a['${col[property]}']);
        }
      }
    ${done}
    #} each

    ${each:relationships AS relationship}
      ${if:relationship[type] = many-to-many or relationship[type] = one-to-many}
        if ($whiteList === null || in_array('${relationship[idx]}', $whiteList)) {
          if (isset($a['${relationship[idx]}'])) {
            $rels = $a['${relationship[idx]}'];

            $transformer = Transformer::get('${relationship[rhs]}');
            $relVal = $transformer->fromCollection($rels);

            $model->set${relationship[name]}($relVal);
          }
        }

      ${elseif:relationship[type] = many-to-one}

        if ($whiteList === null || in_array('${relationship[idx]}', $whiteList)) {
          if (isset($a['${relationship[idx]}'])) {
            $relId = $a['${relationship[idx]}'];

            if ($relId !== null) {
              $persister = Persister::get('${relationship[rhs]}');
              $relVal = $persister->getById($relId);
              $model->set${relationship[name]}($relVal);
            }
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
