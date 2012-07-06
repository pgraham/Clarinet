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
namespace zeptech\orm\runtime;

/**
 * This class controls access to invocations of update and create methods.
 * The idea is to treat an invocation of a create or update method as a resource
 * which can only accessed once per save block.  A save block is a series of
 * saves that are triggered by a userland create/update invocation.  I.e, a save
 * block can be started by a persister-persister save invocation.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class SaveLock {

  private static $_locked = null;

  public static function acquire() {
    if (self::$_locked === null) {
      self::$_locked = array();
      return new SaveLock(true);
    }
    return new SaveLock(false);
  }

  public static function isLocked($model) {
    if (self::$_locked === null) {
      return false;
    }

    if (in_array($model, self::$_locked)) {
      return true;
    }

    return false;
  }

  /*
   * ===========================================================================
   * Instance
   * ===========================================================================
   */

  private $_canRelease;

  private function __construct($canRelease) {
    $this->_canRelease = $canRelease;
  }

  public function forceRelease() {
    self::$_locked = null;
  }

  public function lock($model) {
    self::$_locked[] = $model;
  }

  public function release() {
    if ($this->_canRelease) {
      self::$_locked = null;
    }
  }
}
