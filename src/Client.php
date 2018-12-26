<?php

namespace LicenseKeys\Utility;

use Exception;
use Closure;

/**
 * Curl client.
 *
 * @link https://www.10quality.com/product/woocommerce-license-keys/
 * @author Alejandro Mostajo <info@10quality.com> 
 * @version 1.0.3
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
     * Executes CURL call.
     * Returns API response.
     * @since 1.0.0
     * @since 1.0.2 Checks https.
     *
     * @param string         $endPoint API endpoint to call.
     * @param LicenseRequest $license  License request.
     * @param string         $method   Request method.
     *
     * @return mixed|object|null
     */
    public function call($endPoint, LicenseRequest $license, $method = 'POST')
    {
        // Begin
        $this->setCurl(preg_match('/https\:/', $license->url));
        // Make call
        curl_setopt(
            $this->curl,
            CURLOPT_URL,
            $license->url.'?action='.$endPoint
        );
        // Set method
        switch ($method) {
            case 'GET':
                curl_setopt($this->curl, CURLOPT_POST, 0);
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
        // Get response
        $this->response = curl_exec($this->curl);
        if (curl_errno($this->curl)) {
            $error = curl_error($this->curl);
            curl_close($this->curl);
            throw new Exception($error);
        }
        curl_close($this->curl);
        return json_decode($this->response);
    }
    /**
     * Sets curl property and its settings.
     * @since 1.0.0
     *
     * @see http://us3.php.net/manual/en/book.curl.php
     * @see https://gist.github.com/salsalabs/e24c2466496860975e8a
     */
    private function setCurl($is_https = false)
    {
        // Init
        $this->curl = curl_init();
        // Sets basic parameters
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, isset($this->request->settings['timeout']) ? $this->request->settings['timeout'] : 100);
        // Set parameters to maintain cookies across sessions
        curl_setopt($this->curl, CURLOPT_COOKIESESSION, TRUE);
        curl_setopt($this->curl, CURLOPT_COOKIEFILE, '/tmp/cookies_file');
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, '/tmp/cookies_file');
        curl_setopt($this->curl, CURLOPT_USERAGENT,
            'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7'
        );
        if ($is_https)
            $this->setSSL();
    }
    /**
     * Sets SSL curl properties when requesting an https url.
     * @since 1.0.2
     */
    private function setSSL()
    {
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1); 
    }
}