<?php
/*!
 * PHPTelebot
 * Version 1.0
 *
 * Copyright 2016, Radya
 * Released under the GPL-3.0
 */
class PHPTelebot
{
    /**
     * @var array
     */
    public static $getUpdates = [];
    /**
     * @var array
     */
    protected $_command = [];
    /**
     * @var array
     */
    protected $_onMessage = [];
    /**
     * Bot token.
     *
     * @var string
     */
    public static $token = '';
    /**
     * Bot username.
     *
     * @var string
     */
    protected static $username = '';

    /**
     * @var boolen
     */
    protected static $debug = true;

    /**
     * @var string
     */
    protected static $version = '1.0';

    /**
     * PHPTelebot Constructor.
     *
     * @param string $token    [bot token]
     * @param string $username [bot username]
     */
    public function __construct($token = '', $username = '')
    {
        // Check php version
        if (version_compare(phpversion(), '5.4', '<')) {
            die("Php version isn't high enough.\n");
        }

        // Check curl
        if (!function_exists('curl_version')) {
            die("cURL is NOT installed on this server.");
        }

        // Check bot token
        if (empty($token)) {
            die("Bot token should not be empty!\n");
        } 

        self::$token = $token;
        self::$username = $username;
    }

    /**
     * Command.
     *
     * @param string        $paterns
     * @param object|string $callback
     *
     * @return bool
     */
    public function cmd($paterns, $callback)
    {
        if ($paterns != '*') {
            $this->_command[$paterns] = $callback;
        }

        if (strrpos($paterns, '*') !== false) {
            $this->_onMessage['text'] = $callback;
        }

        return true;
    }
    /**
     * Type.
     *
     * @param string        $paterns
     * @param object|string $callback
     *
     * @return bool
     */
    public function on($types, $callback)
    {
        $types = explode('|', $types);
        foreach ($types as $type) {
            $this->_onMessage[$type] = $callback;
        }

        return true;
    }

    /**
     * Custom regex for command.
     *
     * @param string        $regex
     * @param object|string $callback
     *
     * @return bool
     */
    public function regex($regex, $callback)
    {
        $this->_command['customRegex:'.$regex] = $callback;

        return true;
    }

    /**
     * Run telebot.
     *
     * @return bool
     */
    public function run()
    {
        try {
            if (php_sapi_name() == 'cli') {
                echo 'PHPTelebot version '.self::$version;
                echo "\nMode\t: Long Polling\n";
                $options = getopt('q', ['quiet']);
                if (isset($options['q']) || isset($options['quiet'])) {
                    self::$debug = false;
                }
                echo "Debug\t: ".(self::$debug ? 'ON' : 'OFF')."\n";
                $this->longPoll();
            } else {
                $this->webhook();
            }

            return true;
        } catch (Exception $e) {
            echo $e->getMessage()."\n";

            return false;
        }
    }

    /**
     * Webhook.
     */
    private function webhook()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SERVER['CONTENT_TYPE'] == 'application/json') {
            self::$getUpdates = json_decode(file_get_contents('php://input'), true);
            echo $this->process();
        } else {
            throw new Exception('Access not allowed!');
        }
    }

    /**
     * Long Poll Mode.
     */
    private function longPoll()
    {
        $offset = 0;
        while (true) {
            $req = json_decode(Bot::send('getUpdates', ['offset' => $offset, 'timeout' => 30]), true);

            // Check error.
            if (isset($req['error_code'])) {
                if ($req['error_code'] == 404) {
                    $req['description'] = 'Incorrect bot token';
                }
                throw new Exception($req['description']);
            }

            if (!empty($req['result'])) {
                foreach ($req['result'] as $update) {
                    self::$getUpdates = $update;
                    $response = $this->process();

                    if (self::$debug) {
                        $line = "\n--------------------\n";
                        $outputFormat = "$line \033[0;33m%s\033[0m \033[0;32m$update[update_id]\033[0m $line%s";
                        echo sprintf($outputFormat, 'Query ID :', json_encode($update));
                        echo sprintf($outputFormat, 'Response for :', isset($response) ? $response : '--NO RESPONSE--');
                    }
                    $offset = $update['update_id'] + 1;
                }
            }

            // Delay 1 second
            sleep(1);
        }
    }

    /**
     * Process the message.
     *
     * @return string
     */
    private function process()
    {
        $get = self::$getUpdates;
        $run = false;
        $customRegex = false;

        if (Bot::type() == 'text') {
            foreach ($this->_command as $cmd => $call) {
                if (substr($cmd, 0, 12) == 'customRegex:') {
                    $regex = substr($cmd, 12);
                    // Remove bot username from command
                     if (self::$username != '') {
                         $get['message']['text'] = preg_replace('/^\/(.*)@'.self::$username.'(.*)/', '/$1$2', $get['message']['text']);
                     }
                    $customRegex = true;
                } else {
                    if (self::$username != '') {
                        $username = '(?:@'.self::$username.')?';
                    } else {
                        $username = '';
                    }
                    $regex = '/^'.addcslashes($cmd, '/\+*?[^]$(){}=!<>:-').$username.'(?:\s(.*))?$/';
                }
                if ($get['message']['text'] != '*' && preg_match($regex, $get['message']['text'], $matches)) {
                    $run = true;
                    if ($customRegex) {
                        $param = [$matches];
                    } else {
                        $param = isset($matches[1]) ? $matches[1] : '';
                    }
                    break;
                }
            }
        }

        if (isset($this->_onMessage) && $run === false) {
            if (in_array(Bot::type(), array_keys($this->_onMessage))) {
                $run = true;
                $call = $this->_onMessage[Bot::type()];
            } elseif (isset($this->_onMessage['*'])) {
                $run = true;
                $call = $this->_onMessage['*'];
            }

            if ($run) {
                switch (Bot::type()) {
                    case 'callback':
                        $param = $get['callback_query']['data'];
                    break;
                    case 'inline':
                        $param = $get['inline_query']['query'];
                    break;
                    case 'location':
                        $param = [$get['message']['location']['longitude'], $get['message']['location']['latitude']];
                    break;
                    case 'text':
                        $param = $get['message']['text'];
                    break;
                    default:
                        $param = '';
                    break;
                }
            }
        }

        if ($run) {
            if (is_callable($call)) {
                if (!is_array($param)) {
                    $count = count((new ReflectionFunction($call))->getParameters());
                    if ($count > 1) {
                        $param = array_pad(explode(' ', $param, $count), $count, '');
                    } else {
                        $param = [$param];
                    }
                }

                return call_user_func_array($call, $param);
            } else {
                if (!isset($get['inline_query'])) {
                    return Bot::send('sendMessage', ['text' => $call]);
                }
            }
        }
    }
}

require_once __DIR__.'/Bot.php';
