<?PHP

/* main configuration */
require_once(DOC_ROOT . "/_config.inc.php");

if (!defined("_CONFIG_SITE_HOST_URL"))
    define("_CONFIG_SITE_HOST_URL", "");
if (!defined("_CONFIG_SITE_FULL_URL"))
    define("_CONFIG_SITE_FULL_URL", "");
if (!defined("_CONFIG_DB_HOST"))
    define("_CONFIG_DB_HOST", "");
if (!defined("_CONFIG_DB_NAME"))
    define("_CONFIG_DB_NAME", "");
if (!defined("_CONFIG_DB_USER"))
    define("_CONFIG_DB_USER", "");
if (!defined("_CONFIG_DB_PASS"))
    define("_CONFIG_DB_PASS", "");
if (!defined("_CONFIG_DB_DEBUG"))
    define("_CONFIG_DB_DEBUG", true);
if (!defined("_CONFIG_DEMO_MODE"))
    define("_CONFIG_DEMO_MODE", false);

// The Config class provides a single object to store your application's settings.
// Define your settings as public members. (We've already setup the standard options
// required for the Database and Auth classes.) Then, assign values to those settings
// inside the "location" functions. This allows you to have different configuration
// options depending on the server environment you're running on. Ex: local, staging,
// and production.

class Config
{

    // Singleton object. Leave $me alone.
    private static $me;
    // Add your server hostnames to the appropriate arrays. ($_SERVER['HTTP_HOST'])
    private $productionServers = array();
    private $stagingServers = array();
    private $localServers = array();
    // Standard Config Options...
    // ...For Auth Class
    public $authDomain;         // Domain to set for the cookie
    public $useHashedPasswords; // Store hashed passwords in database? (versus plain-text)
    // ...For Database Class
    public $dbHost;       // Database server
    public $dbName;       // Database name
    public $dbUsername;   // Database username
    public $dbPassword;   // Database password
    public $dbDieOnError; // What do do on a database error (see class.database.php for details)
    // Add your config options here...
    public $useDBSessions; // Set to true to store sessions in the database

    // Singleton constructor

    private function __construct()
    {
        /* setup config */
        $this->productionServers = array(_CONFIG_SITE_HOST_URL,
            "www." . _CONFIG_SITE_HOST_URL,
            str_replace("www.", "", _CONFIG_SITE_HOST_URL),
            _CONFIG_SITE_FILE_DOMAIN,
            "www." . _CONFIG_SITE_FILE_DOMAIN,
            str_replace("www.", "", _CONFIG_SITE_FILE_DOMAIN));

        $this->everywhere();

        $i_am_here = $this->whereAmI();

        if ('production' == $i_am_here)
            $this->production();
        elseif ('staging' == $i_am_here)
            $this->staging();
        elseif ('local' == $i_am_here)
            $this->local();
        elseif ('shell' == $i_am_here)
            $this->shell();
        else
            die('<h1>Config not found!</h1> <p>The config for this domain has not been found in <code>/_config.inc.php</code></p>
                     <p><code>$_SERVER[\'HTTP_HOST\']</code> reported current site as <code>' . $_SERVER['HTTP_HOST'] . '</code></p>
					 <p>Please update <code>' . DOC_ROOT . '/_config.inc.php</code> to the correct host:<br/><br/><code>define("_CONFIG_SITE_HOST_URL",         "' . $_SERVER['HTTP_HOST'] . '");</code></p>');
    }

    /**
     * Standard singleton
     * @return Config
     */
    public static function getConfig()
    {
        if (is_null(self::$me))
            self::$me = new Config();
        return self::$me;
    }

    // Allow access to config settings statically.
    // Ex: Config::get('some_value')
    public static function get($key)
    {
        return self::$me->$key;
    }

    // Add code to be run on all servers
    private function everywhere()
    {
        // Store sesions in the database?
        $this->useDBSessions = false;

        // Settings for the Auth class
        $this->authDomain = $_SERVER['HTTP_HOST'];
        $this->useHashedPasswords = true;
        $this->sessionName = 'shorturl';
    }

    // Add code/variables to be run only on production servers
    private function production()
    {
        @ini_set('display_errors', '0');
        define('WEB_ROOT', _CONFIG_SITE_PROTOCOL . "://" . _CONFIG_SITE_FULL_URL);

        $this->dbHost = _CONFIG_DB_HOST;
        $this->dbName = _CONFIG_DB_NAME;
        $this->dbUsername = _CONFIG_DB_USER;
        $this->dbPassword = _CONFIG_DB_PASS;
        $this->dbDieOnError = _CONFIG_DB_DEBUG;
    }

    // Add code/variables to be run only on staging servers
    private function staging()
    {
        @ini_set('display_errors', '1');
        @ini_set('error_reporting', E_ALL);

        define('WEB_ROOT', '');

        $this->dbHost = '';
        $this->dbName = '';
        $this->dbUsername = '';
        $this->dbPassword = '';
        $this->dbDieOnError = false;
    }

    // Add code/variables to be run only on local (testing) servers
    private function local()
    {
        @ini_set('display_errors', '1');
        define('WEB_ROOT', '');

        $this->dbHost = '';
        $this->dbName = '';
        $this->dbUsername = '';
        $this->dbPassword = '';
        $this->dbDieOnError = true;
    }

    // Add code/variables to be run only on when script is launched from the shell
    private function shell()
    {
        @ini_set('display_errors', '1');
        @ini_set('error_reporting', E_ALL);

        define('WEB_ROOT', '');

        $this->dbHost = '';
        $this->dbName = '';
        $this->dbUsername = '';
        $this->dbPassword = '';
        $this->dbDieOnError = true;
    }

    public function whereAmI()
    {
        if (in_array($_SERVER['HTTP_HOST'], $this->productionServers))
            return 'production';
        elseif (in_array($_SERVER['HTTP_HOST'], $this->stagingServers))
            return 'staging';
        elseif (in_array($_SERVER['HTTP_HOST'], $this->localServers))
            return 'local';
        elseif (isset($_ENV['SHELL']))
            return 'shell';
        else
            return false;
    }

}

?>
