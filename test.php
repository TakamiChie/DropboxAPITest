<?php
$dropbox = null;

define("FILE_SETTING", __DIR__ . "/dropbox.conf");

if(isset($_GET['challenge'])){
  echo htmlspecialchars($_GET['challenge']);
}

require_once(__DIR__ . '/vendor/autoload.php');

$fp = fopen(FILE_SETTING, "c+");
if(flock($fp, LOCK_EX)){

  // 読み込み
  $setting = json_decode(fread($fp, filesize(FILE_SETTING)), TRUE);

  try{
    $dropbox = new \Dropbox\Client($setting["token"], "Dropbox Test");
    $cursor = !empty($setting["cursor"]) ? $setting["cursor"] : null;
    $cursor = loadOfDelta($cursor);
    $setting["cursor"] = $cursor;
    // 書き込み
    ftruncate($fp, 0);
    fwrite($fp, json_encode($setting));
    fclose($fp);
  }catch(Exception $e){
    sendWebhook($e->getMessage(), "Dropbox Test\n", ":no_entry_sign:");
  }
}else{
  sendWebhook("File Lock Failed", "Dropbox Test\n", ":no_entry_sign:");
}

function loadOfDelta($cursor){
  global $dropbox;
  $deltaPage = $dropbox->getDelta($cursor);
  $str = "";
  $numAdds = 0;
  $numRemoves = 0;
  foreach ($deltaPage["entries"] as $entry) {
      list($lcPath, $metadata) = $entry;
      if ($metadata === null) {
          $str .= "- $lcPath\n";
          $numRemoves++;
      } else {
          $str .= "+ $lcPath\n";
          $numAdds++;
      }
  }
  $str .= "Num Adds: $numAdds\n";
  $str .= "Num Removes: $numRemoves\n";
  $str .= "Has More: ".$deltaPage["has_more"]."\n";
  $str .= "Cursor: ".$deltaPage["cursor"]."\n";
  sendWebhook($str, "Dropbox Test\n", ":sparkles");
  return $deltaPage["cursor"];
}

function sendWebhook($text, $name, $icon = ":email") {
  global $setting;
  $hookaddr = $setting["webhook"];
  if($hookaddr){
    $payload = array(
          "text" => $text,
          "username" => $name,
          "icon_emoji" => $icon,
        );
    // curl
    $curl = curl_init($hookaddr);
    try{
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HTTPHEADER, array());
      curl_setopt($curl, CURLOPT_POSTFIELDS, array('payload' => json_encode($payload)));
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      $res = curl_exec($curl);
      $err = curl_error($curl);
      if($err) throw new Exception($err);
      if($res != "ok") throw new Exception($res);
    }catch(Exception $e){
      throw $e;
    }
    curl_close($curl);
  }
}
?>