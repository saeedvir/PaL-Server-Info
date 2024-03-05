<?php
/**
 * Php And Laravel (PaL) Server Info
 * Test on PHP version >= 7.4.33
 * @category Server, Benchmark, Php, Laravel, Mysql
 * @package  Php,Laravel
 * @author   Saeed Agha Abdollahian <https://github.com/saeedvir>
 * @link     https://github.com/saeedvir/PaL-Server-Info
 * @version  1.5 (Last Update : 2024-03-05)
 * @since    2024-02-26
 * @license  MIT License https://opensource.org/licenses/MIT
 * @see      https://github.com/saeedvir/PaL-Server-Info
 * @copyright 2024
 */
if (version_compare(PHP_VERSION, '5.6') < 0) {
    echo 'This script requires PHP 5.6 or higher.';
    exit(1);
}
if ((new ServerCheck())->getWebServerEnvironment() === 'CLI') {
    echo 'This script Only Run in Browser. You download cli version from https://github.com/saeedvir/PaL-Server-Info';
    exit(1);
}
//Initialise Variables
$_VERSION = 'v 1.5'; //Current Version , Don't change this !!!

$MYSQL_CONFIG = [
    'host' => 'localhost',
    'username' => 'USER_NAME_HERE', //ex : root
    'password' => 'PASSWORD_HERE', //ex : password
    'db' => 'DB_NAME_HERE',         //ex : laravel_db
    'benchmark_insert' => 100,      //ex : 100
];

$_SCRIPT_FILE = basename(__FILE__);

$laravel_version_select = (!empty($_GET['laravel_version'])) ? $_GET['laravel_version'] : '10.x';

$check_list = (new ServerRequirements)->LaravelRequirementsList($laravel_version_select);
$check_list['Mysql Version'] = @(new ServerCheck($MYSQL_CONFIG))->GetMysqlVersion();
$check_list['Folder Permissions'] = (new ServerCheck)->CheckFolderPermissions();

if ($check_list['Disk Free Space'] >= 262144000) { //byte
    $check_list['Disk Free Space'] = (new Helper)->formatBytes($check_list['Disk Free Space']);
} else {
    $check_list['Disk Free Space'] = 'N/A';
}

$serverinfo_list = (new ServerRequirements)->ServerInfoList();

$optional_list = (new ServerRequirements)->OptionalList();

$info_cards = (new ServerRequirements)->ServerInfoCards();

if (isset($_GET['Recommendations'])) {
    $recommendations = (new Recommendations())->getRecommendations($MYSQL_CONFIG);
}
if (isset($_GET['Webserver-headers']) && !empty($_GET['url'])) {
    $webserver_responses = (new ServerCheck())->checkWebserverHeaders($_GET['url']);

    if ($webserver_responses['success'] === true) {
        $webserver_responses['headers'] = (new Recommendations())->getHeadersRecommendations($webserver_responses['headers']);
    }
}



//Classes and Functions
class ServerRequirements
{

    private $laravel_version;
    public function __construct($laravel_version = '10.x')
    {

        $this->laravel_version = $this->setLaravelVersion($laravel_version);
    }

    public function setLaravelVersion($laravel_version = '10.x')
    {
        $laravel_version = (string)$laravel_version;

        $laravel_versions = ['10.x', '9.x', '8.x', '7.x', '6.x', '5.8'];
        if (in_array($laravel_version, $laravel_versions) !== false) {
            $this->laravel_version = $laravel_version;
        } else {
            $this->laravel_version = '10.x';
        }

        return $this->laravel_version;
    }


    /**
     * ServerInfoList function to retrieve server information.
     */
    public function ServerInfoList()
    {
        $serverInfo = [
            'Web Server' => (new ServerCheck())->getWebServerEnvironment(),
            'Web Server Version' => (new ServerCheck())->getWebServerVersion(),
            'DOCUMENT ROOT' => (isset($_SERVER['DOCUMENT_ROOT'])) ? $_SERVER['DOCUMENT_ROOT'] : 'N/A',
            'PHP INI' => (function_exists('php_ini_loaded_file')) ? php_ini_loaded_file() : 'N/A',
            'upload max filesize' => min(ini_get('post_max_size'), ini_get('upload_max_filesize')),
            'upload tmp dir' => ini_get('upload_tmp_dir'),
            'memory limit' => ini_get('memory_limit'),
            'max execution time' => ini_get('max_execution_time'),
            'max input time' => ini_get('max_input_time'),
            'file uploads' => (ini_get('file_uploads') == true),
            'display errors' => (ini_get('display_errors') == true),
            'log errors' => (ini_get('log_errors') == true),
            'short open tag' => (ini_get('short_open_tag') == true),
            'allow url fopen' => (ini_get('allow_url_fopen') == true),
            'allow url include' => (ini_get('allow_url_include') == true),
            'disable functions' => ini_get('disable_functions'),
            'open basedir' => (ini_get('open_basedir') == true),
            'php safe mode' => (ini_get('safe_mode') == true),
        ];

        $dangerousFunctions = [
            'eval' => (function_exists('eval')),
            'exec' => (function_exists('exec')),
            'shell_exec' => (function_exists('shell_exec')),
            'system' => (function_exists('system')),
            'passthru' => (function_exists('passthru')),
            'popen' => (function_exists('popen')),
            'proc_open' => (function_exists('proc_open')),
            'pcntl_exec' => (function_exists('pcntl_exec')),
        ];

        $serverInfo['php dangerous functions'] = $dangerousFunctions;

        return $serverInfo;
    }
    /**
     * OptionalList function.
     *
     * @return array
     */
    public function OptionalList()
    {
        $opStatus = function_exists('opcache_get_status') ? opcache_get_status() : false;
        $isOpCacheEnabled = is_array($opStatus) && $opStatus['opcache_enabled'] ?? false;
        $isJitEnabled = is_array($opStatus) && $opStatus['jit']['enabled'] ?? false;

        return [
            'OPCache status' => $isOpCacheEnabled,
            'OPCache JIT' => $isJitEnabled,
            'PCRE JIT' => (new Helper)->checkBoolean(ini_get('pcre.jit')),
            'MemCache' => class_exists('Memcache'),
            'XDebug extension' => extension_loaded('xdebug'),
        ];
    }
    public function ServerInfoCards()
    {
        return   [
            'Server Name' => $_SERVER['SERVER_NAME'] ?? 'N/A',
            'Server IP' => $_SERVER['SERVER_ADDR'] ?? 'N/A',
            'Server Port' => $_SERVER['SERVER_PORT'] ?? 'N/A',
            'Server Protocol' => (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : 'N/A',
            'php version' => phpversion() . ' / ' . PHP_OS_FAMILY,
            'Composer Version' => (new ServerCheck)->getComposerVersion(),
            // 'SAPI' => (function_exists('php_sapi_name')) ? php_sapi_name() : 'N/A',
            // 'Server Software' => (isset($_SERVER['SERVER_SOFTWARE'])) ? $_SERVER['SERVER_SOFTWARE'] : 'N/A',
            // 'Memory Usage' => (new Helper)->formatBytes(memory_get_usage(false)) . ' / ' . (new Helper)->formatBytes(memory_get_peak_usage(true)),
        ];
    }
    public function LaravelRequirementsList($laravel_version = '10.x')
    {
        $laravel_requirements = [
            '10.x' => [
                'php version >= 8.1' => (version_compare(phpversion(), '8.1', '>=')),
                'Ctype PHP Extension' => extension_loaded('ctype'),
                'cURL PHP Extension' => extension_loaded('curl'),
                'dom PHP Extension' => extension_loaded('dom'),
                'Fileinfo PHP Extension' => extension_loaded('fileinfo'),
                'Filter PHP Extension' => extension_loaded('filter'),
                'Hash PHP Extension' => extension_loaded('hash'),
                'Mbstring PHP Extension' => extension_loaded('mbstring'),
                'OpenSSL PHP Extension' => extension_loaded('openssl'),
                'PCRE PHP Extension' => extension_loaded('pcre'),
                'PDO PHP Extension' => extension_loaded('pdo'),
                'Session PHP Extension' => extension_loaded('session'),
                'Tokenizer PHP Extension' => extension_loaded('tokenizer'),
                'XML PHP Extension' => extension_loaded('xml'),
            ],
            '9.x' => [
                'php version >= 8.0' => (version_compare(phpversion(), '8.0', '>=')),
                'Ctype PHP Extension' => extension_loaded('ctype'),
                'cURL PHP Extension' => extension_loaded('curl'),
                'dom PHP Extension' => extension_loaded('dom'),
                'Fileinfo PHP Extension' => extension_loaded('fileinfo'),
                'Filter PHP Extension' => extension_loaded('filter'),
                'Hash PHP Extension' => extension_loaded('hash'),
                'Mbstring PHP Extension' => extension_loaded('mbstring'),
                'OpenSSL PHP Extension' => extension_loaded('openssl'),
                'PCRE PHP Extension' => extension_loaded('pcre'),
                'PDO PHP Extension' => extension_loaded('pdo'),
                'Session PHP Extension' => extension_loaded('session'),
                'Tokenizer PHP Extension' => extension_loaded('tokenizer'),
                'XML PHP Extension' => extension_loaded('xml'),
            ],
            '8.x' => [
                'php version >= 7.3' => (version_compare(phpversion(), '7.3', '>=')),
                'BCmath PHP Extension' => extension_loaded('bcmath'),
                'Ctype PHP Extension' => extension_loaded('ctype'),
                'Fileinfo PHP Extension' => extension_loaded('fileinfo'),
                'JSON PHP Extension' => extension_loaded('json'),
                'Mbstring PHP Extension' => extension_loaded('mbstring'),
                'OpenSSL PHP Extension' => extension_loaded('openssl'),
                'PDO PHP Extension' => extension_loaded('pdo'),
                'Session PHP Extension' => extension_loaded('session'),
                'Tokenizer PHP Extension' => extension_loaded('tokenizer'),
                'XML PHP Extension' => extension_loaded('xml'),
            ],
            '7.x' => [
                'php version >= 7.2.5' => (version_compare(phpversion(), '7.2.5', '>=')),
                'BCmath PHP Extension' => extension_loaded('bcmath'),
                'Ctype PHP Extension' => extension_loaded('ctype'),
                'Fileinfo PHP Extension' => extension_loaded('fileinfo'),
                'JSON PHP Extension' => extension_loaded('json'),
                'Mbstring PHP Extension' => extension_loaded('mbstring'),
                'OpenSSL PHP Extension' => extension_loaded('openssl'),
                'PDO PHP Extension' => extension_loaded('pdo'),
                'Session PHP Extension' => extension_loaded('session'),
                'Tokenizer PHP Extension' => extension_loaded('tokenizer'),
                'XML PHP Extension' => extension_loaded('xml'),
            ],
            '6.x' => [
                'php version >= 7.2.5' => (version_compare(phpversion(), '7.2.5', '>=')),
                'BCmath PHP Extension' => extension_loaded('bcmath'),
                'Ctype PHP Extension' => extension_loaded('ctype'),
                'Fileinfo PHP Extension' => extension_loaded('fileinfo'),
                'JSON PHP Extension' => extension_loaded('json'),
                'Mbstring PHP Extension' => extension_loaded('mbstring'),
                'OpenSSL PHP Extension' => extension_loaded('openssl'),
                'PDO PHP Extension' => extension_loaded('pdo'),
                'Session PHP Extension' => extension_loaded('session'),
                'Tokenizer PHP Extension' => extension_loaded('tokenizer'),
                'XML PHP Extension' => extension_loaded('xml'),
            ],
            '5.8' => [
                'php version >= 7.1.3' => (version_compare(phpversion(), '7.1.3', '>=')),
                'BCmath PHP Extension' => extension_loaded('bcmath'),
                'Ctype PHP Extension' => extension_loaded('ctype'),
                'Fileinfo PHP Extension' => extension_loaded('fileinfo'),
                'JSON PHP Extension' => extension_loaded('json'),
                'Mbstring PHP Extension' => extension_loaded('mbstring'),
                'OpenSSL PHP Extension' => extension_loaded('openssl'),
                'PDO PHP Extension' => extension_loaded('pdo'),
                'Session PHP Extension' => extension_loaded('session'),
                'Tokenizer PHP Extension' => extension_loaded('tokenizer'),
                'XML PHP Extension' => extension_loaded('xml'),
            ],

        ];

        $laravel_version = $this->setLaravelVersion($laravel_version);

        if (!isset($laravel_requirements[$laravel_version])) {
            $laravel_requirements['10.x'];
        }

        $laravel_requirements[$laravel_version]['Mysqli or PDO'] = (class_exists('mysqli') === true || class_exists('PDO') === true) ? true : false;
        $laravel_requirements[$laravel_version]['Disk Free Space'] = (function_exists('disk_free_space')) ? disk_free_space("/") : false;


        return $laravel_requirements[$laravel_version];
    }
}

class ServerCheck
{
    private $mysqlConfig = [
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'db' => 'db_name',
        'benchmark_insert' => 100
    ];

    public function __construct($mysqlConfig = [])
    {
        $this->mysqlConfig = $mysqlConfig;
    }

    public function getWebServerEnvironment()
    {
        $sapi_type = php_sapi_name();

        $server_environments = [
            'cli' => 'CLI',
            'cli-server' => 'PHP CLI Server',
            'cgi-fcgi' => 'Nginx',
            'fpm-fcgi' => 'Nginx',
            'apache2handler' => 'Apache',
            'litespeed' => 'Lightspeed'
        ];

        return $server_environments[$sapi_type] ?? $sapi_type;
    }
    public function getWebServerVersion()
    {
        preg_match_all('/(\d+)/', (isset($_SERVER['SERVER_SOFTWARE'])) ? $_SERVER['SERVER_SOFTWARE'] : 'N/A', $matches);
        return isset($matches[0]) ? implode('.', $matches[0]) : 'N/A';
    }

    /**
     * Retrieves the version of Composer installed on the system.
     *
     * @return string
     */
    public function getComposerVersion()
    {
        $composerVersion = 'N/A';

        if (function_exists('proc_open')) {
            $descriptors = [
                0 => ['pipe', 'r'], // stdin
                1 => ['pipe', 'w'], // stdout
                2 => ['pipe', 'w'], // stderr
            ];
            $process = proc_open('composer --version', $descriptors, $pipes);
            if (is_resource($process)) {
                $composerVersion = stream_get_contents($pipes[1]);
                fclose($pipes[1]);
                proc_close($process);
                $composerVersion = str_replace('Composer version ', '', $composerVersion);
                $composerVersion = substr($composerVersion, 0, strpos($composerVersion, ' '));
            }
        }

        return (!empty($composerVersion)) ? $composerVersion : 'N/A';
    }

    /**
     * Get the MySQL server version.
     *
     * @throws Exception if there is an error with the MySQL connection
     * @return string the version of the MySQL server
     */
    public function GetMysqlVersion()
    {
        try {
            // Create a connection to the MySQL server
            $mysqli = new mysqli($this->mysqlConfig['host'], $this->mysqlConfig['username'], $this->mysqlConfig['password'], $this->mysqlConfig['db']);

            // Check for connection errors
            if ($mysqli->connect_error) {
                return 'Error - Connection failed';
                // die("Connection failed: " . $mysqli->connect_error);
            }

            // Prepare a statement for the query
            $stmt = $mysqli->prepare("SELECT VERSION() as version");

            // Execute the prepared statement
            $stmt->execute();

            // Bind the result of the query to a variable
            $stmt->bind_result($version);

            // Fetch the result
            $stmt->fetch();

            // Close the statement
            $stmt->close();

            // Close the connection
            $mysqli->close();

            return $version;
        } catch (Exception $e) {
            return 'Error - Check Mysql Config on line 26';
        }
    }

    /**
     * Check the folder permissions and display the numeric representation for User, Group, and Others.
     *
     * @return string
     */
    public function CheckFolderPermissions()
    {
        $folder = __DIR__; // Get the current directory

        // Get the numeric representation of the permissions
        $permissions = fileperms($folder);

        // Display the permissions
        return "User - " . (($permissions & 0x0100) ? "1" : "0") .
            ", Group - " . (($permissions & 0x0080) ? "1" : "0") .
            ", Others - " . (($permissions & 0x0040) ? "1" : "0");
    }


    public function initCurlRequest($reqType, $reqURL, $reqBody = '', $headers = [])
    {
        if (!in_array($reqType, ['GET', 'POST', 'PUT', 'DELETE'])) {
            throw new Exception('Curl first parameter must be "GET", "POST", "PUT" or "DELETE"');
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $reqURL);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $reqType);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $reqBody);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);


        if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        }

        $body = curl_exec($ch);

        // extract header
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($body, 0, $headerSize);
        $header = $this->getHeaders($header);

        // extract body
        $body = substr($body, $headerSize);

        $curl_info = curl_getinfo($ch);

        curl_close($ch);

        return [$header, $body, $curl_info];
    }

    private function getHeaders($respHeaders)
    {
        $headers = array();

        $headerText = substr($respHeaders, 0, strpos($respHeaders, "\r\n\r\n"));

        foreach (explode("\r\n", $headerText) as $i => $line) {
            if ($i === 0) {
                $headers['http_code'] = $line;
            } else {
                list($key, $value) = explode(': ', $line);

                $key = strtolower($key);
                $headers[$key] = $value;
            }
        }

        return $headers;
    }


    public function checkWebserverHeaders($url = null)
    {
        if (empty($url)) {
            return [
                'success' => false,
                'message' => 'url is empty',
                'headers' => null
            ];
        }
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return [
                'success' => false,
                'message' => 'url is not valid',
                'headers' => null
            ];
        }
        if (!function_exists('curl_init')) {
            return [
                'success' => false,
                'message' => 'curl not installed',
                'headers' => null
            ];
        }

        list($header, $body, $curl_info) = (new ServerCheck())->initCurlRequest('GET', $url, '', ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36']);

        if (empty($header)) {
            return [
                'success' => false,
                'message' => 'no response , header is empty',
                'headers' => null
            ];
        }
        return [
            'success' => true,
            'message' => 'success',
            'headers' => [
                'http-code' => (isset($curl_info['http_code'])) ? $curl_info['http_code'] : null,
                'header-size' => (isset($curl_info['header_size'])) ? (new Helper)->formatBytes($curl_info['header_size']) : null,
                'request-size' => (isset($curl_info['request_size'])) ? (new Helper)->formatBytes($curl_info['request_size']) : null,
                'total-time' => (isset($curl_info['total_time'])) ? $curl_info['total_time'] : null,
                'namelookup-time' => (isset($curl_info['namelookup_time'])) ? $curl_info['namelookup_time'] : null,
                'redirect-url' => (isset($curl_info['redirect_url'])) ? $curl_info['redirect_url'] : null,
                'primary-ip' => (isset($curl_info['primary_ip'])) ? $curl_info['primary_ip'] : null,
                'primary-port' => (isset($curl_info['primary_port'])) ? $curl_info['primary_port'] : null,
                'scheme' => (isset($curl_info['scheme'])) ? $curl_info['scheme'] : null,

                'connection' => (isset($header['connection'])) ? $header['connection'] : null,
                'content-type' => (isset($header['content-type'])) ? $header['content-type'] : null,
                'content-length' => (isset($header['content-length'])) ? $header['content-length'] : null,
                'x-frame-options' => (isset($header['x-frame-options'])) ? $header['x-frame-options'] : null,
                'x-xss-protection' => (isset($header['x-xss-protection'])) ? $header['x-xss-protection'] : null,
                'permissions-policy' => (isset($header['permissions-policy'])) ? $header['permissions-policy'] : null,
                'x-content-type-options' => (isset($header['x-content-type-options'])) ? $header['x-content-type-options'] : null,
                'x-ua-compatible' => (isset($header['x-ua-compatible'])) ? $header['x-ua-compatible'] : null,
                'accept-ranges' => (isset($header['accept-ranges'])) ? $header['accept-ranges'] : null,
                'set-cookie' => (isset($header['set-cookie'])) ? $header['set-cookie'] : null,
                'via' => (isset($header['via'])) ? $header['via'] : null,
                'location' => (isset($header['location'])) ? $header['location'] : null,
                'retry-after' => (isset($header['retry-after'])) ? $header['retry-after'] : null,
                'content-disposition' => (isset($header['content-disposition'])) ? $header['content-disposition'] : null,
                'content-language' => (isset($header['content-language'])) ? $header['content-language'] : null,

                'x-dns-prefetch-control' => (isset($header['x-dns-prefetch-control'])) ? $header['x-dns-prefetch-control'] : null,
                'x-cache-control' => (isset($header['x-cache-control'])) ? $header['x-cache-control'] : null,
                'cache-control' => (isset($header['cache-control'])) ? $header['cache-control'] : null,
                'content-encoding' => (isset($header['content-encoding'])) ? $header['content-encoding'] : null,
                'expires' => (isset($header['expires'])) ? $header['expires'] : null,
                'pragma' => (isset($header['pragma'])) ? $header['pragma'] : null,
                'vary' => (isset($header['vary'])) ? $header['vary'] : null,
                'etag' => (isset($header['etag'])) ? $header['etag'] : null,
                'last-modified' => (isset($header['last-modified'])) ? $header['last-modified'] : null,
                'transfer-encoding' => (isset($header['transfer-encoding'])) ? $header['transfer-encoding'] : null,
                'x-powered-by' => (isset($header['x-powered-by'])) ? $header['x-powered-by'] : null,
                'server' => (isset($header['server'])) ? $header['server'] : null,
                'x-turbo-charged-by' => (isset($header['x-turbo-charged-by'])) ? $header['x-turbo-charged-by'] : null,

            ],
            'cors' => [
                'access-control-allow-origin' => (isset($header['access-control-allow-origin'])) ? $header['access-control-allow-origin'] : null,
                'access-control-allow-headers' => (isset($header['access-control-allow-headers'])) ? $header['access-control-allow-headers'] : null,
                'access-control-allow-methods' => (isset($header['access-control-allow-methods'])) ? $header['access-control-allow-methods'] : null,
                'access-control-allow-credentials' => (isset($header['access-control-allow-credentials'])) ? $header['access-control-allow-credentials'] : null,
                'access-control-expose-headers' => (isset($header['access-control-expose-headers'])) ? $header['access-control-expose-headers'] : null,
                'access-control-max-age' => (isset($header['access-control-max-age'])) ? $header['access-control-max-age'] : null,
            ]

        ];
    }
}

class Helper
{
    /**
     * Format a given number of bytes into a human-readable format.
     *
     * This method takes a number of bytes and formats it into a human-readable format with the appropriate unit (e.g., KB, MB, GB).
     *
     * @param int|float $bytes The number of bytes to format.
     * @param int $precision (Optional) The number of decimal places to round to. Default is 2.
     * @return string The formatted bytes with the appropriate unit.
     */
    public function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']; // Array of unit abbreviations
        $index = 0; // Initialize index for units array
        while ($bytes >= 1024 && $index < count($units) - 1) { // Loop until bytes is less than 1024 or all units have been used
            $bytes /= 1024; // Divide bytes by 1024
            $index++; // Increment index for units array
        }
        return round($bytes, $precision) . ' ' . $units[$index]; // Return formatted bytes with the appropriate unit
    }

    /**
     * Check if the given value is a boolean representation.
     *
     * @param string $value The value to be checked
     * @return bool
     */

    public function checkBoolean($value)
    {
        $value = strtolower((string)$value);
        if (in_array($value, ['true', '1', 'yes', 'on', 'ok', 'passed'])) {
            return true;
        } elseif (in_array($value, ['false', '0', 'no', 'off', 'nok', 'failed']) || empty($value)) {
            return false;
        }
        return null;
    }

    public function booleanToString($value, $bool_str = ['true' => 'On', 'false' => 'Off'])
    {
        $value = strtolower($value);
        return in_array($value, ['true', '1', 'yes', 'on', 'ok', 'passed']) !== false ? $bool_str['true'] : $bool_str['false'];
    }

    public function getNumbersFromString($str)
    {
        return filter_var($str, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Converts a human readable file size value to a number of bytes that it
     * represents. Supports the following modifiers: K, M, G and T.
     * Invalid input is returned unchanged.
     *
     * Example:
     * <code>
     * $config->human2byte(10);          // 10
     * $config->human2byte('10b');       // 10
     * $config->human2byte('10k');       // 10240
     * $config->human2byte('10K');       // 10240
     * $config->human2byte('10kb');      // 10240
     * $config->human2byte('10Kb');      // 10240
     * // and even
     * $config->human2byte('   10 KB '); // 10240
     * </code>
     *
     * @param number|string $value
     * @return number
     */
    public function stringNumber2Byte($str)
    {
        return preg_replace_callback('/^\s*(\d+)\s*(?:([kmgt]?)b?)?\s*$/i', function ($m) {
            switch (strtolower($m[2])) {
                case 't':
                    $m[1] *= 1024;
                case 'g':
                    $m[1] *= 1024;
                case 'm':
                    $m[1] *= 1024;
                case 'k':
                    $m[1] *= 1024;
            }
            return $m[1];
        }, $str);
    }

    public function httpGet($url, $download_as_file = null)
    {
        if (function_exists('curl_init')) {
            $cURLConnection = curl_init();

            curl_setopt($cURLConnection, CURLOPT_URL, $url);
            curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($cURLConnection);
            curl_close($cURLConnection);
        } else {
            $response = file_get_contents($url);
        }

        if ($download_as_file) {
            file_put_contents(__DIR__.DIRECTORY_SEPARATOR.$download_as_file, $response);
        }

        return $response;
    }

    public function checkForUpdate($version)
    {
        $url = 'https://raw.githubusercontent.com/saeedvir/PaL-Server-Info/main/version';

        $response = trim($this->httpGet($url));

        if ($version !== $response) {
            return true;
        } else {
            return false;
        }
    }

    public function downloadUpdate()
    {
        $url = 'https://raw.githubusercontent.com/saeedvir/PaL-Server-Info/main/Pal-Server-Info.php';

        $download_content = $this->httpGet($url);

        file_put_contents(__DIR__.DIRECTORY_SEPARATOR.basename(__FILE__), $download_content);

        return true;
    }
}

class Benchmark
{
    private $mysqlConfig = [
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'db' => 'db_name',
        'benchmark_insert' => 100
    ];

    public function __construct($mysqlConfig = [])
    {
        $this->mysqlConfig = $mysqlConfig;
    }
    /**
     * A benchmark function to measure the performance of various PHP functions.
     *
     * @return array Benchmark results including types and average time
     */
    public function PhpBenchmark()
    {
        $benchmark_functions = [
            'math' => fn ($multiplier = 1, $count = 200000) => $this->mathBenchmark($multiplier, $count),
            'loops' => fn ($multiplier = 1, $count = 20000000) => $this->loopBenchmark($multiplier, $count),
            'array' => fn ($multiplier = 1, $count = 50000) => $this->arrayBenchmark($multiplier, $count),
            'hash' => fn ($multiplier = 1, $count = 10000) => $this->hashBenchmark($multiplier, $count),
            'json' => fn ($multiplier = 1, $count = 100000) => $this->jsonBenchmark($multiplier, $count),
            'mt_rand' => fn ($multiplier = 1, $count = 1000000) => $this->mtRandBenchmark($multiplier, $count),
            'openssl_random_pseudo_bytes' => fn ($multiplier = 1, $count = 1000000) => $this->opensslRandomPseudoBytesBenchmark($multiplier, $count),
            'file_read' => fn ($multiplier = 1, $count = 1000) => $this->fileReadBenchmark($multiplier, $count),
            'file_write' => fn ($multiplier = 1, $count = 1000) => $this->fileWriteBenchmark($multiplier, $count),
            'file_zip' => fn ($multiplier = 1, $count = 1000) => $this->fileZipBenchmark($multiplier, $count),
            'file_unzip' => fn ($multiplier = 1, $count = 1000) => $this->fileUnzipBenchmark($multiplier, $count),
        ];

        $benchmark_results = [
            'types' => [],
            'avg' => 0,
        ];

        foreach ($benchmark_functions as $key => $benchmark_function) {
            $start_time = microtime(true);
            $benchmark_function();
            $benchmark_results['types'][$key] = microtime(true) - $start_time;
        }

        $benchmark_results['avg'] = array_sum($benchmark_results['types']) / count($benchmark_results['types']);

        return $benchmark_results;
    }

    private function mathBenchmark($multiplier, $count)
    {
        // Math benchmark logic
        $x = 0;
        $count = $count * $multiplier;
        for ($i = 0; $i < $count; $i++) {
            $x += $i + $i;
            $x += $i * $i;
            $x += $i ** $i;
            $x += $i / (($i + 1) * 2);
            $x += $i % (($i + 1) * 2);
            abs($i);
            acos($i);
            acosh($i);
            asin($i);
            asinh($i);
            atan2($i, $i);
            atan($i);
            atanh($i);
            ceil($i);
            cos($i);
            cosh($i);
            decbin($i);
            dechex($i);
            decoct($i);
            deg2rad($i);
            exp($i);
            expm1($i);
            floor($i);
            fmod($i, $i);
            if (function_exists('hypot')) {
                hypot($i, $i);
            }
            is_infinite($i);
            is_finite($i);
            is_nan($i);
            log10($i);
            log1p($i);
            log($i);
            pi();
            pow($i, $i);
            rad2deg($i);
            sin($i);
            sinh($i);
            sqrt($i);
            tan($i);
            tanh($i);
        }

        return $i;
    }

    private function loopBenchmark($multiplier, $count)
    {
        // Loop benchmark logic
        $count = $count * $multiplier;
        for ($i = 0; $i < $count; ++$i) {
            $i;
        }
        $i = 0;
        while ($i < $count) {
            ++$i;
        }
        return $i;
    }

    private function arrayBenchmark($multiplier, $count)
    {
        // Array benchmark logic
        $a = range(0, 100);
        $count = $count * $multiplier;
        for ($i = 0; $i < $count; $i++) {
            array_keys($a);
            array_values($a);
            array_flip($a);
            array_map(function ($e) {
            }, $a);
            array_walk($a, function ($e, $i) {
            });
            array_reverse($a);
            array_sum($a);
            array_merge($a, [101, 102, 103]);
            array_replace($a, [1, 2, 3]);
            array_chunk($a, 2);
        }
        return $a;
    }

    private function hashBenchmark($multiplier, $count)
    {
        // Hash benchmark logic
        $count = $count * $multiplier;
        for ($i = 0; $i < $count; $i++) {
            md5($i);
            sha1($i);
            hash('sha256', $i);
            hash('sha512', $i);
            hash('ripemd160', $i);
            hash('crc32', $i);
            hash('crc32b', $i);
            hash('adler32', $i);
            hash('fnv132', $i);
            hash('fnv164', $i);
            hash('joaat', $i);
            hash('haval128,3', $i);
            hash('haval160,3', $i);
            hash('haval192,3', $i);
            hash('haval224,3', $i);
            hash('haval256,3', $i);
            hash('haval128,4', $i);
            hash('haval160,4', $i);
            hash('haval192,4', $i);
            hash('haval224,4', $i);
            hash('haval256,4', $i);
            hash('haval128,5', $i);
            hash('haval160,5', $i);
            hash('haval192,5', $i);
            hash('haval224,5', $i);
            hash('haval256,5', $i);
        }
        return $i;
    }

    private function jsonBenchmark($multiplier, $count)
    {
        // JSON benchmark logic
        $data = [
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'qux',
            'qux' => 'quux',
            'quux' => 'corge',
            'corge' => 'grault',
            'grault' => 'garply',
            'garply' => 'waldo',
            'waldo' => 'fred',
            'fred' => 'plugh',
            'plugh' => 'xyzzy',
            'xyzzy' => 'thud',
            'thud' => 'end',
        ];
        $count = $count * $multiplier;
        for ($i = 0; $i < $count; $i++) {
            json_encode($data);
            json_decode(json_encode($data));
        }
        return $data;
    }

    private function mtRandBenchmark($multiplier, $count)
    {
        // mt_rand benchmark logic
        $count = $count * $multiplier;
        for ($i = 0; $i < $count; $i++) {
            mt_rand(0, $i);
        }
        return $i;
    }

    private function opensslRandomPseudoBytesBenchmark($multiplier, $count)
    {
        // openssl_random_pseudo_bytes benchmark logic
        if (!function_exists('openssl_random_pseudo_bytes')) {
            return INF;
        }

        $count = $count * $multiplier;
        for ($i = 0; $i < $count; $i++) {
            openssl_random_pseudo_bytes(32);
        }
        return $i;
    }

    private function fileReadBenchmark($multiplier, $count)
    {
        // File read benchmark logic
        file_put_contents('test.txt', "test");
        $count = $count * $multiplier;
        for ($i = 0; $i < $count; $i++) {
            file_get_contents('test.txt');
        }
        unlink('test.txt');
        return $i;
    }

    private function fileWriteBenchmark($multiplier, $count)
    {
        // File write benchmark logic
        $count = $count * $multiplier;
        for ($i = 0; $i < $count; $i++) {
            file_put_contents('test.txt', "test $i");
        }
        unlink('test.txt');
        return $i;
    }

    private function fileZipBenchmark($multiplier, $count)
    {
        // File zip benchmark logic
        file_put_contents('test.txt', "test");
        $count = $count * $multiplier;
        for ($i = 0; $i < $count; $i++) {
            $zip = new ZipArchive();
            $zip->open('test.zip', ZipArchive::CREATE);
            $zip->addFile('test.txt');
            $zip->close();
        }
        unlink('test.txt');
        unlink('test.zip');
        return $i;
    }

    private function fileUnzipBenchmark($multiplier, $count)
    {
        // File unzip benchmark logic
        file_put_contents('test.txt', "test");
        $zip = new ZipArchive();
        $zip->open('test.zip', ZipArchive::CREATE);
        $zip->addFile('test.txt');
        $zip->close();
        $count = $count * $multiplier;
        for ($i = 0; $i < $count; $i++) {
            $zip = new ZipArchive();
            $zip->open('test.zip');
            $zip->extractTo('test');
            $zip->close();
        }
        unlink('test.txt');
        unlink('test.zip');
        unlink('test/test.txt');
        rmdir('test');
        return $i;
    }

    /**
     * A function to benchmark MySQL operations.
     *
     * @return array
     */
    public function MysqlBenchmark()
    {
        /**
         * @return array
         */

        $benchmark_results = [
            'types' => [],
            'avg' => 0,
        ];

        try {
            // Create a connection to the MySQL server
            $mysqli = new mysqli($this->mysqlConfig['host'], $this->mysqlConfig['username'], $this->mysqlConfig['password'], $this->mysqlConfig['db']);

            // Check for connection errors
            if ($mysqli->connect_error) {
                return false;
                // die("Connection failed: " . $mysqli->connect_error);
            }

            $sql = "DROP TABLE IF EXISTS `benchmark__tbl`;";
            $mysqli->query($sql);

            $sql = "CREATE TABLE `benchmark__tbl` (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `col1` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb3_bin',
        `col2` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb3_bin',
        `col3` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb3_bin',
        `col4` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb3_bin',
        `col5` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb3_bin',
        `col6` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb3_bin',
        `col7` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb3_bin',
        `col8` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb3_bin',
        `col9` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb3_bin',
        `col10` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb3_bin',
        `created_at` TIMESTAMP NULL DEFAULT (now()),
        `updated_at` TIMESTAMP NULL DEFAULT (now()),
        PRIMARY KEY (`id`) USING BTREE,
        INDEX `col1` (`col1`) USING BTREE
      )
      COLLATE='utf8mb3_bin'
      ENGINE=InnoDB
      ;";

            // Execute a query to retrieve the MySQL version
            $mysqli->query($sql);

            $benchmark_results['types']['insert'] = microtime(true);
            for ($i = 1; $i <= $this->mysqlConfig['benchmark_insert']; $i++) {
                $sql = "INSERT INTO `benchmark__tbl` (col1, col2, col3,col4, col5, col6,col7, col8, col9,col10) VALUES ('John', 'Doe', 'john@example.com', '1234567890', '1234567890', '1234567890', '1234567890', '1234567890', '1234567890', '1234567890');";
                $result = $mysqli->query($sql);
            }
            $benchmark_results['types']['insert'] = microtime(true) - $benchmark_results['types']['insert'];

            $benchmark_results['types']['select'] = microtime(true);
            $sql = "SELECT * FROM `benchmark__tbl`;";
            $result = $mysqli->query($sql);
            $benchmark_results['types']['select'] = microtime(true) - $benchmark_results['types']['select'];


            $benchmark_results['types']['update'] = microtime(true);
            $sql = "UPDATE `benchmark__tbl` SET `col1`='1234567890', `col2`='1234567890', `col3`='1234567890', `col4`='1234567890', `col5`='1234567890', `col6`='1234567890', `col7`='1234567890', `col8`='1234567890', `col9`='1234567890', `col10`='1234567890';";
            $result = $mysqli->query($sql);
            $benchmark_results['types']['update'] = microtime(true) - $benchmark_results['types']['update'];

            $benchmark_results['types']['delete'] = microtime(true);
            $sql = "DELETE FROM `benchmark__tbl`;";
            $result = $mysqli->query($sql);
            $benchmark_results['types']['delete'] = microtime(true) - $benchmark_results['types']['delete'];

            $sql = "DROP TABLE IF EXISTS `benchmark__tbl`;";
            $mysqli->query($sql);

            // Close the connection
            $mysqli->close();

            $benchmark_results['avg'] = array_sum($benchmark_results['types']) / count($benchmark_results['types']);


            unset($sql, $result, $mysqli);

            return $benchmark_results;
        } catch (Exception $e) {
            //throw $th;
        }
    }
}

class Recommendations
{

    public function __construct()
    {
    }

    private function getCheckList()
    {
        $configPath = 'pal-config.json';
        $configUrl = 'https://raw.githubusercontent.com/saeedvir/PaL-Server-Info/main/pal-config.json';

        //remove old file
        if (file_exists($configPath)) {
            $configDate = date_diff(date_create(date('Y-m-d H:i:s', time())), date_create(date('Y-m-d H:i:s', filectime($configPath))));

            if ($configDate->d > 0) {
                @unlink($configPath);
                usleep(200);
            }
        }

        $response = file_exists($configPath)
            ? file_get_contents($configPath)
            : (new Helper)->httpGet($configUrl, 'pal-config.json');

        if (empty($response) || $response === '404: Not Found') {
            die('pal-config.json Error');
        }
        return json_decode($response, true);
    }

    private function operatorValue($val1, $oprator, $val2 = null)
    {
        switch ($oprator) {
            case '>':
                return $val1 > $val2;
                break;
            case '>=':
                return $val1 >= $val2;
                break;
            case '==':
                return $val1 == $val2;
                break;
            case '===':
                return $val1 === $val2;
                break;
            case '<':
                return $val1 < $val2;
                break;
            case '<=':
                return $val1 <= $val2;
                break;
            case '!=':
                return $val1 != $val2;
                break;
            case '!==':
                return $val1 !== $val2;
                break;
            case 'is_empty':
                return empty($val1);
                break;
            case 'not_empty':
                return !empty($val1);
                break;
            case 'is_true':
                return $val1 === true;
                break;
            case 'is_false':
                return $val1 === false;
                break;
            case 'is_null':
                return $val1 === null;
                break;
            case 'function_exists':
                return function_exists($val1);
                break;
            case 'class_exists':
                return class_exists($val1);
                break;
            case 'function_not_exists':
                return !function_exists($val1);
                break;
            case 'class_not_exists':
                return !class_exists($val1);
                break;
            default:
                return false;
                break;
        }
    }


    public function getRecommendations($MYSQL_CONFIG)
    {
        $checklist  = $this->getCheckList();
        $return_recommendations = [];

        //ini
        foreach ($checklist['ini_settings'] as $k => $val) {
            $ini_data = ini_get($k);
            if ($val['type'] === 'string_number') {
                if (!$this->operatorValue((new Helper)->stringNumber2Byte($ini_data), $val['operation'], (new Helper)->stringNumber2Byte($val['value']))) {
                    $return_recommendations[] = [
                        'title' => $val['title'],
                        'your_value' => $ini_data,
                        'value' => $val['value'],
                        'dev_value' => $val['dev_value'],
                        'operation' => $val['operation'],
                        'type' => 'ini_settings',
                        'tag' => $val['tag'],
                        'how_to_fix' => $val['how_to_fix'],
                    ];
                }
            } elseif ($val['type'] === 'string') {

                if ($k === 'error_reporting') {
                    $ini_data = (new ErrorReporting)->getErrorLevel();
                }
                if (!$this->operatorValue($ini_data, $val['operation'])) {
                    $return_recommendations[] = [
                        'title' => $val['title'],
                        'your_value' => $ini_data,
                        'value' => $val['value'],
                        'dev_value' => $val['dev_value'],
                        'operation' => $val['operation'],
                        'type' => 'ini_settings',
                        'tag' => $val['tag'],
                        'how_to_fix' => $val['how_to_fix'],
                    ];
                }
            } elseif ($val['type'] === 'boolean') {
                if ((new Helper)->booleanToString($ini_data) !== (new Helper)->booleanToString($val['value'])) {
                    $return_recommendations[] = [
                        'title' => $val['title'],
                        'your_value' => (new Helper)->booleanToString($ini_data),
                        'value' => $val['value'],
                        'dev_value' => $val['dev_value'],
                        'operation' => $val['operation'],
                        'type' => 'ini_settings',
                        'tag' => $val['tag'],
                        'how_to_fix' => $val['how_to_fix'],
                    ];
                }
            } else {
                continue;
            }
        }
        //functions_classes
        foreach ($checklist['functions_classes'] as $k => $val) {
            $your_value = $this->operatorValue($k, $val['operation']);
            if ($your_value !== $val['value']) {

                $return_recommendations[] = [
                    'title' => $val['type'] . ' ' . $k,
                    'your_value' => ($your_value) ? 'callable' : 'no callable',
                    'value' => ($val['value']) ? 'callable' : 'no callable',
                    'dev_value' => ($val['dev_value']) ? 'callable' : 'no callable',
                    'operation' => $val['operation'],
                    'type' => 'functions_classes',
                    'tag' => $val['tag'],
                    'how_to_fix' => $val['how_to_fix'],

                ];
            }
        }
        //extensions
        foreach ($checklist['extensions'] as $k => $val) {
            $your_value = extension_loaded($k);
            if ($your_value !== $val['value']) {
                $return_recommendations[] = [
                    'title' => $val['title'],
                    'your_value' => ($your_value) ? 'extension loaded' : 'extension unloaded',
                    'value' => ($val['value']) ? 'extension loaded' : 'extension unloaded',
                    'dev_value' => ($val['dev_value']) ? 'extension loaded' : 'extension unloaded',
                    'operation' => null,
                    'type' => 'extensions',
                    'tag' => $val['tag'],
                    'how_to_fix' => $val['how_to_fix'],
                ];
            }
        }
        //softawres
        //php
        if (!version_compare(phpversion(), $checklist['softawres']['php'], '>=')) {
            $return_recommendations[] = [
                'title' => 'php',
                'your_value' => phpversion(),
                'value' => $checklist['softawres']['php'],
                'dev_value' => null,
                'operation' => null,
                'type' => 'softwares',
                'info' => 'php version must be larger than ' . $checklist['softawres']['php'],
                'tag' => 'software',
                'how_to_fix' => 'install new version',
            ];
        }
        //mysql
        $mysql_version = @(new ServerCheck($MYSQL_CONFIG))->GetMysqlVersion();
        if ($mysql_version && !version_compare($mysql_version, $checklist['softawres']['mysql'], '>=')) {
            $return_recommendations[] = [
                'title' => 'mysql',
                'your_value' => $mysql_version,
                'value' => $checklist['softawres']['mysql'],
                'dev_value' => null,
                'operation' => null,
                'type' => 'softwares',
                'tag' => 'software',
                'info' => 'mysql version must be larger than ' . $checklist['softawres']['mysql'],
                'how_to_fix' => 'install new version',
            ];
        }
        unset($mysql_version);

        //composer
        $composer_version = (new ServerCheck())->getComposerVersion();
        if ($composer_version && !version_compare($composer_version, $checklist['softawres']['composer'], '>=')) {
            $return_recommendations[] = [
                'title' => 'composer',
                'your_value' => $composer_version,
                'value' => $checklist['softawres']['composer'],
                'dev_value' => null,
                'operation' => null,
                'type' => 'softwares',
                'tag' => 'software',
                'info' => 'composer version must be larger than ' . $checklist['softawres']['composer'],
                'how_to_fix' => 'install new version',
            ];
        }
        unset($composer_version);

        //webserver
        $webserver = (new ServerCheck())->getWebServerEnvironment();
        $webserver_version = (new ServerCheck())->getWebServerVersion();
        if (isset($checklist['softawres']['webservers'][$webserver])) {
            if (!version_compare((new ServerCheck())->getWebServerVersion(), $checklist['softawres']['webservers'][$webserver], '>=')) {
                $return_recommendations[] = [
                    'title' => $webserver,
                    'your_value' => $webserver_version,
                    'value' => $checklist['softawres']['webservers'][$webserver],
                    'dev_value' => null,
                    'operation' => null,
                    'type' => 'softwares',
                    'tag' => 'software',
                    'info' => $webserver . ' version must be larger than ' . $checklist['softawres']['webservers'][$webserver],
                    'how_to_fix' => 'install new version',
                ];
            }
        }
        unset($webserver, $webserver_version);

        return $return_recommendations;
    }

    public function getHeadersRecommendations($headers)
    {
        if ($headers['http-code'] === 200) {
            $headers['http-code'] .= ' ' . '<span class="text-success">(Passed)</span>';
        } elseif ($headers['http-code'] >= 500 || $headers['http-code'] < 200) {
            $headers['http-code'] .= ' ' . '<span class="text-danger">(Request Failed !)</span>';
        }

        if ($headers['scheme'] === 'HTTPS') {
            $headers['scheme'] .= ' ' . '<span class="text-success">(Passed)</span>';
        }

        if (empty($headers['cache-control'])) {
            $headers['cache-control'] .= ' ' . '<span class="text-danger">(Empty)</span>';
        }
        if (empty($headers['content-encoding'])) {
            $headers['content-encoding'] .= ' ' . '<span class="text-danger">(Empty)</span>';
        } elseif (stripos($headers['content-encoding'], 'gzip') !== false || stripos($headers['content-encoding'], 'deflate') !== false) {
            $headers['content-encoding'] .= ' ' . '<span class="text-success">(Passed)</span>';
        }
        if (empty($headers['vary'])) {
            $headers['vary'] .= ' ' . '<span class="text-danger">(Empty)</span>';
        }
        if (empty($headers['etag'])) {
            $headers['etag'] .= ' ' . '<span class="text-danger">(Empty)</span>';
        }
        if (!empty($headers['x-powered-by'])) {
            $headers['x-powered-by'] .= ' ' . '<span class="text-danger">(Is Not Empty)</span>';
        }
        if (!empty($headers['server'])) {
            $headers['server'] .= ' ' . '<span class="text-danger">(Is Not Empty)</span>';
        }
        if (is_null($headers['x-frame-options'])) {
            $headers['x-frame-options'] .= ' ' . '<span class="text-danger">(Empty - Not Secure)</span>';
        }
        if (is_null($headers['x-xss-protection'])) {
            $headers['x-xss-protection'] .= ' ' . '<span class="text-danger">(Empty - Not Secure)</span>';
        }

        return $headers;
    }
}

class ErrorReporting
{
    protected $levels = [
        1 => 'E_ERROR',
        2 => 'E_WARNING',
        4 => 'E_PARSE',
        8 => 'E_NOTICE',
        16 => 'E_CORE_ERROR',
        32 => 'E_CORE_WARNING',
        64 => 'E_COMPILE_ERROR',
        128 => 'E_COMPILE_WARNING',
        256 => 'E_USER_ERROR',
        512 => 'E_USER_WARNING',
        1024 => 'E_USER_NOTICE',
        2048 => 'E_STRICT',
        4096 => 'E_RECOVERABLE_ERROR',
        8192 => 'E_DEPRECATED',
        16384 => 'E_USER_DEPRECATED'
    ];

    protected $level;

    public function __construct()
    {
        $this->level = error_reporting();
    }

    public function getErrorLevel()
    {
        $included = $this->_getIncluded();

        $errorLevel = $this->_getErrorDescription($included);

        return $errorLevel;
    }

    public function _getIncluded()
    {
        $included = array();

        foreach ($this->levels as $levelInt => $levelText) {
            // This is where we check if a level was used or not
            if ($this->level && $levelInt) {
                $included[] = $levelInt;
            }
        }

        return $included;
    }

    protected function _getErrorDescription($included)
    {
        $description = '';

        $all = count($this->levels);

        $values = array();
        if (count($included) > $all / 2) {
            $values[] = 'E_ALL';

            foreach ($this->levels as $levelInt => $levelText) {
                if (!in_array($levelInt, $included)) {
                    $values[] = $levelText;
                }
            }
            $description = implode(' &amp; ~', $values);
        } else {
            foreach ($included as $levelInt) {
                $values[] = $this->levels[$levelInt];
            }
            $description = implode(' | ', $values);
        }

        return $description;
    }
}

//Download Update
if (isset($_GET['Download_Update'])) {
    if ((new Helper)->downloadUpdate()) {
        $page = $_SERVER['PHP_SELF'];
        $sec = "1";
        header("Refresh: $sec; url=$page");
    }
}
?>
<!doctype html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PaL Server Info - <?php echo $_VERSION; ?></title>
    <meta name="show_recommendation" content="<?php echo isset($recommendations); ?>">
    <meta name="show_webserver_headers" content="<?php echo isset($webserver_responses); ?>">
    <link href="data:image/x-icon;base64,AAABAAEAEBAQAAEABAAoAQAAFgAAACgAAAAQAAAAIAAAAAEABAAAAAAAgAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAA/4QAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAABABAQAAAQAAEAEBAAABAAAQAQEAAAEREBERAREQAQAQEAEBABABABAQAQEAEAEREBABAREQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD//wAA//8AAL2vAAC9rwAAva8AAIQhAAC1rQAAta0AAIWhAAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA" rel="icon" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            background: royalblue linear-gradient(to right top, #515760, #505e73, #4f6486, #4e6b99, #4d71ad, #447fbf, #348dd1, #089ce2, #00b5ee, #00cef3, #17e5f4, #5ffbf1) center/cover fixed;
            color: white;
            font: 1em "Roboto", sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        table tr td {
            word-wrap: break-word;
            word-break: break-all;
            min-width: 250px;
        }

        .roboto {
            font-family: "Roboto", sans-serif;
        }

        .roboto-thin {
            font-weight: 100;
        }

        .roboto-light {
            font-weight: 300;
        }

        .roboto-regular {
            font-weight: 400;
        }

        .roboto-medium {
            font-weight: 500;
        }

        .roboto-bold {
            font-weight: 700;
        }

        .roboto-black {
            font-weight: 900;
        }

        .roboto-italic {
            font-style: italic;
        }

        #checklist-box {
            margin: 40px auto;
            background-color: aliceblue;
            padding: 20px 0 0;
            border-radius: 10px;
            color: #2F4858;
            box-shadow: 2px 11px 19px -6px rgba(61, 55, 61, 1);
        }

        #content-box {
            padding: 0 20px;
        }

        .checklist-item-title {
            font-size: 1.2em;
            min-width: 240px;
            display: inline-block;
            text-transform: capitalize;
        }


        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 48;
            font-size: 2em;
            vertical-align: middle;
            margin: 0 10px;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        .pulse-animation {
            animation: pulse 2s infinite;
            -moz-animation: pulse 2s infinite;
            -webkit-animation: pulse 2s infinite;
        }


        .checklist-item-icon-check {
            color: #32E2A0;
        }

        .checklist-item-icon-uncheck {
            color: #F7613F;
        }

        .checklist-item-icon-info {
            color: #0092FF;
        }


        .checklist-item-value {
            word-wrap: break-word;
            font-weight: 400;
        }


        @keyframes rotation {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .spin-loader {
            width: 32px;
            height: 32px;
            border: 5px solid #FFF;
            border-bottom-color: #00BCFF;
            border-radius: 50%;
            display: inline-block;
            animation: rotation 1s linear infinite;
            vertical-align: middle;
            margin-left: 12px;
        }


        .list-info {
            list-style-type: disc;
        }

        .list-info li {
            margin-bottom: 10px;
        }

        .list-info li span.list-info-title {
            min-width: 200px;
            display: inline-block;
        }


        #benchmark-loading {
            background-color: #4a4a4a;
            color: #FFF;
            text-align: center;
            padding: 12px 0;
        }

        #benchmark-loading .spin-loader {
            width: 64px;
            height: 64px;
        }

        .recommendations_row {
            margin: 12px auto;
            border-bottom: 1px solid #CDCDCD;
            background-color: #f1f1f1;
            padding: 8px 0;
            border-radius: 8px;
            box-shadow: 0px 1px 4px -2px #9b9b9b;
            line-height: 1.5em;
        }

        .recommendations_row .recommendations_content {
            word-wrap: break-word;
        }

        .recommendations_title {
            border-left: 5px solid #59add3;
            padding: 4px 12px;
            background-color: #495fd5;
            color: #EFEFEF;
            border-radius: 5px;
        }

        .recommendations_extentions_loaded {
            font-size: 1.15rem;
        }

        .code_box {
            background-color: #38383c;
            width: 100%;
            display: block;
            padding: 2em;
            margin: 8px auto;
            border-radius: 8px;
        }

        .accordion-button.collapsed>span.plus_icon {
            visibility: inherit;
        }

        .accordion-button>span.plus_icon {
            visibility: hidden;
        }


        #footer {
            margin: 24px auto 0;
            color: #303030;
            background-color: #607d8b40;
            padding: 8px;
            box-shadow: 0 -1px 6px -4px #3e4f68;
        }
    </style>
</head>

<body class="roboto-medium">
    <main class="container">
        <div id="checklist-box">
            <!-- .Content-Box -->
            <div id="content-box">
                <?php
                if (isset($_GET['Check-For-Update'])) :
                    $check_for_update = (new Helper)->checkForUpdate($_VERSION);
                    if ($check_for_update === true) {
                        $update_message = 'A new version of the program is available.' . '<a class="text-link mx-2" href="?Download_Update">Download Now</a>';
                    } else {
                        $update_message = 'You are using the latest version of the program.';
                    }
                ?>
                    <div class="col">
                        <div class="alert alert-info my-2"><?php echo $update_message; ?></div>
                    </div>
                <?php
                    unset($check_for_update, $update_message);
                endif;
                ?>
                <!-- Info Cards -->
                <div class="row mb-2">
                    <?php
                    foreach ($info_cards as $key => $value) :
                    ?>

                        <div class="col-md-4 col-12">
                            <div class="card my-2">
                                <div class="card-body">
                                    <h5 class="card-title checklist-item-title"><i class="material-symbols-outlined checklist-item-icon-info">info</i> <?php echo $key; ?></h5>
                                    <p class="card-text"><?php echo $value; ?></p>
                                </div>
                            </div>
                        </div>

                    <?php
                    endforeach;
                    ?>

                </div>
                <!-- End Info Cards -->
                <!-- Benchmark Box -->
                <div class="accordion mb-2" id="accordionBenchmark">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                ToolBox
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionBenchmark">
                            <div class="accordion-body">
                                <p class="text-muted">
                                    <span class="material-symbols-outlined text-warning pulse-animation">privacy_tip</span>
                                    Don't forget to enter the mysql username and password in '<?php echo basename($_SERVER["SCRIPT_FILENAME"]); ?>' on line 26
                                </p>
                                <h5>PHP And Mysql Benchmark</h5>
                                <p class="text-muted">This tool performs a benchmark test on MySQL database and PHP server.</p>

                                <a href="?Benchmark&laravel_version=<?php echo $laravel_version_select; ?>" class="btn btn-outline-primary mt-2 mx-2">Php Benchmark</a>
                                <a href="?Mysql-Benchmark&laravel_version=<?php echo $laravel_version_select; ?>" class="btn btn-outline-primary mt-2 mx-2">Mysql Benchmark</a>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6 col-12 mt-2">
                                        <div class="">
                                            <h5>Scan PHP Configurations</h5>
                                            <p class="text-muted">This tool checks PHP settings for performance and security.</p>

                                            <a href="?Recommendations&laravel_version=<?php echo $laravel_version_select; ?>" class="btn btn-outline-primary mt-2 mx-2">Scan Now</a>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12 mt-2">
                                        <div class="">
                                            <h5>Scan WebServer Headers</h5>
                                            <p class="text-muted">This tool checks web server headers and show important headers and values.</p>

                                            <a data-bs-toggle="modal" data-bs-target="#getUrlModal" class="btn btn-outline-primary mt-2 mx-2">Check Now</a>

                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Benchmark Box -->
                <?php
                if (isset($_GET['Benchmark']) || isset($_GET['Mysql-Benchmark'])) :
                ?>
                    <div id="benchmark-loading" class="">
                        <span class="spin-loader"></span>
                    </div>
                <?php
                endif;
                ?>

                <?php
                if (isset($_GET['Benchmark'])) :
                    $benchmark_results = (new Benchmark)->PhpBenchmark();
                    if ($benchmark_results) :
                ?>
                        <!-- Php Benchmark Box -->
                        <div class="row mb-2">
                            <div class="col-12">
                                <ul id="benchmark-items" class="list-group">
                                    <li class="list-group-item list-group-item-action active" aria-current="true">Benchmarks</li>
                                    <div id="benchmark-table" class="table-responsive d-none">
                                        <table class="table table-striped table-hover table-light">
                                            <thead>
                                                <tr>
                                                    <th class="bg-dark text-white" scope="col">Benchmark Type</th>
                                                    <th class="bg-dark text-white" scope="col">Benchmark Time</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($benchmark_results['types'] as $key => $value) :
                                                ?>
                                                    <tr>
                                                        <td><?php echo $key; ?></td>
                                                        <td><?php echo number_format($value, 4); ?> sec</td>
                                                    </tr>
                                                <?php
                                                endforeach;
                                                ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th class="bg-secondary text-dark" scope="col">Benchmark Avg Time</th>
                                                    <th class="bg-secondary text-dark" scope="col"><?php echo number_format($benchmark_results['avg'], 4); ?> sec</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                </ul>
                            </div>
                        </div>
                        <!-- End Php Benchmark Box -->
                <?php
                        unset($benchmark_results);
                    endif;
                endif;
                ?>

                <?php
                if (isset($_GET['Mysql-Benchmark'])) :
                    $benchmark_results = (new Benchmark($MYSQL_CONFIG))->MysqlBenchmark();
                    if ($benchmark_results) :
                ?>
                        <!-- Mysql Benchmark Box -->
                        <div class="row mb-2">
                            <div class="col-12">
                                <ul id="benchmark-items" class="list-group">
                                    <li class="list-group-item list-group-item-action active" aria-current="true">Benchmarks</li>
                                    <div id="benchmark-table" class="table-responsive d-none">
                                        <table class="table table-striped table-hover table-light">
                                            <thead>
                                                <tr>
                                                    <th class="bg-dark text-white" scope="col">Benchmark Type</th>
                                                    <th class="bg-dark text-white" scope="col">Benchmark Time</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($benchmark_results['types'] as $key => $value) :
                                                ?>
                                                    <tr>
                                                        <td><?php echo $key; ?></td>
                                                        <td><?php echo number_format($value, 4); ?> sec</td>
                                                    </tr>
                                                <?php
                                                endforeach;
                                                ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th class="bg-secondary text-dark" scope="col">Benchmark Avg Time</th>
                                                    <th class="bg-secondary text-dark" scope="col"><?php echo number_format($benchmark_results['avg'], 4); ?> sec</th>
                                                </tr>
                                                <tr>
                                                    <th class="bg-secondary text-dark" scope="col">Insert per Second (<?php echo $MYSQL_CONFIG['benchmark_insert'] . ' rows' ?>)</th>
                                                    <th class="bg-secondary text-dark" scope="col"><?php echo number_format($benchmark_results['types']['insert'] / $MYSQL_CONFIG['benchmark_insert'], 4); ?></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                </ul>
                            </div>
                        </div>
                    <?php
                        unset($benchmark_results);
                    else :
                    ?>
                        <div class="alert alert-warning">Error in Mysql Connection - Check Mysql Config on line 26</div>
                        <!-- End Mysql Benchmark Box -->
                <?php
                    endif;
                endif;
                ?>

                <!-- Laravel Requirements Box -->
                <div class="row">
                    <div class="col-md-6 col-12">
                        <ul id="checklist-items" class="list-group">
                            <li class="list-group-item list-group-item-action active" aria-current="true">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-light btn-block w-100 text-dark btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        Laravel <?php echo $laravel_version_select; ?> Requirements
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="?laravel_version=10.x">10.x</a></li>
                                        <li><a class="dropdown-item" href="?laravel_version=9.x">9.x</a></li>
                                        <li><a class="dropdown-item" href="?laravel_version=8.x">8.x</a></li>
                                        <li><a class="dropdown-item" href="?laravel_version=7.x">7.x</a></li>
                                        <li><a class="dropdown-item" href="?laravel_version=6.x">6.x</a></li>
                                        <li><a class="dropdown-item" href="?laravel_version=5.8">5.8</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item" target="_blank" href="https://laravel.com/docs/master/deployment#server-requirements">Visit Laravel Website</a></li>
                                    </ul>
                                </div>
                            </li>
                        </ul>

                        <ul id="optional-items" class="list-group mt-2">
                            <li class="list-group-item list-group-item-action active" aria-current="true">Optional Checklist</li>
                        </ul>
                    </div>
                    <div class="col-md-6 col-12">
                        <ul id="serverinfo-items" class="list-group">
                            <li class="list-group-item list-group-item-action active" aria-current="true">Server Info</li>
                        </ul>
                    </div>
                </div>
                <!-- End Laravel Requirements Box -->

            </div>
            <!-- End .Content-Box -->
            <!-- #footer -->
            <div id="footer" class="roboto roboto-regular roboto-italic text-center">
                <div class="row pt-3">
                    <div class="col-md-3 col-12">
                        <p>
                            <a target="_blank" href="https://github.com/saeedvir/PaL-Server-Info"><span class="material-symbols-outlined">language</span> PaL-Server-Info</a> | <?php echo $_VERSION; ?>
                        </p>
                    </div>
                    <div class="col-md-3 col-12">
                        <p>
                            <a target="_blank" href="https://github.com/saeedvir/PaL-Server-Info/issues"><span class="material-symbols-outlined">bug_report</span> Report A Bug Or Request A Feature</a>
                        </p>
                    </div>
                    <div class="col-md-3 col-12">
                        <p>
                            <span class="material-symbols-outlined text-danger">code</span> Developed By : <a href="https://github.com/saeedvir" target="_blank">Saeed Abdollahian</a>
                        </p>
                    </div>
                    <div class="col-md-3 col-12">
                        <p><a href="?Check-For-Update"><span class="material-symbols-outlined">update</span> Check For Update</a></p>
                    </div>
                    <div class="col-12">
                        <p class="text-center">Copyright © 2024</p>
                    </div>
                </div>
            </div>
            <!-- End #footer -->
        </div>

    </main>
    <!-- End #main -->
    <!-- Recommendations Modal -->
    <?php
    if (isset($recommendations)) :
    ?>
        <div class="modal fade" id="RecommendationsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="RecommendationsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-fullscreen">
                <div class="modal-content text-dark">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="RecommendationsModalLabel">Recommendations</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body bg-secondary">

                        <div class="accordion accordion-flush" id="accordionPhpLoadedModules">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                                        PHP loaded Modules: <span class="plus_icon px-2 text-primary pulse-animation">(click me)</span>
                                    </button>
                                </h2>
                                <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionPhpLoadedModules">
                                    <div class="accordion-body">
                                        <?php
                                        if (function_exists('get_loaded_extensions')) :
                                        ?>
                                            <div class="row recommendations_row">
                                                <div class="col-12">
                                                    <ul class="d-inline-block recommendations_extentions_loaded">
                                                        <?php

                                                        foreach (get_loaded_extensions() as $val) :
                                                        ?>
                                                            <li class="badge bg-primary my-2 mx-1 px-2">
                                                                <span><?php echo $val; ?></span>
                                                            </li>
                                                        <?php
                                                        endforeach;

                                                        ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        <?php
                                        else :
                                        ?>
                                            <div class="alert alert-danger">
                                                <strong>PHP Extensions Not Loaded or Not Supported</strong>
                                            </div>
                                        <?php
                                        endif;
                                        ?>
                                    </div>
                                </div>
                            </div>

                        </div>


                        <?php
                        foreach ($recommendations as $k => $val) :
                        ?>
                            <div class="row recommendations_row">
                                <?php
                                ?>
                                <div class="col-12 recommendations_content my-3">
                                    <h5><span class="recommendations_title"><?php echo $val['title']; ?></span></h5>
                                </div>
                                <div class="col-12 col-md-4 recommendations_content text-primary">
                                    <h6>Your Value: <span class="text-dark"><?php echo $val['your_value']; ?></span></h6>
                                </div>
                                <div class="col-12 col-md-4 recommendations_content text-success">
                                    <h6>Production Value: <span class="text-dark"><?php echo $val['value']; ?></span> </h6>
                                </div>
                                <div class="col-12 col-md-4 recommendations_content text-info">
                                    <h6>Development Value: <span class="text-dark"><?php echo $val['dev_value']; ?></span> </h6>
                                </div>
                                <div class="col-12 recommendations_content mt-3">
                                    <h6>How to Fix?</h6>
                                    <span class="badge bg-info"><?php echo $val['tag']; ?></span>
                                    <code class="code_box">
                                        <?php echo $val['how_to_fix']; ?>
                                    </code>
                                </div>

                                <?php

                                ?>
                            </div>
                        <?php
                        endforeach;

                        ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php
    endif;
    ?>
    <!--End Recommendations Modal -->
    <!--Webserver Headers Modal -->
    <?php
    $http_url = (isset($_SERVER['REQUEST_SCHEME'])) ? $_SERVER['REQUEST_SCHEME'] . '://' : 'http://';
    $http_url .= (isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : '127.0.0.1';


    ?>
    <div class="modal fade" id="getUrlModal" tabindex="-1" aria-labelledby="getUrlModalLabel" aria-hidden="true">
        <div class="modal-dialog <?php if (isset($webserver_responses)) echo 'modal-dialog-scrollable modal-dialog-centered modal-fullscreen' ?>">
            <div class="modal-content text-dark">
                <?php
                if (!isset($webserver_responses)) :
                ?>
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="getUrlModalLabel">HTTP Headers</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="?" method="get">
                            <div class="mb-3">
                                <label for="url-address" class="col-form-label">Enter URL:</label>
                                <input type="text" name="url" required class="form-control" id="url-address" placeholder="https://google.com" value="<?php echo $http_url; ?>">
                                <input type="hidden" name="Webserver-headers">
                                <input type="hidden" name="laravel_version" value="<?php echo $laravel_version_select; ?>">
                            </div>
                            <div>
                                <button id="btn_submit_url" type="submit" class="btn btn-primary">Check Headers</button>

                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                <?php
                    unset($http_url);
                endif;
                if (isset($webserver_responses)) :
                ?>
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="getUrlModalLabel">HTTP Headers Responses</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="lead">
                            Need Help ?
                            <a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers">Http Header Informations</a>
                            <hr>
                        </p>
                        <?php
                        if ($webserver_responses['success'] === true) :
                        ?>
                            <div id="headers-table" class="table-responsive">
                                <table class="table table-striped table-hover table-light">
                                    <thead>
                                        <tr>
                                            <th class="bg-dark text-white" scope="col">header</th>
                                            <th class="bg-dark text-white" scope="col">value</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php
                                        foreach ($webserver_responses['headers'] as $k => $val) :
                                        ?>
                                            <tr>
                                                <td><?php echo $k; ?></td>
                                                <td><?php echo $val; ?></td>
                                            </tr>
                                        <?php
                                        endforeach;

                                        ?>
                                        <tr>
                                            <td class="text-primary" scope="col">CORS Headers:</td>
                                            <td class="" scope="col"></td>
                                        </tr>
                                        <?php
                                        foreach ($webserver_responses['cors'] as $k => $val) :
                                        ?>
                                            <tr>
                                                <td><?php echo $k; ?></td>
                                                <td><?php echo $val; ?></td>
                                            </tr>
                                        <?php
                                        endforeach;

                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php
                        endif;
                        if ($webserver_responses['success'] === false) :
                        ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $webserver_responses['message']; ?>
                            </div>
                        <?php
                        endif;
                        ?>
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-secondary" href="">Close</button>
                    </div>
                <?php
                    unset($webserver_responses);
                endif;
                ?>
            </div>
        </div>
    </div>


    <!--End Webserver Headers Modal -->



    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <!-- Jquery slim Script -->
    <script src="https://code.jquery.com/jquery-3.6.0.slim.min.js"></script>
    <!-- App Scripts -->
    <script type="text/javascript">
        // Define variables to store the checklist, optional list, and server info data
        var checklist_data = <?php echo json_encode($check_list); ?>;
        var optionallist_data = <?php echo json_encode($optional_list); ?>;
        var serverinfo_data = <?php echo json_encode($serverinfo_list); ?>;
        var checklistItems = $('#checklist-items');
        var optionalItems = $('#optional-items');
        var serverinfoItems = $('#serverinfo-items');

        // Define a function to generate a list item with key, value, and parent element
        function generateListItem(key, value, parentElement) {
            let template = '<li class="list-group-item">' +
                '<span class="checklist-item-title roboto roboto-regular roboto-italic">' + key + '</span>';

            if (typeof value === 'object') {

                template += '<ul class="list-info d-block w-100">';
                for (let i in value) {
                    let is_bool_value = typeof value[i] === 'boolean' ? true : false;

                    if (is_bool_value) {
                        template += '<li><span class="list-info-title">' + i + '</span>' +
                            '<span data-check="' + value[i] + '" class="spin-loader">' + '</span></li>';
                    } else {
                        template += '<li><span class="list-info-title">' + i + '</span>' +
                            '<span data-check="' + value[i] + '">' + value[i] + '</span></li>';
                    }
                }
                template += '</ul>';
            } else {
                let is_bool_value = typeof value === 'boolean' ? true : false;

                if (is_bool_value) {
                    template += '<span class="checklist-item-value spin-loader" data-check="' + value + '">' + '</span>';

                } else {
                    template += '<span class="checklist-item-value">' + value + '</span>';

                }
            }

            template += '</li>';
            parentElement.append(template);
        }

        function isValidUrl(urlString) {
            var urlPattern = new RegExp('^(https?:\\/\\/)?' + // validate protocol
                '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' + // validate domain name
                '((\\d{1,3}\\.){3}\\d{1,3}))' + // validate OR ip (v4) address
                '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // validate port and path
                '(\\?[;&a-z\\d%_.~+=-]*)?' + // validate query string
                '(\\#[-a-z\\d_]*)?$', 'i'); // validate fragment locator
            return !!urlPattern.test(urlString);

        }

        // Execute the code when the document is ready
        $(document).ready(function() {
            $.each(checklist_data, function(key, value) {
                generateListItem(key, value, checklistItems);
            });

            $.each(optionallist_data, function(key, value) {
                generateListItem(key, value, optionalItems);
            });

            $.each(serverinfo_data, function(key, value) {
                generateListItem(key, value, serverinfoItems);
            });

            $('#url-address').on('keyup change mousedown', function() {
                if (!isValidUrl($(this).val())) {
                    $(this).addClass('is-invalid').removeClass('is-valid');
                    $('#btn_submit_url').attr('disabled', 'yes');
                } else {
                    $('#btn_submit_url').removeAttr('disabled');
                    $(this).addClass('is-valid').removeClass('is-invalid');
                }
            })

            // Execute code after a timeout of 2 seconds
            setTimeout(function() {
                $('.spin-loader[data-check]').each(function() {
                    let checked = $(this).data('check');
                    $(this).removeClass('spin-loader');
                    if (checked) {
                        $(this).text('check_circle').addClass("material-symbols-outlined checklist-item-icon-check");
                    } else {
                        $(this).text('cancel').addClass("material-symbols-outlined checklist-item-icon-uncheck");
                    }
                });

            }, 2000);

            // Remove the benchmark loading and display the benchmark table with a delay of 1 second
            $('#benchmark-loading').delay(1000).remove().queue(function() {
                $('#benchmark-table').removeClass('d-none');
                $(this).dequeue();
            });

            // Update the browser history state
            history.replaceState({}, '<?php echo $_SCRIPT_FILE; ?>', '/<?php echo $_SCRIPT_FILE; ?>');

            //Check for Show Modal
            if ($('meta[name="show_recommendation"]').attr('content') == '1') {
                let myModal = new bootstrap.Modal(document.getElementById('RecommendationsModal'))
                myModal.show();
                myModal = null;
            }
            if ($('meta[name="show_webserver_headers"]').attr('content') == '1') {
                let myModal = new bootstrap.Modal(document.getElementById('getUrlModal'))
                myModal.show();
                myModal = null;
            }



            console.log('The program was successfully executed.');
        });
    </script>
    <!-- End App Scripts -->
</body>

</html>
