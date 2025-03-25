<?php
function postINFO($url, $post, $token)
{
  $crl = curl_init();
  $authorization[] = "Authorization: " . $token;
  $authorization[] = 'Content-Type: application/x-www-form-urlencoded';

  if (is_array($post))
    $post_enconding = http_build_query($post);
  else
  {
    $post = str_replace('+', '%2B', $post);
    parse_str($post, $params);
    $post_enconding = "";
    $count_params = count($params) - 1;
    $i = 0;
    foreach ($params as $key => $value) {
      $post_enconding .= $key . "=" . urlencode($value);
      if ($i < $count_params)
        $post_enconding .= "&";
      $i++;
    }
  }
  curl_setopt($crl, CURLOPT_URL, $url);
  curl_setopt($crl, CURLOPT_HTTPHEADER, $authorization);
  curl_setopt($crl, CURLOPT_FRESH_CONNECT, true);
  curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($crl, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($crl, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($crl, CURLOPT_POST, TRUE);
  curl_setopt($crl, CURLOPT_POSTFIELDS, $post_enconding);
  $response = curl_exec($crl);
  if (!$response)
    die('Error: "' . curl_error($crl) . '" - Code: ' . curl_errno($crl));
  curl_close($crl);
  return json_decode($response);
}

$function = "addWebSite";
$arguments_fields["protocol"] = 0;

$arguments = json_encode($arguments_fields); //enviar os argumentos como json

$post["function"] = $function;
$post["arguments"] = $arguments;
$token = "98f87568e8c51028d5832ad188a6d214"; // chave descrita acima
$serverip = "172.18.203.225";
$url = "https://$serverip/api/server.php";
$result = postINFO($url, $post, $token);
echo "\n\nresult=";
print_r($result);