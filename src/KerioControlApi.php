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

namespace Kerio\Api;

use Kerio\Api\Http\KerioApi;
use Kerio\Api\Http\KerioApiException;

/**
 * Administration API for Kerio Control.
 * STATUS: In progress, might change in the future
 *
 * This class implements product-specific methods and properties and currently is under development.
 * Class is not intended for stable use yet.
 * Functionality might not be fully verified, documented, or even supported.
 *
 * Please note that changes can be made without further notice.
 *
 * Example:
 * <code>
 * <?php
 * use Kerio\Api\KerioControlApi;
 *
 * $api = new KerioControlApi('Sample application', 'Company Ltd.', '1.0');
 *
 * try {
 *     $api->login('firewall.company.tld', 'admin', 'SecretPassword');
 *     $api->sendRequest('...');
 *     $api->logout();
 * } catch (KerioApiException $error) {
 *     print $error->getMessage();
 * }
 * ?>
 * </code>
 *
 * @copyright Copyright &copy; 2012-2012 Kerio Technologies s.r.o.
 * @license http://www.kerio.com/developers/license/sdk-agreement
 * @version 1.4.0.234
 */
class KerioControlApi extends KerioApi
{
    /**
     * Defines product-specific JSON-RPC settings
     * @var array
     */
    protected $jsonRpc = array(
        'version' => '2.0',
        'port' => 4081,
        'api' => '/admin/api/jsonrpc/'
    );

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
        parent::__construct($name, $vendor, $version);
    }

    /**
     * Get constants defined by product.
     *
     * @return array Array of constants
     */
    public function getConstants()
    {
        $response = $this->sendRequest('Server.getNamedConstantList');
        return $response['constants'];
    }
}
