${if:comment}
// This will not get put into the template but is here to turn on syntax
// highlighting for the php code
<?php
${fi}
// Transform a many-to-one relationship into an array representation
$relVal = $model->get${relationship}();
if ($relVal === null) {
  $a[self::$_PROPERTY_MAP['${relationship}']] = null;
} else {
  $a[self::$_PROPERTY_MAP['${relationship}']] = $relVal->get${rhsIdProperty}();
}

