<?php
/**
 * Php And Laravel (PaL) Server Info
 * Test on PHP version >= 7.4.33
 * @category Server, Benchmark, Php, Laravel, Mysql
 * @package  Php,Laravel
 * @author   Saeed Agha Abdollahian <https://github.com/saeedvir>
 * @link     https://github.com/saeedvir/PaL-Server-Info
 * @version  1.3 (Last Update : 2024-03-04)
 * @since    2024-02-26
 * @license  MIT License https://opensource.org/licenses/MIT
 * @see      https://github.com/saeedvir/PaL-Server-Info
 * @copyright 2024
 */
/**
 * Usage
 * php PaL-cli.php -l 10.x -i -c -o -s -r
 * php PaL-cli.php -wh "https://www.example.com"
 * php PaL-cli.php help
 */
if (version_compare(PHP_VERSION, '5.6') < 0) {
    echo 'This script requires PHP 5.6 or higher.';
    exit(1);
}
if ((new ServerCheck())->getWebServerEnvironment() !== 'CLI') {
    echo 'This script Only Run in CLI.';
    exit(1);
}
//Initialise Variables
$_VERSION = 'v 1.3'; //Current Version , Don't change this !!!

$MYSQL_CONFIG = [
    'host' => 'localhost',
    'username' => 'USER_NAME_HERE', //ex : root
    'password' => 'PASSWORD_HERE', //ex : password
    'db' => 'DB_NAME_HERE',         //ex : laravel_db
    'benchmark_insert' => 100,      //ex : 100
];


//Classes and Functions
class CliHelper
{
    const MAX_ARGV = 1000;

    public $cli_configs = null;

    private $betweenTextSpace = 0;

    private $foreground_colors = [];
    private $background_colors = [];

    private static $foregroundColors = [
        'black'        => '0;30',
        'dark_gray'    => '1;30',
        'blue'         => '0;34',
        'light_blue'   => '1;34',
        'green'        => '0;32',
        'light_green'  => '1;32',
        'cyan'         => '0;36',
        'light_cyan'   => '1;36',
        'red'          => '0;31',
        'light_red'    => '1;31',
        'purple'       => '0;35',
        'light_purple' => '1;35',
        'brown'        => '0;33',
        'yellow'       => '1;33',
        'light_gray'   => '0;37',
        'white'        => '1;37',
    ];
    private static $backgroundColors = [
        'black'      => '40',
        'red'        => '41',
        'green'      => '42',
        'yellow'     => '43',
        'blue'       => '44',
        'magenta'    => '45',
        'cyan'       => '46',
        'light_gray' => '47',
    ];


    public function __construct()
    {
        $this->cli_configs = $this->parseConfigs();

        $this->betweenTextSpace = 0;

        // Set up shell colors
        $this->foreground_colors['black'] = '0;30';
        $this->foreground_colors['dark_gray'] = '1;30';
        $this->foreground_colors['blue'] = '0;34';
        $this->foreground_colors['light_blue'] = '1;34';
        $this->foreground_colors['green'] = '0;32';
        $this->foreground_colors['light_green'] = '1;32';
        $this->foreground_colors['cyan'] = '0;36';
        $this->foreground_colors['light_cyan'] = '1;36';
        $this->foreground_colors['red'] = '0;31';
        $this->foreground_colors['light_red'] = '1;31';
        $this->foreground_colors['purple'] = '0;35';
        $this->foreground_colors['light_purple'] = '1;35';
        $this->foreground_colors['brown'] = '0;33';
        $this->foreground_colors['yellow'] = '1;33';
        $this->foreground_colors['light_gray'] = '0;37';
        $this->foreground_colors['white'] = '1;37';

        $this->background_colors['black'] = '40';
        $this->background_colors['red'] = '41';
        $this->background_colors['green'] = '42';
        $this->background_colors['yellow'] = '43';
        $this->background_colors['blue'] = '44';
        $this->background_colors['magenta'] = '45';
        $this->background_colors['cyan'] = '46';
        $this->background_colors['light_gray'] = '47';
    }

    public function setBetweenTextSpace($space)
    {
        $this->betweenTextSpace = $space;
    }

    public function unsetBetweenTextSpace()
    {
        $this->betweenTextSpace = 0;
    }
    // Returns colored string
    public function getColoredString($string, $foreground_color = null, $background_color = null, $new_line = false)
    {
        $colored_string = '';

        // Check if given foreground color found
        if (isset($this->foreground_colors[$foreground_color])) {
            $colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . 'm';
        }
        // Check if given background color found
        if (isset($this->background_colors[$background_color])) {
            $colored_string .= "\033[" . $this->background_colors[$background_color] . 'm';
        }

        // Add string and end coloring
        $colored_string .= $string . "\033[0m";

        return $new_line ? $colored_string . PHP_EOL : $colored_string;
    }

    // Returns all foreground color names
    public function getForegroundColors()
    {
        return array_keys($this->foreground_colors);
    }

    // Returns all background color names
    public function getBackgroundColors()
    {
        return array_keys($this->background_colors);
    }

    /**
     * 获取带颜色的文字.
     *
     * @param string      $string          black|dark_gray|blue|light_blue|green|light_green|cyan|light_cyan|red|light_red|purple|brown|yellow|light_gray|white
     * @param string|null $foregroundColor 前景颜色 black|red|green|yellow|blue|magenta|cyan|light_gray
     * @param string|null $backgroundColor 背景颜色 同$foregroundColor
     *
     * @return string
     */
    public static function initColoredString(
        $string,
        $foregroundColor = null,
        $backgroundColor = null
    ) {
        $coloredString = '';

        if (isset(static::$foregroundColors[$foregroundColor])) {
            $coloredString .= "\033[" . static::$foregroundColors[$foregroundColor] . 'm';
        }
        if (isset(static::$backgroundColors[$backgroundColor])) {
            $coloredString .= "\033[" . static::$backgroundColors[$backgroundColor] . 'm';
        }

        $coloredString .= $string . "\033[0m";

        return $coloredString;
    }

    /**
     * 输出提示信息.
     *
     * @param $msg
     */
    public static function notice($msg)
    {
        fwrite(STDOUT, self::initColoredString($msg, 'light_gray') . PHP_EOL);
    }

    /**
     * 输出错误信息.
     *
     * @param $msg
     */
    public static function error($msg)
    {
        fwrite(STDERR, self::initColoredString($msg, 'red') . PHP_EOL);
    }

    /**
     * 输出警告信息.
     *
     * @param $msg
     */
    public static function warn($msg)
    {
        fwrite(STDOUT, self::initColoredString($msg, 'yellow') . PHP_EOL);
    }

    /**
     * 输出成功信息.
     *
     * @param $msg
     */
    public static function success($msg)
    {
        fwrite(STDOUT, self::initColoredString($msg, 'green') . PHP_EOL);
    }


    public function getFilename()
    {
        return basename(__FILE__);
    }
    public function isEmptyConfig()
    {
        if ($this->getConfig($this->getFilename())) {
            return true;
        }
        return false;
    }
    public function setConfig($config_key, $config_value)
    {
        $this->cli_configs[$config_key] = $config_value;
    }

    public function getConfig($config_key, $config_default = null)
    {
        if (isset($this->cli_configs[$config_key])) {
            return $this->cli_configs[$config_key];
        } else {
            return $config_default;
        }
    }
    public function getConfigWithArgv($argv, $config_key, $ret_param = false, $config_default = null)
    {
        $arg_key = array_search($config_key, $argv);
        if ($arg_key !== false) {
            if ($ret_param) {
                return isset($argv[$arg_key + 1]) ? $argv[$arg_key + 1] : $config_default;
            }
            return $argv[$arg_key];
        } else {
            return $config_default;
        }
    }
    public function getBoxLine($position = 'top', $colorful = [], $repaat = 10)
    {
        switch ($position) {
            case 'top':
            case 'bottom':
                $boxLine = str_repeat('-', $repaat);
                break;
            case 'left':
                $boxLine = '||' . $this->emptySpace(true);
                break;
            case 'empty-left':
            case 'empty-right':
                $boxLine = '     ';
                break;
            case 'right':
                $boxLine = $this->emptySpace(true) . '||';
                break;
            case 'line':
                $boxLine = '   ' . str_repeat('-', $repaat) . '   ';
                break;
            default:
                return '';
                break;
        }

        if (!empty($colorful)) {
            $boxLine = $this->getColoredString($boxLine, $colorful['fg_color'], $colorful['bg_color'], false);
        }
        return $boxLine;
    }

    public function getLineBetween($message_1, $message_2, $repeat = 20)
    {
        if (strlen($message_1) <= $this->betweenTextSpace) {
            $message_1 .= str_repeat(' ', $this->betweenTextSpace);
        }
        return $message_1 . $this->getBoxLine('line', [], $repeat) . $message_2;
    }

    public function boxedMessage($message, $colorful = [])
    {

        if (empty($colorful)) {
            $colorful = ['fg_color' => 'green', 'bg_color' => 'black'];
        }
        $repeat = strlen($message) + 10;
        $this->printMessage($this->getBoxLine('top', $colorful, $repeat));
        $this->printMessage($this->getBoxLine('left', $colorful) . $message . $this->getBoxLine('right', $colorful));
        $this->printMessage($this->getBoxLine('bottom', $colorful, $repeat));
    }
    public function headerMessage($message)
    {
        return "\n" . '# ' . $message . "\n\n";
    }

    public function printScriptInfo($version)
    {
        $this->boxedMessage('PaL Server Info Script ' . $version);
        // $this->boxedMessage('https://github.com/saeedvir/PaL-Server-Info', ['fg_color' => 'cyan', 'bg_color' => 'black']);
    }

    public function printMessage($message, $colorful = [], $new_line = true)
    {
        if (!empty($colorful)) {
            $message = $this->getColoredString($message, $colorful['fg_color'], $colorful['bg_color'], false);
        }
        echo $message . ($new_line ? PHP_EOL : '');
    }

    public function printArrayMessage($arr, $boolean_convert = false)
    {

        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $sub_key => $sub_value) {
                    $sub_value = ($boolean_convert === true && (is_bool($sub_value) || (new Helper)->checkBoolean($sub_value) !== null)) ?  (new Helper)->booleanToString($sub_value, ['true' => 'Passed', 'false' => 'Failed']) : $sub_value;

                    if ($sub_value === 'Passed') {
                        $sub_value = $this->getColoredString($sub_value, 'green', 'black', false);
                    } elseif ($sub_value === 'Failed') {
                        $sub_value = $this->getColoredString($sub_value, 'red', 'black', false);
                    } else {
                        $sub_value = $this->getColoredString($sub_value, 'yellow', 'black', false);
                    }
                    $this->printMessage($this->getBoxLine('empty-left') . $this->getLineBetween($sub_key, $sub_value));
                }
            } else {
                $value = ($boolean_convert === true && (is_bool($value) || (new Helper)->checkBoolean($value) !== null)) ? (new Helper)->booleanToString($value, ['true' => 'Passed', 'false' => 'Failed']) : $value;

                if ($value === 'Passed') {
                    $value = $this->getColoredString($value, 'green', 'black', false);
                } elseif ($value === 'Failed') {
                    $value = $this->getColoredString($value, 'red', 'black', false);
                } else {
                    $value = $this->getColoredString($value, 'yellow', 'black', false);
                }

                $this->printMessage($this->getBoxLine('empty-left') . $this->getLineBetween($key, $value));
                $this->emptyLine("\n");
            }
        }
    }
    public function emptyLine($el = "\n\n", $ret = false)
    {
        if ($ret) {
            return $el;
        }
        echo $el;
    }
    public function emptySpace($ret = false)
    {
        if ($ret) {
            return "   ";
        }
        echo "   ";
    }
    // function colorfulMessage($str, $type = 'i'){
    //     switch ($type) {
    //         case 'e': //error
    //             return "\033[31m$str \033[0m";
    //         break;
    //         case 's': //success
    //             return "\033[32m$str \033[0m";
    //         break;
    //         case 'w': //warning
    //             return "\033[33m$str \033[0m";
    //         break;  
    //         case 'i': //info
    //             return "\033[36m$str \033[0m";
    //         break;      
    //         default:
    //             return $str;
    //         break;
    //     }
    // }

    /**
     * Parse arguments
     * 
     * @param array|string [$message] input arguments
     * @return array Configs Key/Value
     */
    public function parseConfigs(&$message = null)
    {
        if (is_string($message)) {
            $argv = explode(' ', $message);
        } else if (is_array($message)) {
            $argv = $message;
        } else {
            global $argv;
            if (isset($argv) && count($argv) > 1) {
                array_shift($argv);
            }
        }

        $index = 0;
        $configs = array();
        while ($index < self::MAX_ARGV && isset($argv[$index])) {
            if (preg_match('/^([^-\=]+.*)$/', $argv[$index], $matches) === 1) {
                // not have ant -= prefix
                $configs[$matches[1]] = true;
            } else if (preg_match('/^-+(.+)$/', $argv[$index], $matches) === 1) {
                // match prefix - with next parameter
                if (preg_match('/^-+(.+)\=(.+)$/', $argv[$index], $subMatches) === 1) {
                    $configs[$subMatches[1]] = $subMatches[2];
                } else if (isset($argv[$index + 1]) && preg_match('/^[^-\=]+$/', $argv[$index + 1]) === 1) {
                    // have sub parameter
                    $configs[$matches[1]] = $argv[$index + 1];
                    $index++;
                } elseif (strpos($matches[0], '--') === false) {
                    for ($j = 0; $j < strlen($matches[1]); $j += 1) {
                        $configs[$matches[1][$j]] = true;
                    }
                } else if (isset($argv[$index + 1]) && preg_match('/^[^-].+$/', $argv[$index + 1]) === 1) {
                    $configs[$matches[1]] = $argv[$index + 1];
                    $index++;
                } else {
                    $configs[$matches[1]] = true;
                }
            }
            $index++;
        }

        return $configs;
    }
}
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

        if (PHP_SAPI === 'cli') {
            $serverInfo['Web Server Version'] = 'PHP ' . phpversion();
            $serverInfo['DOCUMENT ROOT'] = __DIR__;
            $serverInfo['max execution time'] = 'in CLI : 0';
            $serverInfo['max input time'] = 'in CLI : -1';
        }

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
            return 'Error - Check Mysql Config on line 32';
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
        // curl_setopt($ch, CURLOPT_VERBOSE, true);
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
            file_put_contents($download_as_file, $response);
        }

        return $response;
    }

    public function checkForUpdate()
    {
        global $_VERSION;
        $url = 'https://raw.githubusercontent.com/saeedvir/PaL-Server-Info/main/version';

        $response = trim($this->httpGet($url));

        if ($_VERSION !== $response) {
            return true;
        } else {
            return false;
        }
    }

    public function downloadUpdate()
    {
        $url = 'https://raw.githubusercontent.com/saeedvir/PaL-Server-Info/main/PaL-cli.php';

        $download_content = $this->httpGet($url);

        file_put_contents(basename(__FILE__), $download_content);

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


    public function getRecommendations($MYSQL_CONFIG=[])
    {
        $checklist  = $this->getCheckList();

        $return_recommendations = [];
        //ini
        foreach ($checklist['ini_settings'] as $k => $val) {
            if((new ServerCheck())->getWebServerEnvironment() === 'CLI'){
                if(in_array($k,['max_execution_time','max_input_time'])){
                    continue;
                }
            }
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

    public function getHeadersRecommendations($cliHelper, $headers)
    {
        if ($headers['http-code'] === 200) {
            $headers['http-code'] .= ' ' . $cliHelper->getColoredString('Passed', 'green', 'black', false);
        } elseif ($headers['http-code'] >= 500 || $headers['http-code'] < 200) {
            $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . 'Request Failed !', ['fg_color' => 'red', 'bg_color' => 'black']);
            $cliHelper->emptyLine("\n");

            $headers['http-code'] .= ' ' . $cliHelper->getColoredString('Failed', 'red', 'black', false);
        }

        if ($headers['scheme'] === 'HTTPS') {
            $headers['scheme'] .= ' ' . $cliHelper->getColoredString('Passed', 'green', 'black', false);
        }

        if (empty($headers['cache-control'])) {
            $headers['cache-control'] .= ' ' . $cliHelper->getColoredString('(Empty)', 'red', 'black', false);
        }
        if (empty($headers['content-encoding'])) {
            $headers['content-encoding'] .= ' ' . $cliHelper->getColoredString('(Empty)', 'red', 'black', false);
        } elseif (stripos($headers['content-encoding'], 'gzip') !== false || stripos($headers['content-encoding'], 'deflate') !== false) {
            $headers['content-encoding'] .= ' ' . $cliHelper->getColoredString('Passed', 'green', 'black', false);
        }
        if (empty($headers['vary'])) {
            $headers['vary'] .= ' ' . $cliHelper->getColoredString('(Empty)', 'red', 'black', false);
        }
        if (empty($headers['etag'])) {
            $headers['etag'] .= ' ' . $cliHelper->getColoredString('(Empty)', 'red', 'black', false);
        }
        if (!empty($headers['x-powered-by'])) {
            $headers['x-powered-by'] .= ' ' . $cliHelper->getColoredString('(is Not Empty)', 'red', 'black', false);
        }
        if (!empty($headers['server'])) {
            $headers['server'] .= ' ' . $cliHelper->getColoredString('(is Not Empty)', 'red', 'black', false);
        }
        if (is_null($headers['x-frame-options'])) {
            $headers['x-frame-options'] .= ' ' . $cliHelper->getColoredString('(Empty - not secure)', 'red', 'black', false);
        }
        if (is_null($headers['x-xss-protection'])) {
            $headers['x-xss-protection'] .= ' ' . $cliHelper->getColoredString('(Empty - not secure)', 'red', 'black', false);
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

$cliHelper = new CliHelper();

$cliHelper->printScriptInfo($_VERSION);
// Show Help and Instructions
if ($argc === 1 || $cliHelper->getConfig('help')) {
    $cliHelper->setBetweenTextSpace(1);

    $cliHelper->printMessage($cliHelper->headerMessage('Help And Instructions'), ['fg_color' => 'cyan', 'bg_color' => 'black']);
    $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . $cliHelper->getLineBetween('i', 'show informations'));
    $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . $cliHelper->getLineBetween('-l', 'set laravel version for check list for ex: 10.x or 9.x or 8.x or 7.x or 6.x or 5.8'));
    $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . $cliHelper->getLineBetween('c', 'laravel check list'));
    $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . $cliHelper->getLineBetween('o', 'optional check list'));
    $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . $cliHelper->getLineBetween('s', 'server check list'));
    $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . $cliHelper->getLineBetween('wh', 'webserver headers check list'));
    $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . $cliHelper->getLineBetween('b', 'php benchmark'));
    $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . $cliHelper->getLineBetween('mb', 'mysql benchmark'));
    $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . $cliHelper->getLineBetween('r', 'scan php config and Recommendations'));
    $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . $cliHelper->getLineBetween('up', 'self update'));
    $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . $cliHelper->getLineBetween('v', 'version'));
    $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . $cliHelper->getLineBetween('help', 'help'));

    $cliHelper->printMessage($cliHelper->headerMessage('Usage Example'), ['fg_color' => 'cyan', 'bg_color' => 'black']);
    $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . 'php ' . $cliHelper->getFilename() . ' -l 10.x -c -o -s -i -r');
    $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . 'php ' . $cliHelper->getFilename() . ' -s -i -r');
    $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . 'php ' . $cliHelper->getFilename() . ' -wh "https://google.com"');
    $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . 'php ' . $cliHelper->getFilename() . ' -b -mb');
    $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . 'php ' . $cliHelper->getFilename() . ' up');
    $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . 'php ' . $cliHelper->getFilename() . ' -v');


    exit();
}
//Show Version
if ($cliHelper->getConfig('v')) {
    exit();
}

//Check for update and Download Update
if ($cliHelper->getConfig('up')) {
    $cliHelper->printMessage($cliHelper->headerMessage('Self Updating'), ['fg_color' => 'cyan', 'bg_color' => 'black']);

    $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . 'Checking for update ...');

    $check_for_update = (new Helper)->checkForUpdate();
    if ($check_for_update === true) {

        $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . 'A new version of the program is available.');
        $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . 'Downloading ...');
        if ((new Helper)->downloadUpdate()) {
            $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . 'New Version Downloaded successfully.', ['fg_color' => 'green', 'bg_color' => 'black']);
            $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . 'Please restart the program.', ['fg_color' => 'yellow', 'bg_color' => 'black']);
        } else {
            $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . 'Download Failed. Please try again.', ['fg_color' => 'red', 'bg_color' => 'black']);
        }
    } else {
        $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . 'You are using the latest version of the program.', ['fg_color' => 'yellow', 'bg_color' => 'black']);
    }

    exit();
}

// Check for mysql config
if ($MYSQL_CONFIG['username'] == 'USER_NAME_HERE' || $MYSQL_CONFIG['password'] == 'PASSWORD_HERE' || $MYSQL_CONFIG['db'] == 'DB_NAME_HERE') {
    $cliHelper->boxedMessage('Don`t forget to enter the mysql username and password in ' . basename($_SERVER["SCRIPT_FILENAME"]) . ' on line 32', ['fg_color' => 'yellow', 'bg_color' => 'black']);
    $cliHelper->emptyLine();
}

//Set Laravel Version
if ($cliHelper->getConfig('l', '10.x')) {
    //Check Laravel Version
    $laravel_version_select = (new ServerRequirements)->setLaravelVersion($cliHelper->getConfig('l', '10.x'));

    // $cliHelper->printMessage('Laravel '.$laravel_version_select.' version is selected');
}

//Infromations
if ($cliHelper->getConfig('i')) {
    $cliHelper->setBetweenTextSpace(5);
    $cliHelper->printMessage($cliHelper->headerMessage('Infromations'), ['fg_color' => 'cyan', 'bg_color' => 'black']);

    $info_cards = (new ServerRequirements)->ServerInfoCards();
    $cliHelper->printArrayMessage($info_cards);

    $cliHelper->unsetBetweenTextSpace();
}

//Laravel Check List
if ($cliHelper->getConfig('c')) {
    $cliHelper->setBetweenTextSpace(5);
    $cliHelper->printMessage($cliHelper->headerMessage('Laravel Check List' . ' ( Laravel Version : ' . $laravel_version_select . ')'), ['fg_color' => 'cyan', 'bg_color' => 'black']);
    $check_list = (new ServerRequirements)->LaravelRequirementsList($laravel_version_select);
    $check_list['Mysql Version'] = @(new ServerCheck($MYSQL_CONFIG))->GetMysqlVersion();
    $check_list['Folder Permissions'] = (new ServerCheck)->CheckFolderPermissions();

    if ($check_list['Disk Free Space'] >= 262144000) { //byte
        $check_list['Disk Free Space'] = (new Helper)->formatBytes($check_list['Disk Free Space']);
    } else {
        $check_list['Disk Free Space'] = 'N/A';
    }

    $cliHelper->printArrayMessage($check_list, true);

    $cliHelper->unsetBetweenTextSpace();
}

//Optional Check List
if ($cliHelper->getConfig('o')) {
    $cliHelper->setBetweenTextSpace(5);
    $cliHelper->printMessage($cliHelper->headerMessage('Optional Check List'), ['fg_color' => 'cyan', 'bg_color' => 'black']);

    $optional_list = (new ServerRequirements)->OptionalList();
    $cliHelper->printArrayMessage($optional_list, true);

    $cliHelper->unsetBetweenTextSpace();
}

//Server Information List
if ($cliHelper->getConfig('s')) {
    $cliHelper->setBetweenTextSpace(5);
    $cliHelper->printMessage($cliHelper->headerMessage('Server Information List'), ['fg_color' => 'cyan', 'bg_color' => 'black']);

    $serverinfo_list = (new ServerRequirements)->ServerInfoList();
    $cliHelper->printArrayMessage($serverinfo_list, true);

    $cliHelper->unsetBetweenTextSpace();
}

//PHP Benchmark
if ($cliHelper->getConfig('b')) {

    $cliHelper->printMessage($cliHelper->headerMessage('PHP Benchmark'), ['fg_color' => 'cyan', 'bg_color' => 'black']);

    $cliHelper->printMessage('Benchmark starting ...');
    $benchmark_results = (new Benchmark)->PhpBenchmark();

    if ($benchmark_results) {
        $cliHelper->setBetweenTextSpace(5);

        $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . $cliHelper->getLineBetween('Benchmark Type', 'wasted time (second)'));
        $cliHelper->emptyLine("\n");
        $cliHelper->printArrayMessage($benchmark_results['types'], true);

        $cliHelper->boxedMessage('Avg :' . number_format($benchmark_results['avg'], 4) . ' sec');
        $cliHelper->unsetBetweenTextSpace();
    } else {
        $cliHelper->printMessage('php Benchmark Failed.', ['fg_color' => 'red', 'bg_color' => 'black']);
    }
}

//Mysql Benchmark (Only for Mysql)
if ($cliHelper->getConfig('mb')) {
    $cliHelper->printMessage($cliHelper->headerMessage('Mysql Benchmark'), ['fg_color' => 'cyan', 'bg_color' => 'black']);

    $cliHelper->printMessage('Mysql Benchmark starting ...');
    $benchmark_results = (new Benchmark($MYSQL_CONFIG))->MysqlBenchmark();
    if ($benchmark_results) {
        $cliHelper->setBetweenTextSpace(5);

        $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . $cliHelper->getLineBetween('Benchmark Type', 'wasted time (second)'));
        $cliHelper->emptyLine("\n");
        $cliHelper->printArrayMessage($benchmark_results['types'], true);

        $cliHelper->boxedMessage($MYSQL_CONFIG['benchmark_insert'] . ' rows');

        $cliHelper->boxedMessage(number_format($benchmark_results['types']['insert'] / $MYSQL_CONFIG['benchmark_insert'], 4));
        $cliHelper->boxedMessage('Avg:' . number_format($benchmark_results['avg'], 4) . ' sec');

        $cliHelper->unsetBetweenTextSpace();
    } else {
        $cliHelper->printMessage('Error in Mysql Connection - Check Mysql Config on line 32', ['fg_color' => 'red', 'bg_color' => 'black']);
        $cliHelper->printMessage('Mysql Benchmark Failed. Please try again.', ['fg_color' => 'red', 'bg_color' => 'black']);
    }
}

//Recommendations List
if ($cliHelper->getConfig('r', false)) {
    $cliHelper->printMessage($cliHelper->headerMessage('Recommendations ...'), ['fg_color' => 'cyan', 'bg_color' => 'black']);

    $recommendations = (new Recommendations())->getRecommendations($MYSQL_CONFIG);

    if (isset($recommendations)) {

        foreach ($recommendations as $key => $value) {
            $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . '[ ' . $value['title'] . ' ]', ['fg_color' => 'light_blue', 'bg_color' => 'black']);
            $cliHelper->emptyLine();
            $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . 'Your value : ' . $value['your_value'], ['fg_color' => 'green', 'bg_color' => 'black']);
            $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . 'Production value : ' . $value['value'], ['fg_color' => 'yellow', 'bg_color' => 'black']);
            $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . 'Development value : ' . $value['dev_value'], ['fg_color' => 'light_purple', 'bg_color' => 'black']);
            $cliHelper->emptyLine("\n");
            $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . 'How to fix ? ', ['fg_color' => 'cyan', 'bg_color' => 'black']);
            $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . $cliHelper->getBoxLine('empty-left') . $value['how_to_fix'], ['fg_color' => 'white', 'bg_color' => 'black']);
            $cliHelper->emptyLine();
            $cliHelper->printMessage($cliHelper->getBoxLine('line', [], 50));
            $cliHelper->emptyLine();

            // break;
        }
    }
}

if ($cliHelper->getConfigWithArgv($argv, '-wh', false, null)) {

    $url = $cliHelper->getConfigWithArgv($argv, '-wh', true, null);
    $cliHelper->printMessage($cliHelper->headerMessage('Checking webserver headers ...'), ['fg_color' => 'cyan', 'bg_color' => 'black']);

    $webserver_responses = (new ServerCheck())->checkWebserverHeaders($url);
    if ($webserver_responses['success'] === false) {
        $cliHelper->printMessage($cliHelper->getBoxLine('empty-left') . $webserver_responses['message'], ['fg_color' => 'red', 'bg_color' => 'black']);
        exit();
    }

    $webserver_responses['headers'] = (new Recommendations())->getHeadersRecommendations($cliHelper, $webserver_responses['headers']);


    $webserver_headers = $webserver_responses['headers'];


    if ($webserver_headers) {
        $cliHelper->printArrayMessage($webserver_headers);
    }

    if (!empty($webserver_responses['cors'])) {
        $cliHelper->printMessage($cliHelper->headerMessage('Checking CORS webserver headers ...'), ['fg_color' => 'cyan', 'bg_color' => 'black']);

        $cliHelper->printArrayMessage($webserver_responses['cors']);
    }
}
