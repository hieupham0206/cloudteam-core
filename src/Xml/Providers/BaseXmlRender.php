<?php

namespace Cloudteam\Core\Xml\Providers;

class BaseXmlRender
{
    public function renderCommonXml($class, $hDonElem, $bodyElement, $fileID = '')
    {
        $datas    = $class->datas;

        $subBodyElem = "DL$bodyElement";
        if ($bodyElement === 'BTHDLieu') {
            $subBodyElem = 'DLBTHop';
        }

        $tmpElem = $class->domDocument->createElement($subBodyElem);

        if ($fileID) {
            $tmpDomAttribute = $class->domDocument->createAttribute('Id');
            $tmpDomAttribute->value = $fileID;
            $tmpElem->appendChild($tmpDomAttribute);
        }

        $bodyElem  = $hDonElem->appendChild($tmpElem);

        //$bodyElem  = $hDonElem->appendChild($class->domDocument->createElement($subBodyElem));
        $bodyDatas = $datas['DLieu'][$bodyElement][$subBodyElem];
        $class->createXmlBody($bodyElem, $bodyDatas);

        if (isset($datas['DLieu'][$bodyElement]['MCCQT'])) {
            $hDonElem->appendChild($class->domDocument->createElement('MCCQT', $datas['DLieu'][$bodyElement]['MCCQT']));
        }

        $dsckSElem = $hDonElem->appendChild($class->domDocument->createElement('DSCKS'));
        $dsckDatas = $datas['DLieu'][$bodyElement]['DSCKS'];

        $class->createXmlBody($dsckSElem, $dsckDatas);
    }
}