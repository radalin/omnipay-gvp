<?php

namespace Omnipay\Gvp\Message;

use Exception;
use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Gvp Response
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-gvp
 */
class Response extends AbstractResponse implements RedirectResponseInterface
{
    protected $statusCode;

    /**
     * construct
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     *
     * @throws InvalidResponseException
     */
    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $data = (array) simplexml_load_string((string) $response->getBody());
        } catch (Exception $ex) {
            throw new InvalidResponseException();
        }

        $this->statusCode = $response->getStatusCode();

        parent::__construct($request, $data);
    }

    /**
     * Get a code describing the status of this response
     *
     * @return string
     */
    public function getCode()
    {
        return $this->statusCode;
    }

    /**
     * Whether or not response is successful
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return '00' === (string) $this->data["Transaction"]->Response->Code;
    }

    /**
     * Get transaction reference
     *
     * @return string
     */
    public function getTransactionReference()
    {

        return $this->isSuccessful()
            ? (string) $this->data["Transaction"]->RetrefNum : '';
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return (string) $this->data["Transaction"]->Response->Message;
    }

    /**
     * Get error
     *
     * @return string
     */
    public function getError()
    {
        return $this->data["Transaction"]->Response->ErrorMsg." / "
            .$this->data["Transaction"]->Response->SysErrMsg;
    }

    /**
     * Get error code
     *
     * @return string
     */
    public function getErrorCode()
    {
        return (string) $this->data["Transaction"]->Response->ReasonCode;
    }

    /**
     * Get Redirect url
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        if ($this->isRedirect()) {
            $data = array(
                'TransId' => (string) $this->data["Transaction"]->RetrefNum,
            );

            return $this->getRequest()->getEndpoint().'/test/index?'
                .http_build_query($data);
        }
    }

    /**
     * Get is redirect
     *
     * @return boolean
     */
    public function isRedirect()
    {
        return false; //todo
    }

    /**
     * Get Redirect method
     *
     * @return POST
     */
    public function getRedirectMethod()
    {
        return 'POST';
    }

    /**
     * Get Redirect url
     *
     * @return null
     */
    public function getRedirectData()
    {
        return null;
    }
}
