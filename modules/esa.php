<?php

require_once(ESA_DIR.'/vendor/autoload.php');

use Smarty\Smarty;

class esa {
    private static $instance = NULL;
    private $db = NULL;
    private $mainTemplate = '';
    private $htmlOutput = TRUE;
    private $smarty = NULL;
    private $userId = NULL;
    private $userLogin = NULL;
    private $knownBrowsers = ['firefox', 'edge', 'chrome', 'safari'];
    private $knownOs = ['windows', 'macosx', 'linux', 'android'];
    private $knownTypes = ['human', 'robot'];
    private $baseUrl = "/esa";
    private $currentSiteId = '';
    private $currentSiteName = '';
    private $url = [];
    private $urlCount = 0;
    private $sessionData = array();

    public static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new esa();
        }
        return self::$instance;
    }

    function __construct() {
        $this->smarty = new Smarty;
        $this->smarty->setErrorReporting(1);
        $this->smarty->setTemplateDir(ESA_DIR.'/templates/');
        $this->smarty->setCompileDir(ESA_DIR.'/cache/templates_c/');
        $this->smarty->setCacheDir(ESA_DIR.'/cache/cache/');
        $this->smarty->assign('basePath', $this->baseUrl);
        $this->smarty->debugging = FALSE;
        $this->smarty->error_reporting = 0;
        $this->db = DB::getInstance();
        $this->db->setConnectionParameters(ESA_DB_HOST ,ESA_DB_NAME, ESA_DB_USER, ESA_DB_PASSWORD);
        $this->db->connect();

    }
    
    function isValidSession($sessionId) {
        $sessionParams = explode('||',$sessionId);
        if (count($sessionParams) != 3) {
            return FALSE;
        }

        if (md5(ESA_SALT.$sessionParams[0].'||'.$sessionParams[1]) == $sessionParams[2]) {
            $result = $this->db->execute("select * from ".ESA_DB_PREFIX."_users where id = ? and login = ?", [$sessionParams[0], $sessionParams[1]]);
		    if ($this->db->count($result) == 1) {
                return TRUE;
            }
        }

        return FALSE;
    }

    function isValidLogin() {
        $login = $_POST['login'];
        $passwd = $_POST['password'];
        $passwdHash = md5(ESA_SALT.$passwd);
        $result = $this->db->execute("select * from ".ESA_DB_PREFIX."_users where login = ? and pass_hash = ?", [$login, $passwdHash]);
		if ($this->db->count($result) > 0) {
            $row = $this->db->rowByNames($result);
            $this->userId = $row['id'];
            $this->userLogin = $row['login'];
			return TRUE;
		} else {
            return FALSE;
        }
    }

    function setMainMenu() {
        $mainMenuItems = array(
            array("url" => $this->baseUrl, "name" => "Overview", "active" => "is-active"),
            array("url" => $this->baseUrl.'/visitor', "name" => "Visitors", "active" => ""),
            array("url" => $this->baseUrl.'/browser', "name" => "Browsers", "active" => ""),
            array("url" => $this->baseUrl.'/location', "name" => "Locations", "active" => ""),
        );
        $this->smarty->assign('maniMenu', $mainMenuItems);
    }

    function setSites() {
        $sites = array();
        $result = $this->db->execute("select * from ".ESA_DB_PREFIX."_sites ORDER BY name", []);
        $l = $this->db->count($result);
        for ($i=0;$i<$l;$i++) {
            $row = $this->db->rowByNames($result);
            if ($this->currentSiteId == '') {
                $this->currentSiteId = $row['key'];
                $this->currentSiteName = $row['name'];
                
            }
            if ($row['key'] == $this->currentSiteId) {
                $active = 1;
                $this->currentSiteName = $row['name'];
            } else {
                $active = 0;
            }
            $site = array(
                "key" => $row['key'],
                "name" => $row['name'],
                "active" => $active
            );
            $sites[] = $site;
        }
        $this->smarty->assign('sites', $sites);
        $this->smarty->assign('siteName', $this->currentSiteName);
    }

    function routeParse() {
        $request_url = $_SERVER["REQUEST_URI"];
        if (strpos($request_url,'?')>0) {
            $request_url = substr($request_url,0,strpos($request_url,'?'));
        }
        $request_url = str_replace($this->baseUrl, '', $request_url);
        $param = explode('/',strtolower($request_url));
        $url = array();
        $url = array_values(array_filter($param, fn($value) => !is_null($value) && $value !== ''));
        $urlCount = count($url);
        if ($urlCount > 0) {
            switch ($url[0]) {
                case 'site': // change selected site
                    if($urlCount > 1 && $url[1] != '') {
                        $this->currentSiteId = $url[1];
                        $this->sessionData['siteKey'] = $url[1];
                    }
                    break;
                case 'visitor': // change selected site
                    break;
                case 'browser': // change selected site
                    break;
                case 'location': // change selected site
                    break;
                case 'session': // change selected site
                    break;
                default:
                    break;
            }        
        }

        $this->url = $url;
        $this->urlCount = count($url);
    }

    function sessionParse() {
        if (isset($_SESSION) && array_key_exists("esa", $_SESSION) && is_array($_SESSION['esa'])) {
            $this->sessionData = $_SESSION['esa'];
            foreach($_SESSION['esa'] as $key => $value) {
                if($key == 'siteKey') {                    
                    $this->currentSiteId = $_SESSION['esa']['siteKey'];
                }
            }

        }
    }

    public function proccessRequest() {
        $this->sessionParse();
        $this->routeParse();
        $this->setMainMenu();
        $this->setSites();

        if(isset($_COOKIE[ESA_COOKIENAME]) && $this->isValidSession($_COOKIE[ESA_COOKIENAME])) {
            $url = $this->proccessUrl($_SERVER["REQUEST_URI"]);
            //TODO: routing for drill down
            if (count($url) > 0 && $url[0] == 'api' && $_SERVER['HTTP_ACCEPT'] == 'application/json') {
                header('Content-Type: application/json');
                $urlCount = count($url);
                if ($urlCount > 1) {
                    switch ($url[1]) {
                        case 'visits': // change selected site
                            echo json_encode($this->getChartData());
                            break;
                        case 'session': // change selected site
                            if ($urlCount > 2) {
                                echo json_encode($this->getSessionData(intval($url[2])));
                            } else {
                                echo '{"state": "error", "descritption": "no session id"}';
                            }
                            break;
                        default:
                            echo '{"state": "error", "descritption": "unknown api"}';
                            break;                        
                    }
                } else {
                    echo '{"state": "error", "descritption": "missing api"}';
                }
                exit();
            } else {
                $this->smarty->assign('mainBody', "dashboard.html");
                $this->smarty->assign('analyticsData', $this->getAnalytcisData());
                $this->mainTemplate = "main.html";
            }
            
        } elseif(array_key_exists("login", $_POST) && array_key_exists("password", $_POST)) {
            if($this->isValidLogin()) {
                $sessionCookie = $this->userId.'||'.$this->userLogin;
                $sessionCookie = $sessionCookie.'||'.md5(ESA_SALT.$sessionCookie);
                $cookieOptions = array (
                    'expires' => 0,
                    'path' => ESA_ABSOLUTEPATH, 
                    'domain' => $_SERVER["HTTP_HOST"],
                    'secure' => TRUE,
                    'httponly' => TRUE,
                    'samesite' => 'Strict'
                );
                setcookie (ESA_COOKIENAME, $sessionCookie, $cookieOptions);
                header("Location: ".ESA_ABSOLUTEPATH);
                exit();

            } else {
                $this->smarty->assign('errorMessage', "Invalid login");
                $this->smarty->assign('mainBody', "login.html");
                $this->mainTemplate = "main.html";                    
            }

        } else {
            $this->smarty->assign('mainBody', "login.html");
            $this->mainTemplate = "main.html";
        }

        $_SESSION['esa'] = $this->sessionData;

        if ($this->mainTemplate != '') {
            $this->smarty->display($this->mainTemplate);
        } else {
            $this->smarty->display("maintanance.html");
        }
    }

    function proccessUrl($requestPath) {
        $requestPath = str_replace(ESA_ABSOLUTEPATH, '', $requestPath);
        $requestParams = explode('/', $requestPath);
        if($requestParams[0] == '') {
            array_shift($requestParams);
        }
        return $requestParams;
    }

    function getChartData() {
        $out = [];
        $out['status'] = 'ok';
        $result = $this->db->execute("SELECT date_format(timestamp, '%Y-%m-%d') as date, count(DISTINCT session_id) as counter 
        FROM ".ESA_DB_PREFIX."_events 
        WHERE timestamp > NOW() + INTERVAL -14 DAY 
        AND site_id = ?
        GROUP BY date 
        ORDER BY date", [$this->currentSiteId]);

        $l = $this->db->count($result);
        $out['data'] = [];
        for ($i=0;$i<$l;$i++) {
            $row = $this->db->rowByNames($result);
            $out['data'][$row['date']] = $row['counter'];
            
        }

        return $out;
        # SELECT date_format(timestamp, '%Y-%m-%d') as date, count(DISTINCT session_id) FROM `esa_events` group by date; 

    }

    function getSessionData($sessionId) {
        $out = [];
        $out['status'] = 'ok';
        $result = $this->db->execute("SELECT * FROM `esa_events` 
            left join esa_url ON esa_events.url_id = esa_url.id 
            left join esa_os ON esa_events.os_id = esa_os.id 
            left join esa_browser ON esa_events.browser_id = esa_browser.id 
            left join esa_location ON esa_events.location_id = esa_location.id 
            left join esa_refferer ON esa_events.refferer_id = esa_refferer.id 
            where session_id = ?", [$sessionId]);

        $l = $this->db->count($result);
        $out['data'] = [];
        for ($i=0;$i<$l;$i++) {
            $row = $this->db->rowByNames($result);
            $out['data'][$row['timestamp']] = array(
                'path' => $row['path'],
                'browser_name' => $row['name'],
                'browser_type' => $row['type'],
                'browser_category' => $row['category'],
                'country' => $row['country'],
                'city' => $row['city'],
            );
            
        }


        return $out;

    }

    function prepareName($name) {
        if ($name == null) {
            return $name;
        }
        $name = strtolower($name);
        $name = str_replace(' ', '', $name);
        if (in_array($name, $this->knownBrowsers) or in_array($name, $this->knownOs) or in_array($name, $this->knownTypes)) {
            return $name;
        } else {
            return null;
        }
        
    }

    function getAnalytcisData() {
        $out = [];
        // sessions 
        $result = $this->db->execute("SELECT count(DISTINCT session_id) as counter FROM ".ESA_DB_PREFIX."_events WHERE timestamp > NOW() + INTERVAL -14 DAY AND site_id = ?", [$this->currentSiteId]);
        $row = $this->db->rowByNames($result);
        $out['visits'] = $row['counter'];

        // visitors
        $result = $this->db->execute("SELECT count(DISTINCT visitor_id) as counter FROM ".ESA_DB_PREFIX."_events WHERE timestamp > NOW() + INTERVAL -14 DAY AND site_id = ?", [$this->currentSiteId]);
        $row = $this->db->rowByNames($result);
        $out['unique'] = $row['counter'];

        // views
        $result = $this->db->execute("SELECT count(*) as counter FROM ".ESA_DB_PREFIX."_events WHERE timestamp > NOW() + INTERVAL -14 DAY AND site_id = ?", [$this->currentSiteId]);
        $row = $this->db->rowByNames($result);
        $out['pages'] = $row['counter'];

        // bounce rate 
        $result = $this->db->execute("SELECT count(*) as c2 FROM (SELECT count(*) as c1 FROM ".ESA_DB_PREFIX."_events WHERE timestamp > NOW() + INTERVAL -14 DAY AND site_id = ? GROUP BY session_id HAVING c1 = 1) as t2", [$this->currentSiteId]);
        $row = $this->db->rowByNames($result);
        if ($out['visits'] > 0) {
            $out['bounce'] = round(($row['c2']/$out['visits'])*100,2).'%';
        } else {
            $out['bounce'] = 'N/A';
        }

        // last 20 visits 

        $result = $this->db->execute("SELECT g.views_count, g.session_id, e.visitor_id, g.m_ts as timestamp, l.ip, l.host, l.countryCode, r.url, r.site, r.type as rtype, o.name as oname, b.name as bname, b.type as btype, b.category, u.domain, u.path FROM (
            SELECT session_id, COUNT(*) as views_count, min(id) as s_id, MAX(timestamp) as m_ts FROM ".ESA_DB_PREFIX."_events WHERE site_id = ? GROUP BY session_id ORDER by m_ts DESC LIMIT 0,20) as g
        LEFT JOIN ".ESA_DB_PREFIX."_events as e ON e.id = g.s_id
        LEFT JOIN ".ESA_DB_PREFIX."_location as l ON l.id = e.location_id 
        LEFT JOIN ".ESA_DB_PREFIX."_refferer as r ON r.id = e.refferer_id 
        LEFT JOIN ".ESA_DB_PREFIX."_os as o ON o.id = e.os_id 
        LEFT JOIN ".ESA_DB_PREFIX."_browser as b ON b.id = e.browser_id 
        LEFT JOIN ".ESA_DB_PREFIX."_url as u ON u.id = e.url_id", [$this->currentSiteId]);
        $l = $this->db->count($result);
        $out['latestVisits'] = [];
        for ($i=0;$i<$l;$i++) {
            $row = $this->db->rowByNames($result);
            $out['latestVisits'][$row['session_id']] = array(
                'viewCount' => $row['views_count'],
                'sessionId' => $row['session_id'],
                'visitorId' => $row['visitor_id'],
                'visitorIP' => $row['ip'],
                'visitorHost' => $row['host'],
                'visitorCountry' => $row['countryCode'],
                'time' => $row['timestamp'],
                'reffererUrl' => $row['url'],
                'reffererSite' => $row['site'],
                'reffererType' => $row['rtype'],
                'osName' => $row['oname'],
                'osNameCss' => $this->prepareName($row['oname']),
                'browserName' => $row['bname'],
                'browserNameCss' => $this->prepareName($row['bname']),
                'browserType' => $row['btype'],
                'browserTypeCss' => $this->prepareName($row['btype']),
                'siteDomain' => $row['domain'],
                'sitePath' => $row['path']
            );
        }
        
        return $out;
    }
}

?>
