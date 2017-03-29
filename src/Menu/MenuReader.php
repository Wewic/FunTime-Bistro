<?php declare(strict_types = 1);

namespace ProjectFunTime\Menu;

interface MenuReader
{
   public function readMenu($accType) : array;
}