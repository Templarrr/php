<?php
/**
 * @author Konstanti Zhirnov
 * @copyright 2010
 */
  require_once 'includes/initialize.php';
  require_once 'notify/NotifyQueue.class.php';

  $test = false;
  $testID   = '4397170518446810707'; // http://appsmail.ru/platform/mail/heartless.gd/
  $password = 'cOAIYVyzGtH48Pt8756';

?>
<?php
  $echo = '';
  if(isset($_POST['sendMessage'])){
    if (isset($_POST['password']) && ($_POST['password'] == $password)) {

      set_time_limit(0);
      ini_set('memory_limit', '512M');

      $iDatabase = iFactory::singleton('iDatabase_' . DB_DATABASE_CLASS);
      if ($test) {
        $qUsers = $iDatabase->query('SELECT DISTINCT(idVKUser) FROM :tblUsers WHERE idVKUser = :testID');
        $qUsers->bindValue(':testID', $testID);
      } else {
        $qUsers = $iDatabase->query('SELECT DISTINCT(idVKUser) FROM :tblUsers WHERE idVKUser IS NOT NULL');
      }

      $qUsers->bindTable(':tblUsers', TABLE_USERS);
      $qUsers->execute();

      $ids = array();

      if(!$iDatabase->isError() && $qUsers->numberOfRows() > 0) {
        while ($qUsers->next()) {
          $ids[] = $qUsers->value('idVKUser');
        }

        $curDir = getcwd();
        chdir('notify');
        NotifyQueue::addTask($ids, $_POST['message']);
        echo 'All records processed<br />';
        chdir($curDir);
      }
      echo "Done;";
    } else {
      $echo = 'Wrong passord! <hr />';
    }
  } else {
     $echo = '<hr />';
  }

  if ($echo != '') {
    ?>
<html>
<head>
  <meta content="text/html; charset=UTF-8" http-equiv="Content-type" />
</head>
<body>
  <?php
    echo $echo;
  ?>
 <form method="POST">
  Message:<br />
  <input type='text' name='message' size='100' />
  <br />
  Password:<br />
  <input type='password' name='password' size='50' />
  <br />
  <input type='submit' name='sendMessage' value='Send' />
 </form>
</body>
</html>
<html>
<?php
  }

  require_once('includes/finalize.php');
?>