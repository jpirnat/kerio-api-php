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

use Exception;

/**
 * Kerio API Exception Class.
 *
 * This class extends the Exception class to provide CSS-based error message formating.
 *
 * @copyright Copyright &copy; 2012-2012 Kerio Technologies s.r.o.
 * @license http://www.kerio.com/developers/license/sdk-agreement
 * @version 1.4.0.234
 */
class KerioApiException extends Exception
{
    /**
     * Positional parameters
     * @var array
     */
    private $positionalParameters = array();

    /**
     * Error code
     * @var int $code
     */
    protected $code;

    /**
     * Error message
     * @var string $message
     */
    protected $message = '';

    /**
     * Request message
     * @var string $request
     */
    protected $request = '';

    /**
     * Response message
     * @var string $response
     */
    protected $response = '';

    /**
     * Exception constructor.
     *
     * @param string $message Message to display
     * @param int|string $code Can be integer or string
     * @param array $positionalParameters Positional parameters in message
     * @param string $request
     * @param string $response
     */
    public function __construct($message, $code = '', $positionalParameters = [], $request = '', $response = '')
    {
        $this->message = $message;

        if (is_int($code) || is_string($code)) {
            $this->code = $code;
        }
        if (is_array($positionalParameters)) {
            $this->positionalParameters = $positionalParameters;
            $this->setPositionalParameterToString();
        }
        if (is_string($request)) {
            $this->request = $request;
        }
        if (is_string($response)) {
            $this->response = $response;
        }
    }

    /**
     * Get request data.
     *
     * @return string JSON request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get response data.
     *
     * @return string JSON response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Replace positional parameter with a string
     *
     * @return void
     */
    private function setPositionalParameterToString()
    {
        if (preg_match_all('/%\d/', $this->message, $matches)) {
            /* Found positional parameters */
            $index = 0;
            foreach ($matches[0] as $occurence) {
                $replaceWith = $this->positionalParameters[$index];
                $this->message = str_replace($occurence, $replaceWith, $this->message);
                $index++;
            }
        }
    }
}
