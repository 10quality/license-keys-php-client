<?php

namespace LicenseKeys\Utility;

use Exception;
use Closure;

/**
 * License Keys's API wrapper.
 *
 * @link https://www.10quality.com/product/woocommerce-license-keys/
 * @author Alejandro Mostajo <info@10quality.com> 
 * @version php5-1.0.0
 * @package LicenseKeys\Utility
 * @license MIT
 */
class Api
{
    /**
     * Activates a license key.
     * Returns call response.
     * @since 1.0.0
     * @since php5-1.0.0 Callables instead of closures.
     *
     * @param Client   $client      Client to use for api calls.
     * @param callable $getCallable Callable that returns a LicenseRequest.
     * @param callable $setCallable Callable that sets a LicenseRequest casted as string.
     *
     * @throws Exception when LicenseRequest is not present.
     *
     * @return object|stdClass
     */
    public static function activate(Client $client, $getCallable, $setCallable)
    {
        // Prepare
        $license = call_user_func_array($getCallable, []);
        if (!is_a($license, 'LicenseKeys\\Utility\\LicenseRequest'))
            throw new Exception('Callable must return an object instance of LicenseRequest.');
        // Call
        $license->request['domain'] = $_SERVER['SERVER_NAME'];
        $response = $client->call('license_key_activate', $license);
        if (isset($response->error)
            && $response->error === false
        ) {
            $license->data = (array)$response->data;
            $license->touch();
            call_user_func_array($setCallable, [(string)$license]);
        }
        return $response;
    }
    /**
     * Validates a license key.
     * Returns flag indicating if license key is valid.
     * @since 1.0.0
     * @since 1.0.3 Force parameter added.
     * @since 1.0.4 Checks if license key is empty.
     * @since php5-1.0.0 Callables instead of closures.
     *
     * @param Client   $client      Client to use for api calls.
     * @param callable $getCallable Callable that returns a LicenseRequest.
     * @param callable $setCallable Callable that sets a LicenseRequest casted as string.
     * @param bool     $force       Flag that forces validation against the server.
     *
     * @throws Exception when LicenseRequest is not present.
     *
     * @return bool
     */
    public static function validate(Client $client, $getCallable, $setCallable, $force = false)
    {
        // Prepare
        $license = call_user_func_array($getCallable, []);
        if (!is_a($license, 'LicenseKeys\\Utility\\LicenseRequest'))
            throw new Exception('Callable must return an object instance of LicenseRequest.');
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
        $license->request['domain'] = $_SERVER['SERVER_NAME'];
        $response = $client->call('license_key_validate', $license);
        if ($response
            && isset($response->error)
            && $response->error === false
        ) {
            $license->data = (array)$response->data;
            $license->touch();
            call_user_func_array($setCallable, [(string)$license]);
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
                call_user_func_array($setCallable, [(string)$license]);
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
     * @since 1.0.1 Removes license on activation_id errors as well.
     * @since php5-1.0.0 Callables instead of closures.
     *
     * @param Client   $client      Client to use for api calls.
     * @param callable $getCallable Callable that returns a LicenseRequest.
     * @param callable $setCallable Callable that sets a LicenseRequest casted as string.
     *
     * @throws Exception when LicenseRequest is not present.
     *
     * @return object|stdClass
     */
    public static function deactivate(Client $client, $getCallable, $setCallable)
    {
        // Prepare
        $license = call_user_func_array($getCallable, []);
        if (!is_a($license, 'LicenseKeys\\Utility\\LicenseRequest'))
            throw new Exception('Callable must return an object instance of LicenseRequest.');
        // Call
        $license->request['domain'] = $_SERVER['SERVER_NAME'];
        $response = $client->call('license_key_deactivate', $license);
        // Remove license
        if (isset($response->error)) {
            if ($response->error === false) {
                call_user_func_array($setCallable, [null]);
            } else if (isset($response->errors)) {
                foreach ($response->errors as $key => $message) {
                    if ($key === 'activation_id') {
                        call_user_func_array($setCallable, [null]);
                        break;
                    }
                }
            }
        }
        return $response;
    }
}