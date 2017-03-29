<?php declare(strict_types = 1);

namespace ProjectFunTime\Database;

class MySQLDatabaseProvider implements DatabaseProvider
{
   private $dbProvider;

   public function __construct()
   {
// change to match your environment
      $dbhost = "funtime.cue09nzmpyxl.us-west-2.rds.amazonaws.com:3306";
      $dbuser = "awsuser";
      $dbpass = "mypassword";
      $dbname = "funtime";

      $this->dbProvider = new \mysqli($dbhost, $dbuser, $dbpass, $dbname);
   }

   public function selectQuery($query) : array
   {
      $queryResult = $this->dbProvider->query($query);
      $queryArr = \mysqli_fetch_array($queryResult, MYSQLI_ASSOC);

      if (is_null($queryArr)) {
         return [];
      } 
      else {
         return $queryArr;
      }
   }

   public function selectMultipleRowsQuery($query) : array
   {
      $queryResult = $this->dbProvider->query($query);
      $queryArr = \mysqli_fetch_all($queryResult, MYSQLI_ASSOC);

      return $queryArr;
   }

   public function insertQuery($query)
   {
      $queryResult = $this->dbProvider->query($query);
      return $queryResult;
   }

   public function updateQuery($query)
   {
      return $this->insertQuery($query);
   }

   public function applyQueries($queryArr)
   {
      $this->dbProvider->autocommit(FALSE);
      foreach ($queryArr as $query) {
         $queryResult = $this->dbProvider->query($query);

         if (!$queryResult) {
            return false;
         }
      }

      $this->dbProvider->commit();
      return true;
   }
}