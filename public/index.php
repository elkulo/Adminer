<?php
define('SITE_TITLE', 'Adminer');

require_once __DIR__ . '/../inc/OTPBasicAuth.php';
new OTPBasicAuth();

function adminer_object()
{
  return new class extends Adminer
  {
    function name()
    {
      return SITE_TITLE;
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
require_once __DIR__ . '/../inc/Adminer.php';
