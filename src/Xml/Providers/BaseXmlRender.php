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

        if (isset($datas['DLieu'][$bodyElement]['MCCQT'])) {
            $hDonElem->appendChild($class->domDocument->createElement('MCCQT', $datas['DLieu'][$bodyElement]['MCCQT']));
        } else {
            $dsckSElem = $hDonElem->appendChild($class->domDocument->createElement('DSCKS'));
            $dsckDatas = $datas['DLieu'][$bodyElement]['DSCKS'];

            $class->createXmlBody($dsckSElem, $dsckDatas);
        }
    }
}