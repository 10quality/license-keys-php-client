<?php

use LicenseKeys\Utility\Client;

/**
 * Tests Client class.
 *
 * @author Alejandro Mostajo <info@10quality.com> 
 * @version 1.0.0
 * @package LicenseKeys\Utility
 * @license MIT
 */
class ClientTest extends Api_TestCase
{
    /**
     * Tests constructor.
     * @since 1.0.0
     */
    public function testConstructor()
    {
        // Prepare
        $client = new Client();
        // Assert
        $this->assertInstanceOf(Client::class, $client);
        $this->assertTrue(is_object($client));
    }
    /**
     * Tests static constructor.
     * @since 1.0.0
     */
    public function testStaticConstructor()
    {
        // Prepare
        $client = Client::instance();
        // Assert
        $this->assertInstanceOf(Client::class, $client);
        $this->assertTrue(is_object($client));
    }
    /**
     * Tests simple empty call.
     * @since 1.0.0
     */
    public function testEmptyCall()
    {
        // Prepare
        $client = new Client();
        $response = $client->call('test.php', $this->getLicenseRequestMock());
        // Assert
        $this->assertNull($response);
    }
}