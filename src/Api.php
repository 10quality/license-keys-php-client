<?php

namespace LicenseKeys\Utility;

use Exception;
use Closure;

/**
 * License Keys's API wrapper.
 *
 * @link https://www.10quality.com/product/woocommerce-license-keys/
 * @author Alejandro Mostajo <info@10quality.com> 
 * @version 1.0.9
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
        $license->request['domain'] = isset( $_SERVER['SERVER_NAME'] ) ? $_SERVER['SERVER_NAME'] : 'Unknown';
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
     * @since 1.0.3 Force parameter added.
     * @since 1.0.4 Checks if license key is empty.
     * @since 1.0.6 Connection retries.
     * @since 1.0.7 Bug fixes.
     *
     * @param Client  $client         Client to use for api calls.
     * @param Closure $getRequest     Callable that returns a LicenseRequest.
     * @param Closure $setRequest     Callable that sets (updates) a LicenseRequest casted as string.
     * @param bool    $force          Flag that forces validation against the server.
     * @param bool    $allowRetry     Allow to connection retries.
     * @param int     $retryAttempts  Retry attempts.
     * @param string  $retryFrequency Retry frequency.
     *
     * @throws Exception when LicenseRequest is not present.
     *
     * @return bool
     */
    public static function validate(
        Client $client, Closure $getRequest, Closure $setRequest, $force = false,
        $allowRetry = false, $retryAttempts = 2, $retryFrequency = '+1 hour'
    ) {
        // Prepare
        $license = $getRequest();
        if (!is_a($license, LicenseRequest::class))
            throw new Exception('Closure must return an object instance of LicenseRequest.');
        $license->updateVersion();
        // Check license data
        if ($license->isEmpty || $license->data['has_expired']) {
            return false;
        }
        // No need to check if license already expired.
        if ($license->data['has_expired'])
            return false;
        // Validate cached license data
        if ( ! $force 
            && time() < $license->nextCheck
            && $license->isValid
        ) {
            return true;
        }
        // Call
        $license->request['domain'] = isset( $_SERVER['SERVER_NAME'] ) ? $_SERVER['SERVER_NAME'] : 'Unknown';
        $response = null;
        try {
            $response = $client->call('license_key_validate', $license);
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Could not resolve host') === false)
                throw $e;
        }
        if ($response
            && isset($response->error)
        ) {
            if (isset($response->data))
                $license->data = (array)$response->data;
            $license->touch();
            $setRequest((string)$license);
            return $response->error === false;
        } else if (empty($response)
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
        } else if (empty($response)
            && $allowRetry
            && $license->retries < $retryAttempts
        ) {
            $license->addRetryAttempt($retryFrequency);
            $setRequest((string)$license);
            return true;
        }
        return false;
    }
    /**
     * Deactivates a license key.
     * Returns call response.
     * @since 1.0.0
     * @since 1.0.1 Removes license on activation_id errors as well.
     * @since 1.0.6 Versioning support.
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
        $license->updateVersion();
        // Call
        $license->request['domain'] = isset( $_SERVER['SERVER_NAME'] ) ? $_SERVER['SERVER_NAME'] : 'Unknown';
        $response = $client->call('license_key_deactivate', $license);
        // Remove license
        if (isset($response->error)) {
            if ($response->error === false) {
                $setRequest(null);
            } else if (isset($response->errors)) {
                foreach ($response->errors as $key => $message) {
                    if ($key === 'activation_id') {
                        $setRequest(null);
                        break;
                    }
                }
            }
        }
        return $response;
    }
    /**
     * Validates a license key (NO SERVER VALIDATION).
     * @since 1.0.9
     *
     * @param Closure $getRequest     Callable that returns a LicenseRequest.
     *
     * @throws Exception when LicenseRequest is not present.
     *
     * @return bool
     */
    public static function softValidate( Closure $getRequest )
    {
        // Prepare
        $license = $getRequest();
        if (!is_a($license, LicenseRequest::class))
            throw new Exception('Closure must return an object instance of LicenseRequest.');
        $license->updateVersion();
        // Check license data
        if ($license->isEmpty || $license->data['has_expired']) {
            return false;
        }
        // Validate cached license data
        return $license->isValid;
    }
}