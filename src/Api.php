<?php

namespace LicenseKeys\Utility;

use Exception;
use Closure;

/**
 * License Keys's API wrapper.
 *
 * @link https://www.10quality.com/product/woocommerce-license-keys/
 * @author Alejandro Mostajo <info@10quality.com> 
 * @version 1.0.0
 * @package LicenseKeys\Utility
 * @license MIT
 */
class Api
{
    /**
     * Activates a license key.
     * Returns call response.
     * @since 1.0.0
     *
     * @param Client  $client     Client to use for api calls.
     * @param Closure $getRequest Callable that returns a LicenseRequest.
     * @param Closure $setRequest Callable that sets a LicenseRequest casted as string.
     *
     * @throws Exception when LicenseRequest is not present.
     *
     * @return object|stdClass
     */
    public static function activate(Client $client, Closure $getRequest, Closure $setRequest)
    {
        // Prepare
        $license = $getRequest();
        if (!is_a($license, LicenseRequest::class))
            throw new Exception('Closure must return an object instance of LicenseRequest.');
        // Call
        $license->request['domain'] = $_SERVER['SERVER_NAME'];
        $response = $client->call('license_key_activate', $license);
        if (isset($response->error)
            && $response->error === false
        ) {
            $license->data = (array)$response->data;
            $license->touch();
            $setRequest((string)$license);
        }
        return $response;
    }
    /**
     * Validates a license key.
     * Returns flag indicating if license key is valid.
     * @since 1.0.0
     *
     * @param Client  $client     Client to use for api calls.
     * @param Closure $getRequest Callable that returns a LicenseRequest.
     * @param Closure $setRequest Callable that sets (updates) a LicenseRequest casted as string.
     *
     * @throws Exception when LicenseRequest is not present.
     *
     * @return bool
     */
    public static function validate(Client $client, Closure $getRequest, Closure $setRequest)
    {
        // Prepare
        $license = $getRequest();
        if (!is_a($license, LicenseRequest::class))
            throw new Exception('Closure must return an object instance of LicenseRequest.');
        // No need to check if license already expired.
        if ($license->data['has_expired'])
            return false;
        // Validate cached license data
        if (time() < $license->nextCheck
            && $license->isValid
        ) {
            return true;
        }
        // Call
        $license->request['domain'] = $_SERVER['SERVER_NAME'];
        $response = $client->call('license_key_validate', $license);
        if ($response
            && isset($response->error)
            && $response->error === false
        ) {
            $license->data = (array)$response->data;
            $license->touch();
            $setRequest((string)$license);
            return true;
        } else if (($response === null || $response === '')
            && $license->url
            && isset($license->data['allow_offline'])
            && isset($license->data['offline_interval'])
            && isset($license->data['offline_value'])
            && $license->data['allow_offline'] === true
        ) {
            if (!$license->isOffline) {
                $license->enableOffline();
                $setRequest((string)$license);
                return true;
            } else if ($license->isOfflineValid) {
                return true;
            }
        }
        return false;
    }
    /**
     * Deactivates a license key.
     * Returns call response.
     * @since 1.0.0
     *
     * @param Client  $client     Client to use for api calls.
     * @param Closure $getRequest Callable that returns a LicenseRequest.
     * @param Closure $setRequest Callable that updates a LicenseRequest casted as string.
     *
     * @throws Exception when LicenseRequest is not present.
     *
     * @return object|stdClass
     */
    public static function deactivate(Client $client, Closure $getRequest, Closure $setRequest)
    {
        // Prepare
        $license = $getRequest();
        if (!is_a($license, LicenseRequest::class))
            throw new Exception('Closure must return an object instance of LicenseRequest.');
        // Call
        $license->request['domain'] = $_SERVER['SERVER_NAME'];
        $response = $client->call('license_key_deactivate', $license);
        if (isset($response->error)
            && $response->error === false
        ) {
            $setRequest(null);
        }
        return $response;
    }
}