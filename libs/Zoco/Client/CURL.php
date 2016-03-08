<?php

namespace Zoco\Client;

/**
 * CURL HTTP客户端
 * Class CURL
 *
 * @package Zoco\Client
 */
class CURL {
    /**
     * @var
     */
    public $info;
    /**
     * @var
     */
    public $url;
    /**
     * @var bool
     */
    public $debug = false;
    /**
     * @var
     */
    public $errMsg;
    /**
     * @var
     */
    public $errCode;
    /**
     * @var
     */
    public $httpCode;
    /**
     * CURL handler
     *
     * @var
     */
    protected $ch;
    /**
     * @var string
     */
    protected $userAgent = "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:28.0) Gecko/20100101 Firefox/28.0";
    /**
     * header
     *
     * @var array
     */
    protected $reqHeader = array();

    /**
     * @param bool|false $debug
     */
    public function __construct($debug = false) {
        $this->debug = $debug;
        $this->init();
    }

    /**
     * init curl session
     */
    public function init() {
        /**
         * initialize curl handle
         */
        $this->ch = curl_init();

        /**
         * set various options
         */
        /**
         * set error in case http return code bigger than 300
         */
        curl_setopt($this->ch, CURLOPT_FAILONERROR, true);

        /**
         * allow redirects
         */
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);

        /**
         * use gzip if possible
         */
        curl_setopt($this->ch, CURLOPT_ENCODING, 'gzip deflate');

        /**
         * do not verify ssl
         * this is important for windows
         * as well for being able to access pages with non valid cert
         */
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
    }

    /**
     * set username/pass for basic http auth
     *
     * @param $username
     * @param $password
     */
    public function setCredentials($username, $password) {
        curl_setopt($this->ch, CURLOPT_USERPWD, "$username:$password");
    }

    /**
     * @param $refererUrl
     */
    public function setReferer($refererUrl) {
        curl_setopt($this->ch, CURLOPT_REFERER, $refererUrl);
    }

    /**
     * @param null $userAgent
     */
    public function setUserAgent($userAgent = null) {
        $this->userAgent = $userAgent;
        curl_setopt($this->ch, CURLOPT_USERAGENT, $userAgent);
    }

    /**
     * Set to receive output headers in all output functions
     *
     * @param $value
     */
    public function includeResponseHeaders($value) {
        curl_setopt($this->ch, CURLOPT_HEADER, $value);
    }

    /**
     * Set proxy to use for each curl request
     *
     * @param $proxy
     */
    public function setProxy($proxy) {
        curl_setopt($this->ch, CURLOPT_PROXY, $proxy);
    }

    /**
     * set ssl
     */
    public function setSSL() {
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
    }

    /**
     * send post data to target url
     * return data returned from url or false if error occur
     *
     * @param      $url
     * @param      $postData
     * @param null $ip
     * @param int  $timeout
     * @return bool|mixed
     */
    public function post($url, $postData, $ip = null, $timeout = 10) {
        /**
         * set various curl options first
         */

        /**
         * set url to post to
         */
        curl_setopt($this->ch, CURLOPT_URL, $url);

        /**
         * return into a variable rather than displaying it
         */
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

        /**
         * bind to specific ip address if it is sent trough arguments
         */
        if ($ip) {
            if ($this->debug) {
                echo "Binding to ip $ip\n";
            }
            curl_setopt($this->ch, CURLOPT_INTERFACE, $ip);
        }

        /**
         * set curl function timeout to $timeout
         */
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);

        /**
         * set method to post
         */
        curl_setopt($this->ch, CURLOPT_POST, true);

        /**
         * generate post string
         */
        $postArray = array();
        if (is_array($postData)) {
            foreach ($postData as $key => $value) {
                $postArray[] = urlencode($key) . '=' . urlencode($value);
            }
            $postString = implode('&', $postArray);

            if ($this->debug) {
                echo "Url: $url\nPost String: $postString\n";
            }
        } else {
            $postString = $postData;
        }

        /**
         * set post string
         */
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postString);

        return $this->execute();
    }

    /**
     * @return bool|mixed
     */
    protected function execute() {
        /**
         * finally send curl request
         */
        $result     = curl_exec($this->ch);
        $this->info = curl_getinfo($this->ch);
        if ($this->info) {
            $this->httpCode = $this->info['http_code'];
        }
        if (curl_errno($this->ch)) {
            $this->errCode = curl_errno($this->ch);
            $this->errMsg  = curl_error($this->ch) . '[' . $this->errCode . ']';
            if ($this->debug) {
                \Zoco::$php->log->warn($this->errMsg);
            }

            return false;
        } else {
            return $result;
        }
    }

    /**
     * fetch data from target URL
     * return data returned from url or false if error occur
     *
     * @param      $url
     * @param null $ip
     * @param int  $timeout
     * @return bool|mixed
     */
    public function get($url, $ip = null, $timeout = 5) {
        /**
         * set url to post to
         */
        curl_setopt($this->ch, CURLOPT_URL, $url);

        /**
         * set method to get
         */
        curl_setopt($this->ch, CURLOPT_HTTPGET, true);

        /**
         * return into a variable rather than displaying it
         */
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

        if (empty($this->reqHeader['User-Agent'])) {
            curl_setopt($this->ch, CURLOPT_USERAGENT, $this->userAgent);
        }

        $this->url = $url;

        if ($this->reqHeader) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->reqHeader);
        }

        /**
         * bind to specific ip address if it is sent trough arguments
         */
        if ($ip) {
            if ($this->debug) {
                echo "Binding to ip $ip\n";
            }
            curl_setopt($this->ch, CURLOPT_TIMEOUT, $ip);
        }

        /**
         * set curl function timeout to $timeout
         */
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);

        return $this->execute();
    }

    /**
     * Fetch data from target URL
     * and store it directly to file
     *
     * @param      $url
     * @param      $fp
     * @param null $ip
     * @param int  $timeout
     * @return bool|mixed
     */
    public function download($url, $fp, $ip = null, $timeout = 5) {
        /**
         * set url to post to
         */
        curl_setopt($this->ch, CURLOPT_URL, $url);

        /**
         * set method to get
         */
        curl_setopt($this->ch, CURLOPT_HTTPGET, true);

        /**
         * store data into file rather than displaying it
         */
        curl_setopt($this->ch, CURLOPT_FILE, $fp);

        /**
         * bind to specific ip address if it is sent trough arguments
         */
        if ($ip) {
            if ($this->debug) {
                echo "Binding to ip $ip\n";
            }
            /**
             * set curl function timeout to $timeout
             */
            curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);

            return $this->execute();
        }

        return false;
    }

    /**
     * Send multiPart post data to the target URL
     * return data returned from url or false if error occur
     *
     * @param       $url
     * @param       $postData
     * @param array $fileFieldArray
     * @param null  $ip
     * @param int   $timeout
     * @return bool|mixed
     */
    public function sendMultiPartPostData($url, $postData, $fileFieldArray = array(), $ip = null, $timeout = 30) {
        /**
         * set various curl options first
         */

        /**
         * set url to post to
         */
        curl_setopt($this->ch, CURLOPT_URL, $url);

        /**
         * return into a variable rather than displaying it
         */
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

        /**
         * bind to specific ip address if it is sent trough arguments
         */
        if ($ip) {
            if ($this->debug) {
                echo "Binding to ip $ip\n";
            }
            curl_setopt($this->ch, CURLOPT_INTERFACE, $ip);
        }

        /**
         * set curl function timeout to $timeout
         */
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $timeout);

        /**
         * set method to post
         */
        curl_setopt($this->ch, CURLOPT_POST, true);

        /**
         * disable Expect header
         * hack to make it working
         */
        $headers = array("Expect: ");
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);

        /**
         * generate post string
         */
        $postArray       = array();
        $postStringArray = array();

        if (!is_array($postArray)) {
            return false;
        }

        foreach ($postData as $key => $value) {
            $postArray[$key]   = $value;
            $postStringArray[] = urlencode($key) . '=' . urlencode($value);
        }

        $postString = implode('&', $postStringArray);

        if ($this->debug) {
            echo "Post String: $postString\n";
        }

        /**
         * set multiPart form data - file array field-value pairs
         */
        if (!empty($fileFieldArray)) {
            foreach ($fileFieldArray as $varName => $varValue) {
                /**
                 * windows
                 */
                if (strpos(PHP_OS, 'WIN') !== false) {
                    $varValue = str_replace("/", "\\", $varValue);
                }
                $fileFieldArray[$varName] = '@' . $varValue;
            }
        }

        /**
         * set post data
         */
        $resultPost = array_merge($postData, $fileFieldArray);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $resultPost);

        $result = curl_exec($this->ch);

        if (curl_errno($this->ch)) {
            if ($this->debug) {
                echo "Error occur in curl\n";
                echo "Error numbers: " . curl_errno($this->ch) . "\n";
                echo "Error message: " . curl_error($this->ch) . "\n";
            }

            return false;
        } else {
            return $result;
        }
    }

    /**
     * Set file location where cookie data will be stored and send on each new request
     *
     * @param $cookieFile
     */
    public function storeCookies($cookieFile) {
        /**
         * use cookies on each request (cookies stored in $cookie_file)
         */
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $cookieFile);
    }

    /**
     * @param $k
     * @param $v
     */
    public function setHeader($k, $v) {
        $this->reqHeader[$k] = $v;
    }

    /**
     * @param array $header
     */
    public function addHeaders(array $header) {
        $this->reqHeader = array_merge($this->reqHeader, $header);
    }

    /**
     * set custom cookie
     *
     * @param $cookie
     */
    public function setCookie($cookie) {
        curl_setopt($this->ch, CURLOPT_COOKIE, $cookie);
    }

    /**
     * Get last URL info
     * useful when original url was redirected to other location
     *
     * @return mixed
     */
    public function getEffectiveUrl() {
        return curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL);
    }

    /**
     * get http response code
     *
     * @return mixed
     */
    public function getHttpResponseCode() {
        return curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
    }

    /**
     * close curl session and free resource
     * Usually no need to call this function directly
     * in case you do you have to call init() to recreate curl
     */
    public function close() {
        /**
         * close curl session and free up resources
         */
        curl_close($this->ch);
    }
}