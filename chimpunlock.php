<?php
  include_once('MCAPI.class.php');
  #$username = 'foxycart';
  #$password = 'bNzamino3';

  (isset($_COOKIE['chimpunlock']) and
   list($username, $password) = explode('|', $_COOKIE['chimpunlock'])) or
   (isset($_POST['username']) and $username = $_POST['username'] and
   isset($_POST['password']) and $password = $_POST['password']) or
   list($username, $password) = array(null, null);

  $mc = new MCAPI($username, $password);

  if (!$mc->api_key) {
    setcookie('chimpunlock', null);
    die((isset($_POST['username']) ? 'Couldn\'t log in: '.$mc->errorMessage : '').'
      <form action="#" method="POST">
        <input type="text" name="username"/>
        <input type="password" name="pass"/>
        <input type="submit" value="Login"/>
      </form>');
  }
  else {
    setcookie('chimpunlock', "$username|$password");
    header('Location: '.$_SERVER['PHP_SELF']) and die();
  }

  $mc->closeOneOhSecurityHole($username, $password) or
   $mc->errorCode == 101 or
   die('Security hole still open.'.$mc->errorCode.': '.$mc->errorMessage);

  if (isset($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
    case 'expire':
      $mc->apikeyExpire($username, $password);
      break;
    case 'add':
      $mc->apikeyAdd($username, $password, $_REQUEST['key']);
      break;
    }
    header('Location: '.$_SERVER['PHP_SELF']) and die();
  }


  echo '
    <table>
      <thead>
        <tr>
          <th>key</th>
          <th></th>
        </tr>
      </thead>
      <tbody>';

  foreach ($mc->apikeys($username, $password, true) as $key) {
    extract($key);
    echo "
    <tr>
      <td>$apikey</td>
      <td>$created_at</td>
      <td>$expired_at</td>
      <td><a href='?action=expire&key=$apikey' class='cmd expire'>expire</a></td>
    </tr>";
  }
  echo "
      </tbody>
    </table>";

 ?>
  <a href="?action=add" class='cmd add'>add new</a>
