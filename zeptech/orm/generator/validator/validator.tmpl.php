<?php
namespace zeptech\dynamic\orm\validator;

use \zeptech\orm\runtime\ValidationException;
use \DateTime;
use \DateTimeZone;
use \Exception;

/**
 * This is a validator actor generated by Clarinet.
 *
 * DO NOT modify this file.  Instead, modify the model class of this validator,
 * then run the Clarinet generator to re-generate this file.
 */
class ${actor} {

  public function validate(\${class} $model, &$e = false) {

    $msgs = array();
    ${each:properties as prop}
      $msg = $this->_check${prop[name]}($model->get${prop[name]}());
      if ($msg !== null) {
        $msgs[] = $msg;
      }
    ${done}

    if (count($msgs) > 0) {
      $ex = new ValidationException($msgs, '${class}');
      if ($e !== false) {
        $e = $ex;
      } else {
        throw $ex;
      }
      return false;
    }
    return true;
  }

  ${each:properties as prop}
    private function _check${prop[name]}($val) {
      if ($val === null) {
        ${if:prop[notNull]}
          return "${prop[name]} cannot be null";
        ${else}
          return null;
        ${fi}
      }

      ${if:prop[values] ISSET}
        $accepted = array("${join:prop[values]:","}");
        if (!in_array($val, $accepted)) {
          return "$val is not an accepted value for ${prop[name]}. Accepted values are: ${join:prop[values]:,}";
        }
      ${elseif:prop[type] = email}
        if (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
          return "'$val' is not a valid email.";
        }
      ${elseif:prop[type] = date}
        try {
          $date = new DateTime($val, new DateTimeZone('UTC'));
        } catch (Exception $e) {
          return "'$val' is not a valid date.";
        }
      ${fi}

      // No validation errors
      return null;
    }
  ${done}

}
