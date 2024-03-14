<?php
function hasAttribute($reflection, $attribute)
{
  return count($reflection->getAttributes($attribute)) > 0;
}

/**
 * @template T
 * @param ReflectionClass|ReflectionMethod|ReflectionProperty|ReflectionParameter $reflection
 * @param class-string<T> $attribute
 * @return T|false
 */
function getAttribute($reflection, $attribute)
{
  $attributes = $reflection->getAttributes($attribute);
  if (count($attributes) > 0) {
    return $attributes[0]->newInstance();
  }
  return false;
}
