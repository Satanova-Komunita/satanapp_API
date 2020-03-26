<?php
class Customer
{
  /**
   * Instance of connection to database
   *
   * @var Database
   */
  private $conn;

  /**
   * ID of customer
   *
   * @var int
   */
  public $ID;
  /**
   * Name of customer
   *
   * @var int
   */
  public $name;
  /**
   * ICO of customer
   *
   * @var int
   */
  public $ICO;
  /**
   * City where residence of customer lays
   *
   * @var string
   */
  public $city;
  /**
   * Street where residence of customer lays
   *
   * @var string
   */
  public $street;
  /**
   * PSC corresponding to customers address
   *
   * @var int
   */
  public $psc;

  /**
   * Initializes new instance of class Customer
   *
   * @param Databse $db
   */
  public function __construct($db)
  {
    $this->conn = $db;
  }

  /**
   * Returns record of customer with specified $ICO
   *
   * @return array
   */
  function readOne ()
  {
    $query = "SELECT name, ICO, city, street, psc
      FROM Customers
      WHERE ICO = ?";
    
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(1, $this->ID);
    $stmt->execute();
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $this->name = $row['name'];
    $this->ICO = $row['ICO'];
    $this->city = $row['city'];
    $this->street = $row['street'];
    $this->psc = $row['psc'];

    if (isset($this->name))
    {
      $user = array(
        "ID" => $this->ID,
        "name" => $this->name,
        "ICO" => $this->ICO,
        "city" => $this->city,
        "street" => $this->street,
        "psc" => $this->psc
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
   * Creates new record in database with specified parameters
   *
   * @return array
   */
  function create ()
  {
    $query = "INSERT INTO Customers
      SET name=:name, ICO=:ICO, city=:city, street=:street, psc=:psc";
    
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":name", $this->name);
    $stmt->bindParam(":ICO", $this->ICO);
    $stmt->bindParam(":city", $this->city);
    $stmt->bindParam(":street", $this->street);
    $stmt->bindParam(":psc", $this->psc);

    if ($stmt->execute())
      return array(
        "status" => 201,
        "statusMsg" => "Created",
        "data" => array("message" => "Customer successfuly created")
      );
    else
      return array(
        "status" => 503,
        "statusMsg" => "Service unavailable",
        "data" => array("message" => "Unable to create customer, try again later!")
      );
  }

  /**
   * Updates existing customer with $ID with specified parameters
   *
   * @return array
   */
  function update ()
  {
    $query = "UPDATE Customers
      SET name=:name, ICO=:ICO, city=:city, street=:street, psc=:psc
      WHERE ID=:ID";
    
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":name", $this->name);
    $stmt->bindParam(":ICO", $this->ICO);
    $stmt->bindParam(":city", $this->city);
    $stmt->bindParam(":street", $this->street);
    $stmt->bindParam(":psc", $this->psc);
    $stmt->bindParam(":ID", $this->ID);

    if ($stmt->execute())
      return array(
        "status" => 200,
        "statusMsg" => "Ok",
        "data" => array("message" => "Customer was successfuly updated.")
      );
    else
      return array(
        "status" => 500,
        "statusMsg" => "Internal Server Error",
        "data" => array("message" => "Unable to update customer.")
      );
  }
}
?>
