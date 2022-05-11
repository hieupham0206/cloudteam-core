<?php
/**
 * User: ADMIN
 * Date: 14/01/2020 4:38 CH
 */

namespace Cloudteam\Core\Xml;

use DOMDocument;
use Illuminate\Support\Facades\Log;
use RobRichards\XMLSecLibs\XMLSecEnc;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use RuntimeException;

/**
 * Class XmlCore
 *
 * @SuppressWarnings(PHPMD)
 */
class XmlCoreTT32
{
    /**
     * @var string
     */
    public $errMsg;
    /**
     * @var DOMDocument
     */
    public $domDocument;
    /**
     * @var string|array
     */
    private $datas;
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
    private $bundleFilePath;
    /**
     * @var bool
     */
    private $isKeyFile = false;

    public function __construct($datas = [], $signatures = [])
    {
        $this->domDocument                     = new DOMDocument('1.0', 'utf-8');
        $this->domDocument->preserveWhiteSpace = false;
        $this->domDocument->formatOutput       = false;
        $this->domDocument->encoding           = 'UTF-8';

        $this->datas = $datas;

        if ($signatures) {
			[$this->keyFilePath, $this->certFilePath] = $signatures;
        } else {
			$this->isKeyFile    = true;
			$this->keyFilePath  = __DIR__ . '/files/0106026495-998.key';
			$this->certFilePath = __DIR__ . '/files/0106026495-998.crt';
        }

        $this->loadXml();
    }

    private function loadXml()
    {
        $root    = $this->domDocument->appendChild($this->domDocument->createElement('Invoice'));
        $newnode = $this->domDocument->createElement('Content');
        $content = $root->appendChild($newnode);
//        $newnode->setAttribute('Id', 'SigningData');
        $this->toXml($content, $this->datas);
    }

    private function toXml($rootElem, array $datas)
    {
        $collection = collect($datas);

        $products = $collection->pluck('Products');
        $contents = $datas['Content'];
        foreach ($contents as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            $rootElem->appendChild($this->domDocument->createElement($key, $value));
        }

        $productsNode = $rootElem->appendChild($this->domDocument->createElement('Products'));
        foreach ($products as $productDetails) {
            foreach ($productDetails as $productDetail) {
                $productNode = $productsNode->appendChild($this->domDocument->createElement('Product'));
                foreach ($productDetail as $key => $item) {
                    $productNode->appendChild($this->domDocument->createElement($key, htmlspecialchars($item)));
                }
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
     * @param string $path
     *
     * @throws \Exception
     *@return XmlCoreTT32
     */
    public function make($path = '')
    {
        $data = $this->getRawData();

        $path = $path ?: storage_path('app/') . 'file_signed.xml';

        file_put_contents($path, $data);

        return $this;
    }

    /**
     * Ký vào file. Nếu Cert hết hạn sẽ báo lỗi.
     *
     * @param string $xmlString
     *
     * @throws \Exception
	 *@return XmlCoreTT32
     */
    public function sign($xmlString = '')
    {
        if ( ! $this->isCertValid()) {
            throw new RuntimeException('CERTIFICATE EXPIRED');
        }

        if ($xmlString) {
            $this->datas = $xmlString;
            $this->loadXml();
        }

        // Create a new Security object
        $objDSig = new XMLSecurityDSig('');
        // Use the c14n exclusive canonicalization
        $objDSig->setCanonicalMethod(\RobRichards\XMLSecLibs\XMLSecurityDSig::C14N);

        /** @noinspection PhpParamsInspection */
        $objDSig->addReference(
            $this->domDocument->getElementsByTagName('Content')->item(0), \RobRichards\XMLSecLibs\XMLSecurityDSig::SHA256,
            ['http://www.w3.org/2000/09/xmldsig#enveloped-signature'], ['overwrite' => false]
        );

        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $objKey->loadKey($this->keyFilePath, $this->isKeyFile);

        $objDSig->sign($objKey);

        $objDSig->add509Cert($this->getCertificateContent());

        $objDSig->appendSignature($this->domDocument->documentElement);

        return $this;
    }

    /**
     * @param bool $sign
     *
     * @return bool|string|string[]
     */
    public function getRawData($sign = true)
    {
        try {
            if ($sign) {
                $this->sign();
            }

            $data = $this->domDocument->saveXML();

            $data = str_replace(["\t", "\n\r", "\n", "\r"], '', $data);

            return $data;
        } catch (\RuntimeException $e) {
            $this->errMsg = $e->getMessage();

            return false;
        } catch (\Exception $e) {
            $this->errMsg = $e->getMessage();

            return false;
        }
    }

    /**
     * @param XMLSecurityDSig $objXMLSecDSig
     * @param DOMDocument $doc
     *
     * @return array
     * @throws \Exception
     */
    private static function validateXml(XMLSecurityDSig $objXMLSecDSig, DOMDocument $doc): array
    {
        $objDSig = $objXMLSecDSig->locateSignature($doc);
        if ( ! $objDSig) {
            info("validateXml failed: Cannot locate Signature Node");
            return [
                'message' => 'Cannot locate Signature Node.',
                'result'  => false,
            ];
        }
        $objXMLSecDSig->canonicalizeSignedInfo();

        $retVal = $objXMLSecDSig->validateReference();
        if ( ! $retVal) {
            info("validateXml failed: Reference Validation Failed.");
            return [
                'message' => 'Reference Validation Failed.',
                'result'  => false,
            ];
        }

        $objKey = $objXMLSecDSig->locateKey();
        XMLSecEnc::staticLocateKeyInfo($objKey, $objDSig);

        if ($objXMLSecDSig->verify($objKey) === 1) {
            info("validateXml ok");
            return [
                'message' => 'Signature validated.',
                'result'  => true,
            ];
        }
        info("validateXml failed");
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
            $xmlData = str_replace(["\t", "\n\r", "\n", "\r"], '', $xmlData);

            $doc = new DOMDocument();
            if (@$doc->loadXML($xmlData)) {
                $objXMLSecDSig = new XMLSecurityDSig();
                return self::validateXml($objXMLSecDSig, $doc);
//                // ** TAM DISABLE TINH NĂNG NÀY ** /
//                return [
//                    'message' => 'Signature validated.',
//                    'result'  => true,
//                ];
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

    /**
     * @param $filePath
     *
     * @return string|array
     * @throws \Exception
     */
    public static function verifyFile($filePath)
    {
        try {
            $doc = new DOMDocument();
            $doc->load($filePath);
            $objXMLSecDSig = new XMLSecurityDSig();

            //return self::validateXml($objXMLSecDSig, $doc);
            // ** TAM DISABLE TINH NĂNG NÀY ** /
            return [
                'message' => 'Signature validated.',
                'result'  => true,
            ];
        } catch (\Exception $e) {
            Log::error("Verify XML file failed: {$e->getMessage()}");

            return [
                'message' => $e->getMessage(),
                'result'  => false,
            ];
        }
    }
}