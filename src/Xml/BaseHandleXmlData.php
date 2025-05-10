<?php

namespace Cloudteam\Core\Xml;

use Cloudteam\Core\Utils\MoneyHelper;

class BaseHandleXmlData
{
    protected string $xml2String;

    protected string $srcImage;

    protected string $portalLink;

    protected array $general;

    protected array $data;

    protected array $digitalSign;

    /* start_Dành cho HD*/
    protected array $dataInvoice;

    protected array $generalInvoice;

    protected array $contentInvoice;

    protected array $salesman;

    protected array $buyer;

    protected array $products;

    protected array $payment;

    protected array $DSCKS;

    protected array $TTChung;

    protected array $TCTTNhap;

    protected array $NNT;

    protected array $TTNCNKTru;

    /* End_Dành cho HD*/

    /* start_Dành cho TB*/
    protected array $dataAnnouncement;

    protected array $contentAnnouncement;

    protected array $invoiceList;

    public function __construct($simpleXMLElement)
    {
        $pathFileVat      = config('app.path_file_vat', 'cloudteam');
        $this->srcImage   = config("logo.{$pathFileVat}_base64");
        $this->portalLink = config('app.portal_link');

        $this->handleData($simpleXMLElement);
    }

    protected function handleData($simpleXMLElement): void
    {
        $xml2String  = $this->xml2String = json_encode((array)$simpleXMLElement);
        $dataValues  = json_decode($xml2String, true);
        $digitalSign = $dataInvoice = $general = $generalInvoice = $contentInvoice = $salesman = $buyer = $products = $payment = $dataAnnouncement = $invoiceList = [];
        $DSCKS       = $TTChung = $TCTTNhap = $NNT = $TTNCNKTru = [];
        if ($dataValues) {
            //note: trường hợp XML có cả thẻ TDiep => lấy dữ liệu bat dau từ thẻ HDon
            if (!empty($dataValues['DLieu'])) {
                $dataValues = $dataValues['DLieu']['HDon'];
            }
            if (!empty($dataValues['DLHDon'])) {
                $dataInvoice = $dataValues['DLHDon'];
                if (!empty($dataInvoice['TTChung'])) {
                    $generalInvoice = $dataInvoice['TTChung'];
                }
                if (!empty($dataInvoice['NDHDon'])) {
                    $contentInvoice = $dataInvoice['NDHDon'];
                    if (!empty($contentInvoice['NBan'])) {
                        $salesman = $contentInvoice['NBan'];
                    }
                    if (!empty($contentInvoice['NMua'])) {
                        $buyer = $contentInvoice['NMua'];
                    }
                    if (!empty($contentInvoice['DSHHDVu']['HHDVu'])) {
                        $products = $contentInvoice['DSHHDVu']['HHDVu'];
                        if (empty($products[0])) {
                            $products = [$products];
                        }
                        $products = array_map(function ($product) {
                            if (empty($product['THHDVu'])) {
                                return [];
                            }
                            if (($TChat = $product['TChat'] ?? null) && $TChat == 4) {
                                return [
                                    'TChat'           => $TChat,
                                    'ItemName'        => $product['THHDVu'],
                                    'ItemCode'        => '',
                                    'ItemUnitName'    => '',
                                    'ItemQuantity'    => '',
                                    'ItemUnitAmount'  => '',
                                    'ItemVatRate'     => '',
                                    'VATRate'         => '',
                                    'ItemTotalAmount' => '',
                                ];
                            } else {
                                $quantity = $product['SLuong'] ?? 0;
                                $price    = empty($product['DGia']) ? '' : $product['DGia'];
                                $vatRate  = empty($product['TSuat']) ? '' : $product['TSuat'];
                                if (is_numeric($price)) {
                                    if ($price != 0) {
                                        $amount = MoneyHelper::getAmountAfterTax($price, (int)$vatRate);
                                    } else {
                                        $amount = 0;
                                    }
                                } else {
                                    $amount = '';
                                }

                                $tSuat = $product['TSuat'] ?? '';

                                return [
                                    'ItemName'        => $product['THHDVu'],
                                    'ItemCode'        => $product['MHHDVu'] ?? '',
                                    'ItemUnitName'    => $product['DVTinh'] ?? '',
                                    'ItemQuantity'    => $quantity,
                                    'ItemUnitAmount'  => $amount,
                                    'ItemVatRate'     => is_array($tSuat) && !$tSuat ? null : $tSuat,
                                    'VATRate'         => is_array($tSuat) && !$tSuat ? null : $tSuat,
                                    'ItemTotalAmount' => is_numeric($price) ? $quantity * $amount : '',
                                ];
                            }
                        }, $products);
                    }
                    if (!empty($contentInvoice['TToan'])) {
                        $payment = $contentInvoice['TToan'];
                    }
                }
            }
            if (!empty($dataValues['DSCKS'])) {
                $digitalSign = $dataValues['DSCKS'];
            }
            if (!empty($dataValues['DLTBao'])) {
                $dataAnnouncement = $dataValues['DLTBao'];
                if (!empty($dataAnnouncement['DSHDon']['HDon'])) {
                    $invoiceList = $dataAnnouncement['DSHDon']['HDon'];
                    if (empty($invoiceList[0])) {
                        $invoiceList = [$invoiceList];
                    }
                }
            }
            if (!empty($dataValues['DLCTu'])) {
                $DLCTu = $dataValues['DLCTu'];
                $DSCKS = $dataValues['DSCKS'];
                if (!empty($DLCTu['TTChung'])) {
                    $TTChung = $DLCTu['TTChung'];
                }
                if (!empty($DLCTu['NDCTu'])) {
                    $NDCTu = $DLCTu['NDCTu'];
                    if (!empty($NDCTu['TCTTNhap'])) {
                        $TCTTNhap = $NDCTu['TCTTNhap'];
                    }
                    if (!empty($NDCTu['NNT'])) {
                        $NNT = $NDCTu['NNT'];
                    }
                    if (!empty($NDCTu['TTNCNKTru'])) {
                        $TTNCNKTru = $NDCTu['TTNCNKTru'];
                    }
                }
            }

            $this->data    = $dataValues;
            $this->general = $general;

            $this->digitalSign    = $digitalSign;
            $this->dataInvoice    = $dataInvoice;
            $this->generalInvoice = $generalInvoice;
            $this->contentInvoice = $contentInvoice;
            $this->salesman       = $salesman;
            $this->buyer          = $buyer;
            $this->products       = $products;
            $this->payment        = $payment;

            $this->dataAnnouncement = $dataAnnouncement;
            unset($dataAnnouncement['DSHDon']);
            $this->contentAnnouncement = $dataAnnouncement;
            $this->invoiceList         = $invoiceList;

            $this->DSCKS     = $DSCKS;
            $this->TTChung   = $TTChung;
            $this->TCTTNhap  = $TCTTNhap;
            $this->NNT       = $NNT;
            $this->TTNCNKTru = $TTNCNKTru;
        }
    }

    protected function mapOtherFieldXML(&$dataInvoices, $xmlOtherField): void
    {
        if ($salesmanTTKhacs = $this->salesman['TTKhac']['TTin'] ?? null) {
            foreach ($salesmanTTKhacs as $ttKhacs) {
                if (isset($salesmanTTKhacs['TTruong']) && is_string($ttKhacs)) {
                    $ttKhacs = $salesmanTTKhacs;
                }
                $TTruong = mb_strtolower($ttKhacs['TTruong'] ?? null);
                $DLieu   = $ttKhacs['DLieu'] ?? null;
                if ($TTruong && $DLieu) {
                    if ($TTruong == mb_strtolower($xmlOtherField['ofPartner'] ?? '')) {
                        $dataInvoices['DeliveryOrderBy'] = htmlspecialchars_decode($DLieu);
                    }
                    if ($TTruong == mb_strtolower($xmlOtherField['reference'] ?? '')) {
                        $dataInvoices['DeliveryOrderAbout'] = htmlspecialchars_decode($DLieu);
                    }
                    if ($TTruong == mb_strtolower($xmlOtherField['storeName'] ?? '')) {
                        $dataInvoices['StoreName'] = $DLieu;
                    }
                    if ($TTruong == mb_strtolower($xmlOtherField['storeTaxCode'] ?? '')) {
                        $dataInvoices['StoreTaxCode'] = $DLieu;
                    }
                    if ($TTruong == mb_strtolower($xmlOtherField['storeAddress'] ?? '')) {
                        $dataInvoices['StoreAddress'] = htmlspecialchars_decode($DLieu);
                    }
                    if ($TTruong == mb_strtolower($xmlOtherField['comPhone'] ?? '')) {
                        $dataInvoices['ComPhone'] = $DLieu;
                    }
                    if ($TTruong == mb_strtolower($xmlOtherField['comEnglishName'] ?? '')) {
                        $dataInvoices['ComEnglishName'] = $DLieu;
                    }
                    if ($TTruong == mb_strtolower($xmlOtherField['oriComName'] ?? '')) {
                        $companyMain['tax_name'] = htmlspecialchars_decode($DLieu);
                    }
                    if ($TTruong == mb_strtolower($xmlOtherField['oriComTaxCode'] ?? '')) {
                        $companyMain['tax_code'] = $DLieu;
                    }
                    if ($TTruong == mb_strtolower($xmlOtherField['oriComAddress'] ?? '')) {
                        $companyMain['address'] = htmlspecialchars_decode($DLieu);
                    }
                    if ($TTruong == mb_strtolower($xmlOtherField['oriComPhone'] ?? '')) {
                        $companyMain['phone'] = $DLieu;
                    }
                    if (isset($companyMain)) {
                        $dataInvoices['CompanyMain'] = $companyMain;
                    }
                }
            }
        }
        if ($invoiceTTKhacs = $this->dataInvoice['TTKhac']['TTin'] ?? null) {
            foreach ($invoiceTTKhacs as $ttKhacs) {
                if (isset($invoiceTTKhacs['TTruong']) && is_string($ttKhacs)) {
                    $ttKhacs = $invoiceTTKhacs;
                }
                $TTruong = mb_strtolower($ttKhacs['TTruong'] ?? null);
                $DLieu   = $ttKhacs['DLieu'] ?? null;
                if ($TTruong && $DLieu) {
                    if ($TTruong == mb_strtolower($xmlOtherField['fileID'] ?? '')) {
                        $dataInvoices['Fkey'] = $DLieu;
                    }
                    if ($TTruong == mb_strtolower($xmlOtherField['transID'] ?? '')) {
                        $dataInvoices['BillNo'] = $DLieu;
                    }
                    if ($TTruong == mb_strtolower($xmlOtherField['linkPortal'] ?? '')) {
                        $dataInvoices['PortalLink'] = $DLieu;
                    }
                }
            }
        }
        if ($buyerTTKhacs = $this->buyer['TTKhac']['TTin'] ?? null) {
            foreach ($buyerTTKhacs as $ttKhacs) {
                if (isset($buyerTTKhacs['TTruong']) && is_string($ttKhacs)) {
                    $ttKhacs = $buyerTTKhacs;
                }
                $TTruong = mb_strtolower($ttKhacs['TTruong'] ?? null);
                $DLieu   = $ttKhacs['DLieu'] ?? null;
                if ($TTruong && $DLieu) {
                    if ($TTruong == mb_strtolower($xmlOtherField['HVTNMHang'] ?? '')) {
                        $dataInvoices['Buyer'] = $DLieu;
                    }
                    if ($TTruong == mb_strtolower($xmlOtherField['DChi'] ?? '')) {
                        $dataInvoices['CusAddress'] = $DLieu;
                    }
                    if ($TTruong == mb_strtolower($xmlOtherField['DCTDTu'] ?? '')) {
                        $dataInvoices['CusEmail'] = $DLieu;
                    }
                    if ((empty($dataInvoices['CusTaxCode']) || !$dataInvoices['CusTaxCode']) && ($TTruong == mb_strtolower($xmlOtherField['MSTNNNgoai'] ?? ''))) {
                        $dataInvoices['CusTaxCode'] = $DLieu;
                    }
                }
            }
        }
    }


    protected function getExplainText(int $value): string
    {
        if ($value === 1) {
            return 'Hủy';
        }
        if ($value === 2) {
            return 'Điều chỉnh';
        }
        if ($value === 3) {
            return 'Thay thế';
        }
        if ($value === 4) {
            return 'Giải trình';
        }
        if ($value === 5) {
            return 'Sai sót do tổng hợp';
        }

        return 'Mới';
    }
}