<?php
/**
 * User class is manipulating with records of users 
 * and reads invoices user created
 */
class User
{
  /**
   * Instance of connection to database
   *
   * @var Database
   */
  private $conn;

  /**
   * ID of usea
   *
   * @var int
   */
  public $ID;
  /**
   * First name of user
   *
   * @var string
   */
  public $firstName;
  /**
   * Last name of user
   *
   * @var string
   */
  public $lastName;
  /**
   * Email of user
   *
   * @var string
   */
  public $email;
  /**
   * Password of user
   *
   * @var string
   */
  public $password;

  /**
   * Initializes new instance of User class
   *
   * @param Database $db
   */
  public function __construct($db)
  {
    $this->conn = $db;
  }

  /**
   * Reads records of invoices user with $ID created
   *
   * @return array
   */
  function readInvoices ()
  {
    $query = "SELECT ID, path, technicianID, firmID
      FROM Invoices
      WHERE technicianID=?";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $this->ID);

    $stmt->execute();

    $num = $stmt->rowCount();
    if ($num > 0)
    {
      $responseArr = array();

      while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
      {
        extract($row);

        $usersInvoices = array(
          "ID" => $ID,
          "path" => $path
        );

        array_push($responseArr, $usersInvoices);
      }
      return array(
        "status" => 200,
        "statusMsg" => "OK", 
        "data" => $responseArr
      );
    }
    else
    {
      return array(
        "status" => 200,
        "statusMsg" => "OK", 
        "data" => array(
          "message" => "No invoices found for this user"
        )
        
      );
    }
  }

  /**
   * Reads record of user with specified $ID
   *
   * @return array
   */
  function readOne ()
  {
    $query = "SELECT Users.firstName as `firstName`, Users.lastName as `lastName`,
        Users.email as `email`
      FROM Users
      WHERE Users.ID = ?";
    
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(1, $this->ID);

    $stmt->execute();
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $this->firstName = $row['firstName'];
    $this->lastName = $row['lastName'];
    $this->email = $row['email'];

    if (isset($this->firstName))
    {
      $user = array(
        "ID" => $this->ID,
        "firstName" => $this->firstName,
        "lastName" => $this->lastName,
        "email" => $this->email
      );
      return array(
        "status" => 200,
        "statusMsg" => "OK",
        "data" => $user
      );
    }
    else
    {
      return array(
        "status" => 200,
        "statusMsg" => "OK",
        "data" => array("message" => "No user found with this ID.")
      );
    }
  }

  /**
   * Creates new user with specified parameters
   *
   * @return array
   */
  function create ()
  {
    $query = "INSERT INTO Users
      SET firstName=?, lastName=?, email=?, password=?";
    
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(1, $this->firstName);
    $stmt->bindParam(2, $this->lastName);
    $stmt->bindParam(3, $this->email);
    $stmt->bindParam(4, $this->password);

    if ($stmt->execute())
      return array(
        "status" => 201,
        "statusMsg" => "Created",
        "data" => array("message" => "User successfuly created")
      );
    else
      return array(
        "status" => 503,
        "statusMsg" => "Service unavailable",
        "data" => array("message" => "Unable to create user, try again later!")
      );
  }

  /**
   * Updates record of user with $ID with specified parameters
   *
   * @return array
   */
  function update ()
  {
    $query = "UPDATE Users
      SET firstName=:firstName, lastName=:lastName, email=:email
      WHERE ID=:ID";
    
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":firstName", $this->firstName);
    $stmt->bindParam(":lastName", $this->lastName);
    $stmt->bindParam(":email", $this->email);
    $stmt->bindParam(":ID", $this->ID);

    if ($stmt->execute())
      return array(
        "status" => 200,
        "statusMsg" => "Ok",
        "data" => array("message" => "User was successfuly updated")
      );
    else
      return array(
        "status" => 500,
        "statusMsg" => "Internal Server Error",
        "data" => array("message" => "Unable to update user")
      );
  }

  /**
   * Deletes user with specified $ID
   *
   * @return array
   */
  function delete ()
  {
    $query = "DELETE FROM Users WHERE ID = ?";
    
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(1, $this->ID);

    if ($stmt->execute())
      return array(
        "status" => 200,
        "statusMsg" => "Ok",
        "data" => array("message" => "User was successfuly deleted")
      );
    else
      return array(
        "status" => 500,
        "statusMsg" => "Internal Server Error",
        "data" => array("message" => "Unable to delete user")
      );
  }
}
?>