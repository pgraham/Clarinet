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
 */
namespace zeptech\orm\generator\model;

/**
 * This interface is implemented by multiton objects that provide a unique
 * string identifier for each instance in the same runtime.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
interface Identifiable {

  /**
   * Getter for the instances string identifier.  Each instance of Identifiable
   * in the same runtime must return a unique identifier.
   *
   * @return string
   */
  public function getIdentifier();

}
