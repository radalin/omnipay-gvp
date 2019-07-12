<?php namespace Omnipay\Gvp\Message;

use Omnipay\Tests\TestCase;

/**
 * Gvp Gateway ResponseTest
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-gvp
 */
class ResponseTest extends TestCase
{
    public function testPurchaseSuccess()
    {
        $httpResponse = $this->getMockHttpResponse('PurchaseSuccess.txt');
        $response = new Response($this->getMockRequest(), $httpResponse->getBody());

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('xxx', $response->getTransactionReference());
        $this->assertSame('Approved', $response->getMessage());
        $this->assertEmpty($response->getRedirectUrl());
    }

    public function testPurchaseFailure()
    {
        $httpResponse = $this->getMockHttpResponse('PurchaseFailure.txt');
        $response = new Response($this->getMockRequest(), $httpResponse->getBody());

        $this->assertFalse($response->isSuccessful());
        $this->assertSame('', $response->getTransactionReference());
        $this->assertSame('Declined', $response->getMessage());
    }

    //    public function testRedirect()
    //    {
    //        $httpResponse = $this->getMockHttpResponse('PurchaseRedirect.txt');
    //
    //        $request = m::mock('\Omnipay\Common\Message\AbstractRequest');
    //        $request->shouldReceive('getReturnUrl')->once()->andReturn('http://sanalmagaza.org/');
    //
    //        $response = new Response($request, $httpResponse->getBody());
    //
    //        $this->assertTrue($response->isRedirect());
    //        $this->assertSame('POST', $response->getRedirectMethod());
    //        $this->assertSame('http://sanalmagaza.org/', $response->getRedirectUrl());
    //
    //        $expectedData = [
    //            'ReturnUrl' => 'http://sanalmagaza.org/',
    //            'ReferanceId' => '130215141054377801316798',
    //        );
    //        $this->assertEquals($expectedData, $response->getRedirectData());
    //    }
}
