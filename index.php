<?php
  // required headers
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json; charset=UTF-8');
  header('Access-Control-Max-Age: 3600');
  header('Allow: GET, POST, DELETE, PUT');
  // including external files
  include_once './resources/rest.php';

  $method = $_SERVER['REQUEST_METHOD'];


  // will try to assign values to it's corresponding variable
  try
  {
    $object = !empty($_GET['object']) ? strtolower($_GET['object']) : null;
    $ID = !empty($_GET['ID']) ? $_GET['ID'] : null;
    $sub = !empty($_GET['sub']) ? strtolower($_GET['sub']) : null;
    $subID = !empty($_GET['subID']) ? $_GET['subID'] : null;

    $rest = new Rest($method, $object, $ID, $sub, $subID);
    $rest->makeRequest();
   
  } catch (Exception $e) {
    echo 'Caught exception: '. $e->getMessage(), "\n";
  }
?>
