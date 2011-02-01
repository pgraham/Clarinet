<?php
/**
 * =============================================================================
 * Copyright (c) 2011, Philip Graham
 * All rights reserved.
 *
 * This file is part of Clarinet and is licensed by the Copyright holder under
 * the 3-clause BSD License.  The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package clarinet
 */
namespace clarinet;

/**
 * This class encapsulates a set of criteria for a SELECT statement.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet
 */
class Criteria {

  /* Set of expressions that are AND'd together to create the criteria */
  private $_clauses = Array();

  /* Limit for the number of rows to query. */
  private $_limit = null;

  /* Clauses are created with parameterized values, these are the parameters */
  private $_params = Array();

  public function __toString() {
    $c = '';

    if (count($this->_clauses) > 0) {
      $c .= ' WHERE ' . implode(' AND ', $this->_clauses);
    }

    if ($this->_limit !== null) {
      $c .= ' LIMIT ' .$this->_limit;
    }

    return $c;
  }

  public function addEquals($column, $value) {
    $idx = count($this->_params);
    $paramName = ":param$idx";
    $this->_clauses[] = "$column = $paramName";
    $this->_params[$paramName] = $value;
  }

  public function getParameters() {
    return $this->_params;
  }

  public function setLimit($limit) {
    if (!is_int($limit)) {
      throw new Exception("Limit clause must be an integer value."
        . " $limit given");
    }
    $this->_limit = $limit;
  }
}
