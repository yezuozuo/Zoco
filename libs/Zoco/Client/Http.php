<?php

namespace Zoco\Client;

/**
 * Class Http
 *
 * @package Zoco\Client
 */
class Http {
    /**
     * @var
     */
    public $host;

    /**
     * @var int
     */
    public $port;

    /**
     * @var
     */
    public $path;

    /**
     * @var
     */
    public $method;

    /**
     * @var string
     */
    public $postData = '';

    /**
     * @var array
     */
    public $cookies = array();

    /**
     * @var
     */
    public $referer;

    /**
     * @var string
     */
    public $accept = 'text/xml,application/xml,application/xhtml+xml,text/html,text/plain,image/png,image/jpeg,image/gif,*/*';

    /**
     * @var string
     */
    public $acceptEncoding = 'gzip';

    /**
     * @var string
     */
    public $acceptLanguage = 'en-us';

    /**
     * @var string
     */
    public $userAgent = 'Zoco HttpClient';

    /**
     * options
     */
    /**
     * @var int
     */
    public $timeout = 20;


    /**
     * @var bool
     */
    public $useGzip = true;

    /**
     * If true,received cookies are placed in the $this->cookies array ready for the next request
     *
     * @var
     */
    public $persistCookie;

    /**
     * Note: This currently ignores the cookie path (and time) completely. Time is not important,
     * but path could possibly lead to security problems.
     */

    /**
     * For each request, sends path of last request as referer
     *
     * @var bool
     */
    public $persistReferers = true;

    /**
     * @var bool
     */
    public $debug = true;

    /**
     * Automatically redirect if Location or URI header is found
     *
     * @var bool
     */
    public $handleRedirects = true;

    /**
     * @var int
     */
    public $maxRedirects = 5;

    /**
     * If true, stops receiving once headers have been read.
     *
     * @var bool
     */
    public $headersOnly = false;

    /**
     * Basic authorization variables
     *
     * @var
     */
    public $username;

    /**
     * @var
     */
    public $password;

    /**
     * Response
     */
    /**
     * @var
     */
    public $status;

    /**
     * @var array
     */
    public $headers = array();

    /**
     * @var string
     */
    public $content = '';

    /**
     * @var
     */
    public $errorMsg;

    /**
     * @var int
     */
    public $redirectCount = 0;

    /**
     * @var string
     */
    public $cookieHost = '';

    /**
     * @param     $host
     * @param int $port
     */
    public function __construct($host, $port = 80) {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * @param $url
     * @return bool|string
     */
    static public function quickGet($url) {
        $bits = parse_url($url);
        $host = $bits['host'];
        $port = isset($bits['port']) ? $bits['port'] : 80;
        $path = isset($bits['path']) ? $bits['path'] : '/';
        if (isset($bits['query'])) {
            $path .= '?' . $bits['query'];
        }
        $client = new self($host, $port);
        if (!$client->get($path)) {
            return false;
        } else {
            return $client->getContent();
        }
    }

    /**
     * @param            $path
     * @param bool|false $data
     * @return bool
     */
    public function get($path, $data = false) {
        $this->path   = $path;
        $this->method = 'GET';
        if ($data) {
            $this->path .= '?' . $this->buildQueryString($data);
        }

        return $this->doRequest();
    }

    /**
     * @param $data
     * @return string
     */
    private function buildQueryString($data) {
        $queryString = '';
        if (is_array($data)) {
            /**
             * change data into post able data
             */
            foreach ($data as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $val2) {
                        $queryString .= urlencode($key) . '=' . urlencode($val2) . '&';
                    }
                } else {
                    $queryString .= urlencode($key) . '=' . urlencode($val) . '&';
                }
            }
            $queryString = substr($queryString, 0, -1);
        } else {
            $queryString = $data;
        }

        return $queryString;
    }

    /**
     * @return bool
     */
    private function doRequest() {
        /**
         * Performs the actual HTTP request, returning true or false depending on result
         */
        if (!$fp = @fsockopen($this->host, $this->port, $errno, $errStr, $this->timeout)) {
            /**
             * set error message
             */
            switch ($errno) {
                case -3:
                    $this->errorMsg = 'Socket creation failed (-3)';
                    break;
                case -4:
                    $this->errorMsg = 'DNS lookup failure (-4)';
                    break;
                case -5:
                    $this->errorMsg = 'Connection refused or timed out (-5)';
                    break;
                default:
                    $this->errorMsg = 'Connection failed (' . $errno . ')';
                    $this->errorMsg .= ' ' . $errStr;
                    $this->debug($this->errorMsg);
                    break;
            }

            return false;
        }

        socket_set_timeout($fp, $this->timeout);
        $request = $this->buildRequest();
        $this->debug('Request', $request);
        fwrite($fp, $request);
        $this->headers  = array();
        $this->content  = '';
        $this->errorMsg = '';

        /**
         * flags
         */
        $inHeaders = true;
        $atStart   = true;

        /**
         * Now start reading back the response
         */
        while (!feof($fp)) {
            $line = fgets($fp, 4096);
            if ($atStart) {
                /**
                 * Deal with first line of returned data
                 */
                $atStart = false;
                if (!preg_match('/HTTP\/(\\d\\.\\d)\\s*(\\d+)\\s*(.*)/', $line, $match)) {
                    $this->errorMsg = "Status code line invalid: " . htmlentities($line);
                    $this->debug($this->errorMsg);

                    return false;
                }

                $this->status = $match[2];
                $this->debug(trim($line));
                continue;
            }
            if ($inHeaders) {
                if (trim($line) == '') {
                    $inHeaders = false;
                    $this->debug('Received Headers', $this->headers);
                    if ($this->headersOnly) {
                        /**
                         * Skip the rest of the input
                         */
                        break;
                    }
                    continue;
                }
                if (!preg_match('/([^:]+):\\s*(.*)/', $line, $match)) {
                    /**
                     * Skip to the next header
                     */
                    continue;
                }
                $key   = strtolower(trim($match[1]));
                $value = trim($match[2]);

                /**
                 * Deal with the possibility of multiple headers of same name
                 */
                if (isset($this->headers[$key])) {
                    if (is_array($this->headers[$key])) {
                        $this->headers[$key][] = $value;
                    } else {
                        $this->headers[$key] = array($this->headers[$key], $value);
                    }
                } else {
                    $this->headers[$key] = $value;
                }
                continue;
            }
            /**
             * We're not in the headers, so append the line to the contents
             */
            $this->content .= $line;
        }
        fclose($fp);

        /**
         * If data is compressed, unCompress it
         */
        if (isset($this->headers['content-encoding']) && $this->headers['content-encoding'] == 'gzip') {
            $this->debug('Content is gzip encoded, unzipping it');
            $this->content = substr($this->content, 10);
            $this->content = gzinflate($this->content);
        }

        /**
         * If $persistCookies, deal with any cookies
         */
        if ($this->persistCookie && isset($this->headers['set-cookie']) && $this->host == $this->cookieHost) {
            $cookies = $this->headers['set-cookie'];
            if (!is_array($cookies)) {
                $cookies = array($cookies);
            }
            foreach ($cookies as $cookie) {
                if (preg_match('/([^=]+)=([^;]+);/', $cookie, $match)) {
                    $this->cookies[$match[1]] = $match[2];
                }
            }
            /**
             * Record domain of cookies for security reasons
             */
            $this->cookieHost = $this->host;
        }

        /**
         * If $persistReferers, set the referer ready for the next request
         */
        if ($this->persistReferers) {
            $requestUrl = $this->getRequestUrl();
            $this->debug('Persisting referer: ' . $requestUrl);
            $this->referer = $requestUrl;
        }

        /**
         * Finally, if handleRedirects and a redirect is sent, do that
         */
        if ($this->handleRedirects) {
            if (++$this->redirectCount >= $this->maxRedirects) {
                $this->errorMsg = 'Number of redirects exceeded maximum (' . $this->maxRedirects . ')';
                $this->debug($this->errorMsg);
                $this->redirectCount = 0;

                return false;
            }
            $location = isset($this->headers['location']) ? $this->headers['location'] : '';
            $uri      = isset($this->headers['uri']) ? $this->headers['uri'] : '';
            if ($location || $uri) {
                $url = parse_url($location . $uri);

                /**
                 * This will FAIL if redirect is to a different site
                 */

                return $this->get($url['path']);
            }
        }

        return true;
    }

    /**
     * @param            $msg
     * @param bool|false $object
     */
    public function debug($msg, $object = false) {
        if ($this->debug) {
            echo '<div style="border: 1px solid red; padding: 0.5em; margin: 0.5em;"><strong>HttpClient Debug:</strong>' . $msg;

            if ($object) {
                ob_start();
                print_r($object);
                $content = htmlentities(ob_get_contents());
                ob_end_clean();
                echo '<pre>' . $content . '</pre>';
            }

            echo '</div>';
        }
    }

    /**
     * @return string
     */
    public function buildRequest() {
        $headers   = array();
        $headers[] = "{$this->method} {$this->path} HTTP/1.0";
        $headers[] = "Host: {$this->host}";
        $headers[] = "User-Agent: {$this->userAgent}";
        $headers[] = "Accept: {$this->accept}";
        if ($this->useGzip) {
            $headers[] = "Accept-encoding: {$this->acceptEncoding}";
        }
        $headers[] = "Accept-language: {$this->acceptLanguage}";
        if ($this->referer) {
            $headers[] = "Referer: {$this->referer}";
        }
        if ($this->cookies) {
            $cookie = 'Cookie: ';
            foreach ($this->cookies as $key => $value) {
                $cookie .= "$key=$value";
            }
            $headers[] = $cookie;
        }
        if ($this->username && $this->password) {
            $headers[] = 'Authorization: BASIC ' . base64_encode($this->username . ':' . $this->password);
        }
        if ($this->postData) {
            $headers[] = 'Content-Type: application\x-www-form-urlencoded';
            $headers[] = 'Content-Length: ' . strlen($this->postData);
        }
        $request = implode("\r\n", $headers) . "\r\n\r\n" . $this->postData;

        return $request;
    }

    /**
     * @return string
     */
    public function getRequestUrl() {
        $url = 'http://' . $this->host;
        if ($this->port != 80) {
            $url .= ':' . $this->port;
        }
        $url .= $this->path;

        return $url;
    }

    /**
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * @param $url
     * @param $data
     * @return bool|string
     */
    static public function quickPost($url, $data) {
        $bits = parse_url($url);
        $host = $bits['host'];
        $port = isset($bits['port']) ? $bits['port'] : 80;
        $path = isset($bits['path']) ? $bits['path'] : '/';
        if (isset($bits['query'])) {
            $path .= '?' . $bits['query'];
        }
        $client = new self($host, $port);

        if (!$client->post($path, $data)) {
            return false;
        } else {
            return $client->getContent();
        }
    }

    /**
     * @param $path
     * @param $data
     * @return bool
     */
    public function post($path, $data) {
        $this->path     = $path;
        $this->method   = 'POST';
        $this->postData = $this->buildQueryString($data);

        return $this->doRequest();
    }

    /**
     * @return mixed
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * @param $header
     * @return bool
     */
    public function getHeader($header) {
        $header = strtolower($header);
        if (isset($this->headers[$header])) {
            return $this->headers[$header];
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getError() {
        return $this->errorMsg;
    }

    /**
     * @return array
     */
    public function getCookies() {
        return $this->cookies;
    }

    /**
     * @param $string
     */
    public function setUserAgent($string) {
        $this->userAgent = $string;
    }

    /**
     * @param $username
     * @param $password
     */
    public function setAuthorization($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @param $array
     */
    public function setCookie($array) {
        $this->cookies = $array;
    }

    /**
     * @param $boolean
     */
    public function useGzip($boolean) {
        $this->useGzip = $boolean;
    }

    /**
     * @param $boolean
     */
    public function setPersistCookies($boolean) {
        $this->persistCookie = $boolean;
    }

    /**
     * @param $boolean
     */
    public function setPersistReferers($boolean) {
        $this->persistReferers = $boolean;
    }

    /**
     * @param $boolean
     */
    public function setHandleReferers($boolean) {
        $this->handleRedirects = $boolean;
    }

    /**
     * @param $num
     */
    public function setMaxRedirects($num) {
        $this->maxRedirects = $num;
    }

    /**
     * @param $boolean
     */
    public function setHeadersOnly($boolean) {
        $this->headersOnly = $boolean;
    }

    /**
     * @param $boolean
     */
    public function setDebug($boolean) {
        $this->debug = $boolean;
    }
}