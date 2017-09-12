<?php
/**
 * This file is part of the kerio-api-php.
 *
 * Copyright (c) Kerio Technologies s.r.o.
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code
 * or visit Developer Zone. (http://www.kerio.com/developers)
 *
 * Do not modify this source code.
 * Any changes may be overwritten by a new version.
 */

namespace Kerio\Api\Http;

/**
 * Kerio API Class.
 *
 * This is the main class.
 *
 * Example:
 * <code>
 * <?php
 * use Kerio\Api\Http\KerioApi;
 *
 * class MyApi extents KerioApi {
 *
 *     public function __construct($name, $vendor, $version) {
 *         parent::__construct($name, $vendor, $version);
 *     }
 *
 *     public function getFoo() {
 *         return $this->sendRequest('...');
 *     }
 * }
 * ?>
 * </code>
 *
 * @copyright Copyright &copy; 2012-2012 Kerio Technologies s.r.o.
 * @license http://www.kerio.com/developers/license/sdk-agreement
 * @version 1.4.0.234
 */
class KerioApi implements KerioApiInterface
{
    /**
     * End-Line format
     */
    const CRLF = "\r\n";

    /**
     * HTTP server status
     */
    const HTTP_SERVER_OK = 200;

    /**
     * Library name
     * @var string
     */
    public $name = 'Kerio APIs Client Library for PHP';

    /**
     * Library version
     * @var string
     */
    public $version = '1.4.0.234';

    /**
     * Debug mode
     * @var bool
     */
    private $debug = false;

    /**
     * Unique id used in request
     * @var int
     */
    private $requestId = 0;

    /**
     * Hostname
     * @var string
     */
    protected $hostname = '';

    /**
     * X-Token
     * @var string
     */
    protected $token = '';

    /**
     * Cookies
     * @var string
     */
    protected $cookies = '';

    /**
     * Application details
     * @var array
     */
    protected $application = array('name' => '', 'vendor' => '', 'version' => '');

    /**
     * JSON-RPC settings
     * @var array
     */
    protected $jsonRpc = array('version' => '', 'port' => '', 'api' => '');

    /**
     * HTTP headers
     * @var array
     */
    protected $headers = array();

    /**
     * Socket handler
     * @var resource
     */
    private $socketHandler = '';

    /**
     * Socket timeout
     * @var int
     */
    private $timeout = '';

    /**
     * Class constructor.
     *
     * @param string $name Application name
     * @param string $vendor Application vendor
     * @param string $version Application version
     *
     * @throws KerioApiException
     */
    public function __construct($name, $vendor, $version)
    {
        $this->checkPhpEnvironment();
        $this->setApplication($name, $vendor, $version);
        $this->setJsonRpc($this->jsonRpc['version'], $this->jsonRpc['port'], $this->jsonRpc['api']);
    }

    /**
     * Check PHP environment.
     *
     * @return void
     */
    private function checkPhpEnvironment()
    {
        if (version_compare(PHP_VERSION, '5.1.0', '<')) {
            die(
                sprintf(
                    '<h1>kerio-api-php error</h1>Minimum PHP version required is 5.1.0.'
                    . ' Your installation is %s.<br>Please, upgrade your PHP installation.',
                    phpversion()
                )
            );
        }
        if (false === function_exists('openssl_open')) {
            die(
                '<h1>kerio-api-php error</h1>Your PHP installation does not have OpenSSL enabled.<br>'
                . 'To configure OpenSSL support in PHP, please edit your php.ini config file and enable'
                . ' row with php_openssl module, e.g. extension=php_openssl.dll<br>For more information'
                . ' see <a href="http://www.php.net/manual/en/openssl.installation.php">'
                . 'http://www.php.net/manual/en/openssl.installation.php</a>.'
            );
        }
        if (false === function_exists('json_decode')) {
            die(
                '<h1>kerio-api-php error</h1>Your PHP installation does not have JSON enabled.<br>'
                . 'To configure JSON support in PHP, please edit your php.ini config file and enable'
                . ' row with php_json module, e.g. extension=php_json.dll<br>For more information see'
                . ' <a href="http://www.php.net/manual/en/json.installation.php">'
                . 'http://www.php.net/manual/en/json.installation.php</a>.'
            );
        }
    }

    /**
     * Set application to identify on server.
     *
     * @param string $name Application name
     * @param string $vendor Vendor
     * @param string $version Version
     *
     * @throws KerioApiException
     *
     * @return void
     */
    private function setApplication($name, $vendor, $version)
    {
        if (empty($name) && empty($vendor) && empty($version)) {
            throw new KerioApiException('Application not defined.');
        } else {
            $this->debug(sprintf("Registering application '%s' by '%s' version '%s'<br>", $name, $vendor, $version));
            $this->application = array(
                'name' => $name,
                'vendor' => $vendor,
                'version' => $version
            );
        }
    }

    /**
     * Get application detail.
     *
     * @return array Application details
     */
    final public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set JSON-RPC settings.
     *
     * @see KerioApiInterface::setJsonRpc()
     *
     * @param string $version JSON-RPC version
     * @param int $port JSON-RPC port
     * @param string $api JSON-RPC URI
     *
     * @throws KerioApiException
     *
     * @return void
     */
    final public function setJsonRpc($version, $port, $api)
    {
        if (empty($version) && empty($port) && empty($api)) {
            throw new KerioApiException('JSON-RPC not defined.');
        } else {
            $this->debug(sprintf("Registering JSON-RPC %s on %s using port %d", $version, $api, $port));
            $this->jsonRpc = array(
                'version' => $version,
                'port' => $port,
                'api' => $api
            );
        }
    }

    /**
     * Get JSON-RPC settings.
     *
     * @return array JSON-RPC settings
     */
    final public function getJsonRpc()
    {
        return $this->jsonRpc;
    }

    /**
     * Enable or disable of displaying debug messages.
     *
     * @param bool $boolean
     *
     * @return void
     */
    final public function setDebug($boolean)
    {
        $this->debug = (bool) $boolean;
    }

    /**
     * Get debug settings.
     *
     * @return bool
     */
    final public function getDebug()
    {
        return $this->debug;
    }

    /**
     * Display a message if debug is true.
     *
     * @param string $message Message
     * @param string $css CSS class
     *
     * @return void
     */
    public function debug($message, $css = 'debug')
    {
        if ($this->debug) {
            printf('<div class="%s">%s</div>%s', $css, $message, "\n");
        }
    }

    /**
     * Get product API version.
     *
     * @return int API version
     */
    public function getApiVersion()
    {
        $method = 'Version.getApiVersion';
        $response = $this->sendRequest($method);
        return $response['apiVersion'];
    }

    /**
     * Login method.
     *
     * @see KerioApiInterface::login()
     *
     * @param string $hostname Hostname
     * @param string $username Username
     * @param string $password Password
     *
     * @throws KerioApiException
     *
     * @return array Result
     */
    public function login($hostname, $username, $password)
    {
        $this->clean();

        if (empty($hostname)) {
            throw new KerioApiException('Cannot login. Hostname not set.');
        } elseif (empty($username)) {
            throw new KerioApiException('Cannot login. Username not set.');
        } elseif (empty($this->application)) {
            throw new KerioApiException('Cannot login. Application not defined.');
        }

        $this->setHostname($hostname);

        $method = 'Session.login';
        $params = array(
            'userName' => $username,
            'password' => $password,
            'application' => $this->application
        );

        $response = $this->sendRequest($method, $params);
        return $response;
    }

    /**
     * Logout method.
     *
     * @see KerioApiInterface::logout()
     *
     * @return array Result
     */
    public function logout()
    {
        $method = 'Session.logout';
        $response = $this->sendRequest($method);
        $this->clean();
        return $response;
    }

    /**
     * Clean data.
     *
     * @return void
     */
    public function clean()
    {
        if ($this->token) {
            $this->debug('Removing X-Token.');
            $this->token = '';
        }
        if ($this->cookies) {
            $this->debug('Removing Cookies.');
            $this->cookies = '';
        }
        $this->hostname = '';
        $this->socketHandler = '';
    }

    /**
     * Get full HTTP request.
     *
     * @param string $method HTTP method [POST,GET,PUT]
     * @param string $body HTTP body
     *
     * @throws KerioApiException
     *
     * @return string HTTP request
     */
    protected function getHttpRequest($method, $body)
    {
        /* Clean data */
        $this->headers = array();
        $fullRequest = '';

        /* Prepare headers and get request body*/
        switch ($method) {
            case 'POST': // common requests
                $bodyRequest = $this->getHttpPostRequest($body);
                break;
            case 'GET': // download
                $bodyRequest = $this->getHttpGetRequest($body);
                break;
            case 'PUT': // upload
                $bodyRequest = $this->getHttpPutRequest($body);
                break;
            default:
                throw new KerioApiException('Cannot send request, unknown method.');
        }

        /* Add port to headers if non-default is used */
        $port = ($this->jsonRpc['port'] == 443)
            ? ''
            : sprintf(':%d', $this->jsonRpc['port']);

        /* Set common headers */
        $this->headers['Host:'] = sprintf('%s%s', $this->hostname, $port);
        $this->headers['Content-Length:'] = strlen($bodyRequest);
        $this->headers['Connection:'] = 'close';

        /* Set X-Token and Cookies */
        if ($this->token) {
            $this->headers['Cookie:'] = $this->cookies;
            $this->headers['X-Token:'] = $this->token;
        }

        /* Build request */
        foreach ($this->headers as $item => $value) {
            $fullRequest .= $item . ' ' . $value . self::CRLF;
        }
        $fullRequest .= self::CRLF;
        $fullRequest .= $bodyRequest;

        /* Return */
        return $fullRequest;
    }

    /**
     * Get headers for POST request.
     *
     * @param string $data Request body
     *
     * @return string Request body
     */
    protected function getHttpPostRequest($data)
    {
        $this->headers['POST'] = sprintf('%s HTTP/1.1', $this->jsonRpc['api']);
        $this->headers['Accept:'] = 'application/json-rpc';
        $this->headers['Content-Type:'] = 'application/json-rpc; charset=UTF-8';
        $this->headers['User-Agent:'] = sprintf('%s/%s', $this->name, $this->version);

        return str_replace(array("\r", "\r\n", "\n", "\t"), '', $data) . self::CRLF;
    }

    /**
     * Get headers for GET request.
     *
     * @param string $data Request body
     *
     * @return string Request body
     */
    protected function getHttpGetRequest($data)
    {
        $this->headers['GET'] = sprintf('%s HTTP/1.1', $data);
        $this->headers['Accept:'] = '*/*';

        return $data . self::CRLF;
    }

    /**
     * Get headers for PUT request.
     *
     * @param string $data Request body
     *
     * @return string Request body
     */
    protected function getHttpPutRequest($data)
    {
        $boundary = sprintf('---------------------%s', substr(md5(rand(0, 32000)), 0, 10));

        $this->headers['POST'] = sprintf('%s%s HTTP/1.1', $this->jsonRpc['api'], 'upload/');
        $this->headers['Accept:'] = '*/*';
        $this->headers['Content-Type:'] = sprintf('multipart/form-data; boundary=%s', $boundary);

        $body = '--' . $boundary . self::CRLF;
        $body .= 'Content-Disposition: form-data; name="unknown"; filename="newFile.bin"' . self::CRLF;
        $body .= self::CRLF;
        $body .= $data . self::CRLF;
        $body .= '--' . $boundary . '--' . self::CRLF;

        return $body;
    }

    /**
     * Send request using method and its params.
     *
     * @see KerioApiInterface::sendRequest()
     *
     * @param string $method Interface.method
     * @param string[] $params Params of 'Interface.method'.
     *
     * @return array Returns same type as param is, e.g. JSON if method is also JSON
     */
    public function sendRequest($method, $params = [])
    {
        $request = array(
            'jsonrpc' => $this->jsonRpc['version'],
            'id' => $this->getRequestId(),
            'token' => $this->token,
            'method' => $method,
            'params' => $params
        );

        if (empty($this->token)) {
            unset($request['token']);
        }
        if (empty($params)) {
            unset($request['params']);
        }

        $json_request = json_encode($request);

        /* Send data to server */
        $json_response = $this->send('POST', $json_request);

        /* Return */
        $response = json_decode($json_response, true);
        return $response['result'];
    }

    /**
     * Send JSON request.
     *
     * @param string $json JSON request
     *
     * @return string JSON response
     */
    public function sendRequestJson($json)
    {
        return $this->send('POST', $json);
    }

    /**
     * Send data to server.
     *
     * @param string $method Request method [POST,GET,PUT]
     * @param string $data Request body
     *
     * @throws KerioApiException
     *
     * @return string Server response
     */
    protected function send($method, $data)
    {
        if (empty($this->hostname)) {
            throw new KerioApiException('Cannot send data before login.');
        }

        /* Get full HTTP request */
        $request = $this->getHttpRequest($method, $data);
        $this->debug(sprintf("&rarr; Raw request:\n<pre>%s</pre>", $request));

        /* Open socket */
        $this->socketHandler = new KerioApiSocket($this->hostname, $this->jsonRpc['port'], $this->timeout);

        /* Send data */
        $rawResponse = $this->socketHandler->send($request);
        $this->debug(sprintf("&larr; Raw response:\n<pre>%s</pre>", $rawResponse));

        /* Parse response */
        $headers = $this->socketHandler->getHeaders();
        $body = $this->socketHandler->getBody();
        $this->checkHttpResponse(self::HTTP_SERVER_OK, $headers);

        /* Decode JSON response */
        $response = json_decode($body, true);
        if (($method == 'POST') && empty($response)) {
            throw new KerioApiException('Invalid JSON data, cannot parse response.');
        }

        /* Set CSRF token */
        if (empty($this->token)) {
            if (isset($response['result']['token'])) {
                $this->setToken($response['result']['token']);
            }
        }

        /* Handle errors */
        if (isset($response['error'])) {
            if (false === empty($response['error'])) {
                $message = $response['error']['message'];
                $code = $response['error']['code'];
                $params = (isset($response['error']['data']))
                    ? $response['error']['data']['messageParameters']['positionalParameters']
                    : '';
                throw new KerioApiException($message, $code, $params, $data, $body);
            }
        } elseif (isset($response['result']['errors'])) {
            if (false === empty($response['result']['errors'])) {
                $message = $response['result']['errors'][0]['message'];
                $code = $response['result']['errors'][0]['code'];
                $params = $response['result']['errors'][0]['messageParameters']['positionalParameters'];
                throw new KerioApiException($message, $code, $params, $data, $body);
            }
        }

        /* Handle Cookies */
        if (empty($this->cookies)) {
            $this->setCookieFromHeaders($headers);
        }

        /* Return */
        return $body;
    }

    /**
     * Get a file from server.
     *
     * @param string $url File url
     * @param string $directory Save directory
     * @param string $filename Save as, optional. Default is file.bin
     *
     * @throws KerioApiException
     *
     * @return bool True on success
     */
    public function downloadFile($url, $directory, $filename = '')
    {
        $saveAs = sprintf('%s/%s', $directory, $filename);

        $data = $this->send('GET', $url);

        $this->debug(sprintf('Saving file %s', $saveAs));
        if (false === @file_put_contents($saveAs, $data)) {
            throw new KerioApiException(sprintf('Unable to save file %s', $saveAs));
        }
        return true;
    }

    /**
     * Get a file from server.
     *
     * @param string $url File url
     *
     * @return string File content
     */
    public function getFile($url)
    {
        return $this->send('GET', $url);
    }

    /**
     * Put a file to server.
     *
     * @param string $filename Absolute path to file
     *
     * @throws KerioApiException
     *
     * @return array Result
     */
    public function uploadFile($filename)
    {
        $data = @file_get_contents($filename);

        if ($data) {
            $this->debug(sprintf('Uploading file %s', $filename));
            $json_response = $this->send('PUT', $data);
        } else {
            throw new KerioApiException(sprintf('Unable to open file %s', $filename));
        }

        $response = json_decode($json_response, true);
        return $response['result'];
    }

    /**
     * Check HTTP/1.1 response header.
     *
     * @param int $code Requested HTTP code
     * @param string $headers HTTP headers
     *
     * @throws KerioApiException
     *
     * @return bool True if match
     */
    protected function checkHttpResponse($code, $headers)
    {
        preg_match('#HTTP/\d+\.\d+ (\d+) (.+)#', $headers, $result);
        switch ($result[1]) {
            case $code:
                return true;
            default:
                $remote = sprintf('https://%s:%d%s', $this->hostname, $this->jsonRpc['port'], $this->jsonRpc['api']);
                throw new KerioApiException(sprintf('%d - %s on remote server %s', $result[1], $result[2], $remote));
        }
    }

    /**
     * Set hostname.
     *
     * @param string $hostname Hostname
     *
     * @return void
     */
    public function setHostname($hostname)
    {
        $hostname = preg_split('/:/', $hostname);
        $this->hostname = $hostname[0];
        if (isset($hostname[1])) {
            $this->setJsonRpc($this->jsonRpc['version'], $hostname[1], $this->jsonRpc['api']);
        }
    }

    /**
     * Get request ID.
     *
     * @return int
     */
    private function getRequestId()
    {
        $this->requestId++;
        return $this->requestId;
    }

    /**
     * Set security Cross-Site Request Forgery X-Token.
     *
     * @param string $token X-Token value
     *
     * @return void
     */
    protected function setToken($token)
    {
        $this->debug(sprintf('Setting X-Token %s.', $token));
        $this->token = $token;
    }

    /**
     * Get security Cross-Site Request Forgery X-Token.
     *
     * @return string X-Token value
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set Cookies.
     *
     * @param string $cookies Cookies
     *
     * @return void
     */
    protected function setCookie($cookies)
    {
        $this->cookies = $cookies;
    }

    /**
     * Get Cookies.
     *
     * @return string Cookies
     */
    public function getCookie()
    {
        return $this->cookies;
    }

    /**
     * Set Cookie from response.
     *
     * @param string $headers HTTP headers
     *
     * @return void
     */
    private function setCookieFromHeaders($headers)
    {
        foreach (explode("\n", $headers) as $line) {
            if (preg_match_all('/Set-Cookie:\s(\w*)=(\w*)/', $line, $result)) {
                foreach ($result[1] as $index => $cookie) {
                    $this->debug(sprintf('Setting %s=%s.', $cookie, $result[2][$index]));
                    $this->setCookie(sprintf('%s %s=%s;', $this->getCookie(), $cookie, $result[2][$index]));
                }
            }
        }
    }

    /**
     * Set connection timeout.
     *
     * @param int $timeout Timeout in seconds
     *
     * @return void
     */
    public function setTimeout($timeout)
    {
        $this->timeout = (int) $timeout;
    }
}
