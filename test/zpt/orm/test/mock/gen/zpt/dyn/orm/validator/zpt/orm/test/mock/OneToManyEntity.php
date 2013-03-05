<?php
namespace zpt\dyn\orm\validator;

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
class zpt_orm_test_mock_OneToManyEntity {

  public function validate(\zpt\orm\test\mock\OneToManyEntity $model, &$e = false) {

    $msgs = array();
    $msg = $this->_checkname($model->getname());
    if ($msg !== null) {
      $msgs[] = $msg;
    }

    if (count($msgs) > 0) {
      $ex = new ValidationException($msgs, 'zpt\orm\test\mock\OneToManyEntity');
      if ($e !== false) {
        $e = $ex;
      } else {
        throw $ex;
      }
      return false;
    }
    return true;
  }

  private function _checkname($val) {
    if ($val === null) {
      return null;
    }


    // No validation errors
    return null;
  }

}