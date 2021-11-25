<?php
define('ADMINER_FILE', __DIR__ . '/../adminer.php');

function adminer_object()
{
  return new class extends Adminer
  {
    function name()
    {
      return 'Adminer | el.kulo';
    }
    function css()
    {
      $return = [];
      $filename = 'style.css';
      if (file_exists($filename)) {
        $return[] = $filename . '?ver=' . time();
      }
      return $return;
    }
  };
}

if (file_exists(ADMINER_FILE)) {
  require_once ADMINER_FILE;
} else {
  header("HTTP/1.1 404 Not Found");
  exit('404 Not Found.');
}
