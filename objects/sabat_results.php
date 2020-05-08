<?php
/**
 * Results of sabat voting
 */
class SabatResult
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
  public $sabatID;
  /**
   * Type of results (proposal_votes/candidates)
   *
   * @var string
   */
  public $type;

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
   * Creates new user with specified parameters
   *
   * @return array
   */
  public function getVotes ()
  {
    $query = "SELECT DISTINCT
        Sabat_proposal_votes.sabat_proposal_ID as `proposalID`,
        Sabat_proposals.name as `name`,
        SUM(Sabat_proposal_votes.value) as `votes`
      FROM Sabat_proposal_votes
      LEFT JOIN Sabat_proposals ON Sabat_proposal_votes.sabat_proposal_ID = Sabat_proposals.ID
      LEFT JOIN Sabats on Sabat_proposals.sabat_ID = Sabats.ID
      WHERE Sabats.ID = ?
      GROUP BY Sabat_proposal_votes.sabat_proposal_ID";

    if (!$this->votesAvailable())
    {
      return array(
        "status" => 503,
        "statusMsg" => "Service unavailable",
        "data" => array("message" => "Nelze zobrazit výsledky po čas hlasování.")
      );
    }
    else 
    {
      $stmt = $this->conn->prepare($query);
      $stmt->bindParam(1, $this->sabatID);


      $stmt->execute();

      $num = $stmt->rowCount();
      if ($num > 0)
      {
        $responseArr = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
          extract($row);
          $vote = array(
            "name" => $name,
            "votes" => $votes
          );

          array_push($responseArr, $vote);
        }
        return array(
          "status" => 200,
          "statusMsg" => "OK",
          "data" => $responseArr
        );
      }
      else
        return array(
          "status" => 200,
          "statusMsg" => "OK",
          "data" => array("message" => "Výsledky nebyly nalezeny.")
        );
    }
  }

  /**
   * Function determines whether votes can be read
   *
   * @param proposalID int
   * @return bool
   */
  private function votesAvailable ()
  {
    $query = "SELECT Sabats.voting_enabled as `votingEnabled`
      FROM Sabats
      WHERE Sabats.ID=?";

    $stmt = $this->conn->prepare($query);
    
    $stmt->bindParam(1, $this->sabatID);

    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row['votingEnabled'] == 0) return true;
    return false;
  }

}
?>
