<?php
namespace clarinet\transformer;

use \clarinet\ActorFactory;
use \clarinet\Criteria;
use \clarinet\Exception;

/**
 * This is a transformer class generate by Clarinet.  Do NOT modify this file.
 * Instead, modify the model class of this transformer, then run the clarinet
 * generator to re-generate this file.
 *
 * Notes about array -> model transformations:
 *
 * - All array indexes are the lowercase version of the property/relationship
 * - Entities involved in a relationship with the entity being transformed are
 *   always represented by IDs, not models, in the array representation.
 */
class ${actor} {

  public function asArray(\${class} $model) {
    $a = Array();

    $a['${id}'] = $model->get${id}();

    ${each:properties AS property}
      $a['${property}'] = $model->get${property}();
    ${done}

    ${each:relationships AS relationship}
      $relVal = $model->get${relationship[name]}();
      if ($relVal === null) {
        $a['${relationship[name]}'] = null;
      } else {

        ${if:relationship[type] = many-to-many or relationship[type] = one-to-many }

          $relIds = array();
          foreach ($relVal AS $rel) {
            $relIds[] = $rel->get${relationship[rhsIdProperty]}();
          }
          $a['${relationship[name]}'] = $relIds;

        ${elseif:relationship[type] = many-to-one}

          $relId = $relVal->get${relationship[rhsIdProperty]}();
          $a['${relationship[name]}'] = $relId;

        ${fi}

      }

    ${done}

    return $a;
  }

  public function fromArray(array $a) {
    if (isset($a['${id}'])) {
      $persister = ActorFactory::getActor('persister', '${class}');
      $model = $persister->getById($a['${id}']);
    } else {
      $model = new \${class}();
    }

    ${each:properties AS property}
      if (isset($a['${property}'])) {
        $val = $a['${property}'];
        $model->set${property}($val);
      }
    ${done}

    ${each:relationships AS relationship}
    
      ${if:relationship[type] = many-to-many or relationship[type] = one-to-many}

        if (isset($a['${relationship[name]}'])) {
          $relIds = $a['${relationship[name]}'];

          $c = new Criteria();
          $c->addIn('${relationship[rhsIdProperty]}', $relIds);

          $persister = ActorFactory::getActor('persister',
            '${relationship[rhs]}');
          $relVal = $persister->retrieve($c);
          $model->set${relationship[name]}($relVal);
        }

      ${elseif:relationship[type] = many-to-one}

        if (isset($a['${relationship[name]}'])) {
          $relId = $a['${relationship[name]}'];

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
   * Transform the value returned by the DB into the appropriate type for
   * the model.  This is needed since PDO seems to only return string values.
   *
   * @param string $dbVal The value from the PDO result set to convert.
   */
  public function idFromDb($dbVal) {
    return ${from_db_id_cast}$dbVal;
  }
}
