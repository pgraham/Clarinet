<?php
namespace clarinet\transformer;

use \clarinet\Exception;

/**
 * This is a transformer class generate by Clarinet.  Do NOT modify this file.
 * Instead, modify the model class of this transformer, then run the clarinet
 * generator to re-generate this file.
 */
class clarinet_test_mock_SimpleEntity {

  public function asArray(\clarinet\test\mock\SimpleEntity $model) {
    $a = Array();

    $a['id'] = $model->getId();
    $a['name'] = $model->getName();
    $a['value'] = $model->getValue();

    return $a;
  }

  public function fromArray(Array $a) {
    $model = new \clarinet\test\mock\SimpleEntity();

    $model->setId($a['id']);
    $model->setName($a['name']);
    $model->setValue($a['value']);

    return $model;
  }


}
