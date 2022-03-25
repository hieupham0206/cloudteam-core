<?php

namespace Cloudteam\Core\Xml\Providers;

class MInvoiceXmlRender extends BaseXmlRender
{
	public function renderXml($class, $bodyElement)
	{
		$hDonElem = $class->domDocument->appendChild($class->domDocument->createElement($bodyElement));

		$this->renderCommonXml($class, $hDonElem, $bodyElement);
	}
}