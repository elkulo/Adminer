<?php
define('ADMINER_FILE', __DIR__ . '/../inc/Adminer.php');
define('AUTH_FILE', __DIR__ . '/../inc/GoogleAuthenticator.php');

function basic_auth_login()
{
  $hashes = [
    'admin' => password_hash('hogehoge', PASSWORD_DEFAULT),
  ];

  if (
    !isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) ||
    !password_verify($_SERVER['PHP_AUTH_PW'], isset($hashes[$_SERVER['PHP_AUTH_USER']]) ? $hashes[$_SERVER['PHP_AUTH_USER']] : '$2y$10$abcdefghijklmnopqrstuv')
  ) {
    // 初回時または認証が失敗したとき
    header('WWW-Authenticate: Basic realm="Enter username and password."');
    header('Content-Type: text/plain; charset=utf-8');
    exit('403 forbidden.');
  }
}

function basic_auth_logout()
{
  unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
}
//basic_auth_logout();

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
  basic_auth_login();
  require_once ADMINER_FILE;
} else {
  header('HTTP/1.1 404 Not Found');
  header('Content-Type: text/plain; charset=utf-8');
  exit('404 not found.');
}
