<?php

use LicenseKeys\Utility\Api;
use LicenseKeys\Utility\Client;
use LicenseKeys\Utility\LicenseRequest;

/**
 * Tests Api class.
 *
 * @author Alejandro Mostajo <info@10quality.com> 
 * @version php5-1.0.4
 * @package LicenseKeys\Utility
 * @license MIT
 */
class ApiTest extends Api_TestCase
{
    /**
     * Tests exception on activate endpoint.
     * @since 1.0.0
     * @expectedException Exception
     */
    public function testActivateException()
    {
        // Prepare
        $response = Api::activate(
            $this->getSimpleClientMock(),
            function() {},
            function() {}
        );
        // Assert
        $this->assertFail('Closure must return an object instance of LicenseRequest.');
    }
    /**
     * Tests exception on validate endpoint.
     * @since 1.0.0
     * @expectedException Exception
     */
    public function testValidateException()
    {
        // Prepare
        $response = Api::validate(
            $this->getSimpleClientMock(),
            function() {},
            function() {}
        );
        // Assert
        $this->assertFail('Closure must return an object instance of LicenseRequest.');
    }
    /**
     * Tests activate result with no response.
     * @since 1.0.0
     */
    public function testActivateWithNoResponse()
    {
        // Prepare
        $response = Api::activate(
            $this->getClientMock(''),
            function() { return $this->getLicenseRequestMock(); },
            function() {}
        );
        // Assert
        $this->assertNull($response);
    }
    /**
     * Tests activate result with error response.
     * @since 1.0.0
     */
    public function testActivateWithError()
    {
        // Prepare
        $response = Api::activate(
            $this->getClientMock('{"error":true}'),
            function() { return $this->getLicenseRequestMock(); },
            function() {}
        );
        // Assert
        $this->assertInternalType('object', $response);
        $this->assertTrue($response->error);
    }
    /**
     * Tests activate.
     * @since 1.0.0
     */
    public function testActivate()
    {
        // Prepare
        $response = '{"error":false,"data":{"activation_id":1,"expire":897}}';
        $license = '{"settings":[],"request":[],"data":{"activation_id":1,"expire":897}}';
        ob_start();
        $response = Api::activate(
            $this->getClientMock($response),
            function() use(&$license) { return $this->getTouchedLicenseRequestMock($license); },
            function($string) { echo $string; }
        );
        $echoed = ob_get_clean();
        // Assert response
        $this->assertInternalType('object', $response);
        // Assert set closure
        $this->assertInternalType('string', $echoed);
        $this->assertEquals($license, $echoed);
    }
    /**
     * Tests validate result with no response.
     * @since 1.0.0
     */
    public function testValidateWithNoResponse()
    {
        // Prepare
        $valid = Api::validate(
            $this->getClientMock(''),
            function() { return $this->getLicenseRequestMock(); },
            function() {}
        );
        // Assert
        $this->assertInternalType('bool', $valid);
        $this->assertFalse($valid);
    }
    /**
     * Tests validate result error response.
     * @since 1.0.0
     */
    public function testValidateWithError()
    {
        // Prepare
        $valid = Api::validate(
            $this->getClientMock('{"error":true}'),
            function() { return $this->getLicenseRequestMock(); },
            function() {}
        );
        // Assert
        $this->assertInternalType('bool', $valid);
        $this->assertFalse($valid);
    }
    /**
     * Tests validate.
     * @since 1.0.0
     */
    public function testValidate()
    {
        // Prepare
        $response = '{"error":false,"data":{"activation_id":1,"expire":897}}';
        $license = '{"settings":[],"request":[],"data":{"activation_id":1,"expire":897}}';
        ob_start();
        $valid = Api::validate(
            $this->getClientMock($response),
            function() use(&$license) { return $this->getTouchedLicenseRequestMock($license); },
            function($string) { echo $string; }
        );
        $echoed = ob_get_clean();
        // Assert response
        $this->assertInternalType('bool', $valid);
        $this->assertTrue($valid);
        // Assert set closure
        $this->assertInternalType('string', $echoed);
        $this->assertEquals($license, $echoed);
    }
    /**
     * Tests deactivate result with no response.
     * @since 1.0.0
     */
    public function testDeactivateWithNoResponse()
    {
        // Prepare
        $response = Api::deactivate(
            $this->getClientMock(''),
            function() { return $this->getLicenseRequestMock(); },
            function() {}
        );
        // Assert
        $this->assertNull($response);
    }
    /**
     * Tests deactivate.
     * @since 1.0.0
     */
    public function testDeactivate()
    {
        // Prepare
        $response = '{"error":false,"message":"deactivated"}';
        ob_start();
        $response = Api::deactivate(
            $this->getClientMock($response),
            function() { return $this->getLicenseRequestMock(); },
            function($string) { echo $string; }
        );
        $echoed = ob_get_clean();
        // Assert response
        $this->assertInternalType('object', $response);
        // Assert set closure
        $this->assertInternalType('string', $echoed);
        $this->assertEquals('', $echoed);
    }
    /**
     * Tests validate with connection retry.
     * @since 1.0.6
     */
    public function testValidateWithDefaultRetries()
    {
        // Prepare
        $license = '{"settings":{"retries":0},"request":[],"data":{"activation_id":1,"expire":897}}';
        // Exec
        $valid = Api::validate(
            $this->getClientMock(0),
            function() use($license) { return $this->getRetriedLicenseRequestMock($license); },
            function() {},
            false,
            true
        );
        // Assert
        $this->assertInternalType('bool', $valid);
        $this->assertTrue($valid);
    }
    /**
     * Tests retry on unreachable source.
     * @since 1.0.0
     */
    public function testUnknowSource()
    {
        // Prepare
        $license = new LicenseRequest(
            '{"settings":{"url":"http:\/\/www.thissiteshouldnotexist--1900200.test","frequency":"daily","retries":0},'
                .'"request":[],'
                .'"data":{"has_expired":false}}'
        );
        // Call
        $valid = Api::validate(
            Client::instance(),
            function() use($license) { return $license; },
            function() {},
            true,
            true
        );
        // Assert
        $this->assertTrue($valid);
    }
    /**
     * Tests failed retry on unreachable source.
     * @since 1.0.0
     */
    public function testUnknowSourceMaxRetries()
    {
        // Prepare
        $license = new LicenseRequest(
            '{"settings":{"url":"http:\/\/www.thissiteshouldnotexist--1900200.test","frequency":"daily","retries":1,"version":"1.0.6"},'
                .'"request":[],'
                .'"data":{"has_expired":false}}'
        );
        // Call
        $valid = Api::validate(
            Client::instance(),
            function() use($license) { return $license; },
            function() {},
            true,
            true,
            1
        );
        // Assert
        $this->assertFalse($valid);
    }
    /**
     * Tests soft validate.
     * @since php5-1.0.4
     */
    public function testSoftValidateHasExpired()
    {
        // Prepare
        $license = new LicenseRequest(
            '{"settings":[],"request":[],"data":{"activation_id":1,"expire":897,"has_expired":true}}'
        );
        // Exec
        $valid = Api::softValidate(
            function() use($license) { return $license; }
        );
        // Assert
        $this->assertInternalType('bool', $valid);
        $this->assertFalse($valid);
    }
    /**
     * Tests soft validate.
     * @since php5-1.0.4
     */
    public function testSoftValidateMissingFrequency()
    {
        // Prepare
        $license = new LicenseRequest(
            '{"settings":[],"request":[],"data":{"activation_id":1,"expire":897,"has_expired":false}}'
        );
        // Exec
        $valid = Api::softValidate(
            function() use($license) { return $license; }
        );
        // Assert
        $this->assertInternalType('bool', $valid);
        $this->assertFalse($valid);
    }
    /**
     * Tests soft validate.
     * @since php5-1.0.4
     */
    public function testSoftValidateExpiredTime()
    {
        // Prepare
        $license = new LicenseRequest(
            '{"settings":{"frequency":"daily"},"request":[],"data":{"activation_id":1,"expire":897,"has_expired":false}}'
        );
        // Exec
        $valid = Api::softValidate(
            function() use($license) { return $license; }
        );
        // Assert
        $this->assertInternalType('bool', $valid);
        $this->assertFalse($valid);
    }
    /**
     * Tests soft validate.
     * @since php5-1.0.4
     */
    public function testSoftValidateValidExpiry()
    {
        // Prepare
        $time = time() + 10000;
        $license = new LicenseRequest(
            '{"settings":{"frequency":"daily"},"request":[],"data":{"activation_id":1,"expire":' . $time . ',"has_expired":false}}'
        );
        // Exec
        $valid = Api::softValidate(
            function() use($license) { return $license; }
        );
        // Assert
        $this->assertInternalType('bool', $valid);
        $this->assertTrue($valid);
    }
    /**
     * Tests soft validate.
     * @since php5-1.0.4
     */
    public function testSoftValidateValidLifetime()
    {
        // Prepare
        $license = new LicenseRequest(
            '{"settings":{"frequency":"daily"},"request":[],"data":{"activation_id":1,"expire":null,"has_expired":false}}'
        );
        // Exec
        $valid = Api::softValidate(
            function() use($license) { return $license; }
        );
        // Assert
        $this->assertInternalType('bool', $valid);
        $this->assertTrue($valid);
    }
    /**
     * Tests exception on softValidate method.
     * @since php5-1.0.4
     * @expectedException Exception
     */
    public function testSoftValidateException()
    {
        // Prepare
        $response = Api::softValidate(
            function() {}
        );
        // Assert
        $this->assertFail('Closure must return an object instance of LicenseRequest.');
    }
}