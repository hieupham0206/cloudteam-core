<?php
/**
 * User: ADMIN
 * Date: 16/01/2020 4:35 CH
 */

namespace Cloudteam\Core\Xml;

use RobRichards\XMLSecLibs\XMLSecurityDSig as BaseXMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use DOMXPath;

class XMLSecurityDSig extends BaseXMLSecurityDSig
{
    /** @var DomXPath|null */
    private $xPathCtx = null;

    /**
     * This variable contains an associative array of validated nodes.
     * @var array|null
     */
    private $validatedNodes = null;

    public const BASE_TEMPLATE = '<Signature xmlns="http://www.w3.org/2000/09/xmldsig#" Id="seller">
  <SignedInfo>
    <SignatureMethod />
  </SignedInfo>
</Signature>';

    /**
     * @param XMLSecurityKey $objKey
     * @param string $data
     *
     * @return mixed|string
     */
    public function signData($objKey, $data)
    {
        $data = str_replace(["\t", "\n\r", "\n", "\r", '  '], '', $data);

        return $objKey->signData($data);
    }

    /**
     * Returns the XPathObj or null if xPathCtx is set and sigNode is empty.
     *
     * @return DOMXPath|null
     */
    private function getXPathObj()
    {
        if (empty($this->xPathCtx) && ! empty($this->sigNode)) {
            $xpath = new DOMXPath($this->sigNode->ownerDocument);
            $xpath->registerNamespace('secdsig', self::XMLDSIGNS);
            $this->xPathCtx = $xpath;
        }

        return $this->xPathCtx;
    }

    /**
     * @throws \Exception
     *@return bool
	 */
    public function validateReference()
    {
        $docElem = $this->sigNode->ownerDocument->documentElement;
        //note: do TT78 kí Object trong Signature nên phải bỏ check để hàm validate XML chạy đúng

//        if ( ! $docElem->isSameNode($this->sigNode) && $this->sigNode->parentNode != null) {
//            $this->sigNode->parentNode->removeChild($this->sigNode);
//        }

        $xpath   = $this->getXPathObj();
        $query   = './secdsig:SignedInfo[1]/secdsig:Reference';
        $nodeset = $xpath->query($query, $this->sigNode);
        if ($nodeset->length == 0) {
            throw new \Exception("Reference nodes not found");

//            return false;
        }

        /* Initialize/reset the list of validated nodes. */
        $this->validatedNodes = [];
        $result               = true;

        foreach ($nodeset as $refNode) {
            if ( ! $this->processRefNode($refNode)) {
                $this->validatedNodes = null;
                $result               = false;

                break;
            }
        }

        return $result;
    }

    public function addCustomObject($data, $objectId=null)
    {
        $objNode = $this->createNewSignNode('Object');
        if ($objectId) {
            $objNode->setAttribute('Id', $objectId);
        }
        $this->sigNode->appendChild($objNode);

        if ($data instanceof \DOMElement) {
            $newData = $this->sigNode->ownerDocument->importNode($data, true);
        } else {
            $newData = $this->sigNode->ownerDocument->createTextNode($data);
        }

        $objNode->appendChild($newData);

        return $objNode;
    }
}