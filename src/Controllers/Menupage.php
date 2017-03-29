<?php declare(strict_types = 1);

namespace ProjectFunTime\Controllers;

use Http\Request;
use Http\Response;
use ProjectFunTime\Template\FrontendRenderer;
use ProjectFunTime\Database\DatabaseProvider;
use ProjectFunTime\Session\SessionWrapper;
use ProjectFunTime\Exceptions\PermissionException;
use ProjectFunTime\Exceptions\MissingEntityException;
use ProjectFunTime\Exceptions\EntityExistsException;
use ProjectFunTime\Exceptions\SQLException;
use \InvalidArgumentException;

class Menupage
{
   private $request;
   private $response;
   private $renderer;
   private $dbProvider;
   private $session;

   private $templateDir = 'Menu';

   public function __construct(
      Request $request,
      Response $response,
      FrontendRenderer $renderer,
      DatabaseProvider $dbProvider,
      SessionWrapper $session)
   {
      $this->request = $request;
      $this->response = $response;
      $this->renderer = $renderer;
      $this->dbProvider = $dbProvider;
      $this->session = $session;
   }

   private function in_array_case($val, $arr)
   {
      foreach($arr as $arrElement) {
         if (strcasecmp($val, $arrElement) == 0) {
            return true;
         }
      }
      return false;
   }

   private function filterArray($arr, $key, $val) : array
   {
      $result = [];

      for ($i = 0; $i < count($arr); $i++) {
         $element = $arr[$i];

         if (strcasecmp($element[$key], $val) == 0) {
            $result[] = $element;
         }
      }

      return $result;
   }

   private function filterCategoryArray($arr, $category) : array
   {
      return $this->filterArray($arr, 'category', $category);
   }

   private function filterOtherCategoryArray($arr, $categoryArr) : array
   {
      $result = [];

      for ($i = 0; $i < count($arr); $i++) {
         $element = $arr[$i];

         if (!$this->in_array_case($element['category'], $categoryArr)) {
            $result[] = $element;
         }
      }

      return $result;
   }

   private function flagHealthyMenuItems($menuItems) : array
   {
      $healthyOptionQuery = "SELECT DISTINCT name, price, category, description, quantity " .
                            "FROM Menuitem m " .
                            "WHERE m_deleted = 'F' " .
                            "AND NOT EXISTS " .
                            "(SELECT DISTINCT menuItem_name " .
                            "FROM MadeOf mo " .
                            "WHERE m.name = mo.menuItem_name " .
                            "AND ingredient_name IN " .
                               "(SELECT name " .
                                "FROM Ingredient WHERE type = 'Meat'))";
      $healthyOptionResult = $this->dbProvider->selectMultipleRowsQuery($healthyOptionQuery);

      foreach ($menuItems as $index => $menuItem) {
         if (!empty($this->filterArray($healthyOptionResult, 'name', $menuItem['name']))) {
            $menuItems[$index]['healthy'] = 'true';
         }
      }

      return $menuItems;
   }

   public function showAllMenuItems()
   {
      $accType = $this->session->getValue('accType');

      if (is_null($accType)) {
         header('Location: /');
         exit();
      }

      $menuQueryStr = "SELECT menu_id, name, price, category, description, quantity FROM Menuitem " .
                      "WHERE m_deleted = 'F'";
      $menuResult = $this->dbProvider->selectMultipleRowsQuery($menuQueryStr);

      $menuResult = $this->flagHealthyMenuItems($menuResult);

      $appetizers = $this->filterCategoryArray($menuResult, 'appetizer');
      $entrees = $this->filterCategoryArray($menuResult, 'entree');
      $desserts = $this->filterCategoryArray($menuResult, 'dessert');
      $drinks = $this->filterCategoryArray($menuResult, 'drink');

      $others = $this->filterOtherCategoryArray($menuResult, ['appetizer', 'entree', 'dessert', 'drink']);

      $data = [
         'appetizers' => $appetizers,
         'entrees' => $entrees,
         'desserts' => $desserts,
         'drinks' => $drinks,
         'others' => $others,
         'action' => 'viewMenu'
      ];

      $html = $this->renderer->render($this->templateDir, 'Menupage', $data);
      $this->response->setContent($html);
   }

   public function showCreateMenuItemForm()
   {
      $ingredQueryStr = "SELECT ing_id, name, type FROM Ingredient " .
                        "WHERE i_deleted = 'F'";
      $ingredQueryResult = $this->dbProvider->selectMultipleRowsQuery($ingredQueryStr);

      $data = [
         'action' => 'create',
         'ingredients' => $ingredQueryResult
      ];

      $html = $this->renderer->render($this->templateDir, 'MenuItemFormpage', $data);
      $this->response->setContent($html);
   }

   public function create()
   {
      $menuName = trim($this->request->getParameter('menu-item-name'));
      $menuCat = trim($this->request->getParameter('menu-item-category'));
      $menuPrice = trim($this->request->getParameter('menu-item-price'));
      $menuQty = trim($this->request->getParameter('menu-item-quantity'));
      $menuDesc = trim($this->request->getParameter('menu-item-description'));
      $menuItemIngreds = $this->request->getParameter('menu-item-ingredients');

      $ingredNames = [];
      foreach ($menuItemIngreds as $ingred) {
         if (is_null($ingred['ingredName']) || strlen(trim($ingred['ingredName'])) == 0) {
            throw new InvalidArgumentException("ingredient input invalid.");
         }

         array_push($ingredNames, trim($ingred['ingredName']));
      }

      $accType = $this->session->getValue('accType');

      if (is_null($accType) ||
          (strcasecmp($accType, 'chef') != 0 &&
          strcasecmp($accType, 'admin') != 0)) {
         throw new PermissionException("Must be admin or chef in order to create menu item");
      }

      if (is_null($menuName) || strlen($menuName) == 0 ||
          is_null($menuPrice) || strlen($menuPrice) == 0 ||
          !is_numeric($menuPrice) ||
          is_null($menuCat) || strlen($menuCat) == 0 ||
          is_null($menuQty) || strlen($menuQty) == 0 || 
          !is_numeric($menuQty)) {
         throw new InvalidArgumentException("required form input missing. Name, Category, Price, Quantity and Description must be valid.");
      }

      $menuQueryStr = "SELECT * FROM Menuitem " .
                      "WHERE name = '$menuName' " .
                      "AND m_deleted = 'F'";
      $menuQueryResult = $this->dbProvider->selectQuery($menuQueryStr);

      if (!empty($menuQueryResult)) {
         throw new EntityExistsException("Menu item exists with name $menuName");
      }

      $deletedMenuQueryStr = "SELECT * FROM Menuitem " .
                             "WHERE name = '$menuName' " .
                             "AND m_deleted = 'T'";
      $deletedMenuQueryResult = $this->dbProvider->selectQuery($deletedMenuQueryStr);

      if (!empty($deletedMenuQueryResult)) {
         $createMenuQueryStr = "UPDATE Menuitem " .
                                 "SET price = '$menuPrice', " .
                                 "category = '$menuCat', " .
                                 "description = '$menuDesc', " .
                                 "quantity = '$menuQty', " .
                                 "m_deleted = 'F' " .
                                 "WHERE name = '$menuName'";
         $created = $this->dbProvider->updateQuery($createMenuQueryStr);
      }
      else {
         $createMenuQueryStr = "INSERT INTO Menuitem " .
                               "(name, price, category, description, quantity, m_deleted) " .
                               "VALUES " .
                               "('$menuName', '$menuPrice', '$menuCat', '$menuDesc', '$menuQty', 'F' )";
         $created = $this->dbProvider->insertQuery($createMenuQueryStr);
      }
      
      if (!$created) { 
         throw new SQLException("Failed to create Menu item with $menuName");
      }

      $madeOfQueryStr = "INSERT INTO MadeOf " .
                        "(menuItem_name, ingredient_name) " .
                        "VALUES ";

      $valuesClause = '';
      foreach($ingredNames as $ingredName) {
         $valuesClause .= "('$menuName', '$ingredName'), ";
      }
      $valuesClause = rtrim($valuesClause, ', ');

      $madeOfQueryStr = $madeOfQueryStr . $valuesClause;
      $insertQueryResult = $this->dbProvider->insertQuery($madeOfQueryStr);

      if (!$insertQueryResult) {
         throw new SQLException("Failed to associate Menu item with ingredients");
      }
   }

   public function showUpdateMenuItemForm($routeParams)
   {
      $menuId = $routeParams['id'];

      $menuItemQueryStr = "SELECT name, price, category, description, quantity FROM Menuitem " .
                          "WHERE menu_id = $menuId " .
                          "AND m_deleted = 'F'";
      $menuItemResult = $this->dbProvider->selectQuery($menuItemQueryStr);

      $ingredQueryStr = "SELECT i.name, i.type FROM Ingredient i, MadeOf mo, Menuitem m " .
                        "WHERE mo.menuItem_name = m.name " .
                        "AND m.menu_id = $menuId " .
                        "AND mo.ingredient_name = i.name";
      $ingredQueryResult = $this->dbProvider->selectMultipleRowsQuery($ingredQueryStr);

      if (empty($menuItemResult)) {
         throw new MissingEntityException('Unable to find menu item information');
      }

      $data = [
         'action' => 'update',
         'id' => $menuId,
         'name' => $menuItemResult['name'],
         'category' => $menuItemResult['category'],
         'price' => $menuItemResult['price'],
         'quantity' => $menuItemResult['quantity'],
         'description' => $menuItemResult['description'],
         'ingredients' => $ingredQueryResult
      ];

      $html = $this->renderer->render($this->templateDir, 'MenuItemFormpage', $data);
      $this->response->setContent($html);
   }

   public function update()
   {
      $menuId = trim($this->request->getParameter('menu-id'));

      $newMenuName = trim($this->request->getParameter('new-menu-item-name'));
      $newMenuPrice = trim($this->request->getParameter('new-menu-item-price'));
      $newMenuCat = trim($this->request->getParameter('new-menu-item-category'));
      $newMenuDesc = trim($this->request->getParameter('new-menu-item-description'));
      $newMenuQty = trim($this->request->getParameter('new-menu-item-quantity'));

      $accType = $this->session->getValue('accType');
      if (is_null($accType) ||
          (strcasecmp($accType, 'chef') != 0 &&
          strcasecmp($accType, 'admin') != 0)) {
         throw new PermissionException("Must be admin or chef in order to update menu items");
      }

      if (is_null($newMenuName) || strlen($newMenuName) == 0 ||
          is_null($newMenuPrice) || strlen($newMenuPrice) == 0 || !is_numeric($newMenuPrice) ||
          is_null($newMenuCat) || strlen($newMenuCat) == 0 ||
          is_null($newMenuQty) || strlen($newMenuQty) == 0 || !is_numeric($newMenuQty)) {
         throw new InvalidArgumentException("required form input missing. Either invalid name, price, category, or quantity.");
      }

      $validateQueryStr = "SELECT * FROM Menuitem " .
                          "WHERE menu_id = '$menuId' " .
                          "AND m_deleted = 'F'";
      $validateResult = $this->dbProvider->selectQuery($validateQueryStr);

      if (!empty($validateResult)) {
         $updateQueryStr = "UPDATE Menuitem " .
                           "SET name = '$newMenuName', " .
                           "price = '$newMenuPrice', " .
                           "category = '$newMenuCat', " .
                           "description = '$newMenuDesc', " .
                           "quantity = '$newMenuQty' " .
                           "WHERE menu_id = '$menuId' " .
                           "AND m_deleted = 'F'";

         $updated = $this->dbProvider->updateQuery($updateQueryStr);

         if (!$updated) {
            throw new SQLException("Failed to update Menu item $menuName with $newMenuName");
         }
      }
      else {
         throw new MissingEntityException("Unable to find Menu Item with Id $menuId to update");
      }

   }

   public function delete()
   {
      $menuId = trim($this->request->getParameter('menu-id'));

      $accType = $this->session->getValue('accType');
      if (is_null($accType) ||
          (strcasecmp($accType, 'admin') != 0 &&
           strcasecmp($accType, 'chef') != 0)) {
         throw new PermissionException("Must be admin or chef in order to delete menu item");
      }

      if (is_null($menuId) || strlen($menuId) == 0) {
         throw new InvalidArgumentException("Menu item id missing.");
      }

      $validateQueryStr = "SELECT * FROM Menuitem " .
                          "WHERE menu_id = $menuId " .
                          "AND m_deleted = 'F'";
      $validateResult = $this->dbProvider->selectQuery($validateQueryStr);

      if (!empty($validateResult)) {
         $softDeleteQuery = "UPDATE Menuitem " .
                            "SET m_deleted = 'T' " .
                            "WHERE menu_id = $menuId " .
                            "AND m_deleted = 'F'";
         $softDeleteResult = $this->dbProvider->updateQuery($softDeleteQuery);

         if (!$softDeleteResult) {
            throw new SQLException("Failed to (soft-)delete Menu Item");
         }
      }
      else {
         throw new MissingEntityException("Unable to find Menu item with id  $menuId to delete");
      }
   }

   public function showMenuItemSearchForm()
   {
      $html = $this->renderer->render($this->templateDir, 'SearchMenuFormpage');
      $this->response->setContent($html);
   }

   public function showMenuItemSearchResult()
   {
      $params = $this->request->getParameters();
      $accType = $this->session->getValue('accType');

      if (is_null($accType)) {
         header('Location: /');
         exit();
      }

      $whereClause = '';
      if (array_key_exists('qtyOp', $params) && array_key_exists('qtyVal', $params)) {
         $quantityOp = $params['qtyOp'];
         $quantityVal = $params['qtyVal'];

         if (is_null($quantityOp) || strlen($quantityOp) == 0 ||
             is_null($quantityVal) || strlen($quantityVal) == 0) {
            throw new InvalidArgumentException('required form input missing. Either quantity op or value');
         }

         if (!is_numeric($quantityVal)) {
            throw new InvalidArgumentException('quantity value is invalid.');
         }

         $quantityOpChar = '';
         switch ($quantityOp) {
            case 'gt':
               $quantityOpChar = '>';
               break;
            case 'lt':
               $quantityOpChar = '<';
               break;
            case 'eq':
               $quantityOpChar = '=';
               break;
            default:
               throw InvalidArugmentException('quantity op not recognized.');
         }

         $whereClause .= " quantity $quantityOpChar $quantityVal AND ";
      }

      if (array_key_exists('priceOp', $params) && array_key_exists('priceVal', $params)) {
         $priceOp = $params['priceOp'];
         $priceVal = $params['priceVal'];

         if (is_null($priceOp) || strlen($priceOp) == 0 ||
             is_null($priceVal) || strlen($priceVal) == 0) {
            throw new InvalidArgumentException('required form input missing. Either price op or value');
         }

         if (!is_numeric($priceVal)) {
            throw new InvalidArgumentException('price value is invalid.');
         }

         $priceOpChar = '';
         switch ($priceOp) {
            case 'gt':
               $priceOpChar = '>';
               break;
            case 'lt':
               $priceOpChar = '<';
               break;
            case 'eq':
               $priceOpChar = '=';
               break;
            default:
               throw InvalidArgumentException('price op not recognized.');
         }

         $whereClause .= " price $priceOpChar $priceVal AND ";
      }

      $selectClause = '';
      if (array_key_exists('name', $params) && strcmp($params['name'], 'checked') == 0) {
         $selectClause .= 'name, ';
      }
      if (array_key_exists('category', $params) && strcmp($params['category'], 'checked') == 0) {
         $selectClause .= 'category, ';
      }
      if (array_key_exists('quantity', $params) && strcmp($params['quantity'], 'checked') == 0) {
         $selectClause .= 'quantity, ';
      }
      if (array_key_exists('price', $params) && strcmp($params['price'], 'checked') == 0) {
         $selectClause .= 'price, ';
      }
      if (array_key_exists('description', $params) && strcmp($params['description'], 'checked') == 0) {
         $selectClause .= 'description, ';
      }
      $selectClause = rtrim($selectClause, ', ');

      $menuQueryStr = '';
      if (strcmp($selectClause, '') == 0) {
         throw new InvalidArgumentException('Invalid details to show. Select at least one.');
      }
      if (strcmp($whereClause, '') == 0) {
         $menuQueryStr = "SELECT $selectClause FROM Menuitem " .
                         "WHERE m_deleted = 'F'";
      }
      else {
         $menuQueryStr = "SELECT $selectClause FROM Menuitem " .
                         "WHERE $whereClause " .
                         "m_deleted = 'F'";
      }

      $menuResult = $this->dbProvider->selectMultipleRowsQuery($menuQueryStr);

      $menuResult = $this->flagHealthyMenuItems($menuResult);

      $len = count($menuResult);
      $mid = ceil($len / 2);

      $menuCol1 = array_slice($menuResult, 0, $mid);
      $menuCol2 = array_slice($menuResult, (int) $mid);

      $data = [
         'column1' => $menuCol1,
         'column2' => $menuCol2,
         'action' => 'search'
      ];
      $html = $this->renderer->render($this->templateDir, 'SearchMenuResultpage', $data);
      $this->response->setContent($html);
   }

}