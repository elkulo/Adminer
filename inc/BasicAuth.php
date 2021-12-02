<?php
require_once __DIR__ . '/GoogleAuthenticator.php';

/**
 * BasicAuth
 */
class BasicAuth
{

  private const PW_FILE = __DIR__ . '/../.htpasswd';

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

      // ユーザー確認
      if (file_exists(static::PW_FILE)) {
        $f = fopen(static::PW_FILE, 'r');
        while ($data = fgetcsv($f, 0, ':')) {
          if (isset($data[0], $data[1])) {
            $this->login();
          }
        }
        fclose($f);
      } else {
        throw new Exception('403 forbidden.');
      }

      header('Content-Type: text/html; charset=utf-8');
      printf(
        '<!DOCTYPE html><html><head><meta charset="UTF-8" /><title>%1$s</title><meta name="robots" content="noindex,follow" /></head>
          <body style="background:#1c1b22;color:#fff;text-align:center;">
            <p>秘密鍵</p><p>%2$s</p><p><img src="%3$s" /></p>
          </body>
        </html>',
        SITE_TITLE . ' | 登録',
        $secret,
        $qr_code
      );
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

      if (file_exists(static::PW_FILE)) {
        $f = fopen(static::PW_FILE, 'r');
        while ($data = fgetcsv($f, 0, ':')) {
          if (isset($data[0], $data[1])) {
            $users[$data[0]] = $data[1];
          }
        }
        fclose($f);
      } else {
        throw new Exception('403 forbidden.');
      }

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
    header('Content-Type: text/html; charset=utf-8');
    printf(
      '<!DOCTYPE html><html><head><meta charset="UTF-8" /><title>%1$s</title><meta name="robots" content="noindex,follow" /></head>
        <body style="background:#1c1b22;color:#fff;text-align:center;">
          <p>See you ヾ(*´∀｀*)ﾉ</p>
        </body>
      </html>',
      SITE_TITLE . ' | ログアウト'
    );
    exit;
  }
}
