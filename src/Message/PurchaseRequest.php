<?php

namespace Omnipay\Gvp\Message;

use DOMDocument;
use Omnipay\Common\Message\AbstractRequest;

/**
 * Gvp Purchase Request
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-gvp
 */
class PurchaseRequest extends AbstractRequest
{
    protected $actionType = 'sales';
    protected $endpoint = '';
    protected $endpoints = array(
        'test' => 'https://sanalposprovtest.garanti.com.tr/VPServlet',
        'purchase' => 'https://sanalposprov.garanti.com.tr/VPServlet',
    );
    protected $currencyCodes = array(
        'TRY' => 949,
        'YTL' => 949,
        'TRL' => 949,
        'TL' => 949,
        'USD' => 840,
        'EUR' => 978,
        'GBP' => 826,
        'JPY' => 392,
    );

    public function getData()
    {
        $this->validate('amount', 'card');
        $this->getCard()->validate();
        $currency = $this->getCurrency();

        $data['Transaction'] = array(
            'Type' => $this->actionType,
            'InstallmentCnt' => $this->getInstallment(),
            'Amount' => $this->getAmountInteger(),
            'CurrencyCode' => $this->currencyCodes[$currency],
            'CardholderPresentCode' => "0",
            'MotoInd' => "N",
            'Description' => "",
            'OriginalRetrefNum' => $this->getTransactionId(),
            'CepBank' => array(
                'GSMNumber' => $this->getCard()->getBillingPhone(),
                'CepBank' => "",
            ),
            'PaymentType' => "K"
            // K->Kredi Kartı, D->Debit Kart, V->Vadesiz Hesap
        );

        return $data;
    }

    public function getInstallment()
    {
        return $this->getParameter('installment');
    }

    public function sendData($data)
    {

        // API info
        $data['Version'] = "v0.01";
        $data['Mode'] = $this->getTestMode() ? 'TEST' : 'PROD';

        $data['Terminal'] = array(
            'ProvUserID' => $this->getUserName(),
            'HashData' => $this->getTransactionHash($this->getPassword()),
            'UserID' => $this->getUserName(),
            'ID' => $this->getTerminalId(),
            'MerchantID' => $this->getMerchantId(),
        );

        $data['Card'] = array(
            'Number' => $this->getCard()->getNumber(),
            'ExpireDate' => $this->getCard()->getExpiryDate('my'),
            'CVV2' => $this->getCard()->getCvv(),
        );

        $data['Order'] = array(
            'OrderID' => $this->getTransactionId(),
            'GroupID' => "",
        );

        $data['Customer'] = array(
            'IPAddress' => '127.0.0.1', //$this->getClientIp(),
            'EmailAddress' => $this->getCard()->getEmail(),
        );

        // Build api post url
        $this->endpoint = $this->getTestMode() == true
            ? $this->endpoints["test"] : $this->endpoints["purchase"];

        $document = new DOMDocument('1.0', 'UTF-8');
        $root = $document->createElement('GVPSRequest');

        // recursive array to xml
        $xml = function ($root, $data) use ($document, &$xml) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $subs = $document->createElement($key);
                    $root->appendChild($subs);
                    $xml($subs, $value);
                } else {
                    $root->appendChild($document->createElement($key, $value));
                }
            }
        };

        $xml($root, $data);

        $document->appendChild($root);

        $httpResponse = $this->httpClient->request(
            'POST',
            $this->endpoint,
            array(),
            $document->saveXML()
        );

        $data = (array) simplexml_load_string($httpResponse->getBody());

        return $this->response = new Response($this, $data);
    }

    public function getUserName()
    {
        return $this->getParameter('username');
    }

    /**
     * @param string $password
     *
     * @return string
     */
    private function getTransactionHash($password)
    {
        return strtoupper(
            sha1(
                $this->getTransactionId().
                $this->getTerminalId().
                $this->getCard()->getNumber().
                $this->getAmountInteger().
                $this->getSecurityHash($password)
            )
        );
    }

    public function getTerminalId()
    {
        return $this->getParameter('terminalId');
    }

    private function getSecurityHash($password)
    {
        $tidPrefix = str_repeat('0', 9 - strlen($this->getTerminalId()));
        $terminalId = sprintf('%s%s', $tidPrefix, $this->getTerminalId());

        return strtoupper(sha1($password.$terminalId));
    }

    public function getPassword()
    {
        return $this->getParameter('password');
    }

    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    public function setTerminalId($value)
    {
        return $this->setParameter('terminalId', $value);
    }

    public function setUserName($value)
    {
        return $this->setParameter('username', $value);
    }

    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    public function getRefundUserName()
    {
        return $this->getParameter('refundusername');
    }

    public function setRefundUserName($value)
    {
        return $this->setParameter('refundusername', $value);
    }

    public function getRefundPassword()
    {
        return $this->getParameter('refundpassword');
    }

    public function setRefundPassword($value)
    {
        return $this->setParameter('refundpassword', $value);
    }

    public function getSecurekey()
    {
        return $this->getParameter('securekey');
    }

    public function setSecurekey($value)
    {
        return $this->setParameter('securekey', $value);
    }

    public function setInstallment($value)
    {
        return $this->setParameter('installment', $value);
    }

    public function getType()
    {
        return $this->getParameter('type');
    }

    public function setType($value)
    {
        return $this->setParameter('type', $value);
    }
}
