<?php
// include globally used functions
include_once './resources/functions.php';
// include files for creating & managing JWTs from php-jwt
include_once './libs/php-jwt/src/BeforeValidException.php';
include_once './libs/php-jwt/src/ExpiredException.php';
include_once './libs/php-jwt/src/JWT.php';
include_once './libs/php-jwt/src/SignatureInvalidException.php';
use \Firebase\JWT\JWT;

/**
 * Define global used constants by this file
 */
define('key', "");
define('iss', "");

/**
 * Creates refresh JWT by $user's own parameters
 *
 * @param array $user
 * @return string
 */
function createRefreshToken ($user)
{
  
  $token = array(
    "iss" => constant('iss'),
    "sub" => $user['ID'],
    "data" => array(
      "member_number" => $user['member_number'],
    )
  );

  $jwt = JWT::encode($token, constant('key'));
  return $jwt;
}

/**
 * Checks if $token is not expired and then decodes it
 *
 * @param string $token
 * @return object|void
 */
function decodeToken ($token)
{
  if (!isTokenExpired($token))
    return JWT::decode($token, constant('key'), array('HS256'));
  else
    response(403, "Forbidden", array("message" => "Token expired"));
}

/**
 * Decides whether $token is expired or not
 *
 * @param string $token
 * @return boolean|void
 */
function isTokenExpired ($token)
{
  try {
    $decoded = JWT::decode($token, constant('key'), array('HS256'));
    return false;
  } catch (Exception $e)
  {
    if ($e->getMessage() == "Expired token")
    {
      return true;
    }
    response(403, "Forbidden", array("message" => $e->getMessage()));
  }
}
?>
