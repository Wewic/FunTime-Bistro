<?php declare(strict_types = 1);

namespace ProjectFunTime\Controllers;

use Http\Request;
use Http\Response;
use ProjectFunTime\Template\FrontendRenderer;
use ProjectFunTime\Database\DatabaseProvider;
use ProjectFunTime\Session\SessionWrapper;
use ProjectFunTime\Exceptions\EntityExistsException;
use ProjectFunTime\Exceptions\SQLException;
use \InvalidArgumentException;

class Loginpage
{
   private $request;
   private $response;
   private $renderer;
   private $dbProvider;
   private $session;

   private $templateDir = 'Login';

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

   public function show()
   {
      $html = $this->renderer->render($this->templateDir, 'Loginpage');
      $this->response->setContent($html);
   }

   public function login()
   {
      $username = $this->request->getParameter('login-username');
      $password = $this->request->getParameter('login-password');
      $accType = $this->request->getParameter('login-acc-type');

      if (is_null($username) || strlen($username) == 0 ||
          is_null($password) || strlen($password) == 0 ||
          is_null($accType) || strlen($accType) == 0) {
         throw new InvalidArgumentException('required form input missing. Either username, password, or accType.');
      }

      $queryStr = "SELECT * FROM Users " .
                  "WHERE userName = '$username' AND " .
                  "password = '$password' AND " .
                  "type = '$accType' AND " .
                  "u_deleted = 'F'";
      $result = $this->dbProvider->selectQuery($queryStr);

      if (empty($result)) {
         throw new InvalidArgumentException('invalid credentials provided.');
      }
      else {
         $this->session->setValue('accType', $accType);
         $this->session->setValue('name', $result["name"]);
         $this->session->setValue('userName', $result["userName"]);
      }
   }

   public function createAccount()
   {
      // if want to be really careful. limit character size on inputs..
      // needs disclaimer about password being saved as plaintext
      $name = $this->request->getParameter('reg-name');
      $username = $this->request->getParameter('reg-username');
      $password = $this->request->getParameter('reg-password');
      $phone = $this->request->getParameter('reg-phone');
      $address = $this->request->getParameter('reg-address');

      if (is_null($name) || strlen($name) == 0 ||
          is_null($username) || strlen($username) == 0 ||
          is_null($password) || strlen($password) == 0) {
         throw new InvalidArgumentException("required form input missing. Either name, username, or password.");
      }

      $usernameQueryStr = "SELECT * FROM Users WHERE userName = '$username'";
      $usernameQueryResult = $this->dbProvider->selectQuery($usernameQueryStr);

      if (!empty($usernameQueryResult)) {
         throw new EntityExistsException("User exists with username $username");
      }

      $registerQueryStr = "INSERT INTO Users " .
                          "(userName, password, type, name, phone, address, createDate, u_deleted) " .
                          "VALUE " .
                          "('$username', '$password', 'customer', '$name', '$phone', '$address', now(), 'F')";

      $created = $this->dbProvider->insertQuery($registerQueryStr);
      
      if (!$created) {
         throw new SQLException("Failed to create User with $name, $username, $password");
      }
   }
}