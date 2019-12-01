<?php
use LicenseKeys\Utility\Client;
use LicenseKeys\Utility\LicenseRequest;
/**
 * Tests EventTest class.
 *
 * @author Alejandro Mostajo <info@10quality.com> 
 * @version 1.2.0
 * @package LicenseKeys\Utility
 * @license MIT
 */
class EventTest extends Api_TestCase
{
    /**
     * Tests event.
     * @since 1.2.0
     */
    public function testEventStart()
    {
        // Prepare
        $microtimeStart = null;
        $license = $this->getLicenseRequestMock('{"settings":{"url":"https://google.com/"},"request":[],"data":[]}');
        // Execute
        $response = Client::instance()
            ->on('start', function($microtime) use(&$microtimeStart) {
                $microtimeStart = $microtime;
            })
            ->call('gmail', $license, 'GET');
        // Assert
        $this->assertNotNull($microtimeStart);
    }
    /**
     * Tests event.
     * @since 1.2.0
     */
    public function testEventFinish()
    {
        // Prepare
        $microtimeStart = null;
        $microtimeFinish = null;
        $license = $this->getLicenseRequestMock('{"settings":{"url":"https://google.com/"},"request":[],"data":[]}');
        // Execute
        $response = Client::instance()
            ->on('finish', function($finish, $start) use(&$microtimeStart, &$microtimeFinish) {
                $microtimeStart = $start;
                $microtimeFinish = $finish;
            })
            ->call('gmail', $license, 'GET');
        // Assert
        $this->assertNotNull($microtimeFinish);
        $this->assertNotEquals($microtimeStart, $microtimeFinish);
    }
    /**
     * Tests event.
     * @since 1.2.0
     */
    public function testEventEndpoint()
    {
        // Prepare
        $endpoint = null;
        $url = null;
        $license = LicenseRequest::create(
            'https://google.com/',
            'test',
            'UNIT',
            'test'
        );
        // Execute
        $response = Client::instance()
            ->on('endpoint', function($ep, $u) use(&$endpoint, &$url) {
                $endpoint = $ep;
                $url = $u;
            })
            ->call('gmail', $license, 'GET');
        // Assert
        $this->assertNotNull($endpoint);
        $this->assertNotNull($url);
        $this->assertEquals('?action=gmail', $endpoint);
        $this->assertEquals('https://google.com/?action=gmail', $url);
    }
    /**
     * Tests event.
     * @since 1.2.0
     */
    public function testEventWpRestEndpoint()
    {
        // Prepare
        $endpoint = null;
        $url = null;
        $license = LicenseRequest::create(
            'https://google.com',
            'test',
            'UNIT',
            'test',
            null,
            'wp_rest'
        );
        // Execute
        $response = Client::instance()
            ->on('endpoint', function($ep, $u) use(&$endpoint, &$url) {
                $endpoint = $ep;
                $url = $u;
            })
            ->call('license_key_validate', $license, 'GET');
        // Assert
        $this->assertNotNull($endpoint);
        $this->assertNotNull($url);
        $this->assertEquals('/wp-json/woo-license-keys/v1/validate', $endpoint);
        $this->assertEquals('https://google.com/wp-json/woo-license-keys/v1/validate', $url);
    }
}