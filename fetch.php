<?php
$ch = curl_init();
$headers = [];
foreach(getallheaders() as $key => $val) {
  if (!in_array(strtolower($key), ['accept-encoding', 'host'])) {
    // TODO: Figure out why the above headers break it.
    $headers[] = "$key: $val";
  }
}
$url = rawurldecode($_GET['url']);
curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
curl_setopt($ch, CURLOPT_FAILONERROR, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $_SERVER['HTTP_HOST']!='localhost');

// https://stackoverflow.com/a/41135574 ----------------------------------------
// $ch = curl_init();
$headers = [];
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// this function is called by curl for each header received
curl_setopt($ch, CURLOPT_HEADERFUNCTION,
  function($curl, $header) use (&$headers)
  {
    $len = strlen($header);
    $header = explode(':', $header, 2);
    if (count($header) < 2) // ignore invalid headers
      return $len;

    $headers[strtolower(trim($header[0]))][] = trim($header[1]);

    return $len;
  }
);

$data = curl_exec($ch);
// print_r($headers);
// -----------------------------------------------------------------------------

foreach ($headers as $key => $values) {
  if ($key != 'transfer-encoding') {
    // TODO: Figure out a more robust way of dealing with different encodings.
    foreach ($values as $val) {
      header("$key: $val");
    }
  }
}
echo $data;
?>
