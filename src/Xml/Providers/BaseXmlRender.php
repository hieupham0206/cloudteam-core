<?php

namespace Cloudteam\Core\Xml\Providers;

class BaseXmlRender
{
	public function renderCommonXml($class, $hDonElem, $bodyElement)
	{
		$datas    = $class->datas;

		$subBodyElem = "DL$bodyElement";
		if ($bodyElement === 'BTHDLieu') {
			$subBodyElem = 'DLBTHop';
		}
		$bodyElem  = $hDonElem->appendChild($class->domDocument->createElement($subBodyElem));
		$bodyDatas = $datas['DLieu'][$bodyElement][$subBodyElem];

		$class->createXmlBody($bodyElem, $bodyDatas);

		$dsckSElem = $hDonElem->appendChild($class->domDocument->createElement('DSCKS'));
		$dsckDatas = $datas['DLieu'][$bodyElement]['DSCKS'];

		if (! empty($datas['DLieu'][$bodyElement]['MCCQT'])) {
			$hDonElem->appendChild($class->domDocument->createElement('MCCQT', $datas['DLieu'][$bodyElement]['MCCQT']));
		}

		$class->createXmlBody($dsckSElem, $dsckDatas);
	}
}