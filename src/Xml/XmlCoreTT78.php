<?php
/**
 * User: ADMIN
 * Date: 14/01/2020 4:38 CH
 */

namespace Cloudteam\Core\Xml;

use Cloudteam\Core\Xml\Providers\BaseXmlRender;
use Cloudteam\Core\Xml\Providers\MInvoiceXmlRender;
use Cloudteam\Core\Xml\Providers\ViettelXmlRender;
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
	/**
	 * @var string
	 */
	public string $errMsg;
	/**
	 * @var DOMDocument
	 */
	public DOMDocument $domDocument;
	/**
	 * @var string|array
	 */
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
	 * @var bool
	 */
	private bool $isKeyFile = false;

	public function __construct($datas = [], $signatures = [], $invoiceProviderName = '')
	{
		$this->domDocument                     = new DOMDocument('1.0', 'utf-8');
		$this->domDocument->preserveWhiteSpace = false;
		$this->domDocument->formatOutput       = false;
		$this->domDocument->encoding           = 'UTF-8';

		$this->datas = $datas;

		if ($invoiceProviderName === 'MInvoice') {
			$this->xmlRender = new MInvoiceXmlRender();
		}

		if ($invoiceProviderName === 'Viettel') {
			$this->xmlRender = new ViettelXmlRender();
		}

		if ($signatures) {
			[$this->keyFilePath, $this->certFilePath] = $signatures;
		} else {
			$this->isKeyFile    = true;
			$this->keyFilePath  = __DIR__ . '/files/0106026495-998.key';
			$this->certFilePath = __DIR__ . '/files/0106026495-998.crt';
		}
	}

	private function renderXml($bodyElement)
	{
		$this->xmlRender->renderXml($this, $bodyElement);
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

					if ($itemKey !== 'HDon') {
						$subOfSubMainElem = $subMainElem->appendChild($this->domDocument->createElement($itemKey));
					}

					foreach ($subItems as $subKey => $subItem) {
						if ($itemKey === 'HDon') {
							$subOfSubMainElem = $subMainElem->appendChild($this->domDocument->createElement($itemKey));
						}

						if (is_array($subItem) && isMultidimensionalArray($subItem) && $itemKey == 'TToan') {
							$tempMainElem = $subOfSubMainElem->appendChild($this->domDocument->createElement($subKey));

							foreach ($subItem as $subItemValKey => $subItemVal) {
								$tempElem = $tempMainElem->appendChild($this->domDocument->createElement($subItemValKey));

								foreach ($subItemVal as $tempKey => $lastItem) {
									$tempElem->appendChild($this->domDocument->createElement($tempKey, htmlspecialchars($lastItem)));
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
									$tempElem->appendChild($this->domDocument->createElement($subItemValKey, $subItemVal));
								}
							}

							continue;
						}

						$subOfSubMainElem->appendChild($this->domDocument->createElement($subKey, $subItem));
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
				$subMainElem->appendChild($this->domDocument->createElement($subKey, $itemDatas));
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
	 * Ký vào file. Nếu Cert hết hạn sẽ báo lỗi.
	 *
	 * @throws \Exception
	 * @return XmlCoreTT78
	 * @noinspection PhpParamsInspection
	 */
	public function sign($signElement, $bodyElement, $signElemIndex = 0)
	{
		if (! $this->isCertValid()) {
			throw new RuntimeException('CERTIFICATE EXPIRED');
		}

		$objDSig = new XMLSecurityDSig('');
		$objDSig->setCanonicalMethod(\RobRichards\XMLSecLibs\XMLSecurityDSig::C14N);

		$signingTimeObject  = $this->domDocument->createElement('SignatureProperties');
		$signPropertyObject = $signingTimeObject->appendChild($this->domDocument->createElement('SignatureProperty'));
		$signPropertyObject->appendChild($this->domDocument->createElement('SigningTime', now()->toDateTimeLocalString()));

		$signPropertyObject->setAttribute('Target', '#signtime');

		$objNode = $objDSig->addCustomObject($signingTimeObject, 'signtime');

		$subBodyElem = "DL$bodyElement";
		if ($bodyElement === 'BTHDLieu') {
			$subBodyElem = 'DLBTHop';
		}

		$objDSig->addReference(
			$this->domDocument->getElementsByTagName($subBodyElem)->item(0), \RobRichards\XMLSecLibs\XMLSecurityDSig::SHA1,
			['http://www.w3.org/2000/09/xmldsig#enveloped-signature'], ['overwrite' => false]
		);
		$objDSig->addReference(
			$objNode, \RobRichards\XMLSecLibs\XMLSecurityDSig::SHA1,
			null, ['overwrite' => false]
		);

		$objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, ['type' => 'private']);
		$objKey->loadKey($this->keyFilePath, $this->isKeyFile);

		$objDSig->sign($objKey);

		$objDSig->add509Cert($this->getCertificateContent(), true, false, ['subjectName' => true]);

		$objDSig->appendSignature(
			$this->domDocument->documentElement->getElementsByTagName($signElement)->item($signElemIndex)
		);

		return $this;
	}

	/**
	 * @param bool $sign
	 *
	 * @return bool|string|string[]
	 */
	public function getRawData($signElement, $bodyElement, $signElemIndex = 0, $sign = true)
	{
		try {
			$this->renderXml($bodyElement);

			if ($sign) {
				$this->sign($signElement, $bodyElement, $signElemIndex);
			}

			$data = $this->domDocument->saveXML();

			return str_replace(["\t", "\n\r", "\n", "\r", '  '], '', $data);
		} catch (\RuntimeException | \Exception $exception) {
			$this->errMsg = "{$exception->getMessage()} - {$exception->getFile()} - {$exception->getLine()}";
			Log::error($this->errMsg);

			return false;
		}
	}

	/**
	 * @param XMLSecurityDSig $objXMLSecDSig
	 * @param DOMDocument     $doc
	 *
	 * @throws \Exception
	 * @return array
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
	 * @throws \Exception
	 * @return string|array
	 */
	public static function verify($xmlData)
	{
		try {
			$xmlData = str_replace(["\t", "\n\r", "\n", "\r", '  '], '', $xmlData);

			$doc = new DOMDocument();
			if (@$doc->loadXML($xmlData)) {
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