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
 * Kerio API Interface.
 *
 * This interface describes basic methods for the Kerio API.
 *
 * @copyright Copyright &copy; 2012-2012 Kerio Technologies s.r.o.
 * @license http://www.kerio.com/developers/license/sdk-agreement
 * @version 1.4.0.234
 */
interface KerioApiInterface
{
    /**
     * Class constructor.
     *
     * @param string $name Application name
     * @param string $vendor Application vendor
     * @param string $version Application version
     */
    public function __construct($name, $vendor, $version);

    /**
     * Set JSON-RPC settings.
     *
     * @param string $version
     * @param int $port
     * @param string $api
     */
    public function setJsonRpc($version, $port, $api);

    /**
     * Send request using method and its params.
     *
     * @param string $method
     * @param array $params
     */
    public function sendRequest($method, $params = []);

    /**
     * Login method.
     *
     * @param string $hostname
     * @param string $username
     * @param string $password
     */
    public function login($hostname, $username, $password);

    /**
     * Logout method.
     */
    public function logout();
}
