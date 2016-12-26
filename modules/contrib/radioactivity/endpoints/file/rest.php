<?php

/**
 * @file
 * Rest endpoint processing for radioactivity module.
 */

header("Content-Type: application/json; charset=utf-8");

$file = sys_get_temp_dir() . '/radioactivity-payload.json';

$data = file_get_contents("php://input");

/**
 * Verify incident, very simply.
 *
 * @param string|int|object... $data
 *   Received in $_POST.
 *
 * @return bool
 *   True when $data is a valid object, false if not.
 */
function verify_incident($data) {
  $incidents = json_decode($data, TRUE);
  $keys = ['fn', 'et', 'id', 'e', 'h'];
  foreach ($incidents as $incident) {
    if (count($keys) !== count($incident)
      || count(array_intersect_key(array_flip($keys), $incident)) !== count($keys)) {
      return FALSE;
    }
  }
  return TRUE;
}

/**
 * Exist with status.
 *
 * @param string|int|object... $status
 *   The status code (ok, error).
 * @param string|int|object... $message
 *   The message describing what went wrong.
 */
function rest_exit($status, $message) {
  echo json_encode([
    'status' => $status,
    'message' => $message,
  ]);
  exit();
}

/**
 * PROCESS REQUESTS.
 */

if (strlen($data) > 0) {

  // There is POST data, process it.
  if (!verify_incident($data)) {
    rest_exit('error', 'Invalid json.');
  }

  $fh = fopen($file, "a+");
  fwrite($fh, $data . ',' . PHP_EOL);
  fclose($fh);

  rest_exit('ok', 'Inserted.');

}
else {

  // No post data, check the parameters.
  if (isset($_GET['clear'])) {

    if (file_exists($file)) {
      unlink($file);
    }
    rest_exit('ok', 'Cleared.');

  }
  elseif (isset($_GET['get'])) {

    if (file_exists($file)) {
      // Get file contents and clear file.
      $fh = fopen($file, "r");
      $data = fread($fh, filesize($file));
      fclose($fh);

      echo "[" . rtrim($data, ',' . PHP_EOL) . "]";
    }
    else {
      echo "[]";
    }

    exit();
  }
  else {

    rest_exit('error', 'Nothing to do.');

  }
}
