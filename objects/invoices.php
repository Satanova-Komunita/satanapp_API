<?php
/**
 * Invoices class is creating new invoices
 */
class Invoice
{
  /**
   * Instance of connection to database
   *
   * @var Database
   */
  private $conn;

  /**
   * ID of invoice
   *
   * @var int
   */
  public $ID;
  /**
   * Path where PDF file of invoice lays
   *
   * @var string
   */
  public $path;
  /**
   * ID of technician who created invoices
   *
   * @var int
   */
  public $technicianID;
  /**
   * ID of firm which is issuing invoice
   *
   * @var int
   */
  public $firmID;

  /**
   * Initializes new instance of class Invoice
   *
   * @param Database $db
   */
  public function __construct($db)
  {
    $this->conn = $db;
  }

  /**
   * Creates new invoice with specified parameters
   *
   * @return array
   */
  function create ()
  {
    var_dump("createInvoice");
    $query = "INSERT INTO Invoices
      SET path=:path, technicianID=:technicianID, firmID=:technicianID";
    
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(":path", $this->path);
    $stmt->bindParam(":technicianID", $this->technicianID);
    $stmt->bindParam(":firmID", $this->firmID);

    if ($stmt->execute())
      return array(
        "status" => 201,
        "statusMsg" => "Created",
        "data" => array("message" => "Invoice successfuly created")
      );
    else
      return array(
        "status" => 503,
        "statusMsg" => "Service unavailable",
        "data" => array("message" => "Unable to create invoice, try again later!")
      );
  }
}
?>
