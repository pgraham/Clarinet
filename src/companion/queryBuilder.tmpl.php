<?php
namespace /*# companionNs #*/;

use zpt\db\DatabaseConnection;
use zpt\orm\Criteria;

/**
 * This is a query builder class generated by clarinet. Do _not_modify this file
 * but instead modify the model class of this query builder and re-generate
 * this file using the appropriate generator.
 */
class /*# companionClass #*/ {

  private $_c;

  private $_joinedTo = array();

  public function __construct(DatabaseConnection $db) {
    $this->_c = new Criteria($db->getQueryAdapter());
  }

  public function addFilter($field, $value, $op = '=') {
    if (strpos($field, '.') !== false) {
      list($rel, $prop) = explode('.', $field);
      $this->_addRelFilter($rel, $prop, $value, $op);
    } else {
      $this->_addPropFilter($field, $value, $op);
    }
    return $this;
  }

  public function addSort($field, $dir = 'asc') {
    if (strpos($field, '.') !== false) {
      list($rel, $prop) = explode('.', $field);
      $this->_addRelSort($rel, $prop, $dir);
    } else {
      $this->_addPropSort($field, $dir);
    }
    return $this;
  }

  public function getCriteria() {
    return $this->_c;
  }

  public function setLimit($limit, $offset = 0) {
    $this->_c->setLimit($limit, $offset);
    return $this;
  }

  private function _addPropFilter($propName, $value, $op) {
    $prop = $this->_getProp($propName);
    if ($prop !== null) {
      $this->_c->addPredicate($prop['col'], $value, $op);
    } else {
      $this->_c->addPredicate($propName, $value, $op);
    }
  }

  private function _addPropSort($propName, $dir) {
    $prop = $this->_getProp($propName);
    if ($prop !== null) {
      $this->_c->addSort($prop['col'], $dir);
    } else {
      $this->_c->addSort($propName, $dir);
    }
  }

  private function _addRelFilter($relName, $propName, $value, $op) {
    $rel = $this->_getRel($relName);
    if ($rel === null || !isset($rel['props'][$propName])) {
      $this->_c->addPredicate("$relName.$propName", $value, $op);
    } else {

      $tbl = $rel['tbl'];
      $col = $rel['props'][$propName];

      $this->_addRel($rel);
      $this->_c->addPredicate("$tbl.$col", $value, $op);
    }
  }

  private function _addRelSort($relName, $propName, $dir) {
    $rel = $this->_getRel($relName);
    if ($rel === null || !isset($rel['props'][$propName])) {
      $this->_c->addSort("$relName.$propName", $dir);
    } else {

      $tbl = $rel['tbl'];
      $col = $rel['props'][$propName];

      $this->_addRel($rel);
      $this->_c->addSort("$tbl.$col", $dir);
    }
  }

  private function _addRel($rel) {
    if (in_array($rel['name'], $this->_joinedTo)) {
      return;
    }

    if ($rel['type'] === 'many-to-one') {
      $this->_c->addInnerJoin($rel['tbl'], $rel['lhs_col'], $rel['rhs_id_col']);
    } else if ($rel['type'] === 'one-to-many') {
      $this->_c->addInnerJoin($rel['tbl'], $rel['lhs_id_col'], $rel['rhs_col']);

    } else {
      $this->_c->addInnerJoin($rel['link'], $rel['lhs_id_col'], $rel['link_lhs']);
      $this->_c->chainInnerJoin($rel['tbl'], $rel['link_rhs'], $rel['rhs_id_col']);

    }
    $this->_joinedTo[] = $rel['tbl'];
  }

  private function _getProp($prop) {
    switch ($prop) {
      #{ each properties as prop
        case '/*# prop[name] #*/':
        return /*# php:prop #*/;
        break;
      #}

      default:
      // Property does not exist so there is nothing to return
      return null;
    }
  }

  private function _getRel($rel) {
    switch ($rel) {
      #{ each relationships as rel
        case '/*# rel[name] #*/':
        return /*# php:rel #*/;
        break;
      #}

      default:
      // Relationship does not exist so there is nothing to return
      return null;
    }
  }
}
