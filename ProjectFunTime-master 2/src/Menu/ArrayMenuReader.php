<?php declare(strict_types = 1);

namespace ProjectFunTime\Menu;

class ArrayMenuReader implements MenuReader
{
   public function readMenu($accType) : array
   {
      if (is_null($accType)) {
         return [];
      }
      elseif (strcasecmp($accType, 'customer') == 0) {
         return [
            ['href' => '#', 'text' => 'Menu',
             'children' => [['href' => '/menuItems', 'text' => 'View'],
                            ['href' => '/menuItem/search', 'text' => 'Search']]],
            ['href' => '#', 'text' => 'Orders',
             'children' => [['href' => '/order/current', 'text' => 'Current'],
                            ['href' => '/orders/paid', 'text' => 'History']]],
            ['href' => '/ingredients', 'text' => 'Ingredients'],
            ['href' => '/account', 'text' => 'Account']
         ];
      }
      elseif (strcasecmp($accType, 'chef') == 0 ||
              strcasecmp($accType, 'admin') == 0) {
         return [
            ['href' => '#', 'text' => 'Menu',
             'children' => [['href' => '/menuItem/create', 'text' => 'Create'],
                            ['href' => '/menuItems', 'text' => 'View'],
                            ['href' => '/menuItem/search', 'text' => 'Search']]],
            ['href' => '#', 'text' => 'Orders',
             'children' => [['href' => '/order/current', 'text' => 'Current'],
                            ['href' => '/orders/paid', 'text' => 'History']]],
            ['href' => '/ingredients', 'text' => 'Ingredients'],
            ['href' => '/account', 'text' => 'Account']
         ];
      }
   }
}