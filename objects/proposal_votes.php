<?php
/**
 * ProposalVote class is sending votes for proposal to Database
 */
class ProposalVote
{
  /**
   * Instance of connection to database
   *
   * @var Database
   */
  private $conn;

  /**
   * ID of voting member
   *
   * @var int
   */
  public $memberID;
  /**
   * Array of objects containing vote for each individual proposal
   *
   * @var array
   */
  public $votes;

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
  function addVotes ()
  {
    $query = "";

    // insert SQL command for inserting data to $query
    foreach ($this->votes as $vote)
    {
      $query .= "INSERT INTO Sabat_proposal_votes
        SET member_ID=".$this->memberID.",
            sabat_proposal_ID=".$vote->proposal_ID.",
            value=".$vote->votes.";";
    }

    $stmt = $this->conn->prepare($query);

    if ($stmt->execute())
      return array(
        "status" => 201,
        "statusMsg" => "Created",
        "data" => array("message" => "Hlas(y) byl(y) úspěšně zaznamenán(y).")
      );
    else
      return array(
        "status" => 503,
        "statusMsg" => "Service unavailable",
        "data" => array("message" => "Nepodařilo se zaznamenat hlas(y).")
      );
  }
}
?>
