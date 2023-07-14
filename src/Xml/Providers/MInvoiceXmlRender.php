<?php

namespace Cloudteam\Core\Xml\Providers;

class MInvoiceXmlRender extends BaseXmlRender
{
    public function renderXml($class, $bodyElement, $xmlFormat = '')
    {
        $datas = $class->datas;

        if ($xmlFormat === 'TDiep') {
            $root = $class->domDocument->appendChild($class->domDocument->createElement('TDiep'));

            $ttChungElem = $root->appendChild($class->domDocument->createElement('TTChung'));
            $contents    = $datas['TTChung'];
            foreach ($contents as $key => $value) {
                if (is_array($value)) {
                    continue;
                }
                $ttChungElem->appendChild($class->domDocument->createElement($key, $value));
            }

            $dLieuElem = $root->appendChild($class->domDocument->createElement('DLieu'));
            $hDonElem  = $dLieuElem->appendChild($class->domDocument->createElement($bodyElement));

            $cksNntElem = $root->appendChild($class->domDocument->createElement('CKSNNT'));

            $this->renderCommonXml($class, $hDonElem, $bodyElement);
        } else {
            $hDonElem = $class->domDocument->appendChild($class->domDocument->createElement($bodyElement));

            $this->renderCommonXml($class, $hDonElem, $bodyElement);
        }
    }
}