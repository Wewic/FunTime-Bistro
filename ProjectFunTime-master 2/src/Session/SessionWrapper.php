<?php declare(strict_types = 1);

namespace ProjectFunTime\Session;

interface SessionWrapper
{
   public function getValue($key);

   public function setValue($key, $value);
}