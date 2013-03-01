<?php

  $dir_fs = dirname(realpath($_SERVER['SCRIPT_FILENAME']));
  $dir_fs = str_replace('notify', '', $dir_fs);
  $dir_fs = rtrim($dir_fs, '\/');

  define('DIR_FS_ROOT', $dir_fs . '/'); //Define Root path for site 

  require_once DIR_FS_ROOT . 'includes/initialize.php';
  require_once 'NotifyQueue.class.php';

  $delay = 1000000; // delay between queries. 1 000 000 means 1 second

  echo 'Started: ' . date('Y-m-d H:i:s') . "\n";

  for ($i = 1; $i <= 10; $i++) {
    if (NETWORK == 'vk') {
      $log = NotifyQueue::doTask_VK();
    }

    if (NETWORK == 'mr') {
      $log = NotifyQueue::doTask_MR();
    }

    if ($log) {
      echo $log;
    } else {
      exit("no task\n");
    }

    usleep($delay);
  }
  echo 'Finished: ' . date('Y-m-d H:i:s') . "\n\n";
?>