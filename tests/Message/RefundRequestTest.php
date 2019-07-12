<?php namespace Omnipay\Gvp\Message;

use Omnipay\Tests\TestCase;

/**
 * Gvp Gateway Refund RequestTest
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-gvp
 */
class RefundRequestTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->request = new RefundRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(
            array(
                'amount' => '11.00',
                'currency' => 'TRY',
                'testMode' => true,
                'card' => array(
                    'number' => '5406675406675403',
                    'expiryMonth' => '07',
                    'expiryYear' => '23',
                    'CVV2' => '000',
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
        $this->assertSame('refund', (string) $data['Transaction']['Type']);
        $this->assertSame('11.00', (string) $data['Transaction']['Amount']);
        $this->assertSame('949', (string) $data['Transaction']['CurrencyCode']);
    }
}
