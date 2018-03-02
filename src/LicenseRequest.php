<?php

namespace LicenseKeys\Utility;

/**
 * License Key API request.
 *
 * @author Alejandro Mostajo <info@10quality.com> 
 * @version 1.0.4
 * @package LicenseKeys\Utility
 * @license MIT
 */
class LicenseRequest
{
    /**
     * Daily frequency API validation.
     * @since 1.0.0
     * @var string
     */
    const DAILY_FREQUENCY = 'daily';
    /**
     * Daily frequency API validation.
     * @since 1.0.0
     * @var string
     */
    const HOURLY_FREQUENCY = 'hourly';
    /**
     * Daily frequency API validation.
     * @since 1.0.0
     * @var string
     */
    const WEEKLY_FREQUENCY = 'weekly';
    /**
     * API's base url.
     * @since 1.0.0
     * @var string
     */
    protected $settings = [];
    /**
     * Additional request data.
     * @since 1.0.0
     * @var array
     */
    protected $request = [];
    /**
     * License key data.
     * @since 1.0.0
     * @var array
     */
    protected $data = [];
    /**
     * Default constructor.
     * @since 1.0.0
     *
     * @param string $license License data encoded as JSON.
     */
    public function __construct($license)
    {
        $license = json_decode($license);
        if (isset($license->settings))
            $this->settings = (array)$license->settings;
        if (isset($license->request))
            $this->request = (array)$license->request;
        if (isset($license->data))
            $this->data = (array)$license->data;
        // Check for activation_id
        if (!isset($this->request['activation_id'])
            && isset($this->data['activation_id'])
        )
            $this->request['activation_id'] = $this->data['activation_id'];
        if (!isset($this->request['license_key'])
            && isset($this->data['the_key'])
        )
            $this->request['license_key'] = $this->data['the_key'];
    }
    /**
     * Creates basic license request.
     * @since 1.0.0
     *
     * @param string $url         Base API url.
     * @param string $store_code  Store code.
     * @param string $sku         Product SKU.
     * @param string $license_key Customer license key.
     * @param string $frequency   API validate call frequency.
     *
     * @return object|LicenseRequest
     */
    public static function create($url, $store_code, $sku, $license_key, $frequency = self::DAILY_FREQUENCY)
    {
        $license = [
            'settings'  => [
                            'url'           => $url,
                            'frequency'     => $frequency,
                            'next_check'    => 0,
                        ],
            'request'   => [
                            'store_code'    => $store_code,
                            'sku'           => $sku,
                            'license_key'   => $license_key,
                        ],
            'data'      => []
        ];
        return new self(json_encode($license));
    }
    /**
     * Returns selected properties.
     * @since 1.0.0
     * @since 1.0.4 Added isEmpty.
     *
     * @param string $property Property to return.
     *
     * @return mixed
     */
    public function &__get($property)
    {
        $value = null;
        switch ($property) {
            case 'url':
                if (isset($this->settings['url']))
                    return $this->settings['url'];
                break;
            case 'frequency':
                if (isset($this->settings['frequency']))
                    return $this->settings['frequency'];
                break;
            case 'nextCheck':
                if (isset($this->settings['next_check']))
                    return $this->settings['next_check'];
                break;
            case 'request':
                return $this->request;
            case 'data':
                return $this->data;
            case 'isOffline':
                $value = isset($this->settings['offline']);
                break;
            case 'isOfflineValid':
                $value = $this->isOffline
                    && ($this->settings['offline'] === true
                        || time() < $this->settings['offline']
                    );
                break;
            case 'isValid':
                $value = false;
                if (isset($this->settings['frequency'])
                    && !empty($this->settings['frequency'])
                    && isset($this->data)
                    && !empty($this->data)
                    && is_numeric($this->data['activation_id'])
                ) {
                    $value = $this->data['expire'] === null || time() < $this->data['expire'];
                }
                break;
            case 'isEmpty':
                $value = empty($this->data);
                break;
        }
        return $value;
    }
    /**
     * Returns selected properties.
     * @since 1.0.0
     *
     * @param string $property Property to set.
     * @param mixed  $value    Property value.
     */
    public function __set($property, $value)
    {
        switch ($property) {
            case 'data':
                $this->data = $value;
                break;
        }
    }
    /**
     * Returns license request as string.
     * @since 1.0.0
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode([
            'settings'  => $this->settings,
            'request'   => $this->request,
            'data'      => $this->data
        ]);
    }
    /**
     * Touches request settings to update next check.
     * @since 1.0.0
     *
     * @param bool $disableOffline Disables online mode.
     */
    public function touch($disableOffline = true)
    {
        if ($disableOffline && isset($this->settings['offline']))
            unset($this->settings['offline']);
        if (!isset($this->settings['frequency']))
            return;
        switch ($this->settings['frequency']) {
            case self::DAILY_FREQUENCY:
                $this->settings['next_check'] = strtotime('+1 days');
                break;
            case self::HOURLY_FREQUENCY:
                $this->settings['next_check'] = strtotime('+1 hour');
                break;
            case self::WEEKLY_FREQUENCY:
                $this->settings['next_check'] = strtotime('+1 week');
                break;
            default:
                $this->settings['next_check'] = strtotime($this->settings['frequency']);
                break;
        }
    }
    /**
     * Enables offline mode.
     * @since 1.0.0
     */
    public function enableOffline()
    {
        $this->settings['offline'] = $this->data['offline_interval'] === 'unlimited'
            ? true
            : strtotime('+'.intval($this->data['offline_value']).' '.$this->data['offline_interval']);
        $this->touch(false);
    }
}