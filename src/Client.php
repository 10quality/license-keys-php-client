<?php

namespace LicenseKeys\Utility;

use Exception;

/**
 * Curl client.
 *
 * @link https://www.10quality.com/product/woocommerce-license-keys/
 * @author Alejandro Mostajo <info@10quality.com>
 * @version php5-1.2.2
 * @package LicenseKeys\Utility
 * @license MIT
 */
class Client
{
    /**
     * Instance.
     * @since 1.0.0
     * @var object this
     */
    protected static $instance;
    /**
     * Curl accessor.
     * @since 1.0.0
     * @var object
     */
    protected $curl;
    /**
     * Last response got from API.
     * RAW response
     * @since 1.0.0
     * @var string
     */
    protected $response;
    /**
     * Last response got from API.
     * RAW response
     * @since 1.2.0
     * @var string
     */
    protected $events = [];
    /**
     * Sets request options.
     * @since 1.2.1
     * @var array
     */
    protected $options = [];
    /**
     * Sets request headers.
     * @since 1.2.2
     * @var array
     */
    protected $headers = [];
    /**
     * Static constructor.
     * @since 1.0.0
     */
    public static function instance()
    {
        if (isset(static::$instance))
            return static::$instance;
        static::$instance = new self;
        return static::$instance;
    }
    /**
     * Adds an event handler.
     * @since 1.2.0
     * 
     * @param string   $event
     * @param callable $callable
     *
     * @return this
     */
    public function on($event, $callable)
    {
        if (!is_array($this->events))
            $this->events = [];
        if (is_callable($callable))
            $this->events[$event] = $callable;
        return $this;
    }
    /**
     * Adds a header.
     * @since 1.2.2
     * 
     * @param string|null $key   Null will clear headers.
     * @param string      $value
     *
     * @return this
     */
    public function header($key, $value = null)
    {
        if ($key === null){
            $this->headers = [];
            return $this;
        }
        if (!is_array($this->headers))
            $this->headers = [];
        if ($value !== null)
            $this->headers[$key] = $value;
        return $this;
    }
    /**
     * Sets client options.
     * @since 1.2.1
     * 
     * @param array $options
     * @param callable $callable
     *
     * @return this
     */
    public function set($options)
    {
        if (is_array($options))
            $this->options = $options;
        return $this;
    }
    /**
     * Returns client custom options.
     * @since 1.2.1
     * 
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
    /**
     * Executes CURL call.
     * Returns API response.
     * @since 1.0.0
     *
     * @param string         $endpoint            API endpoint to call.
     * @param LicenseRequest $license             License request.
     * @param string         $method              Request method.
     * @param bool           $bypassAuthorization Bypass authorization header.
     *
     * @return mixed|object|null
     */
    public function call($endpoint, LicenseRequest $license, $method = 'POST', $bypassAuthorization = false)
    {
        $microtime = microtime(true);
        $this->trigger('start', [$microtime]);
        // Begin
        $this->setCurl(preg_match('/https\:/', $license->url), $bypassAuthorization);
        $this->resolveEndpoint( $endpoint, $license );
        // Make call
        $url = $license->url.$endpoint;
        $this->trigger('endpoint', [$endpoint, $url]);
        // Set method
        $this->trigger('request', [$license->request]);
        switch ($method) {
            case 'GET':
                curl_setopt($this->curl, CURLOPT_POST, 0);
                if ($license->request && count($license->request) > 0)
                    $url .= '?' . http_build_query($license->request);
                break;
            case 'POST':
                curl_setopt($this->curl, CURLOPT_POST, 1);
                if ($license->request && count($license->request) > 0)
                    curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($license->request));
                break;
            case 'JPOST':
            case 'JPUT':
            case 'JGET':
            case 'JDELETE':
                $json = json_encode($license->request);                                     
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, preg_replace('/J/', '', $method, -1));
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $json);
                // Rewrite headers
                curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: '.strlen($json),
                ));     
                break;
        }
        curl_setopt( $this->curl, CURLOPT_URL, $url);
        // Get response
        $this->response = curl_exec($this->curl);
        if (curl_errno($this->curl)) {
            $error = curl_error($this->curl);
            curl_close($this->curl);
            if (!empty($error)) {
                throw new Exception($error);
            }
        } else {
            curl_close($this->curl);
        }
        $this->trigger('response', [$this->response]);
        $this->trigger('finish', [microtime(true), $microtime]);
        return empty($this->response) ? null : json_decode($this->response);
    }
    /**
     * Sets curl property and its settings.
     * @since 1.0.0
     *
     * @see http://us3.php.net/manual/en/book.curl.php
     * @see https://gist.github.com/salsalabs/e24c2466496860975e8a
     *
     * @param bool $is_https
     * @param bool $bypassAuthorization Bypass authorization header.
     */
    private function setCurl($is_https = false, $bypassAuthorization = false)
    {
        // Init
        $this->curl = curl_init();
        // Sets basic parameters
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, isset($this->request->settings['timeout']) ? $this->request->settings['timeout'] : 100);
        // Set parameters to maintain cookies across sessions
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, $this->getOption(CURLOPT_COOKIESESSION, TRUE));
        curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->getOption(CURLOPT_COOKIEFILE, '/tmp/cookies_file'));
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->getOption(CURLOPT_COOKIEJAR, '/tmp/cookies_file'));
        curl_setopt($this->curl, CURLOPT_USERAGENT, $this->getOption(
            CURLOPT_USERAGENT,
            'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7'
        ));
        if ($is_https)
            $this->setSSL();
        // Headers
        if ($this->headers && count($this->headers)) {
            $headers = [];
            foreach ($this->headers as $key => $value) {
                if ( $bypassAuthorization && $key === 'Authorization' )
                    continue;
                $headers[] = $key . ': ' . $value;
            }
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
            $this->trigger('headers', [$headers]);
        }
    }
    /**
     * Sets SSL curl properties when requesting an https url.
     * @since 1.0.2
     */
    private function setSSL()
    {
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
    }
    /**
     * Resolve endpoint based on handler setup.
     * @since 1.2.0
     * 
     * @param string         &$endpoint
     * @param LicenseRequest $license   License request.
     */
    private function resolveEndpoint(&$endpoint, LicenseRequest $license)
    {
        switch ($license->handler) {
            case 'wp_rest':
                $endpoint = '/wp-json/woo-license-keys/v1/'.str_replace('license_key_' , '', $endpoint);
                break;
            default:
                $endpoint = '?action='.$endpoint;
                break;
        }
    }
    /**
     * Triggers an event.
     * @since 1.2.0
     * 
     * @param string $event
     * @param array  $args
     */
    private function trigger($event, $args = [])
    {
        if (array_key_exists($event, $this->events))
            call_user_func_array($this->events[$event], $args);
    }
    /**
     * Returs a set option value.
     * @since 1.2.1
     *
     * @param string $option
     * @param mixed  $default
     *
     * @return mixed
     */
    private function getOption($option, $default = 0)
    {
        return array_key_exists($option, $this->options) ? $this->options[$option] : $default;
    }
}