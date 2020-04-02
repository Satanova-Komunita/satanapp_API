<?php
/**
 * Sabat class is manipulating with sabat's votes in proposals and candidates
 * and reads all upcoming sabats
 */
class Sabat
{
  /**
   * Instance of connection to database
   *
   * @var Database
   */
  private $conn;

  /**
   * ID of sabat
   *
   * @var int
   */
  public $ID;
  /**
   * ID of member
   *
   * @var int
   */
  public $memberID;
  /**
   * ID of candidate's role
   *
   * @var int
   */
  public $roleID;
  /**
   * Proposal's name
   *
   * @var string
   */
  public $name;
  /**
   * Proposal's description
   *
   * @var string
   */
  public $description;

  /**
   * Initializes new instance of Sabat class
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
  function readSubject ($subject)
  {
    if ($subject === 'proposals')
    {
      $query = "SELECT ID, name, description
        FROM Sabat_proposals
        WHERE sabat_ID=?";
    } else if ($subject === 'candidates')
    {
      $query = "SELECT Member_role_candidates.ID as `ID`,
          Member_role_candidates.member_ID as `member_ID`,
          Members.first_name as `first_name`,
          Members.last_name as `last_name`,
          Member_role_candidates.role_ID as `role_ID`,
          Roles.name as `role_name`
        FROM Member_role_candidates
        LEFT JOIN Members ON Members.ID = Member_role_candidates.member_ID
        LEFT JOIN Roles ON Roles.ID = Member_role_candidates.role_ID
        WHERE sabat_ID=?";
    } else 
    {
      return ret406();
    }

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $this->ID);

    $stmt->execute();

    $num = $stmt->rowCount();
    if ($num > 0)
    {
      $responseArr = array();
      $subjectVar;

      while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
      {
        extract($row);

        if ($subject === 'proposals')
        {
          $subjectVar = array(
            "ID" => $ID,
            "name" => $name,
            "description" => $description
          );
        } else 
        {
          $subjectVar = array(
            "ID" => $ID,
            "member_ID" => $member_ID,
            "role_ID" => $role_ID,
            "first_name" => $first_name,
            "last_name" => $last_name,
            "role_name" => $role_name
          );
        }

        array_push($responseArr, $subjectVar);
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
          "message" => "Žádna data nenalezena."
        )
      );
    }
  }

  /**
   * Reads records of upcomming sabats
   *
   * @return array
   */
  function read ()
  {
    $query = "SELECT ID, regional_cell_ID, date
      FROM Sabats";
    
    $stmt = $this->conn->prepare($query);

    $stmt->execute();

    $num = $stmt->rowCount();
    if ($num > 0)
    {
      $responseArr = array();

      while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
      {
        extract($row);

        $sabat = array(
          "ID" => $row['ID'],
          "regional_cell_ID" => $row['regional_cell_ID'],
          "date" => $row['date']
        );

        array_push($responseArr, $sabat);
      }

      return array(
        "status" => 200,
        "statusMsg" => "OK",
        "data" => $responseArr
      );
    } else
    {
      return array(
        "status" => 200,
        "statusMsg" => "OK",
        "data" => array("message" => "Žádný nadcházející sabat nenalezen.")
      );
    }
  }

  /**
   * Creates new user with specified parameters
   *
   * @return array
   */
  function create ($subject)
  {
    if ($subject === 'proposals')
    {
      $query = "INSERT INTO Sabat_proposals
        SET sabat_ID=?, proposed_by_member_ID=?, name=?, description=?";
    } else if ($subject === 'candidates')
    {
      $query = "INSERT INTO Member_role_candidates
        SET member_ID=?, role_ID=?, sabat_ID=?";
    } else
    {
      return ret406;
    }
    
    $stmt = $this->conn->prepare($query);

    if ($subject === 'proposals')
    {
      $stmt->bindParam(1, $this->ID);
      $stmt->bindParam(2, $this->memberID);
      $stmt->bindParam(3, $this->name);
      $stmt->bindParam(4, $this->description);
    } else
    {
      $stmt->bindParam(1, $this->memberID);
      $stmt->bindParam(2, $this->roleID);
      $stmt->bindParam(3, $this->ID);
    }

    if ($stmt->execute())
      return array(
        "status" => 201,
        "statusMsg" => "Created",
        "data" => array("message" => "Akce byla úspěšná")
      );
    else
      return array(
        "status" => 503,
        "statusMsg" => "Service unavailable",
        "data" => array("message" => "Nepodařilo se provést akci.")
      );
  }
}
?>
