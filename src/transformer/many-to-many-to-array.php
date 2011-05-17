${if:comment}
// This will not get put into the template but is here to turn on syntax
// highlighting for the php code
<?php
${fi}
// Transform a many-to-many relationship into an array representation
$relVal = $model->get${relationship}();
if ($relVal === null) {
  $a[self::$_PROPERTY_MAP['${relationship}']] = null;
} else {
  $relIds = array();
  foreach ($relVal AS $rel) {
    $relIds[] = $rel->get${rhsIdProperty}();
  }
  $a[self::$_PROPERTY_MAP['${relationship}']] = $relIds;
}

