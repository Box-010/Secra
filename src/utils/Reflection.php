<?php
function hasAttribute($reflection, $attribute)
{
  return count($reflection->getAttributes($attribute)) > 0;
}

function getAttribute($reflection, $attribute)
{
  $attributes = $reflection->getAttributes($attribute);
  if (count($attributes) > 0) {
    return $attributes[0]->newInstance();
  }
  return false;
}
