<?php
/**
 * This script generates a base database from the mock_db.sql file as an SQLite
 * database in __DIR__ . '/mock_db.template.sq3'
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
define('TEMPLATE_FILE', __DIR__ . '/mock_db.template.sq3');
if (file_exists(TEMPLATE_FILE)) {
  unlink(TEMPLATE_FILE);
}

$pdo = new PDO('sqlite:' . TEMPLATE_FILE);

$lines = explode("\n", file_get_contents(__DIR__ . '/mock_db.sql'));

$curStmt = '';
foreach ($lines AS $line) {
  if (substr($line, 0, 2) == '--') {
    continue;
  }
  $curStmt .= $line;

  if (substr($curStmt, -1) == ';') {
    echo $curStmt . "\n\n";
    $pdo->exec($curStmt);
    $curStmt = '';
  }
}
