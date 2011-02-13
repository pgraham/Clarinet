<?php
namespace clarinet\transformer;

use \clarinet\Exception;

/**
 * This is a transformer class generate by Clarinet.  Do NOT modify this file.
 * Instead, modify the model class of this transformer, then run the clarinet
 * generator to re-generate this file.
 */
class clarinet_test_mock_OneToManyRhs {

  public function asArray(\clarinet\test\mock\OneToManyRhs $model) {
    $a = Array();

    $a['id'] = $model->getId();
    $a['name'] = $model->getName();

    return $a;
  }

  public function fromArray(Array $a) {
    $model = new \clarinet\test\mock\OneToManyRhs();

    $model->setId($a['id']);
    $model->setName($a['name']);

    return $model;
  }


}
