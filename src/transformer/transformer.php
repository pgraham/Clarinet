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

  private static $_PROPERTY_MAP = array(
    ${join:property_map:,}
  );

  public function asArray(\${class} $model) {
    $a = Array();

    ${each:properties AS property}
      $a[self::$_PROPERTY_MAP['${property}']] = $model->get${property}();
    ${done}

    ${each:relationshipsToArray AS relationship}
      ${relationship}
    ${done}

    return $a;
  }

  public function fromArray(array $a) {
    $model = new \${class}();

    ${each:properties AS property}
      $val = null;
      if (isset($a[self::$_PROPERTY_MAP['${property}']])) {
        $val = $a[self::$_PROPERTY_MAP['${property}']];
      }
      $model->set${property}($val);

    ${done}

    ${each:relationshipsFromArray AS relationship}
      ${relationship}
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
