<?php
/**
 * Firm class is manipulating with records of firms
 * and reads invoices issued by firm
 */
class Firm
{
  /**
   * Instance of connection to database
   *
   * @var Database
   */
  private $conn;

  /**
   * ID of firm
   *
   * @var int
   */
  public $ID;
  /**
   * Name of firm
   *
   * @var string
   */
  public $name;
  /**
   * ICO of firm
   *
   * @var int
   */
  public $ICO;
  /**
   * City where residence of firm lays
   *
   * @var string
   */
  public $city;
  /**
   * Street where residence of firm lays
   *
   * @var string
   */
  public $street;
  /**
   * PSC corresponding to firms address
   *
   * @var int
   */
  public $psc;
  /**
   * ID of owner of firm
   *
   * @var int
   */
  public $owner;
  
  /**
   * ID of firms technician
   *
   * @var int
   */
  public $userID;

  /**
   * Initializes new instance of Firm class
   *
   * @param Database $db
   */
  public function __construct($db)
  {
    $this->conn = $db;
  }

  /**
   * Reads record of firm with specified $ID
   *
   * @return array
   */
  function readOne ()
  {
    $query = "SELECT name, ICO, city, street, psc, owner
      FROM Firms
      WHERE Firms.ID = ?";
    
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(1, $this->ID);
    $stmt->execute();
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $this->name = $row['name'];
    $this->ICO = $row['ICO'];
    $this->city = $row['city'];
    $this->street = $row['street'];
    $this->psc = $row['psc'];
    $this->owner = $row['owner'];

    if (isset($this->name))
    {
      $user = array(
        "ID" => $this->ID,
        "name" => $this->name,
        "ICO" => $this->ICO,
        "city" => $this->city,
        "street" => $this->street,
        "psc" => $this->psc,
        "owner" => $this->owner
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
        "data" => array("message" => "No firm found with this ID.")
      );
    }
  }

  /**
   * Reads records of invoices issued by firm
   *
   * @return array
   */
  function readInvoices ()
  {
    $query = "SELECT ID, path, firmID, technicianID
      FROM Invoices
      WHERE firmID=?";
    
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(1, $this->ID);

    $stmt->execute();

    if ($stmt->rowCount() > 0)
    {
      $responseArr = array();
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
      {
        extract($row);

        $responseItem = array(
          "ID" => $ID,
          "path" => $path,
          "firmID" => $firmID,
          "technicianID" => $technicianID
        );
        array_push($responseArr, $responseItem);
      }
      return array(
        "status" => 200,
        "statusMsg" => "Ok",
        "data" => $responseArr
      );
    }
    else
    {
      return array(
        "status" => 200,
        "statusMsg" => "Ok",
        "data" => array(
          "message" => "No invoices were created by this firm."
        )
      );
    }
  }

  /**
   * Reads records of technicians participating in firm
   *
   * @return array
   */
  function readTechnicians ()
  {
    $query = "SELECT Users.ID as `ID`, Users.firstName as `firstName`,
        Users.lastName as `lastName`, Users.email as `email`
      FROM FirmTechnicians
      LEFT JOIN Users ON Users.ID = FirmTechnicians.userID
      WHERE FirmTechnicians.firmID = ?";
    
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(1, $this->ID);

    $stmt->execute();

    if ($stmt->rowCount() > 0)
    {
      $responseArr = array();
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
      {
        extract($row);

        $responseItem = array(
          "ID" => $ID,
          "firstName" => $firstName,
          "lastName" => $lastName,
          "email" => $email
        );
        array_push($responseArr, $responseItem);
      }
      return array(
        "status" => 200,
        "statusMsg" => "Ok",
        "data" => $responseArr
      );
    }
    else
    {
      return array(
        "status" => 200,
        "statusMsg" => "Ok",
        "data" => array(
          "message" => "No technicians were found in firm."
        )
      );
    }
  }

  /**
   * Adds new record to database by specified $sub and provided data
   *
   * @param string $sub
   * @return array
   */
  function create ($sub = null)
  {
    $query;
    if ($sub === 'technicians')
    {
      $query = "INSERT INTO FirmTechnicians
        SET firmID=:firmID, userID=:userID";
    }
    else
    {
      $query = "INSERT INTO Firms
        SET name=:name, ICO=:ICO, city=:city, street=:street,
          psc=:psc, owner=:owner";
    }
    
    $stmt = $this->conn->prepare($query);

    $message;
    if ($sub === 'technicians')
    {
      $stmt->bindParam(":firmID", $this->ID);
      $stmt->bindParam(":userID", $this->userID);
      $message = "Technician successfuly added";
    }
    else
    {
      $stmt->bindParam(":name", $this->name);
      $stmt->bindParam(":ICO", $this->ICO);
      $stmt->bindParam(":city", $this->city);
      $stmt->bindParam(":street", $this->street);
      $stmt->bindParam(":psc", $this->psc);
      $stmt->bindParam(":owner", $this->owner);
      $message = "Firm successfuly created";
    }

    if ($stmt->execute())
      return array(
        "status" => 201,
        "statusMsg" => "Created",
        "data" => array("message" => $message)
      );
    else
      return array(
        "status" => 503,
        "statusMsg" => "Service unavailable",
        "data" => array("message" => "Unable to perform action, try again later!")
      );
  }

  /**
   * Updates record of firm with specified $ID
   *
   * @return array
   */
  function update ()
  {
    $query = "UPDATE Firms
      SET name=:name, ICO=:ICO, city=:city, street=:street,
        psc=:psc, owner=:owner
      WHERE ID=:ID";
    
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":name", $this->name);
    $stmt->bindParam(":ICO", $this->ICO);
    $stmt->bindParam(":city", $this->city);
    $stmt->bindParam(":street", $this->street);
    $stmt->bindParam(":psc", $this->psc);
    $stmt->bindParam(":owner", $this->owner);
    $stmt->bindParam(":ID", $this->ID);

    if ($stmt->execute())
      return array(
        "status" => 200,
        "statusMsg" => "Ok",
        "data" => array("message" => "Firm was successfuly updated.")
      );
    else
      return array(
        "status" => 500,
        "statusMsg" => "Internal Server Error",
        "data" => array("message" => "Unable to update firm.")
      );
  }

  /**
   * Deletes record from database by specified $sub and other given parameters
   *
   * @param string $sub
   * @return array
   */
  function delete ($sub = null)
  {
    $query;
    if ($sub === 'technicians')
      $query = "DELETE FROM FirmTechnicians WHERE firmID = ? AND userID = ?";
    else
      $query = "DELETE FROM Firms WHERE ID = ?";

    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(1, $this->ID);

    $message = "Firm was successfuly deleted.";
    if ($sub === 'technicians')
    {
      $stmt->bindParam(2, $this->userID);
      $message = "Technician successfuly deleted.";
    }

    if ($stmt->execute())
      return array(
        "status" => 200,
        "statusMsg" => "Ok",
        "data" => array("message" => $message)
      );
    else
      return array(
        "status" => 500,
        "statusMsg" => "Internal Server Error",
        "data" => array("message" => "Unable to delete.")
      );
  }
}
?>