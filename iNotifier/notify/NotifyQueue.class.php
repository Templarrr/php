<?php

  class NotifyQueue {
    static private $notification_queue = 'queue';
    static private $queue_log          = 'queue_log';
    static private $id_limit = 100;

    static public function setQueueFile($file) {
      self::$notification_queue = $file;
    }

    static public function addTask($ids, $text) {
        $chunks = array_chunk($ids, self::$id_limit);
        $file = fopen(self::$notification_queue, 'a');
        flock($file, LOCK_EX);
        foreach ($chunks as $chunk) {
          $task = json_encode(array('ids' => implode(",",$chunk), 'text' => $text)) . "\n";
          $res = fwrite($file, $task);
        }
        fclose($file);
        return $res;
    }

    static private function popTask() {
      if (strpos(self::$notification_queue, 'notify') === false) {
        self::$notification_queue = 'notify/' . self::$notification_queue;
      }
        $lines = file(self::$notification_queue);
        $file = fopen(self::$notification_queue, 'w');
        $task = array_shift($lines);
        foreach ($lines as $i => $line) {
            fputs($file, $line);
        }
        fclose($file);
        return json_decode($task, true);
    }

    static public function doTask_VK() {
      $task = self::popTask();

      if (!isset($task['ids'])) {
        return false;
      }

      $post = array();
      $post['api_id']  = APP_ID;
      $post['format']  = 'JSON';
      $post['message'] = $task['text'];
      $post['method']  = 'secure.sendNotification';
      $post['random']  = rand();
      $post['timestamp'] = time() + 3600;
      $post['uids']    = $task['ids'];
      $post['v']       = '2.0';
  		$sig = '';
  		foreach($post as $key => $value) {
  			$sig .= $key . '=' . $value;
  		}

  		$sig .= SECRET_KEY;
	  	$post['sig'] = md5($sig);

      ini_set('allow_url_fopen', 1);



      ksort($post);

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL,            'http://api.vkontakte.ru/api.php');
      curl_setopt($ch, CURLOPT_POST,           1);
      curl_setopt($ch, CURLOPT_POSTFIELDS,     http_build_query($post, '', '&'));
      curl_setopt($ch, CURLOPT_HEADER,         false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
      curl_setopt($ch, CURLOPT_TIMEOUT,        60);
      $content = curl_exec($ch);
      curl_close($ch);

      $log = date('Y-m-d H:i:s ');

      $content = json_decode($content, true);
      
      if (isset($content['response'])) {
         $sent_count  = count(explode(',', $content['response']));
         $queue_count = count(explode(',', $task['ids']));

         $log .= $sent_count . '/' . $queue_count;
      } else {
         $log .= json_encode($content['error']);
      }

      self::addLog($log);

      return $log . "\n";
    }

   static public function doTask_MR() {
      $task = self::popTask();

      if (!isset($task['ids'])) {
        return false;
      }

      $params['text']   = $task['text'];
      $params['uids']   = $task['ids'];
  		$params['method'] = 'notifications.send';
  		$params['api_id'] = APP_ID;
  		$params['secure'] = 1;
  		$params['format'] = 'xml';

  		ksort($params);

  		$sig = '';
  		foreach ($params as $k=>$v)
  			$sig .= $k.'='.$v;

  		$sig .= SECRET_KEY;
  		$params['sig'] = md5($sig);

  		$pice = array();
  		foreach ($params as $k => $v)
  			$pice[] = $k . '=' . urlencode($v);

  		$query = 'http://www.appsmail.ru/platform/api?' . implode('&',$pice);

		  $res = file_get_contents($query);

      $log = date('Y-m-d H:i:s ' + $res);

      self::addLog($log);

      return $log . "\n";
    }

    static public function addLog($entry) {
     $dir = getcwd();
      if (strpos(self::$queue_log, 'notify') === false) {
        self::$queue_log = 'notify/' . self::$queue_log;
      }

      $file = fopen(self::$queue_log, 'a+');
      flock($file, LOCK_EX);
      fwrite($file, $entry . "\n");
      fclose($file);
    }

  }