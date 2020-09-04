<?php

use LicenseKeys\Utility\Client;

/**
 * Tests Client class.
 *
 * @author Alejandro Mostajo <info@10quality.com> 
 * @version 1.2.1
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
    /**
     * Tests setOption.
     * @since 1.2.1
     * @group curl
     */
    public function testSetOption()
    {
        // Prepare
        $license = $this->getLicenseRequestMock('{"settings":{"url":"https://google.com/"},"request":[],"data":[]}');
        // Execute
        $response = Client::instance()
            ->set([
                CURLOPT_COOKIESESSION => false,
                CURLOPT_COOKIEFILE => '/tmp/phpunit_files/',
            ])
            ->call('gmail', $license, 'GET');
        $options = Client::instance()->getOptions();
        // Assert
        $this->assertArrayHasKey(CURLOPT_COOKIESESSION, $options);
        $this->assertArrayHasKey(CURLOPT_COOKIEFILE, $options);
        $this->assertArrayNotHasKey(CURLOPT_COOKIEJAR, $options);
        $this->assertEquals(false, $options[CURLOPT_COOKIESESSION]);
        $this->assertEquals('/tmp/phpunit_files/', $options[CURLOPT_COOKIEFILE]);
    }
}