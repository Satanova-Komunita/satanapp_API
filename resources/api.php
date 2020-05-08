<?php
// include globally used functions
include_once './resources/functions.php';
/**
 * Api class is validating endpoint reached by user and
 * requesting responses depending on used method, endpoint and given data
 */
class Api
{
  /**
   * REST method used with request
   *
   * @var string
   */
  private $method;
  /**
   * Object we are referring to
   *
   * @var string
   */
  private $object;
  /**
   * ID of object
   *
   * @var int
   */
  private $ID;
  /**
   * Subject we are referring to
   *
   * @var string
   */
  private $subject;
  /**
   * ID of subject
   *
   * @var int
   */
  private $subjectID;
  /**
   * Decoded JWT
   *
   * @var object
   */
  private $jwt;

  /**
   * Initializes new instance of Api class
   *
   * @param string $method
   * @param string $object
   * @param int    $ID
   * @param string $subject
   * @param int    $subjectID
   * @param string $jwt
   */
  public function __construct(
    $method, $object,
    $ID = null, $subject = null,
    $subjectID = null, $jwt
  ){
    $this->method = $method;
    $this->object = $object;
    $this->ID = $ID;
    $this->subject = $subject;
    $this->subjectID = $subjectID;
    $this->jwt = $jwt;
  }

  /**
   * Validates endpoint depending on defined object
   *
   * @return boolean
   */
  public function validateEndpoint ()
  {
    switch ($this->object)
    {
      case 'proposal-votes':
        if ($this->ID === null)
          return true;
        else
          return false;
        break;
      case 'sabats':
        if ($this->ID === null || $this->subject === 'proposals' || $this->subject === 'candidates')
          return true;
        else
          return false;
        break;
      case 'sabat-results':
        if ($this->ID !== null && ($this->subject === 'proposals' || $this->subject === 'candidates'))
          return true;
        else
          return false;
        break;
      case 'login':
        if ($this->ID === null)
        {
          include_once './resources/auth.php';
          return true;
        }
        else
          return false;
        break;
      default:
        return false;
    }
  }

  /**
   * Request response for defined object
   *
   * @return void
   */
  public function requestResponse ()
  {
    // include database and instantiate database obj
    include_once './config/database.php';
    $database = new Database();
    $db = $database->getConnection();

    /*
     * decides which file to include depending on 
     * $this->object then will choose object's function 
     * depending on $this->method
     */
    switch ($this->object)
    {
      case 'proposal-votes':
        return $this->proposalVotesResponse($db);
        break;
      case 'sabats':
        return $this->sabatsResponse($db);
        break;
      case 'login':
        return $this->loginResponse($db);
        break;
      case 'sabat-results':
        return $this->sabatResultsResponse($db);
        break;
    }
  }
  /** 
   * Includes sabat_results file, initializes new instance of its class and
   * tries to generate results from sabat's voting
   *
   * @param Database $db
   * @return void
   */ 
  private function sabatResultsResponse ($db)
  {
    include_once('./objects/sabat_results.php');
    $sabResult = new SabatResult($db);
    switch ($this->method)
    {
    case 'GET':
      $sabResult->sabatID = $this->ID;
      $sabResult->type = $this->subject;
      return $sabResult->getVotes();
      break;
    default:
      response(405, "Method not allowed", null);
    }
  }
  /** 
   * Includes Proposal_votes file, initializes new instance of its class and
   * tries to add new votes to DB
   *
   * @param Database $db
   * @return void
   */ 
  private function proposalVotesResponse ($db)
  {
    include_once('./objects/proposal_votes.php');
    $propVote = new ProposalVote($db);
    switch ($this->method)
    {
    case 'POST':
      $data = json_decode(file_get_contents('php://input'));
      if (
        !empty($data->member_ID) &&
        is_array($data->votes) &&
        !empty($data->votes[0]->proposal_ID)
      ){
        $d = new DateTime('now', new DateTimeZone('Europe/Prague'));

        $propVote->memberID = sanitize($data->member_ID);
        $propVote->votes = $data->votes;
        $propVote->createdAt = $d->format('Y-m-d H:i:s');

        return $propVote->addVotes();
      }
      else
        return ret406();
      break;
    default:
      response(405, "Method not allowed", null);
    }
  }
  /**
   * Includes Sabats file, initializes new instance of its class and performs
   * action depending on used method, specified subject and passed data from user
   *
   * @param Database $db
   * @return void
   */
  private function sabatsResponse ($db)
  {
    include_once('./objects/sabats.php');
    $sabat = new Sabat($db);
    switch ($this->method)
    {
      case 'GET':
        if ($this->ID === null)
          return $sabat->read();
        else if ($this->subject === 'proposals' || $this->subject === 'candidates')
        {
          $sabat->ID = $this->ID;
          return $sabat->readSubject($this->subject);
        }
        else
          return ret406();
        break;
      case 'POST':
        $data = json_decode(file_get_contents('php://input'));
        
        if (
          $this->ID !== null &&
          (!empty($data->description) && !empty($data->name)) ||
          (!empty($data->role_ID) && !empty($data->member_ID))
        ){
          if ($this->subject === 'candidates')
          {
            $sabat->ID = $this->ID;
            $sabat->roleID = sanitize($data->role_ID);
            $sabat->memberID = sanitize($data->member_ID);
          }
          else if ($this->subject === 'proposals')
          {
            $sabat->ID = $this->ID;
            $sabat->name = sanitize($data->name);
            $sabat->description = sanitize($data->description);
            $sabat->memberID = $this->jwt->sub;
          }

          return $sabat->create($this->subject);
        }
        else
          return ret406();
        break;
      default:
        response(405, "Method Not Allowed", null);
    }
  }
  /**
   * Tries to login user with given member number
   *
   * @param Database $db
   * @return void
   */
  private function loginResponse ($db)
  {
    if ($this->method == 'POST')
    {
      $data = json_decode(file_get_contents('php://input'));
      if (!empty($data->member_number))
      {
        $query = "SELECT ID, member_number
          FROM Members
          WHERE Members.member_number=?";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, sanitize($data->member_number));
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user['member_number'] != "")
        {
          $token = createRefreshToken($user);
          response(200, "OK", array(
            "JWT" => $token,
            "userData" => array(
              "ID" => $user['ID'],
              "member_number" => $user['member_number']
            )
          ));
        }
        else
          response(200, "OK", array("message" => "Neplatné členské číslo"));
      }
      else
        return ret406();
    }
    else
      response(405, "Method Not Allowed", null);
  }
}
?>
