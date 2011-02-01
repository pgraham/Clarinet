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
 * This file sets up the environment for running tests.
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @package clarinet/test
 */

/*
 * -----------------------------------------------------------------------------
 * SINCE BASSOON RELIES ON REED IN ORDER TO PROPERLY TEST SOME CLASSES WE NEED
 * TO LOAD SOME REED CLASSES.  FOR THIS REASON THE TESTS WON'T RUN UNTIL THIS
 * PATH POINTS TO THE SOURCE DIRECTORY OR A REED INSTALLATION.
 * -----------------------------------------------------------------------------
 */
define('REED_PATH', __DIR__ . '/../../reed/src');
require_once REED_PATH . '/Autoloader.php';

require_once __DIR__ . '/../src/Autoloader.php';
require_once __DIR__ . '/Autoloader.php';
