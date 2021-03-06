<?php

namespace ttrssCli\Services;

use ttrssCli\Exceptions\UserNotExist;

class TTRss
{
    protected $ttrss_dir = '';

    public function __construct($ttrssDir)
    {
        if (!is_dir($ttrssDir)) {
            throw new \InvalidArgumentException(sprintf('Ttrss path "%s" is not directory.', $ttrssDir));
        }

        $this->ttrss_dir = rtrim($ttrssDir, '/');
    }

    public function init()
    {
        spl_autoload_register(function($class) {
            $class_file = str_replace("_", "/", strtolower(basename($class)));
            $file = "{$this->getTtrssDir()}/include/../classes/$class_file.php";
            if (file_exists($file)) {
                require $file;
            }
        }, false, false);

        require_once "{$this->getTtrssDir()}/config.php";

        set_include_path(implode(PATH_SEPARATOR, [
            $this->getTtrssDir() . '/api',
            $this->getTtrssDir(),
            $this->getTtrssDir() . '/include',
            get_include_path()
        ]));
//        chdir("..");

//        define('TTRSS_SESSION_NAME', 'ttrss_api_sid');
//        define('NO_SESSION_AUTOSTART', true);

        require_once "autoload.php";
        require_once "db.php";
        require_once "db-prefs.php";
        require_once "functions.php";
        require_once "sessions.php";

//        ini_set("session.gc_maxlifetime", 86400);

//        define('AUTH_DISABLE_OTP', true);

//        if (defined('ENABLE_GZIP_OUTPUT') && ENABLE_GZIP_OUTPUT &&
//            function_exists("ob_gzhandler")) {
//
//            ob_start("ob_gzhandler");
//        } else {
//            ob_start();
//        }

//        $input = file_get_contents("php://input");

//        if (defined('_API_DEBUG_HTTP_ENABLED') && _API_DEBUG_HTTP_ENABLED) {
            // Override $_REQUEST with JSON-encoded data if available
            // fallback on HTTP parameters
//            if ($input) {
//                $input = json_decode($input, true);
//                if ($input) $_REQUEST = $input;
//            }
//        } else {
            // Accept JSON only
//            $input = json_decode($input, true);
//            $_REQUEST = $input;
//        }

//        if ($_REQUEST["sid"]) {
//            session_id($_REQUEST["sid"]);
//            @session_start();
//        } else if (defined('_API_DEBUG_HTTP_ENABLED')) {
//            @session_start();
//        }

        startup_gettext();

        if (!init_plugins()) return;

//        $method = strtolower($_REQUEST["op"]);
//
//        $handler = new API($_REQUEST);
//
//        if ($handler->before($method)) {
//            if ($method && method_exists($handler, $method)) {
//                $handler->$method();
//            } else if (method_exists($handler, 'index')) {
//                $handler->index($method);
//            }
//            $handler->after();
//        }

//        header("Api-Content-Length: " . ob_get_length());

//        ob_end_flush();

    }

    public function getTtrssDir()
    {
        return $this->ttrss_dir;
    }

    public function changeUserPassword($username, $password)
    {
        error_reporting(E_ALL);

        $authBasic = new \Auth_Base();
        $userId = $authBasic->find_user_by_login($username);

        if (false === $userId) {
            throw new UserNotExist(sprintf('User "%s" not exist in ttrss.', $username));
        }

        $_REQUEST["login"] = $username;
        $_REQUEST["id"] = $userId;
        $_REQUEST["password"] = $password;
        $_SESSION["uid"] = $userId;
        $_SESSION["clientTzOffset"] = 0;
        $_SESSION["access_level"] = 10;

        $obj = new \Pref_Users([]);
        ob_start();
        $obj->edit();
        $details = ob_get_clean();
        $dom = new \DOMDocument();
        $searchPage = mb_convert_encoding($details, 'HTML-ENTITIES', "UTF-8");
        $result = $dom->loadHTML("<body>$searchPage</body>");
        if (!$result) {
            throw new \RuntimeException(sprintf('Cant parse ttrss response.'));
        }

        $xpath = new \DOMXPath($dom);
        $element = $xpath->query('//input[@name="email"]');
        if ($element->length != 1) {
            throw new \RuntimeException(sprintf('Not found email element in ttrss response.'));
        }

        $email = $element->item(0)->attributes->getNamedItem('value')->nodeValue;

        $element = $xpath->query('//input[@name="access_level"]');
        if ($element->length != 1) {
            throw new \RuntimeException(sprintf('Not found access_level element in ttrss response.'));
        }
        $accessLevel = $element->item(0)->attributes->getNamedItem('value')->nodeValue;

        $_REQUEST["access_level"] = $accessLevel;
        $_REQUEST["email"] = $email;

        $obj->editSave();
    }

    public function changeUserEmail($login, $email)
    {
//        error_reporting(E_ALL);
        $authBasic = new \Auth_Base();
        $userId = $authBasic->find_user_by_login($login);

        if (false === $userId) {
            throw new UserNotExist(sprintf('User "%s" not exist in ttrss.', $login));
        }

        $_REQUEST["login"] = $login;
        $_REQUEST["id"] = $userId;
        $_REQUEST["email"] = $email;
        $_REQUEST["password"] = '';
        $_SESSION["uid"] = $userId;
        $_SESSION["clientTzOffset"] = 0;
        $_SESSION["access_level"] = 10;

        $obj = new \Pref_Users([]);
        ob_start();
        $obj->edit();
        $details = ob_get_clean();
        $dom = new \DOMDocument();
        $searchPage = mb_convert_encoding($details, 'HTML-ENTITIES', "UTF-8");
        $result = $dom->loadHTML("<body>$searchPage</body>");
        if (!$result) {
            throw new \RuntimeException(sprintf('Cant parse ttrss response.'));
        }

        $xpath = new \DOMXPath($dom);
        $element = $xpath->query('//input[@name="access_level"]');
        if ($element->length != 1) {
            throw new \RuntimeException(sprintf('Not found access_level element in ttrss response.'));
        }
        $accessLevel = $element->item(0)->attributes->getNamedItem('value')->nodeValue;

        $_REQUEST["access_level"] = $accessLevel;

        $obj->editSave();
    }

    public function exportOpml($login, $showSettings)
    {
        $authBasic = new \Auth_Base();
        $userId = $authBasic->find_user_by_login($login);

        if (false === $userId) {
            throw new UserNotExist(sprintf('User "%s" not exist in ttrss.', $login));
        }

        $_REQUEST["filename"] = 'TinyTinyRSS.opml';
        $_REQUEST["settings"] = !!$showSettings;
        $_SESSION["uid"] = $userId;

        ob_start();
        $opml = new \Opml([]);
        $opml->export();
        $content = ob_get_clean();

        return $content;
    }

    public function importOpml($login, $opmlPath)
    {
        $authBasic = new \Auth_Base();
        $userId = $authBasic->find_user_by_login($login);

        if (false === $userId) {
            throw new UserNotExist(sprintf('User "%s" not exist in ttrss.', $login));
        }

        if (!is_file($opmlPath)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not file', $opmlPath));
        }

        if (!is_readable($opmlPath)) {
            throw new \InvalidArgumentException(sprintf('File "%s" is not readable', $opmlPath));
        }

        $doc = new \DOMDocument();
        $result = $doc->loadXML(file_get_contents($opmlPath));
        if (false === $result) {
            throw new \RuntimeException(sprintf('Can\'t load OPML file "%s"', $opmlPath));
        }

        $_SESSION["uid"] = $userId;
        ob_start();
        $opml = new \Opml([]);
        $reflector = new \ReflectionObject($opml);
        $method = $reflector->getMethod('opml_import_category');
        $method->setAccessible(true);
        $method->invokeArgs($opml, [$doc, false, $userId, false]);
        $content = ob_get_clean();
        return str_replace('<br/>', PHP_EOL, $content);
    }

    public function sendTestEmail($to, $name = null)
    {
        if (null === $name) {
            $name = ucfirst(strstr($to, '@', true));
        }

        $subject = __("[tt-rss] Testing sending email");
        $message = 'Testing sending email.';
        $mail = new \ttrssMailer();
        $rc = $mail->quickMail($to, $name, $subject, $message, false);
        if (!$rc) {
            throw new \RuntimeException($mail->ErrorInfo);
        }
    }
}