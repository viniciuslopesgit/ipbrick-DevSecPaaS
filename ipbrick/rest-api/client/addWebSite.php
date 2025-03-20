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
$arguments_fields["protocol"] = 2;
$arguments_fields["servername"] = "website.domain.com";
$arguments_fields["serveralias"] = "";
$arguments_fields["serveradmin"] = "administrator@domain.com";
$arguments_fields["ftplogin"] = "admin";
$arguments_fields["ftppass"] = "Aa123456!";
$arguments_fields["documentroot"] = "facebookdemovlucoippt";
$arguments_fields["internet"] = 1;
$arguments_fields["safe_mode"] = 0;
$arguments_fields["open_basedir"] = "home1/_sites/websitedomaincom/tmp";
// $arguments_fields["charset"] = "On";
// $arguments_fields["canonicalname"] = "UseCanonicalName Off";

$arguments = json_encode($arguments_fields); //enviar os argumentos como json

$post["function"] = $function;
$post["arguments"] = $arguments;
$token = "98f87568e8c51028d5832ad188a6d214"; // chave descrita acima
$serverip = "172.18.203.225";
$url = "https://$serverip/api/server.php";
$result = postINFO($url, $post, $token);
echo "\n\nresult=";
print_r($result);