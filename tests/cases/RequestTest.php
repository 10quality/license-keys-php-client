<?php

use LicenseKeys\Utility\LicenseRequest;

/**
 * Tests RequestTest class.
 *
 * @author Alejandro Mostajo <info@10quality.com> 
 * @version 1.0.0
 * @package LicenseKeys\Utility
 * @license MIT
 */
class RequestTest extends Api_TestCase
{
    /**
     * Tests constructor.
     * @since 1.0.0
     */
    public function testConstructor()
    {
        // Prepare
        $license = new LicenseRequest('');
        // Assert
        $this->assertInstanceOf(LicenseRequest::class, $license);
        $this->assertTrue(is_object($license));
    }
    /**
     * Tests static constructor.
     * @since 1.0.0
     */
    public function testStaticConstructor()
    {
        // Prepare
        $license = LicenseRequest::create(
            'http://localhost/test',
            'STORECODE4',
            'SKU1',
            'aKey-777'
        );
        // Assert object
        $this->assertInstanceOf(LicenseRequest::class, $license);
        $this->assertTrue(is_object($license));
        // Assert properties
        $this->assertEquals('http://localhost/test', $license->url);
        $this->assertEquals(LicenseRequest::DAILY_FREQUENCY, $license->frequency);
        $this->assertEquals(0, $license->nextCheck);
        $this->assertInternalType('array', $license->data);
        $this->assertInternalType('array', $license->request);
        $this->assertEmpty($license->data);
        $this->assertNotEmpty($license->request);
        $this->assertFalse($license->isValid);
        $this->assertNull($license->settings);
        $this->assertFalse($license->isOffline);
        // Assert request
        $this->assertArrayHasKey('store_code', $license->request);
        $this->assertArrayHasKey('sku', $license->request);
        $this->assertArrayHasKey('license_key', $license->request);
        // Assert request data
        $this->assertEquals('STORECODE4', $license->request['store_code']);
        $this->assertEquals('SKU1', $license->request['sku']);
        $this->assertEquals('aKey-777', $license->request['license_key']);
    }
    /**
     * Tests domain injection.
     * @since 1.0.0
     */
    public function testDomainInjection()
    {
        // Prepare
        $license = LicenseRequest::create(
            'http://localhost/test',
            'STORECODE4',
            'SKU1',
            'aKey-777'
        );
        $license->request['domain'] = 'localhost';
        // Assert
        $this->assertArrayHasKey('domain', $license->request);
        $this->assertEquals('localhost', $license->request['domain']);
    }
    /**
     * Tests constants.
     * @since 1.0.0
     */
    public function testConstants()
    {
        // Assert
        $this->assertInternalType('string', LicenseRequest::DAILY_FREQUENCY);
        $this->assertInternalType('string', LicenseRequest::HOURLY_FREQUENCY);
        $this->assertInternalType('string', LicenseRequest::WEEKLY_FREQUENCY);
    }
    /**
     * Tests construct with a valid JSON license sample.
     * @since 1.0.0
     */
    public function testJSONConstruct()
    {
        // Prepare
        $license = new LicenseRequest(
            '{"settings":{"url":"http:\/\/localhost\/test","frequency":"daily","next_check":100},'
                .'"request":{"store_code":"STORECODE4","sku":"SKU1","license_key":"aKey-777"},'
                .'"data":{"expire":999999999999,"activation_id":404,"expire_date":"2020-02-25 18:57",'
                .'"timezone":"UTC","the_key":"aKey-777","url":"http:\/\/localhost\/test\/?key=aKey-777",'
                .'"has_expired":false,"status":"active","allow_offline":true,"offline_interval":"days","offline_value":1}}'
        );
        // Assert properties
        $this->assertEquals('http://localhost/test', $license->url);
        $this->assertEquals(LicenseRequest::DAILY_FREQUENCY, $license->frequency);
        $this->assertEquals(100, $license->nextCheck);
        $this->assertInternalType('array', $license->data);
        $this->assertInternalType('array', $license->request);
        $this->assertNotEmpty($license->data);
        $this->assertNotEmpty($license->request);
        $this->assertTrue($license->isValid);
        $this->assertNull($license->settings);
        // Assert request
        $this->assertArrayHasKey('store_code', $license->request);
        $this->assertArrayHasKey('sku', $license->request);
        $this->assertArrayHasKey('license_key', $license->request);
        $this->assertArrayHasKey('activation_id', $license->request);
        // Assert request data
        $this->assertEquals('STORECODE4', $license->request['store_code']);
        $this->assertEquals('SKU1', $license->request['sku']);
        $this->assertEquals('aKey-777', $license->request['license_key']);
        $this->assertEquals('404', $license->request['activation_id']);
        // Assert data
        $this->assertArrayHasKey('the_key', $license->data);
        $this->assertArrayHasKey('expire', $license->data);
        $this->assertArrayHasKey('has_expired', $license->data);
    }
    /**
     * Tests construct with a valid JSON license sample.
     * @since 1.0.0
     */
    public function testCasting()
    {
        // Prepare
        $license = LicenseRequest::create(
            'http://localhost/test',
            'STORECODE4',
            'SKU1',
            'aKey-777'
        );
        // Assert properties
        $this->assertEquals(
            '{"settings":{"url":"http:\/\/localhost\/test","frequency":"daily","next_check":0,"version":"1.1.0","retries":0},'
                .'"request":{"store_code":"STORECODE4","sku":"SKU1","license_key":"aKey-777"},'
                .'"data":[],"meta":[]}',
            (string)$license
        );
    }
    /**
     * Tests invalidation of license due to missing frequency.
     * @since 1.0.0
     */
    public function testUnsetFrequency()
    {
        // Prepare
        $license = new LicenseRequest(
            '{"settings":{"url":"http:\/\/localhost\/test","next_check":100},'
                .'"request":{"store_code":"STORECODE4","sku":"SKU1","license_key":"aKey-777"},'
                .'"data":{"expire":999999999999,"activation_id":404,"expire_date":"2020-02-25 18:57",'
                .'"timezone":"UTC","the_key":"aKey-777","url":"http:\/\/localhost\/test\/?key=aKey-777",'
                .'"has_expired":false,"status":"active","allow_offline":true,"offline_interval":"days","offline_value":1}}'
        );
        // Assert properties
        $this->assertFalse($license->isValid);
    }
    /**
     * Tests offline mode.
     * @since 1.0.0
     */
    public function testOffline()
    {
        // Prepare
        $license = new LicenseRequest(
            '{"settings":{"url":"http:\/\/localhost\/test","frequency":"daily","next_check":100},'
                .'"request":[],'
                .'"data":{"allow_offline":true,"offline_interval":"days","offline_value":1}}'
        );
        $license->enableOffline();
        // Assert properties
        $this->assertNotEquals(100, $license->nextCheck);
        $this->assertTrue($license->isOffline);
        $this->assertTrue($license->isOfflineValid);
    }
    /**
     * Tests add meta data.
     * @since 1.1.0
     */
    public function testAddGetMeta()
    {
        // Prepare
        $license = new LicenseRequest(
            '{"settings":{"url":"http:\/\/localhost\/test","frequency":"daily","next_check":100},'
                .'"request":[],'
                .'"data":{"allow_offline":true,"offline_interval":"days","offline_value":1},'
                .'"meta":[]}'
        );
        $obj = new stdClass;
        $obj->a = 10;
        $obj->b = 20;
        // Execute
        $license->add('int', 1);
        $license->add('string', 'test');
        $license->add('float', 0.9);
        $license->add('array', [1,2]);
        $license->add('obj', $obj);
        // Assert properties
        $this->assertInternalType('int', $license->get('int'));
        $this->assertEquals(1, $license->get('int'));
        $this->assertInternalType('string', $license->get('string'));
        $this->assertEquals('test', $license->get('string'));
        $this->assertInternalType('float', $license->get('float'));
        $this->assertEquals(0.9, $license->get('float'));
        $this->assertInternalType('array', $license->get('array'));
        $this->assertEquals([1,2], $license->get('array'));
        $this->assertInternalType('array', $license->get('obj'));
        $this->assertEquals(['a' => 10, 'b' => 20], $license->get('obj'));
    }
    /**
     * Tests add meta data.
     * @since 1.1.0
     */
    public function testGetDefaultMeta()
    {
        // Prepare
        $license = new LicenseRequest(
            '{"settings":{"url":"http:\/\/localhost\/test","frequency":"daily","next_check":100},'
                .'"request":[],'
                .'"data":{"allow_offline":true,"offline_interval":"days","offline_value":1},'
                .'"meta":[]}'
        );
        // Execute
        $license->add('a', 1);
        // Assert properties
        $this->assertNull($license->get('b'));
        $this->assertInternalType('float', $license->get('b', 0.9));
        $this->assertEquals(0.9, $license->get('b', 0.9));
    }
    /**
     * Tests wrong key data type.
     * @since 1.1.0
     * @expectedException Exception
     */
    public function testAddMetaException()
    {// Prepare
        $license = new LicenseRequest(
            '{"settings":{"url":"http:\/\/localhost\/test","frequency":"daily","next_check":100},'
                .'"request":[],'
                .'"data":{"allow_offline":true,"offline_interval":"days","offline_value":1},'
                .'"meta":[]}'
        );
        // Execute
        $license->add(1);
    }
    /**
     * Tests wrong key data type.
     * @since 1.1.0
     * @expectedException Exception
     */
    public function testGetMetaException()
    {// Prepare
        $license = new LicenseRequest(
            '{"settings":{"url":"http:\/\/localhost\/test","frequency":"daily","next_check":100},'
                .'"request":[],'
                .'"data":{"allow_offline":true,"offline_interval":"days","offline_value":1},'
                .'"meta":[]}'
        );
        // Execute
        $license->get(1);
    }
    /**
     * Tests construct with a valid JSON license sample.
     * @since 1.1.0
     */
    public function testMetaCasting()
    {
        // Prepare
        $license = LicenseRequest::create(
            'http://localhost/test',
            'STORECODE4',
            'SKU1',
            'aKey-777'
        );
        // Execute
        $license->add('a', 1);
        $license->add('b', 'b');
        // Assert properties
        $this->assertEquals(
            '{"settings":{"url":"http:\/\/localhost\/test","frequency":"daily","next_check":0,"version":"1.1.0","retries":0},'
                .'"request":{"store_code":"STORECODE4","sku":"SKU1","license_key":"aKey-777"},'
                .'"data":[],"meta":{"a":1,"b":"b"}}',
            (string)$license
        );
    }
}