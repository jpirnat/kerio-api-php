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
 * Kerio API Socket Class.
 *
 * This class implements basic methods used in HTTP communication.
 *
 * @copyright Copyright &copy; 2012-2012 Kerio Technologies s.r.o.
 * @license http://www.kerio.com/developers/license/sdk-agreement
 * @version 1.4.0.234
 */
class KerioApiSocket implements KerioApiSocketInterface
{
    /**
     * Socket buffer size
     */
    const BUFFER_SIZE = 10240;

    /**
     * Socket handler
     * @var resource
     */
    private $socketHandler = '';

    /**
     * Communication timeout
     * @var int
     */
    private $timeout = 10;

    /**
     * Server hostname
     * @var string
     */
    private $hostname = '';

    /**
     * Server port
     * @var int
     */
    private $port = '';

    /**
     * SSL encryption
     * @var string
     */
    private $cipher = 'ssl://';

    /**
     * Headers
     * @var string
     */
    private $headers = '';

    /**
     * Body
     * @var string
     */
    private $body = '';

    /**
     * Socket error message
     * @var string
     */
    private $errorMessage = '';

    /**
     * Socket error code
     * @var int
     */
    private $errorCode = 0;

    /**
     * Class constructor.
     *
     * @param string $hostname Hostname
     * @param int $port Port
     * @param int|null $timeout Timeout, optional
     */
    public function __construct($hostname, $port, $timeout = null)
    {
        /* Set host */
        $this->hostname = $hostname;
        $this->port = $port;

        /* Set timeout */
        if (is_int($timeout)) {
            $this->timeout = $timeout;
        }

        /* Open socket to server */
        $this->open();
    }

    /**
     * Class destructor.
     *
     * @return void
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Open socket to server.
     *
     * @return void
     */
    protected function open()
    {
        $errstr = "";
        $errno  = "";
        $context = stream_context_create();
        stream_context_set_option($context, "ssl", "allow_self_signed", true);
        stream_context_set_option($context, "ssl", "verify_peer", false);
        stream_context_set_option($context, "ssl", "verify_peer_name", false);
        $this->socketHandler = @stream_socket_client(
            $this->cipher . $this->hostname . ':' .  $this->port,
            $errno,
            $errstr,
            $this->timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );
        $this->errorCode = $errno;
        $this->errorMessage = $errstr;
    }

    /**
     * Close socket to server.
     *
     * @return void
     */
    protected function close()
    {
        @fclose($this->socketHandler);
        unset($this->socketHandler);
    }

    /**
     * Send data to socket.
     *
     * @see KerioApiSocketInterface::send()
     *
     * @param string $data Data to socket
     *
     * @throws KerioApiException
     *
     * @return string Data from socket
     */
    public function send($data)
    {
        if ($this->checkConnection()) {
            @fwrite($this->socketHandler, $data);
            return $this->read();
        } else {
            throw new KerioApiException(
                sprintf(
                    "Cannot connect to %s using port %d.",
                    $this->hostname,
                    $this->port
                )
            );
        }
    }

    /**
     * Read data from socket.
     *
     * @throws KerioApiException
     *
     * @return string HTTP data from socket
     */
    protected function read()
    {
        if ($this->socketHandler) {
            $response = '';
            while (false === feof($this->socketHandler)) {
                $response .= fgets($this->socketHandler, self::BUFFER_SIZE);
            }

            list($this->headers, $this->body) = explode("\r\n\r\n", $response);

            if (false !== strpos(strtolower($this->headers), 'transfer-encoding: chunked')) {
                $this->unchunkHttp();
            }

            return $response;
        } else {
            throw new KerioApiException('Cannot read data from server, connection timeout.');
        }
    }

    /**
     * Unchunk HTTP/1.1 body.
     *
     * @return void
     */
    private function unchunkHttp()
    {
        $body = $this->body;
        for ($new = ''; !empty($body); $str = trim($body)) {
            $pos  = strpos($body, "\r\n");
            $len  = hexdec(substr($body, 0, $pos));
            $new .= substr($body, $pos + 2, $len);
            $body = substr($body, $pos + 2 + $len);
        }
        $this->body = $new;
    }

    /**
     * Set connection encryption to ssl://
     *
     * @param bool $boolean True if ssl:// is used
     *
     * @return void
     */
    public function setEncryption($boolean)
    {
        $this->cipher = ($boolean) ? 'ssl://' : '';
    }

    /**
     * Check connection to server.
     *
     * @return bool True on success
     */
    final public function checkConnection()
    {
        if ($this->checkHost()) {
            $socket = @fsockopen($this->hostname, $this->port, $errno, $errstr, $this->timeout);
            $this->errorCode = $errno;
            $this->errorMessage = $errstr;
            return ($socket) ? true : false;
        } else {
            return false;
        }
    }

    /**
     * Check if DNS host is valid.
     *
     * @return bool True on success
     */
    final public function checkHost()
    {
        return gethostbyname($this->hostname) ? true : false;
    }

    /**
     * Get headers.
     *
     * @return string
     */
    final public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get body.
     *
     * @return string
     */
    final public function getBody()
    {
        return $this->body;
    }

    /**
     * Get socker error message.
     *
     * @return string
     */
    final public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Get socket error code.
     *
     * @return int
     */
    final public function getErrorCode()
    {
        return $this->errorCode;
    }
}
