<?php
/**
 * Database class performs connection to database
 */
class Database
{
  /**
   * Address of server
   *
   * @var string
   */
  private $host = '127.0.0.1:8889';
  /**
   * Database we are trying to connect to
   *
   * @var string
   */
  private $dbName = 'satan';
  /**
   * Username used as credentials
   *
   * @var string
   */
  private $username = 'root';
  /**
   * Password used as credentials
   *
   * @var string
   */
  private $password = 'root';
  /**
   * Connection to database
   *
   * @var PDO
   */
  public $conn;


  /**
   * Connects to database with provided credentials
   *
   * @return PDO
   */
  public function getConnection ()
  {
    $this->conn = null;

    try
    {
      $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' .
        $this->dbName, $this->username, $this->password);
      $this->conn->exec('set names utf8');
    } catch (PDOException $e)
    {
      echo 'Connection error: ' . $e->getMessage();
    }

    return $this->conn;
  }
}
?>
