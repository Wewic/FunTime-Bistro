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

class Ingredientpage
{
   private $request;
   private $response;
   private $renderer;
   private $dbProvider;
   private $session;

   private $templateDir = 'Ingredient';

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
      $accType = $this->session->getValue('accType');

      if (is_null($accType)) {
         header('Location: /');
         exit();
      }

      if (strcasecmp($accType, 'customer') == 0) {
         $page = 'IngredientPartialViewpage';
      }
      elseif (strcasecmp($accType, 'chef') == 0 || strcasecmp($accType, 'admin') == 0) {
         $page = 'IngredientFullViewpage';
      }

      $ingredQueryStr = "SELECT name, type FROM Ingredient " .
                        "WHERE i_deleted = 'F'";
      $ingredResult = $this->dbProvider->selectMultipleRowsQuery($ingredQueryStr);

      $data = [
         'ingredients' => $ingredResult
      ];
      $html = $this->renderer->render($this->templateDir, $page, $data);
      $this->response->setContent($html);
   }

   public function create()
   {
      $ingredName = trim($this->request->getParameter('ingredient-name'));
      $ingredType = trim($this->request->getParameter('ingredient-type'));

      $accType = $this->session->getValue('accType');

      if (is_null($accType) ||
          (strcasecmp($accType, 'chef') != 0 &&
          strcasecmp($accType, 'admin') != 0)) {
         throw new PermissionException("Must be admin or chef in order to create ingredient");
      }

      if (is_null($ingredName) || strlen($ingredName) == 0 ||
          is_null($ingredType) || strlen($ingredType) == 0) {
         throw new InvalidArgumentException("required form input missing. Ingredient name or type.");
      }

      $ingredQueryStr = "SELECT * FROM Ingredient " .
                        "WHERE name = '$ingredName' AND i_deleted = 'F'";
      $ingredQueryResult = $this->dbProvider->selectQuery($ingredQueryStr);

      if (!empty($ingredQueryResult)) { //never gets thrown?? 
         throw new EntityExistsException("Ingredient exists with name $ingredName");
      }

      $deletedIngredQueryStr = "SELECT * FROM Ingredient " .
                               "WHERE name = '$ingredName' AND i_deleted = 'T'";

      $deletedIngredQueryResult = $this->dbProvider->selectQuery($deletedIngredQueryStr);

      if (!empty($deletedIngredQueryResult)) {
         $createIngredQueryStr = "UPDATE Ingredient " .
                                 "SET type = '$ingredType', i_deleted = 'F' " .
                                 "WHERE name = '$ingredName' AND i_deleted = 'T'";
      }
      else {
         $createIngredQueryStr = "INSERT INTO Ingredient (name, type, i_deleted) VALUES ('$ingredName', '$ingredType', 'F')";
      }

      $created = $this->dbProvider->insertQuery($createIngredQueryStr);
      
      if (!$created) { 
         throw new SQLException("Failed to create Ingredient with $ingredName");
      }
   }

   public function update()
   {
      $ingredName = trim($this->request->getParameter('ingredient-name'));
      $newIngredName = trim($this->request->getParameter('new-ingredient-name'));
      $newIngredType = trim($this->request->getParameter('new-ingredient-type'));

      $accType = $this->session->getValue('accType');
      if (is_null($accType) ||
          (strcasecmp($accType, 'chef') != 0 &&
          strcasecmp($accType, 'admin') != 0)) {
         throw new PermissionException("Must be admin or chef in order to update ingredient");
      }

      if (is_null($ingredName) || strlen($ingredName) == 0 ||
          is_null($newIngredName) || strlen($newIngredName) == 0 ||
          is_null($newIngredType) || strlen($newIngredType) == 0) {
         throw new InvalidArgumentException("required form input missing. Either ingredient name, new ingredient name or type.");
      }

      $validateQueryStr = "SELECT * FROM Ingredient " .
                          "WHERE name = '$ingredName' AND i_deleted = 'F'";
      $validateResult = $this->dbProvider->selectQuery($validateQueryStr);

      if (!empty($validateResult)) {
         $updateQueryStr = "UPDATE Ingredient " .
                           "SET name = '$newIngredName', type = '$newIngredType'" .
                           "WHERE name = '$ingredName' AND i_deleted = 'F'";

         $updated = $this->dbProvider->updateQuery($updateQueryStr);

         if (!$updated) {
            throw new SQLException("Failed to update Ingredient $ingredName with $newIngredName");
         }
      }
      else {
         throw new MissingEntityException("Unable to find Ingredient $ingredName to update");
      }
   }

   public function delete()
   {
      $ingredName = trim($this->request->getParameter('ingredient-name'));

      $accType = $this->session->getValue('accType');
      if (is_null($accType) ||
          (strcasecmp($accType, 'admin') != 0 &&
           strcasecmp($accType, 'chef') != 0)) {
         throw new PermissionException("Must be admin or chef in order to delete ingredient");
      }

      if (is_null($ingredName) || strlen($ingredName) == 0) {
         throw new InvalidArgumentException("Ingredient name missing.");
      }

      $validateQueryStr = "SELECT * FROM Ingredient " .
                          "WHERE name = '$ingredName'";
      $validateResult = $this->dbProvider->selectQuery($validateQueryStr);

      if (!empty($validateResult)) {
         $softDeleteQuery = "UPDATE Ingredient " .
                            "SET i_deleted = 'T' " .
                            "WHERE name = '$ingredName'";
         $softDeleteResult = $this->dbProvider->updateQuery($softDeleteQuery);

         if (!$softDeleteResult) {
            throw new SQLException("Failed to (soft-)delete Ingredient");
         }
      }
      else {
         throw new MissingEntityException("Unable to find Ingredient $ingredName to delete");
      }
   }
}