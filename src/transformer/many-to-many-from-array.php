${if:comment}
// This will not get put into the template but is here to turn on syntax
// highlighting for the php code
<?php
${fi}
// Transform a many-to-many array representation into a set of models
$relVal = null;
if (isset($a[self::$_PROPERTY_MAP['${relationship}']])) {
  $relIds = $a[self::$_PROPERTY_MAP['${relationship}']];

  $c = new Criteria();
  $c->addIn('${rhsIdProperty}', $relIds);

  $persister = ActorFactory::getActor('persister', '${rhs}');
  $relVal = $persister->retrieve($c);
}
$model->set${relationship}($relVal);

