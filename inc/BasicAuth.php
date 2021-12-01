<?php
require_once __DIR__ . '/GoogleAuthenticator.php';

/**
 * BasicAuth
 */
class BasicAuth
{

  /**
   * __construct
   *
   * @return void
   */
  public function __construct()
  {
    switch (filter_input(INPUT_GET, 'request')) {
      case 'register':
        $this->register();
        break;
      case 'logout':
        $this->logout();
        break;
      default:
        $this->login();
    }
  }

  /**
   * register
   *
   * @return void
   */
  private function register()
  {
    try {
      $ga = new PHPGangsta_GoogleAuthenticator();

      // 秘密鍵の生成
      $secret = $ga->createSecret();

      // ユーザー名
      $user = filter_input(INPUT_GET, 'user');
      if (!$user) {
        throw new Exception('403 forbidden.');
      }

      // QRコードURLの生成と表示
      $qr_code = $ga->getQRCodeGoogleUrl($user, $secret, SITE_TITLE);

      header('Content-Type: text/html; charset=utf-8');
      echo "<p>秘密鍵：{$secret}</p>";
      echo "<p><img src=\"{$qr_code}\" /></p>";
      exit;
    } catch (Exception $e) {
      header('Content-Type: text/plain; charset=utf-8');
      exit($e->getMessage());
    }
  }

  /**
   * login
   *
   * @return void
   */
  private function login()
  {
    try {
      $ga = new PHPGangsta_GoogleAuthenticator();
      $discrepancy = 2;

      $users = [];
      $f = fopen(__DIR__ . '/../secret.csv', 'r');
      while ($data = fgetcsv($f)) {
        if (isset($data[0], $data[1])) {
          $users[$data[0]] = $data[1];
        }
      }
      fclose($f);

      if (isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $users[$_SERVER['PHP_AUTH_USER']])) {
        if (!$ga->verifyCode($users[$_SERVER['PHP_AUTH_USER']], $_SERVER['PHP_AUTH_PW'], $discrepancy)) {
          throw new Exception('403 forbidden.');
        }
      } else {
        throw new Exception('403 forbidden.');
      }
    } catch (Exception $e) {
      header('WWW-Authenticate: Basic realm="Enter username and password."');
      header('Content-Type: text/plain; charset=utf-8');
      exit($e->getMessage());
    }
  }

  /**
   * logout
   *
   * @return void
   */
  private function logout()
  {
    unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Logout');
  }
}
