<?php

namespace Cloudteam\Core\Xml\Providers;

class ViettelXmlRender extends BaseXmlRender
{
	public function renderXml($class, $bodyElement)
	{
		$datas = $class->datas;

		$root = $this->domDocument->appendChild($this->domDocument->createElement('TDiep'));

		$ttChungElem = $root->appendChild($this->domDocument->createElement('TTChung'));
		$contents    = $datas['TTChung'];
		foreach ($contents as $key => $value) {
			if (is_array($value)) {
				continue;
			}
			$ttChungElem->appendChild($this->domDocument->createElement($key, $value));
		}

		$dLieuElem = $root->appendChild($this->domDocument->createElement('DLieu'));
		$hDonElem  = $dLieuElem->appendChild($this->domDocument->createElement($bodyElement));

		$this->renderCommonXml($class, $hDonElem, $bodyElement);
	}
}