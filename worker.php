#!/usr/bin/env php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

class bitrix24worker
{
    private $c;
    private $host;
    private $sessid;
    private $siteId;

    public function __construct($domain)
    {
        $this->checkWorkDay();
        $this->host = 'https://' . $domain . '/';
        $this->c = curl_init();
        curl_setopt($this->c, CURLOPT_URL, $this->host);
        curl_setopt($this->c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->c, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->c, CURLOPT_COOKIEJAR, '/tmp/bitrix24worker.cookies');
        curl_setopt($this->c, CURLOPT_POST, 1);
        curl_setopt($this->c, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.80 Safari/537.36');
    }

    public function checkWorkDay()
    {
        $today = date('Ymd');
        $todayNumber = date('N');
        if ($holidays = trim(@file_get_contents(__DIR__ . '/holidays'))) {
            $holidaysList = array_flip(explode("\n", $holidays));
            if (array_key_exists($today, $holidaysList)) {
                exit;
            }
        }
        if ($workdays = trim(@file_get_contents(__DIR__ . '/workdays'))) {
            $workdaysList = array_flip(explode("\n", $workdays));
            if (array_key_exists($today, $workdaysList)) {
                return;
            }
        }
        if ($todayNumber === '6' || $todayNumber === '7') {
            exit;
        }
    }

    /**
     * @param string $login
     * @param string $password
     */
    public function auth($login, $password)
    {
        $post = array(
            'AUTH_FORM' => 'Y',
            'TYPE' => 'AUTH',
            'USER_REMEMBER' => 'Y',
            'USER_LOGIN' => $login,
            'USER_PASSWORD' => $password
        );

        curl_setopt($this->c, CURLOPT_POSTFIELDS, http_build_query($post));
        $html = curl_exec($this->c);
        $e = explode('sonetLSessid: \'sessid=', $html);
        $e = explode('\'', $e['1']);
        $this->sessid = $e['0'];
        $e = explode('sonetLSiteId: \'', $html);
        $e = explode('\'', $e['1']);
        $this->siteId = $e['0'];
    }

    public function start()
    {
        curl_setopt($this->c, CURLOPT_URL, $this->host . 'bitrix/tools/timeman.php?action=open&site_id=' . $this->siteId . '&sessid=' . $this->sessid);
        curl_setopt($this->c, CURLOPT_POSTFIELDS, 'timestamp=0&report=');
        curl_exec($this->c);
    }

    public function stop()
    {
        curl_setopt($this->c, CURLOPT_URL, $this->host . 'bitrix/tools/timeman.php?action=close&site_id=' . $this->siteId . '&sessid=' . $this->sessid);
        curl_setopt($this->c, CURLOPT_POSTFIELDS, 'timestamp=0&report=');
        curl_exec($this->c);
    }

    public function restart()
    {
        $this->stop();
        curl_setopt($this->c, CURLOPT_URL, $this->host . 'bitrix/tools/timeman.php?action=reopen&site_id=' . $this->siteId . '&sessid=' . $this->sessid);
        curl_setopt($this->c, CURLOPT_POSTFIELDS, 'timestamp=0&report=');
        curl_exec($this->c);
    }

    public function close()
    {
        curl_close($this->c);
    }
}

if (empty($argv[1]) || empty($argv[2]) || empty($argv[3]) || empty($argv[4])) {
    echo 'Specify method, example: php worker.php domain.bitrix24.ru start user@mail.com passWord' . PHP_EOL;
    exit;
}
$domain = $argv[1];
$method = $argv[2];
$login = $argv[3];
$password = $argv[4];

$b24w = new bitrix24worker($domain);
if (!method_exists($b24w, $method)) {
    echo 'Wrong method name, available methods: start, stop, restart' . PHP_EOL;
    exit;
}

$b24w->auth($login, $password);
call_user_func(array($b24w, $method));
$b24w->close();


