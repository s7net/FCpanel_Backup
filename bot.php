<?php
define('API_KEY','TOKEN');
$chat_id = "CH_ID";

function Tel($method,$Bot=[]) {
  $url = "https://api.telegram.org/bot".API_KEY."/".$method;
  $ch = curl_init();
  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
  curl_setopt($ch,CURLOPT_POSTFIELDS,$Bot);
  $res = curl_exec($ch);
  if(curl_error($ch)) {
    var_dump(curl_error($ch));
  } else {
    return json_decode($res);
  }
}

function createZipFile($source, $destination) {
  $zip = new ZipArchive();
  if ($zip->open($destination, ZIPARCHIVE::CREATE) !== TRUE) {
    return false;
  }

  $source = str_replace('\\', '/', realpath($source));

  if (is_dir($source) === true) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

    foreach ($files as $file) {
      $file = str_replace('\\', '/', $file);

      if (in_array(substr($file, strrpos($file, '/')+1), array('.', '..'))) {
        continue;
      }

      $file = realpath($file);

      if (is_dir($file) === true) {
        $zip->addEmptyDir(str_replace($source.'/', '', $file.'/'));
      } else if (is_file($file) === true) {
        $zip->addFromString(str_replace($source.'/', '', $file), file_get_contents($file));
      }
    }
  } else if (is_file($source) === true) {
    $zip->addFromString(basename($source), file_get_contents($source));
  }

  return $zip->close();
}

$date = date("Y_m_d");
$time = date("H:i:s");
$backupFileName = "{$date}_backup.zip";

createZipFile('', $backupFileName);

$result = Tel('SendDocument', [
  'chat_id' => $chat_id,
  'document' => new CURLFile($backupFileName),
  'caption' => "⏰ ساعت : $time\n⏳ تاریخ #date{$date}"
]);

if ($result->ok) {
  unlink($backupFileName);
}
