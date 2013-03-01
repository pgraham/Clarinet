<?php
/**
 * =============================================================================
 * Copyright (c) 2013, Philip Graham
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
 */

require 'SplClassLoader.php';

// Initialize Mockery
// -----------------------------------------------------------------------------
require 'Mockery/Loader.php';
require 'Hamcrest/Hamcrest.php';
$loader = new \Mockery\Loader();
$loader->register();

// Register loader for clarinet classes
$ormPath = realpath(__DIR__ . '/..');

$ldr = new SplClassLoader('zeptech\orm', $ormPath);
$ldr->register();

$ldr = new SplClassLoader('zpt\orm', $ormPath);
$ldr->register();
