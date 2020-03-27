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
      case 'propsal-votes':
        if ($this->ID === null)
          return true;
        else
          return false;
        break;
      case 'sabats':
        if ($this->ID === null || $this->subject === 'proposals' || $this->subject === 'candidates')
        {
          return true;
        }
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
        return $this->usersResponse($db);
        break;
      case 'sabats':
        return $this->usersResponse($db);
        break;
      case 'login':
        return $this->loginResponse($db);
        break;
    }
  }

  /**
   * Includes User file, initializes new instance of its class and performs
   * action depending on used method, specified subject and passed data from user
   *
   * @param Database $db
   * @return void
   */
  private function usersResponse ($db)
  {
    include_once('./objects/users.php');
    $user = new User($db);
    switch ($this->method)
    {
      case 'GET':
        if ($this->subject === 'invoices')
        {
          if (
            $this->ID === $this->jwt->sub ||
            $this->isFirmOwner($db, $this->ID)
          ){
            $user->ID = $this->ID;
            return $user->readInvoices();
          }
          raise403("Can't view someone else's invoices.");
        }
        elseif ($this->ID != null)
        {
          $user->ID = $this->ID;
          return $user->readOne();
        }
        else
          return ret406();
        break;
      case 'PUT':
        include_once './resources/auth.php';
        $data = json_decode(file_get_contents('php://input'));
        if (
          !empty($data->firstName) &&
          !empty($data->lastName) &&
          !empty($data->email)
        ){
          if ($this->ID != $this->jwt->sub)
            raise403("Can't modify someone else's info");
          $user->firstName = sanitize($data->firstName);
          $user->lastName = sanitize($data->lastName);
          $user->email = sanitize($data->email);
          $user->ID = $this->ID;
          $response = $user->update();
          if ($response['status'] === 200)
          {
            $userData = array(
              "firstName" => $user->firstName,
              "lastName" => $user->lastName,
              "email" => $user->email,
              "ID" => $user-ID
            );
            $refreshToken = createRefreshToken($userData);
            $this->setRefreshJWTinDatabase($userData['email'], $refreshToken);
            response(200, "OK", array(
              "refreshJWT" => $refreshToken,
              "userData" => array(
                "ID" => $userData['ID'],
                "firstName" => $userData['firstName'],
                "lastName" => $userData['lastName'],
                "email" => $userData['email']
              )
            ));
          }
          else
            return $response;
        }
        else
          return ret406();
        break;
      case 'DELETE':
        if ($this->ID != $this->jwt->sub)
          raise403("Can't delete someone else");
        $user->ID = $this->ID;
        return $user->delete();
        break;
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
              "email" => $user['member_number']
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
