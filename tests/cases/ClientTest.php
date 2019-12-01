<?php

use LicenseKeys\Utility\Client;

/**
 * Tests Client class.
 *
 * @author Alejandro Mostajo <info@10quality.com> 
 * @version 1.0.2
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
    /**
     * Tests ssl configuration.
     * @since 1.0.2
     */
    public function testSSL()
    {
        // Prepare
        $client = $this->getClientHttpsMock('');
        $response = $client->call(
            'test.php',
            $this->getLicenseRequestMock('{"settings":{"url":"https://test.com/"},"request":[],"data":[]}')
        );
        // Assert
        $this->assertNull($response);
    }
}