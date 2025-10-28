<?php
/**
 * User: ADMIN
 * Date: 14/01/2020 4:38 CH
 */

namespace Cloudteam\Core\Xml;

use Cloudteam\Core\Xml\Providers\BaseXmlRender;
use Cloudteam\Core\Xml\Providers\MInvoiceXmlRender;
use DOMDocument;
use Illuminate\Support\Facades\Log;
use RobRichards\XMLSecLibs\XMLSecEnc;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use RuntimeException;

/**
 * Class XmlCoreTT78
 *
 * @SuppressWarnings(PHPMD)
 */
class XmlCoreTT78
{
    public string $errMsg;

    public string $xmlFormat;

    public int $errCode = -99;

    public DOMDocument $domDocument;


    public array $datas;

    /**
     * @var BaseXmlRender|mixed
     */
    public $xmlRender;

    /**
     * @var string
     */
    private $keyFilePath;

    /**
     * @var string
     */
    private $certFilePath;

    /**
     * @var string
     */
    private $fileID;

    /**
     * @var bool
     */
    private bool $isKeyFile = false;

    public function __construct($datas = [], $signatures = [], $invoiceProviderName = 'MInvoice', $xmlFormat = '', $fileID = '')
    {
        $this->domDocument                     = new DOMDocument('1.0', 'utf-8');
        $this->domDocument->preserveWhiteSpace = false;
        $this->domDocument->formatOutput       = false;
        $this->domDocument->encoding           = 'UTF-8';

        $this->datas     = $datas;
        $this->xmlFormat = $xmlFormat;
        $this->fileID    = $fileID;

        if ($invoiceProviderName === 'MInvoice') {
            $this->xmlRender = new MInvoiceXmlRender();
        }

        //if ($invoiceProviderName === 'Viettel') {
        //    $this->xmlRender = new ViettelXmlRender();
        //}

        //note: nếu setting không có thì default dùng MInvoice render
        if (! $this->xmlRender) {
            $this->xmlRender = new MInvoiceXmlRender();
        }

        if ($signatures) {
            [$this->keyFilePath, $this->certFilePath] = $signatures;
        } else {
            $this->isKeyFile    = true;
            $this->keyFilePath  = __DIR__.'/files/0106026495-998.key';
            $this->certFilePath = __DIR__.'/files/0106026495-998.crt';
        }
    }

    private function renderXml($bodyElement)
    {
        if ($this->xmlRender) {
            $this->xmlRender->renderXml($this, $bodyElement, $this->xmlFormat, $this->fileID);
        }
    }

    public function createXmlBody($mainElem, $datas)
    {
        foreach ($datas as $key => $items) {
            if (! is_array($items)) {
                $mainElem->appendChild($this->domDocument->createElement($key, htmlspecialchars($items)));

                continue;
            }

            if (isMultidimensionalArray($items)) {
                $subMainElem = $mainElem->appendChild($this->domDocument->createElement($key));

                foreach ($items as $itemKey => $item) {
                    if (! is_array($item)) {
                        $subMainElem->appendChild($this->domDocument->createElement($itemKey, htmlspecialchars($item)));

                        continue;
                    }

                    $subItems = $item;

                    if (($itemKey !== 'HDon' && $itemKey !== 'CTu') && ($key !== 'TTKhac' && $itemKey !== 'TTin')) {
                        $subOfSubMainElem = $subMainElem->appendChild($this->domDocument->createElement($itemKey));
                    }

                    if ($key === 'TTKhac' && isMultidimensionalArray($subItems)) {
                        foreach ($subItems as $subItem) {
                            $tempMainElem = $subMainElem->appendChild($this->domDocument->createElement('TTin'));

                            foreach ($subItem as $subItemValKey => $subItemVal) {
                                $tempMainElem->appendChild($this->domDocument->createElement($subItemValKey, htmlspecialchars($subItemVal)));
                            }
                        }
                    } else {
                        foreach ($subItems as $subKey => $subItem) {
                            if ($itemKey === 'HDon' || $itemKey === 'CTu') {
                                $subOfSubMainElem = $subMainElem->appendChild($this->domDocument->createElement($itemKey));
                            }

                            if (is_array($subItem) && isMultidimensionalArray($subItem) && ($itemKey == 'TToan' || $subKey == 'TTKhac')) {
                                $tempMainElem = $subOfSubMainElem->appendChild($this->domDocument->createElement($subKey));

                                foreach ($subItem as $subItemValKey => $subItemVal) {
                                    if (($subItemValKey === 'LTSuat' || $subItemValKey === 'TTin') && isset($subItemVal[0]) && is_array($subItemVal[0])) {
                                        foreach ($subItemVal as $ltsuatItems) {
                                            $tempElem = $tempMainElem->appendChild($this->domDocument->createElement($subItemValKey));

                                            foreach ($ltsuatItems as $tempKey => $lastItem) {
                                                $tempElem->appendChild($this->domDocument->createElement($tempKey, htmlspecialchars($lastItem ?? '')));
                                            }
                                        }
                                    } else {
                                        $tempElem = $tempMainElem->appendChild($this->domDocument->createElement($subItemValKey));

                                        foreach ($subItemVal as $tempKey => $lastItem) {
                                            $tempElem->appendChild($this->domDocument->createElement($tempKey, htmlspecialchars($lastItem ?? '')));
                                        }
                                    }
                                }

                                continue;
                            }

                            if (is_array($subItem)) {
                                foreach ($subItem as $subItemValKey => $subItemVal) {
                                    if (is_int($subKey)) {
                                        $tempElem = $subOfSubMainElem;
                                    } else {
                                        $tempElem = $subOfSubMainElem->appendChild($this->domDocument->createElement($subKey));
                                    }
                                    if (is_array($subItemVal)) {
                                        $this->createXmlBody($tempElem, $subItemVal);
                                    } else {
                                        $tempElem->appendChild($this->domDocument->createElement($subItemValKey, htmlspecialchars($subItemVal ?? '')));
                                    }
                                }

                                continue;
                            }

                            if ($subItem || is_int($subItem) || $subItem == 0) {
                                $subOfSubMainElem->appendChild($this->domDocument->createElement($subKey, htmlspecialchars($subItem ?? '')));
                            }
                        }
                    }
                }

                continue;
            }

            if (! is_int($key)) {
                $subMainElem = $mainElem->appendChild($this->domDocument->createElement($key));
            } else {
                $subMainElem = $mainElem;
            }

            foreach ($items as $subKey => $itemDatas) {
                $finalValue = $itemDatas;
                if (is_string($finalValue)) {
                    $finalValue = htmlspecialchars($finalValue ?? '');
                }
                if (is_array($finalValue)) {
                    $finalValue = '';
                }
                $subMainElem->appendChild($this->domDocument->createElement($subKey, $finalValue));
            }
        }
    }

    /**
     * @return false|string
     */
    private function getCertificateContent()
    {
        return $this->isKeyFile ? file_get_contents($this->certFilePath) : $this->certFilePath;
    }

    /**
     * Kiểm tra Cert hết hạn hay chưa
     *
     * @return bool
     */
    private function isCertValid()
    {
        $cert = openssl_x509_read($this->getCertificateContent());

        $certDatas = openssl_x509_parse($cert);

        $validTo = $certDatas['validTo_time_t'];

        return $validTo > now()->unix();
    }

    /**
     * Ký vào file. Nếu Cert hết hạn sẽ báo lỗi.
     *
     * @return XmlCoreTT78
     * @noinspection PhpParamsInspection
     * @throws \Exception
     */
    public function sign($signElement, $bodyElement, $signElemIndex = 0, $isXmlMTT = false)
    {
        if (! $this->isCertValid()) {
            throw new RuntimeException('CERTIFICATE EXPIRED');
        }

        $objDSig = new XMLSecurityDSig('');
        $objDSig->setCanonicalMethod(\RobRichards\XMLSecLibs\XMLSecurityDSig::C14N);

        $signElementNban = $subSignElementNban = $subBodyElemNBan = $signElemNbanIndex = null;
        //note: $subBodyElem => thẻ cần ký
        if ($signElement !== 'TDiep') {
            $subBodyElem = "DL$bodyElement";
            if ($bodyElement === 'BTHDLieu') {
                $subBodyElem = 'DLBTHop';
            }
        } else {
            if ($isXmlMTT) {
                //note: ký MTT thì kí 2 lần. 1 vào CKSNNT(thuộc the TDiep), 2 vào DSCKS->NBan (thuộc thẻ HDon)
                //$subBodyElem     = 'DLieu';

                $subBodyElem     = 'DLHDon';
                $signElement     = 'CKSNNT';
                $signElemIndex   = 0;

                $subBodyElemNBan    = 'DLHDon';
                $signElementNban    = 'DSCKS';
                $subSignElementNban = 'NBan';
                $signElemNbanIndex  = 0;
            } else {
                $subBodyElem   = 'DLieu';
                //$subBodyElem   = 'DLHDon';
                $signElement   = 'CKSNNT';
                $signElemIndex = 0;
            }
        }

        $signingTimeObject  = $this->domDocument->createElement('SignatureProperties');
        $signPropertyObject = $signingTimeObject->appendChild($this->domDocument->createElement('SignatureProperty'));
        $signPropertyObject->appendChild($this->domDocument->createElement('SigningTime', now()->toDateTimeLocalString()));
        $signPropertyObject->setAttribute('Target', '#signtime');

        $objNode         = $objDSig->addCustomObject($signingTimeObject, 'signtime');

        $objDSig->addReference(
            $this->domDocument->getElementsByTagName($subBodyElem)->item(0),
            \RobRichards\XMLSecLibs\XMLSecurityDSig::SHA1, ['http://www.w3.org/2000/09/xmldsig#enveloped-signature'], ['overwrite' => false]
        );
        $objDSig->addReference(
            $objNode, \RobRichards\XMLSecLibs\XMLSecurityDSig::SHA1, null, ['overwrite' => false]
        );

        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, ['type' => 'private']);
        $objKey->loadKey($this->keyFilePath, $this->isKeyFile);
        $objDSig->sign($objKey);
        $objDSig->add509Cert($this->getCertificateContent(), true, false, ['subjectName' => true]);
        $objDSig->appendSignature(
            $this->domDocument->documentElement->getElementsByTagName($signElement)->item($signElemIndex)
        );

        if ($subBodyElemNBan) {
            $parentNode = $this->domDocument->documentElement
                ->getElementsByTagName($signElementNban) // DSCKS
                ->item($signElemNbanIndex)
                ->getElementsByTagName($subSignElementNban) // NBan
                ->item($signElemIndex);

            if ($parentNode) {
                $sigId      = 'NBan-data123';
                $propId     = 'SignatureProperty-' . $sigId;

                // ===== 2) Signature #2 (NBan) — CHỈ có signtime-NBan (không có signtime)
                $objDSig2 = new XMLSecurityDSig('');
                $objDSig2->setCanonicalMethod(\RobRichards\XMLSecLibs\XMLSecurityDSig::C14N);

                $objDSig2->sigNode->setAttribute('Id', $sigId);

                $signingTimeNbanObject  = $this->domDocument->createElement('SignatureProperties');
                $signPropertyNbanObject = $signingTimeNbanObject->appendChild($this->domDocument->createElement('SignatureProperty'));
                $signPropertyNbanObject->appendChild($this->domDocument->createElement('SigningTime', now()->toDateTimeLocalString()));
                $signPropertyNbanObject->setAttribute('Target', '#signtime-NBan');
                $signPropertyNbanObject->setAttribute('Id', $propId);

                $sigtimeNBanNode = $objDSig2->addCustomObject(data: $signingTimeNbanObject, objectId: 'signtime-NBan');

                $objDSig2->addReference(node: $this->domDocument->getElementsByTagName($subBodyElem)->item(0), algorithm: \RobRichards\XMLSecLibs\XMLSecurityDSig::SHA1, arTransforms: ['http://www.w3.org/2000/09/xmldsig#enveloped-signature'], options: ['overwrite' => false]);
                $objDSig2->addReference(node: $sigtimeNBanNode, algorithm: \RobRichards\XMLSecLibs\XMLSecurityDSig::SHA1, arTransforms: null, options: ['overwrite' => false]);

                $k2 = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, ['type' => 'private']);
                $k2->loadKey($this->keyFilePath, $this->isKeyFile);
                $objDSig2->sign($k2);
                $objDSig2->add509Cert($this->getCertificateContent(), true, false, ['subjectName' => true]);

                $objDSig2->appendSignature($parentNode);
            } else {
                info("Khong tim thay parentNode: $signElementNban-$signElemNbanIndex-$subSignElementNban");
            }
        }

        return $this;
    }

    /**
     * @param bool $sign
     *
     * @return bool|string|string[]
     */
    public function getRawData($signElement, $bodyElement, $signElemIndex = 0, $sign = true, $isXmlMTT = false)
    {
        try {
            $this->renderXml($bodyElement);

            if ($sign) {
                $this->sign($signElement, $bodyElement, $signElemIndex, $isXmlMTT);
            }

            $data = $this->domDocument->saveXML();

            $data = str_replace(["\t", "\n\r", "\n", "\r"], '', $data);

            return str_replace(['>  <', '>    <'], '><', $data);
        } catch (\RuntimeException $exception) {
            $this->errMsg = "{$exception->getMessage()}";
            $this->errCode = -15;
            Log::error("{$exception->getMessage()} - {$exception->getFile()} - {$exception->getLine()}");

            return null;
        } catch (\Exception $exception) {
            $this->errMsg = "{$exception->getMessage()}";
            Log::error("{$exception->getMessage()} - {$exception->getFile()} - {$exception->getLine()}");

            return false;
        }
    }

    /**
     * @param XMLSecurityDSig $objXMLSecDSig
     * @param DOMDocument     $doc
     *
     * @return array
     * @throws \Exception
     */
    private static function validateXml(XMLSecurityDSig $objXMLSecDSig, DOMDocument $doc): array
    {
        $objDSig = $objXMLSecDSig->locateSignature($doc);
        if (! $objDSig) {
            return [
                'message' => 'Cannot locate Signature Node.',
                'result'  => false,
            ];
        }
        $objXMLSecDSig->canonicalizeSignedInfo();

        $retVal = $objXMLSecDSig->validateReference();
        if (! $retVal) {
            return [
                'message' => 'Reference Validation Failed.',
                'result'  => false,
            ];
        }

        $objKey = $objXMLSecDSig->locateKey();
        XMLSecEnc::staticLocateKeyInfo($objKey, $objDSig);

        if ($objXMLSecDSig->verify($objKey) === 1) {
            return [
                'message' => 'Signature validated.',
                'result'  => true,
            ];
        }

        return [
            'message' => 'Failure.',
            'result'  => false,
        ];
    }

    /**
     * @param $xmlData
     *
     * @return string|array
     * @throws \Exception
     */
    public static function verify($xmlData)
    {
        try {
            //$xmlData = str_replace(["\t", "\n\r", "\n", "\r"], '', $xmlData);
            //$xmlData = str_replace(['>  <', '>    <'], '', $xmlData);

            $doc = new DOMDocument();
            if ($xmlData && @$doc->loadXML($xmlData)) {
                $objXMLSecDSig = new XMLSecurityDSig();

                return self::validateXml($objXMLSecDSig, $doc);
            }

            Log::error("Can not load XML data: $xmlData");

            return [
                'message' => "Can not load XML data: $xmlData",
                'result'  => false,
            ];
        } catch (\Exception $e) {
            Log::error("Verify XML data failed: {$e->getMessage()}");

            return [
                'message' => $e->getMessage(),
                'result'  => false,
            ];
        }
    }
}

