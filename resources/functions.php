<?php
/**
 * Sanitizes parameter $i from dangerous tags
 *
 * @param string $i
 * @return string
 */
function sanitize ($i)
{
  return htmlspecialchars(strip_tags($i));
}

/**
 * Returns 406 error code when data is not complete
 *
 * @return array
 */
function ret406 ()
{
  return array(
    "status" => 406,
    "statusMsg" => "Not Acceptable",
    "data" => array("message" =>"Data not complete")
  );
}

/**
 * Raises 403 error code when API endpoint is reached without Authentication
 * or with invalid token
 *
 * @param string $message
 * @return void
 */
function raise403 ($message)
{
  response(403, 'Forbidden', array("message" => $message));
}


/**
 * Sets HTTP codes with code message and displays data
 *
 * @param int $status
 * @param string $statusMsg
 * @param array $data
 * @return void
 */
function response ($status, $statusMsg, $data)
{
  header("HTTP/1.1 ".$status);

  $response['status'] = $status;
  $response['statusMsg'] = $statusMsg;
  $response['data'] = $data;

  $jsonResponse = json_encode($response);
  echo $jsonResponse;die();
}
?>
