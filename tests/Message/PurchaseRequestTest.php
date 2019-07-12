<?php namespace Omnipay\Gvp\Message;

use Omnipay\Tests\TestCase;

class PurchaseRequestTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(
            array(
                'amount' => '11.00',
                'currency' => 'TRY',
                'testMode' => true,
                'card' => array(
                    'number' => '4824894728063019',
                    'expiryMonth' => '23',
                    'expiryYear' => '23',
                    'CVV2' => '172',
                ),
            )
        );
    }

    public function testGetData()
    {
        $data = $this->request->getData();

        /*
         * See https://bugs.php.net/bug.php?id=29500 for why this is cast to string
         */
        $this->assertSame('preauth', (string) $data['Transaction']['Type']);
        $this->assertSame('11.00', (string) $data['Transaction']['Amount']);
        $this->assertSame('949', (string) $data['Transaction']['CurrencyCode']);
    }
}
