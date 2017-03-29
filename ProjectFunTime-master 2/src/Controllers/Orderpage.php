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

class Orderpage {
   private $request;
   private $response;
   private $renderer;
   private $dbProvider;
   private $session;

   private $templateDir = 'Order';

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

   public function showPaidOrders() {
      $accType = $this->session->getValue('accType');
      if (is_null($accType)) {
         header('Location: /');
         exit();
      }
      $user = $this->session->getValue('userName');

      $paidOrder_sql = "SELECT o.order_id AS orderId, " .
                              "SUM(c.qty) AS numOfItems, " .
                              "o.cookeddate AS cookedDate, " .
                              "o.cookedStatus AS cookedStatus, " .
                              "o.orderdate AS orderDate, " .
                              "o.chef_userName AS chef, " .
                              "SUM(Case When c.qty >= 2 Then m.price * c.qty * 0.9 " .
                                                        "ELSE m.price * c.qty END) AS totalPrice " .
                       "FROM Orders o, Contains c, Menuitem m " .
                       "WHERE o.order_id = c.order_id " .
                       "AND c.name = m.name " .
                       "AND o.paymentStatus = 'paid' " .
                       "AND o.customer_userName = '$user' " .
                       "GROUP BY o.order_id";
      $paidOrderResult = $this->dbProvider->selectMultipleRowsQuery($paidOrder_sql);

      $mostExpensiveOrderStr = "SELECT order_id, price FROM (SELECT o.order_id, SUM(Case When c.qty >= 2 Then m.price * c.qty * 0.9 ELSE m.price * c.qty END) as price FROM Orders o, Contains c, Menuitem m WHERE o.order_id = c.order_id AND c.name = m.name GROUP BY o.order_id) as order_price WHERE price =(SELECT max(price) FROM (SELECT o.order_id, SUM(Case When c.qty >= 2 Then m.price * c.qty * 0.9 ELSE m.price * c.qty END) as price FROM Orders o, Contains c, Menuitem m WHERE o.order_id = c.order_id AND c.name = m.name AND o.customer_userName = '$user' AND o.paymentStatus = 'paid' GROUP BY o.order_id) AS order_prices)";
      $mostExpensiveOrderResult = $this->dbProvider->selectQuery($mostExpensiveOrderStr);

/**
      $leastExpensiveOrderStr = "SELECT order_id, price FROM (SELECT o.order_id, SUM(Case When c.qty >= 2 Then m.price * c.qty * 0.9 ELSE m.price * c.qty END) as price FROM Orders o, Contains c, Menuitem m WHERE o.order_id = c.order_id AND c.name = m.name GROUP BY o.order_id) as order_price WHERE price = (SELECT min(price) FROM (SELECT o.order_id, SUM(Case When c.qty >= 2 Then m.price * c.qty * 0.9 ELSE m.price * c.qty END) as price FROM Orders o, Contains c, Menuitem m WHERE o.order_id = c.order_id AND c.name = m.name AND o.customer_userName = '$user' AND o.paymentStatus = 'paid' GROUP BY o.order_id) AS order_prices)";
      $leastExpensiveOrderResult = $this->dbProvider->selectQuery($mostExpensiveOrderStr);
*/

      foreach ($paidOrderResult as $index => $order) {
         if ($mostExpensiveOrderResult['order_id'] == $order['orderId']) {
            $paidOrderResult[$index]['mostExpensive'] = 'true';
         }
//         if ($leastExpensiveOrderResult['order_id'] == $order['orderId']) {
//            $paidOrderResult[$index]['leastExpensive'] = 'true';
//         }
      }

      $data = [
         'paidOrders' => $paidOrderResult
      ];

      $html = $this->renderer->render($this->templateDir, 'PaidOrderpage', $data);
      $this->response->setContent($html);
   }

   public function showCurrentOrder() {
      $accType = $this->session->getValue('accType');
      if (is_null($accType)) {
         header('Location: /');
         exit();
      }

      $user = $this->session->getValue('userName');

      $orderExists = $this->checkOpenOrderExists($user);
      if (!$orderExists) {
         $data = [
            'createButton' => 'show'
         ];

         $html = $this->renderer->render($this->templateDir, 'Orderpage', $data);
         $this->response->setContent($html);
      }
      else {
         $orderId = $this->getOpenOrderId($user);

         $menuItemQueryStr = "SELECT m.menu_id, m.name, m.price, m.quantity AS qtyLeft, " .
                             "c.qty AS qtyInOrder FROM Menuitem m, Contains c " .
                             "WHERE c.order_id = $orderId " .
                             "AND c.name = m.name";
         $menuItemQueryResult = $this->dbProvider->selectMultipleRowsQuery($menuItemQueryStr);

         $totalPriceQueryStr = "SELECT SUM(c.qty) AS numOfItems, " .
                               "SUM(Case When c.qty >= 2 Then m.price * c.qty * 0.9 " .
                                                        "ELSE m.price * c.qty END) AS totalPrice " .
                        "FROM Orders o, Contains c, Menuitem m " .
                        "WHERE o.order_id = c.order_id " .
                        "AND c.name = m.name " .
                        "AND o.customer_userName = '$user' " .
                        "AND o.order_id = $orderId " .
                        "GROUP BY o.order_id";
         $totalPriceQueryResult = $this->dbProvider->selectQuery($totalPriceQueryStr);

         $data = [
            'orderMenuItems' => $menuItemQueryResult
         ];

         if (array_key_exists('numOfItems', $totalPriceQueryResult)) {
            $data['totalQuantity'] = $totalPriceQueryResult['numOfItems'];
         }

         if (array_key_exists('totalPrice', $totalPriceQueryResult)) {
            $data['totalPrice'] = $totalPriceQueryResult['totalPrice'];
         }

         $html = $this->renderer->render($this->templateDir, 'Orderpage', $data);
         $this->response->setContent($html);
      }
   }

   public function showOrderMenuItemForm($routeParams) {
      $menuId = $routeParams['id'];

      $accType = $this->session->getValue('accType');
      if (is_null($accType)) {
         header('Location: /');
         exit();
      }
      $user = $this->session->getValue('userName');

      $orderExists = $this->checkOpenOrderExists($user);
      if (!$orderExists) {
         $this->createOrderHelper($user);
      }
      $orderId = $this->getOpenOrderId($user);

      $menuItemQueryStr = "SELECT m.name, m.category, m.price, m.quantity AS qtyLeft, " .
                          "c.qty AS qtyInOrder, m.description FROM Menuitem m, Contains c " .
                          "WHERE c.order_id = $orderId " .
                          "AND c.name = m.name " .
                          "AND m.menu_id = $menuId";
      $menuItemQueryResult = $this->dbProvider->selectQuery($menuItemQueryStr);

      if (empty($menuItemQueryResult)) {
         throw new MissingEntityException('Unable to find menu item information');
      }

      $data = [
         'action' => 'update',
         'id' => $menuId,
         'name' => $menuItemQueryResult['name'],
         'category' => $menuItemQueryResult['category'],
         'price' => $menuItemQueryResult['price'],
         'qtyLeft' => $menuItemQueryResult['qtyLeft'],
         'qtyInOrder' => $menuItemQueryResult['qtyInOrder'],
         'description' => $menuItemQueryResult['description']
      ];

      $html = $this->renderer->render($this->templateDir, 'OrderMenuItemFormpage', $data);
      $this->response->setContent($html);
   }

   public function createOrder() {
      $accType = $this->session->getValue('accType');
      if (is_null($accType)) {
         throw new PermissionException("Must be logged in to add menuitem in order");
      }

      $user = $this->session->getValue('userName');
      $this->createOrderHelper($user);
   }

   private function createOrderHelper($user) {
      $newOrderQueryStr = "INSERT INTO Orders " .
                          "(customer_userName, orderdate, paymentStatus, cookedStatus) " .
                          "VALUE " .
                          "('$user', now(), 'open', 'open')";
      $newOrderQueryResult = $this->dbProvider->insertQuery($newOrderQueryStr);

      if (!$newOrderQueryResult) {
         throw new SQLException("Failed to create order");
      }
   }

   private function checkOpenOrderExists($user)
   {
      $openOrderQueryStr = "SELECT * FROM Orders " .
                           "WHERE customer_userName = '$user' " .
                           "AND paymentStatus = 'open'";
      $openOrderQueryResult = $this->dbProvider->selectQuery($openOrderQueryStr);

      if (!$openOrderQueryResult) {
         return false;
      }
      else {
         return true;
      }
   }

   private function getOpenOrderId($user)
   {
      $openOrderQueryStr = "SELECT * FROM Orders " .
                           "WHERE customer_userName = '$user' " .
                           "AND paymentStatus = 'open'";
      $openOrderQueryResult = $this->dbProvider->selectQuery($openOrderQueryStr);

      if (!$openOrderQueryResult) {
         throw new SQLException('Unable to find open order.');
      }
      else {
         return $openOrderQueryResult['order_id'];
      }
   }

   private function getMenuNameFromMenuId($menuId)
   {
      $menuQueryStr = "SELECT * FROM Menuitem " .
                      "WHERE menu_id = $menuId";
      $menuQueryResult = $this->dbProvider->selectQuery($menuQueryStr);

      if (!$menuQueryResult) {
         throw new SQLException('Unable to find Menuitem from id.');
      }
      else {
         return $menuQueryResult['name'];
      }
   }

   public function addMenuItem() {
      $accType = $this->session->getValue('accType');
      if (is_null($accType)) {
         throw new PermissionException("Must be logged in to add menuitem in order");
      }

      $menuId = trim($this->request->getParameter('menu-id'));

      if (is_null($menuId) || strlen($menuId) == 0) {
         throw new InvalidArgumentException("Invalid menu id.");
      }

      $user = $this->session->getValue('userName');
      $orderExists = $this->checkOpenOrderExists($user);

      if (!$orderExists) {
         $this->createOrderHelper($user);
      }

      $orderId = $this->getOpenOrderId($user);
      $menuName = $this->getMenuNameFromMenuId($menuId);

      $validateQueryStr = "SELECT * FROM Contains " .
                          "WHERE name = '$menuName' " .
                          "AND order_id = '$orderId'";
      $validateResult = $this->dbProvider->selectQuery($validateQueryStr);

      if (empty($validateResult)) {
         $addQueryStr = "INSERT INTO Contains " .
                        "(order_id, name, qty) " .
                        "VALUE " .
                        "($orderId, '$menuName', '1')";
         $addResult = $this->dbProvider->insertQuery($addQueryStr);

         if (!$addResult) {
            throw new SQLException("Failed to add item into order");
         }
      }
      else {
         throw new EntityExistsException("Item already in Order.");
      }
   }

   public function updateMenuItemQuantity() {
      $menuId = trim($this->request->getParameter('menu-id'));
      $newQty = trim($this->request->getParameter('new-quantity'));

      $accType = $this->session->getValue('accType');
      if (is_null($accType)) {
         throw new PermissionException("Must be logged in to update menuitem in order");
      }

      $userName = $this->session->getValue('userName');
      if (is_null($userName)) {
         throw new PermissionException("Must be logged in to purchase order");
      }

      $orderId = $this->getOpenOrderId($userName);
      $menuName = $this->getMenuNameFromMenuId($menuId);

      if (is_null($menuId) || strlen($menuId) == 0 || !ctype_digit($menuId)) {
         throw new InvalidArgumentException("required form input missing. Invalid menu item name or order Id.");
      }

      $validateQueryStr = "SELECT * FROM Contains " .
                          "WHERE name = '$menuName' " .
                          "AND order_id = $orderId";
      $validateResult = $this->dbProvider->selectQuery($validateQueryStr);

      if (!empty($validateResult)) {
         $updateQueryStr = "UPDATE Contains " .
                           "SET qty = $newQty " .
                           "WHERE order_id = $orderId " .
                           "AND name = '$menuName'";
         $updated = $this->dbProvider->updateQuery($updateQueryStr);

         if (!$updated) {
            throw new SQLException("Failed to update item $menuName in order $orderId with quantity of $newItemQuantity ");
         }
      }
      else {
         throw new MissingEntityException("Item not in Order, quantity cannot be changed!");
      }

   }

   public function removeMenuItem() {
      $menuId = trim($this->request->getParameter('menu-id'));

      $accType = $this->session->getValue('accType');
      if (is_null($accType)) {
         throw new PermissionException("Must be logged in to remove menuitem from order");
      }

      $userName = $this->session->getValue('userName');
      if (is_null($userName)) {
         throw new PermissionException("Must be logged in to purchase order");
      }

      $orderId = $this->getOpenOrderId($userName);

      if (is_null($menuId) || strlen($menuId) == 0 || !ctype_digit($menuId)) {
         throw new InvalidArgumentException("Menu id missing.");
      }

      $menuName = $this->getMenuNameFromMenuId($menuId);

      $validateQueryStr = "SELECT * FROM Contains " .
                          "WHERE name = '$menuName' " .
                          "AND order_id = $orderId";
      $validateResult = $this->dbProvider->selectQuery($validateQueryStr);

      if (!empty($validateResult)) {
         $deleteQueryStr = "DELETE FROM Contains " .
                           "WHERE name = '$menuName' " .
                           "AND order_id = $orderId";

         $deleteResult = $this->dbProvider->updateQuery($deleteQueryStr);

         if (!$deleteResult) {
            throw new SQLException("Item failed to be removed from Order!");
         }
      }
      else {
         throw new MissingEntityException("Unable to find item $menuName in Order $orderid to delete");
      }
   }

   public function purchase()
   {
      $paymentType = trim($this->request->getParameter('paymentType'));

      $accType = $this->session->getValue('accType');
      if (is_null($accType)) {
         throw new PermissionException("Must be logged in to purchase order");
      }

      $userName = $this->session->getValue('userName');
      if (is_null($userName)) {
         throw new PermissionException("Must be logged in to purchase order");
      }

      $orderId = $this->getOpenOrderId($userName);

      //retrieves price of order by $orderId and gives price 10% discount of customer ordered the same item more than once
      $priceQuery = "SELECT SUM(Case When c.qty >= 2 Then m.price * c.qty * 0.9 " .
                       "ELSE m.price * c.qty END) as price " .
                    "FROM Orders o, Contains c, Menuitem m " .
                    "WHERE o.order_id = $orderId " .
                    "AND o.order_id = c.order_id " .
                    "AND c.name = m.name " .
                    "AND o.paymentStatus != 'paid' " .
                    "AND o.customer_userName = '$userName'";
      $priceResult = $this->dbProvider->selectQuery($priceQuery);
      $price = $priceResult['price'];

      //check if there are enough qty in menuitems for purchase
      $check_avai_query = "SELECT m.name FROM Menuitem m, Contains c " .
                          "WHERE m.name = c.name " .
                          "AND c.order_id = $orderId " .
                          "AND m.quantity - c.qty >= 0";
      $check_avai = $this->dbProvider->selectQuery($check_avai_query);

      //updates qty in menuitems
      $del_qty_sql = "UPDATE Menuitem m, Contains c " .
                     "SET m.quantity = m.quantity - c.qty " .
                     "WHERE m.name = c.name " .
                     "AND c.order_id = $orderId " .
                     "AND m.quantity - c.qty >= 0";


      //create invoice
      $create_invoice_sql = "INSERT INTO Invoice " .
                            "(order_id, customer_userName, cost, createdate, paymentType) " .
                            "VALUE " .
                            "($orderId, '$userName', $price, now(), '$paymentType')";

      //soft deletes order from order table
      $del_order_sql = "UPDATE Orders " .
                       "SET paymentStatus = 'paid' " .
                       "WHERE order_id = $orderId";

      if(count($check_avai) > 0) {
         $del_order_query = $this->dbProvider->updateQuery($del_order_sql);

         if ($del_order_query) {
            $create_invoice_query = $this->dbProvider->insertQuery($create_invoice_sql);
            if ($create_invoice_query) {
               $del_qty_query = $this->dbProvider->updateQuery($del_qty_sql);
               $del_order_query = $this->dbProvider->updateQuery($del_order_sql);

               if (!$del_qty_query){
                  throw new SQLException("Order was not paid for");
               }
            }
            else {
               throw new SQLException("Error creating invoice");
            }
         }
         else {
            throw new SQLException("Error deleting order");
         }
      }
      else {
         throw new SQLException("Not enough quantity, please change your order");
      }

   }

   public function showAllChefOrder () {

    $accType = $this->session->getValue('accType');
      if (is_null($accType) || strcasecmp($accType, 'chef' ) != 0) {
         header('Location: /');
         exit();
      }

      $openOrderQueryStr = "SELECT * FROM Orders " . 
                           "WHERE cookedStatus = 'open' " .
                           "AND paymentStatus = 'paid'";
      $openOrderResult = $this->dbProvider->selectMultipleRowsQuery($openOrderQueryStr);

      $inProgOrderQueryStr = "SELECT * FROM Orders " . 
                             "WHERE cookedStatus = 'in progress'" . 
                             "AND paymentStatus = 'paid'";
      $inProgOrderResult = $this->dbProvider->selectMultipleRowsQuery($inProgOrderQueryStr);

      $data = [
         'openOrder' => $openOrderResult,
         'inProgOrder' => $inProgOrderResult
      ];

      $html = $this->renderer->render($this->templateDir, 'ChefOrderpage', $data);
      $this->response->setContent($html);
      }

  public function chefStartOrder ($routeParams) {

      $chefuser = $this->session->getValue('userName');
      $orderid = $this->request->getParameter('order-id');

      $accType = $this->session->getValue('accType');
      if (is_null($accType) || strcasecmp($accType, 'chef') != 0) {
         throw new PermissionException("Must be Chef in order to delete chef account");
      }

      if (is_null($chefuser) || strlen($chefuser) == 0) {
         throw new InvalidArgumentException("Username missing.");
      }

      $orderToUpdateQueryStr = "UPDATE Orders " .
                               "SET cookedStatus = 'in progress' " .
                               ", chef_userName = '$chefuser' " .
                               "WHERE order_id = '$orderid' " .
                               "AND paymentStatus = 'paid' " .
                               "AND cookedStatus = 'open' ";

      $orderToUpdateResult = $this->dbProvider->updateQuery($orderToUpdateQueryStr);

      if (!$orderToUpdateResult) {
            throw new SQLException("Failed to start order");
         }
    }

    public function chefCompleteOrder ($routeParams) {

      $chefuser = $this->session->getValue('userName');
      $orderid = $this->request->getParameter('order-id');

      $accType = $this->session->getValue('accType');
      if (is_null($accType) || strcasecmp($accType, 'chef') != 0) {
         throw new PermissionException("Must be Chef in order to delete chef account");
      }

      if (is_null($chefuser) || strlen($chefuser) == 0) {
         throw new InvalidArgumentException("Username missing.");
      }

      $orderToUpdateQueryStr = "UPDATE Orders " .
                               "SET cookeddate = now(), cookedStatus = 'cooked' " .
                               "WHERE order_id = $orderid " .
                               "AND cookedStatus = 'in progress' ".
                               "AND paymentStatus = 'paid'";

      $orderToUpdateResult = $this->dbProvider->updateQuery($orderToUpdateQueryStr);

      if (!$orderToUpdateResult) {
            throw new SQLException("Failed to start order");
         }
    }
}