${if:comment}
// This will not get put into the template but is here to turn on syntax
// highlighting for the php code
<?php
${fi}
// Transform a many-to-one array representation into a model
$relVal = null;
if (isset($a[self::$_PROPERTY_MAP['${relationship}']])) {
  $relId = $a[self::$_PROPERTY_MAY['${relationship}']];

  if ($relId !== null) {
    $persister = ActorFactory::getActor('persister', '${rhs}');
    $relVal = $persister->getById($relId);
  }
}
$model->set${relationship}($relVal);

