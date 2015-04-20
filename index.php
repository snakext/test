<?php

/*
  if($curl = curl_init()){
  $options = array(
  CURLOPT_URL => 'http://www.gismeteo.ua/weather-mykolaiv-4983/',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => fals,
  );
  curl_setopt_array($curl, $options);
  $result = curl_exec($curl);
  echo $result;
  curl_close($curl);

  }
 */
/*
  if($curl = curl_init()){

  $curlOptions = array(
  CURLOPT_URL => 'http://test/reciever.php',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => array(
  'a' => 4,
  'b' => 7
  )
  );
  curl_setopt_array($curl, $curlOptions);
  $result = curl_exec($curl);
  echo 'result: '.$result;
  curl_close($curl);
  }
 */

//$dom = new domDocument("1.0", "utf-8"); // Создаём XML-документ версии 1.0 с кодировкой utf-8
//$dom->formatOutput = true;
//$root = $dom->createElement("users"); // Создаём корневой элемент
//$dom->appendChild($root);
//$logins = array("User1", "User2", "User3"); // Логины пользователей
//$passwords = array("Pass1", "Pass2", "Pass3"); // Пароли пользователей
//for ($i = 0; $i < count($logins); $i++) {
//    $id = $i + 1; // id-пользователя
//    $user = $dom->createElement("user"); // Создаём узел "user"
//    $user->setAttribute("id", $id); // Устанавливаем атрибут "id" у узла "user"
//    $login = $dom->createElement("login", $logins[$i]); // Создаём узел "login" с текстом внутри
//    $password = $dom->createElement("password", $passwords[$i]); // Создаём узел "password" с текстом внутри
//    $user->appendChild($login); // Добавляем в узел "user" узел "login"
//    $user->appendChild($password); // Добавляем в узел "user" узел "password"
//    $root->appendChild($user); // Добавляем в корневой узел "users" узел "user"
//}
//header('Content-type: text/xml');
//header("Content-Type: application/force-download");
//header('Content-Disposition: attachment; filename=test.xml');
//header('Content-type: text/xml');
//header('Content-Disposition: attachment; filename=dom.xml');
//header("Content-Type: application/force-download");
//header('Pragma: private');
//header('Cache-control: private, must-revalidate');
//echo $dom->saveXML();
//$pass = 'xexexaxa';
//$db_pass = crypt($pass);
//echo $db_pass .'<br>';
//
//echo crypt($pass, $db_pass);

$passport = 'E0000220';
$passport = str_split($passport);
$tmp = '';
$flag = false;
foreach ($passport as $l) {
    if (((int) $l) > 0) {
        $flag = true;
    }
    if ($flag) {
        $tmp .= $l;
    }
}
echo $tmp;
