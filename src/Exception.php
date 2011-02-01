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
 * Clarinet exception class.  All messages are prepended with 'Clarinet: '.
 * The constructor is also overloaded to allow a previous exception to be
 * given without requiring a code.
 *
 * @author Philip Graham <philip@zeptech.ca>
 * @package clarinet
 */
class Exception extends \Exception {

  /**
   * Create a new clarinet exception
   *
   * @param string $msg The exception's message.
   * @param integer|Exception $code Either the exception's code or the causing
   *     exception.
   * @param Exception $previous The exception that is the reason this exception
   *     is being thrown.
   */
  public function __construct($msg, $code = 0, \Exception $previous = null) {
    $msg = 'Clarinet: ' . $msg;

    if ($code instanceof \Exception) {
      $previous = $code;
      $code = 0;
    }
    parent::__construct($msg, $code, $previous);
  }
}
