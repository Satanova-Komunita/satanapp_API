<?php
// include globally used functions
include_once './resources/functions.php';
/**
 * Rest class is able to perform checks on used method
 * and call API's functions if method is permited to use
 */
class Rest 
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
   * Identifier of object
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
   * Identifier of subject
   *
   * @var int
   */
  private $subjectID;

  /**
   * Initializes new instance of Rest class
   *
   * @param string $method
   * @param string $object
   * @param int    $ID
   * @param string $subject
   * @param int    $subjectID
   */
  public function __construct (
    $method, $object,
    $ID = null, $subject = null,
    $subjectID = null
  ){
    $this->method = $method;
    $this->object = $object;
    $this->ID = $ID;
    $this->subject = $subject;
    $this->subjectID = $subjectID;
  }

  /**
   * Checks if user needs JWT, saves it, intializes
   * new instance of API class to perform actions
   * depending on params
   *
   * @return void
   */
  public function makeRequest ()
  {
    /**
     * Holds JWT given by headers
     */
    $jwt = null;
    if ($this->doesNeedToken())
    {
     /*
      * if token should be present, we'll try to get it from headers
      * and then validate it -> otherwise we'll raise 401 error code
      */
      if (isset($_SERVER['Authorization']))
      {
        $bearer = explode(" ", $_SERVER['Authorization']);
        $jwt = $this->validateAndDecodeToken($bearer[1]);
      }
      elseif (getallheaders()['Authorization'])
      {
        $bearer = explode(" ", getallheaders()['Authorization']);
        $jwt = $this->validateAndDecodeToken($bearer[1]);
      }
      else
      {
        header('WWW-Authenticate: Bearer realm="protected area"');
        response(401, "Authorization Required", null);
      }
    }
    // includes API's class file and initializes new instance of it
    include_once './resources/api.php';
    $api = new Api(
      $this->method, $this->object,
      $this->ID, $this->subject,
      $this->subjectID, $jwt
    );

    //
    //
    //
    // TO-DO: not all of these methods might actually be useful
    //
    //
    //
    switch ($this->method)
    {
      case 'GET':
      case 'POST':
      case 'PUT':
      case 'DELETE':
        $this->apiPerformance($api);
        break;
      default:
        response(405, 'Method Not Allowed', null);
    }
  }

  /**
   * Check's if given endpoints are valid and
   * then requests response depending on it
   *
   * @param Api $api
   * @return void
   */
  private function apiPerformance ($api)
  {
    if ($api->validateEndpoint())
    {
      $response = $api->requestResponse();
      response(
        $response['status'],
        $response['statusMsg'],
        $response['data']
      );
    }
    else
      response(404, 'Not Found', null);
  }

  /**
   * Depending on endpoint decides if user needs JWT
   *
   * @return bool
   */
  private function doesNeedToken ()
  {
    if ($this->object === 'login')
    {
      return false;
    }
    return true;
  }

  /**
   * Includes Auth file with function to decode JWT
   *
   * @param string $token
   * @return object|void
   */
  private function validateAndDecodeToken ($token)
  {
    include_once './resources/auth.php';
    return decodeToken($token);
  }

}
?>
