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
   * @return boolean|void
   */
  public function validateEndpoint ()
  {
    switch ($this->object)
    {
      case 'firms':
        if (
          $this->subject === null ||
          $this->subject === 'invoices' ||
          $this->subject === 'technicians'
        ){
          if ($this->isAccessToken())
            return true;
          raise403("Refresh token is not usable on API calls.");
        }
        else
          return false;
        break;
      case 'customers':
        if ($this->subject === null)
        {
          if ($this->isAccessToken())
            return true;
          raise403("Refresh token is not usable on API calls.");
        }
        else
          return false;
        break;
      case 'invoices':
        if ($this->ID === null)
        {
          if ($this->isAccessToken())
            return true;
          raise403("Refresh token is not usable on API calls.");
        }
        else
          return false;
        break;
      case 'users':
        if ($this->ID !== null && $this->subject === null || $this->subject === 'invoices')
        {
          if ($this->isAccessToken())
            return true;
          raise403("Refresh token is not usable on API calls.");
        }
        else
          return false;
        break;
      case 'login':
        if ($this->ID === null)
        {
          if ($this->object == 'login')
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
    if ($this->object)
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
      case 'users':
        return $this->usersResponse($db);
        break;
      case 'firms':
        return $this->firmsResponse($db);
        break;
      case 'customers':
        return $this->customersResponse($db);
        break;
      case 'invoices':
        return $this->invoicesResponse($db);
        break;
      case 'signup':
        return $this->signupResponse($db);
        break;
      case 'login':
        return $this->loginResponse($db);
        break;
      case 'token':
        return $this->tokenResponse($db);
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
   * Includes Invoice file, initializes new instance of its class and creates
   * new record of invoice in database if used POST method
   *
   * @param Database $db
   * @return void
   */
  private function invoicesResponse ($db)
  {
    include_once('./objects/invoices.php');
    $invoice = new Invoice($db);
    switch ($this->method)
    {
      case 'POST':
        $data = json_decode(file_get_contents('php://input'));
        if (
          !empty($data->path) &&
          !empty($data->technicianID) &&
          !empty($data->firmID)
        ){
          $invoice->path = sanitize($data->path);
          $invoice->technicianID = sanitize($data->technicianID);
          $invoice->firmID = sanitize($data->firmID);
          return $invoice->create();
        }
        else
          return ret406();
        break;
    }
  }
  /**
   * Includes Customer file, initializes new instance of its class and performs
   * action depending on used method and passed data from user
   *
   * @param Database $db
   * @return void
   */
  private function customersResponse ($db)
  {
    include_once('./objects/customers.php');
    $customer = new Customer($db);
    switch ($this->method)
    {
      case 'GET':
        if ($this->ID !== null)
        {
          $customer->ID = $this->ID;
          return $customer->readOne();
        }
        else
          return ret406();
        break;
      case 'POST':
        $data = json_decode(file_get_contents('php://input'));
        if (
          !empty($data->name) &&
          !empty($data->ICO) &&
          !empty($data->city) &&
          !empty($data->street) &&
          !empty($data->psc)
        ){
          if ($this->isCustomerRegistered($db, $data->ICO))
          {
            response(200, "OK", array("message" => "Customer was already registered by someone."));
          }
          $customer->name = sanitize($data->name);
          $customer->ICO = sanitize($data->ICO);
          $customer->city = sanitize($data->city);
          $customer->street = sanitize($data->street);
          $customer->psc = sanitize($data->psc);
          return $customer->create();
        }
        else
          return ret406();
      case 'PUT':
        $data = json_decode(file_get_contents('php://input'));
        if (
          $this->ID !== null &&
          !empty($data->name) &&
          !empty($data->ICO) &&
          !empty($data->city) &&
          !empty($data->street) &&
          !empty($data->psc)
        ){
          $customer->ID = $this->ID;
          $customer->name = sanitize($data->name);
          $customer->ICO = sanitize($data->ICO);
          $customer->city = sanitize($data->city);
          $customer->street = sanitize($data->street);
          $customer->psc = sanitize($data->psc);
          return $customer->update();
        }
        else
          return ret406();
        break;
      case 'DELETE':
        $customer->ID = $this->ID;
        return $customer->delete();
        break;
    }
  }
  /**
   * Includes Firm file, initializes new instance of its class and performs
   * action depending on used method, specified subject and passed data from user
   *
   * @param Database $db
   * @return void
   */
  private function firmsResponse ($db)
  {
    include_once('./objects/firms.php');
    $firm = new Firm($db);
    if ($this->ID !== null && !$this->isMemberOfFirm($db, $this->ID))
      raise403("Only members of firm are allowed to perform operations.");
    switch ($this->method)
    {
      case 'GET':
        if ($this->subject === 'invoices')
        {
          $firm->ID = $this->ID;
          return $firm->readInvoices();
        }
        else if ($this->subject === 'technicians')
        {
          $firm->ID = $this->ID;
          return $firm->readTechnicians();
        }
        else if ($this->ID !== null)
        {
          $firm->ID = $this->ID;
          return $firm->readOne();
        }
        else
          return ret406();
        break;
      case 'POST':
        $data = json_decode(file_get_contents('php://input'));
        if ($this->subject === 'technicians')
        {
          if (!empty($data->userID))
          {
            $firm->ID = $this->ID;
            $firm->userID = $data->userID; 
            return $firm->create($this->subject);
          }
          else
            return ret406();
        }
        else
        {
          if (
            !empty($data->name) &&
            !empty($data->ICO) &&
            !empty($data->city) &&
            !empty($data->street) &&
            !empty($data->psc)
          ){
            if ($this->isFirmRegistered($db, $data->ICO))
            {
              response(200, "OK", array("message" => "Firm already registered by someone."));
            }
            $firm->name = sanitize($data->name);
            $firm->ICO = sanitize($data->ICO);
            $firm->city = sanitize($data->city);
            $firm->street = sanitize($data->street);
            $firm->psc = sanitize($data->psc);
            $firm->owner = sanitize($this->jwt->sub);
            return $firm->create();
          }
          else
            return ret406();
        }
      case 'PUT':
        if (!$this->isFirmOwner($db, $this->ID))
          raise403("Only firm's owner is allowed to change it's info!");
        $data = json_decode(file_get_contents('php://input'));
        if (
          $this->ID !== null &&
          !empty($data->name) &&
          !empty($data->ICO) &&
          !empty($data->city) &&
          !empty($data->street) &&
          !empty($data->psc) &&
          !empty($data->owner)
        ){
          $firm->ID = $this->ID;
          $firm->name = sanitize($data->name);
          $firm->ICO = sanitize($data->ICO);
          $firm->city = sanitize($data->city);
          $firm->street = sanitize($data->street);
          $firm->psc = sanitize($data->psc);
          $firm->owner = sanitize($data->owner);
          return $firm->update();
        }
        else
          return ret406();
        break;
      case 'DELETE':
        if (!$this->isFirmOwner($db, $this->ID))
          raise403("Only firm's owner is allowed to delete!");
        $firm->ID = $this->ID;
        if ($this->subject === 'technicians')
          $firm->userID = $this->subjectID;
        return $firm->delete($this->subject);
        break;
    }
  }
  /**
   * Includes User file and initializes new instance of its class and tries
   * to create new user with data he provides
   *
   * @param Database $db
   * @return void
   */
  private function signupResponse ($db)
  {
    include_once('./objects/users.php');
    $user = new User($db);
    if ($this->method == 'POST')
    {
      $data = json_decode(file_get_contents('php://input'));
      if (
        !empty($data->firstName) &&
        !empty($data->lastName) &&
        !empty($data->email) &&
        !empty($data->password)
      ){
        if ($this->isEmailTaken($db, sanitize($data->email)))
          response(409, "Conflict", array("message" => "Email already taken"));
        $user->firstName = sanitize($data->firstName);
        $user->lastName = sanitize($data->lastName);
        $user->email = sanitize($data->email);
        $user->password = makeHash(sanitize($data->password));
        return $user->create();
      }
      else
	 return ret406();
    }
    else
      response(405, "Method Not Allowed", null);
  }
  /**
   * Tries to login user with given credentials
   *
   * @param Database $db
   * @return void
   */
  private function loginResponse ($db)
  {
    if ($this->method == 'POST')
    {
      $data = json_decode(file_get_contents('php://input'));
      if (!empty($data->email) && !empty($data->password))
      {
        $query = "SELECT ID, firstName, lastName, email,
            password, refTok, shouldRevoke, role
          FROM Users
          WHERE Users.email=?";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, sanitize($data->email));
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (makeHash(sanitize($data->password)) == $user['password'])
        {
          /* 
           * if token should be revoken,
           * we will create new token and
           * set shouldRevoke to 0 in database
           */
          if ($user['refTok'] != null && $user['shouldRevoke'] != 1)
          {
            return $this->refreshTokenHandling($user);
          }
          else
          {
            $refreshToken = createRefreshToken($user);
            $this->setRefreshJWTinDatabase($user['email'], $refreshToken);
            response(200, "OK", array(
              "refreshJWT" => $refreshToken,
              "userData" => array(
                "ID" => $user['ID'],
                "firstName" => $user['firstName'],
                "lastName" => $user['lastName'],
                "email" => $user['email']
              )
            ));
          }
        }
        else
          response(200, "OK", array("message" => "Bad credentials"));
      }
      else
        return ret406();
    }
    else
      response(405, "Method Not Allowed", null);
  }

  /**
   * Creates access JWT with given refresh JWT from Authorization header
   *
   * @return void
   */
  private function tokenResponse ()
  {
    if ($this->method == 'GET')
    {
      return array(
        "status" => 200,
        "statusMsg" => "OK",
        "data" => array("accessJWT" => createAccessToken($this->jwt))
      );
    }
    return ret406();
  }
  
  /**
   * Checks if token is expired and if so, it creates new refresh JWT,
   * otherwise it returns old refresh JWT
   *
   * @param array $user
   * @return void
   */
  private function refreshTokenHandling ($user)
  {
    if (isTokenExpired($user['refTok']))
    {
      $refreshToken = createRefreshToken($user);
      $this->setRefreshJWTinDatabase($user['email'], $refreshToken);
      response(200, "OK", array(
        "refreshJWT" => $user['refTok'],
        "userData" => array(
          "ID" => $user['ID'],
          "firstName" => $user['firstName'],
          "lastName" => $user['lastName'],
          "email" => $user['email']
          )
        ));
    }
    response(200, "OK", array(
      "refreshJWT" => $user['refTok'],
      "userData" => array(
        "ID" => $user['ID'],
        "firstName" => $user['firstName'],
        "lastName" => $user['lastName'],
        "email" => $user['email']
      )
    ));
  }

  /**
   * Checks whether firm with $ico is not already registered
   *
   * @param Database $db
   * @param int $ico
   * @return boolean
   */
  private function isFirmRegistered ($db, $ico)
  {
    $query = "SELECT ID
      FROM Firms
      WHERE ICO=?";
    
    $stmt = $db->prepare($query);

    $stmt->bindParam(1, $ico);
    $stmt->execute();
    if ($stmt->rowCount() > 0)
      return true;
    else
      return false;
  }
  /**
   * Determines whether user sending request is owner of firm
   *
   * @param Database $db
   * @param int $firmID
   * @return boolean
   */
  private function isFirmOwner ($db, $firmID)
  {
    $query = "SELECT Firms.ID, Firms.name,
        Firms.owner, FirmTechnicians.ID as `technicianID`
      FROM Firms
      LEFT JOIN FirmTechnicians ON Firms.ID = FirmTechnicians.firmID
      WHERE Firms.ID = ?";

    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $this->ID);
    $stmt->execute();
    if ($stmt->rowCount() === 0)
      return false;
    else
    {
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($this->jwt->sub == $row['owner'])
        return true;
      else
        return false;
    }
  }
  /**
   * Determines whether user sending request is member of firm
   *
   * @param Database $db
   * @param int $firmID
   * @return boolean
   */
  private function isMemberOfFirm ($db, $firmID)
  {
    $query = "SELECT Firms.owner as `userID`
    FROM Firms
    WHERE Firms.ID = :ID
    UNION
    SELECT FirmTechnicians.userID as `userID`
    FROM FirmTechnicians
    WHERE FirmTechnicians.firmID = :ID";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":ID", $firmID);
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
    {
      if ($row['userID'] == $this->jwt->sub)
        return true;
    }
    return false;
  }
  /**
   * Checks if customer with $ico is not already registered
   *
   * @param Database $db
   * @param int $ico
   * @return boolean
   */
  private function isCustomerRegistered ($db, $ico)
  {
    $query = "SELECT ID
      FROM Customers
      WHERE ICO=?";
    
    $stmt = $db->prepare($query);

    $stmt->bindParam(1, $ico);
    $stmt->execute();
    if ($stmt->rowCount() > 0)
      return true;
    else
      return false;
  }
  /**
   * Checks if email provided is not already in database
   *
   * @param Database $db
   * @param string $email
   * @return boolean
   */
  private function isEmailTaken ($db, $email)
  {
    $query = "SELECT Users.ID
      FROM Users
      WHERE Users.email=?";
    
    $stmt = $db->prepare($query);

    $stmt->bindParam(1, $email);
    $stmt->execute();
    if ($stmt->rowCount() > 0)
      return true;
    else
      return false;
  }
  /**
   * Sets refresh JWT in database for user with $email
   *
   * @param string $email
   * @param string $token
   * @return void
   */
  private function setRefreshJWTinDatabase ($email, $token)
  {
    $database = new Database();
    $db = $database->getConnection();

    $query = "UPDATE Users
      SET refTok=:refTok, shouldRevoke='0'
      WHERE email=:email";

    $stmt = $db->prepare($query);
    $stmt->bindParam(":refTok", $token);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
  }
  /**
   * Determines whether token provided via headers is access JWT
   *
   * @return boolean
   */
  private function isAccessToken ()
  {
    // Access JWT does not contain $jwt->data 
    if ($this->jwt != null && !isset($this->jwt->data))
      return true;
    return false;
  }
}
?>
