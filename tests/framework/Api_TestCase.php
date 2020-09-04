<?php

use LicenseKeys\Utility\Client;
use LicenseKeys\Utility\LicenseRequest;
use PHPUnit\Framework\TestCase;

/**
 * Extends PHPUnit TestCase to provide mocks with expected responses.
 *
 * @author Alejandro Mostajo <info@10quality.com> 
 * @version 1.2.1
 * @package LicenseKeys\Utility
 * @license MIT
 */
class Api_TestCase extends TestCase
{
    /**
     * Returns Client Mock with a expected JSON result.
     * @since 1.0.0
     *
     * @param string $response Expected json response.
     * @param bool   $once     Indicates if it is expected call to run once or more times.
     *
     * @param object|Client
     */
    public function getClientMock($response = '{}', $once = true)
    {
        $mock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mock->expects($once ? $this->once() : $this->any())
            ->method('call')
            ->willReturn($response ? json_decode($response) : $response);
        return $mock;
    }
    /**
     * Returns Client Mock.
     * @since 1.0.0
     *
     * @param object|Client
     */
    public function getSimpleClientMock()
    {
        return $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
    /**
     * Returns Client Mock with a expected JSON result.
     * @since 1.0.2
     *
     * @param string $response Expected json response.
     * @param bool   $once     Indicates if it is expected call to run once or more times.
     *
     * @param object|Client
     */
    public function getClientHttpsMock($once = true)
    {
        $mock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mock->expects($once ? $this->once() : $this->any())
            ->method('setSSL');
        return $mock;
    }
    /**
     * Returns LicenseRequest Mock.
     * @since 1.0.0
     *
     * @param object|LicenseRequest
     */
    public function getLicenseRequestMock($string = false)
    {
        return !is_string($string)
            ? $this->getMockBuilder(LicenseRequest::class)
                ->disableOriginalConstructor()
                ->getMock()
            : $this->getMockBuilder(LicenseRequest::class)
                ->setConstructorArgs([$string])
                ->getMock();
    }
    /**
     * Returns LicenseRequest Mock.
     * @since 1.0.0
     *
     * @param object|LicenseRequest
     */
    public function getTouchedLicenseRequestMock($string)
    {
        $mock = $this->getMockBuilder(LicenseRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mock->expects($this->once())
            ->method('touch');
        $mock->expects($this->once())
            ->method('__toString')
            ->willReturn($string);
        return $mock;
    }
    /**
     * Returns LicenseRequest Mock.
     * @since 1.0.0
     *
     * @param object|LicenseRequest
     */
    public function getOfflineLicenseRequestMock($string)
    {
        $mock = $this->getMockBuilder(LicenseRequest::class)
            ->setConstructorArgs([$string])
            ->getMock();
        $mock->expects($this->once())
            ->method('enableOffline');
        $mock->expects($this->once())
            ->method('touch');
        $mock->expects($this->once())
            ->method('__toString')
            ->willReturn($string);
        return $mock;
    }
    /**
     * Returns LicenseRequest Mock.
     * @since 1.0.0
     *
     * @param object|LicenseRequest
     */
    public function getRetriedLicenseRequestMock($string, $once = true)
    {
        $mock = $this->getMockBuilder(LicenseRequest::class)
            ->setConstructorArgs([$string])
            ->getMock();
        $mock->expects($this->once())
            ->method('addRetryAttempt');
        $mock->expects($this->once())
            ->method('__toString')
            ->willReturn($string);
        return $mock;
    }
}