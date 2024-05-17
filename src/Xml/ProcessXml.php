<?php

namespace Cloudteam\Core\Xml;

use Ramsey\Uuid\Uuid;

trait ProcessXml
{
    //https://thuvienphapluat.vn/van-ban/Thue-Phi-Le-Phi/Quyet-dinh-1450-QD-TCT-2021-thanh-phan-chua-du-lieu-nghiep-vu-hoa-don-dien-tu-490526.aspx
    private static string $version = '2.0.1';

    private static string $MSTTCGP = '0312617990';

    protected static function getCommonGeneralInfo($mltDiep, $options): array
    {
        $mtdtChieu = $options['MTDTChieu'] ?? '';
        //note: Mã số thuế tổ chức cung cấp giải pháp hóa đơn điện tử (Viettel (0100109106), VNPT)
        $msttcgp             = $options['MSTTCGP'];
        $uuidV4WithoutHyphen = str_replace('-', '', Uuid::uuid4());
        $mnGui               = self::$MSTTCGP;
        $version             = self::$version;
        $MTDiep              = "{$mnGui}{$uuidV4WithoutHyphen}";
        if ($mltDiep === '206') {
            $version = self::$version;
            $msttcgp = 'TCT';
            $mnGui   = 'V0106026495';
            $MTDiep  = strtoupper("{$mnGui}{$uuidV4WithoutHyphen}");
        }

        //note: MInvoice không cần thông tin này
        return [
            'PBan'      => $version,
            'MNGui'     => $mnGui,
            'MNNhan'    => $msttcgp,
            'MLTDiep'   => $mltDiep,
            'MTDiep'    => $MTDiep,
            'MST'       => $options['MST'] ?? '',
            'SLuong'    => '1',
            'MTDTChieu' => $mtdtChieu,
        ];
    }

    /**
     * Các loại hóa đơn
     */
    public static function prepareSignInvoiceData(array $options): array
    {
        $KHMSHDon = $options['KHMSHDon'];

        //note: nếu là loại Hóa đơn bán hàng
        $isSaleInvoice = $KHMSHDon == 2;

        $commonGeneralInfos = self::getCommonGeneralInfo($options['MLTDiep'], $options);

        $hdonGeneralInfos = [
            'PBan'          => self::$version,
            'THDon'         => $options['THDon'] ?? '',
            'KHMSHDon'      => $KHMSHDon,
            'KHHDon'        => $options['KHHDon'] ?? '',
            'SHDon'         => $options['SHDon'] ?? '',
            'NLap'          => $options['NLap'] ?? '',
            'MSTTCGP'       => self::$MSTTCGP,
            //note: bắt buộc (Nếu có)
            'SBKe'          => '',
            'NBKe'          => '',
            'DVTTe'         => $options['DVTTe'] ?? 'VND',
            'TGia'          => $options['TGia'] ?? '1',
            'HTTToan'       => $options['HTTToan'] ?? '', //Không bắt buộc

            //'HDNTGia'       => $options['HDNTGia'] ?? '', //Không bắt buộc
            //note: bắt buộc (Đối với trường hợp ủy nhiệm lập hóa đơn)
            'MSTDVNUNLHDon' => $options['MSTDVNUNLHDon'] ?? '', //Không bắt buộc
            'TDVNUNLHDon'   => $options['TDVNUNLHDon'] ?? '', //Không bắt buộc
            'DCDVNUNLHDon'  => $options['DCDVNUNLHDon'] ?? '', //Không bắt buộc

            //note: chứa thông tin hóa đơn liên quan trong trường hợp là hoá đơn điều chỉnh hoặc thay thế
            'TTHDLQuan'     => $options['DLHDon']['TTChung']['TTHDLQuan'] ?? '',
            'TTKhac'        => $options['DLHDon']['TTChung']['TTKhac'] ?? '',
        ];

        //note: bắt buộc (Đối với trường hợp là hoá đơn đề nghị cấp mã của cơ quan thuế theo từng lần phát sinh)
        if (! empty($options['DLHDon']['TTChung']['MHSo'])) {
            $hdonGeneralInfos['MHSo'] = $options['DLHDon']['TTChung']['MHSo'];
        }

        $ttoanInfos = $options['DLHDon']['NDHDon']['TToan'] ?? [];
        if ($isSaleInvoice) {
            $hdonGeneralInfos['HDDCKPTQuan'] = 0;

            if ($ttoanInfos) {
                unset($ttoanInfos['THTTLTSuat'], $ttoanInfos['TgTCThue'], $ttoanInfos['TgTThue']);
            }
        }

        $products = $options['DLHDon']['NDHDon']['DSHHDVu']['HHDVu'] ?? '';

        $hdonDatas = [
            'DLHDon' => [
                'TTChung' => $hdonGeneralInfos,
                'NDHDon'  => [
                    'NBan' => $options['DLHDon']['NDHDon']['NBan'],
                    'NMua' => $options['DLHDon']['NDHDon']['NMua'],
                ],
                'TToan'   => $options['DLHDon']['TToan'] ?? [],
                'TTKhac'  => $options['DLHDon']['TTKhac'] ?? '',
            ],
            'DSCKS'  => ['NBan' => []],
        ];
        if ($ttoanInfos) {
            $hdonDatas['DLHDon']['NDHDon']['TToan'] = $ttoanInfos;
        }
        if ($products) {
            $hdonDatas['DLHDon']['NDHDon']['DSHHDVu']['HHDVu'] = $products;
        }
        if (! empty($options['MCCQT'])) {
            $hdonDatas['MCCQT'] = $options['MCCQT'];
        } else {
            $hdonDatas['DSCKS'] = [
                'NBan'     => [],
                'NMua'     => [],
                'CCKSKhac' => [],
            ];
        }

        $finalDatas = [
            'TTChung' => $commonGeneralInfos,
            'DLieu'   => [
                'HDon' => $hdonDatas,
            ],
        ];

        if (! empty($hdonDatas['DSCKS'])) {
            $finalDatas['CKSNNT'] = [];
        }

        return $finalDatas;
    }

    /**
     * Phiều xuất kho kiêm vận chuyển
     */
    protected static function prepareSignDeliveryInvoiceData(array $options): array
    {
        $KHMSHDon           = $options['KHMSHDon'];
        $commonGeneralInfos = self::getCommonGeneralInfo($options['MLTDiep'], $options);

        $hdonGeneralInfos = [
            'PBan'      => self::$version,
            'THDon'     => $options['THDon'] ?? '',
            'KHMSHDon'  => $KHMSHDon,
            'KHHDon'    => $options['KHHDon'] ?? '',
            'SHDon'     => $options['SHDon'] ?? '',
            'NLap'      => $options['NLap'] ?? '',
            'MSTTCGP'   => self::$MSTTCGP,
            //note: Bắt buộc (Nếu có)
            'SBKe'      => '',
            'NBKe'      => '',
            'DVTTe'     => $options['DVTTe'] ?? 'VND',
            'TGia'      => $options['TGia'] ?? '1',

            //note: chứa thông tin hóa đơn liên quan trong trường hợp là hoá đơn điều chỉnh hoặc thay thế
            'TTHDLQuan' => $options['DLHDon']['TTChung']['TTHDLQuan'] ?? '',
            'TTKhac'    => $options['DLHDon']['TTChung']['TTKhac'] ?? '',
        ];

        $hdonDatas = [
            'DLHDon' => [
                'TTChung' => $hdonGeneralInfos,
                'NDHDon'  => [
                    'NBan'    => [
                        'Ten'       => $options['DLHDon']['NDHDon']['NBan']['Ten'] ?? '',
                        'MST'       => $options['DLHDon']['NDHDon']['NBan']['MST'] ?? '',
                        'DChi'      => $options['DLHDon']['NDHDon']['NBan']['DChi'] ?? '',
                        'LDDNBo'    => $options['DLHDon']['NDHDon']['NBan']['LDDNBo'] ?? '',
                        'PTVChuyen' => $options['DLHDon']['NDHDon']['NBan']['PTVChuyen'] ?? '',
                        // Thông tin dưới Không bắt buộc
                        'HDSo'      => $options['DLHDon']['NDHDon']['NBan']['HDSo'] ?? '',
                        'HVTNXHang' => $options['DLHDon']['NDHDon']['NBan']['HVTNXHang'] ?? '',
                        'TNVChuyen' => $options['DLHDon']['NDHDon']['NBan']['TNVChuyen'] ?? '',
                        'TTKhac'    => $options['DLHDon']['NDHDon']['NBan']['TTKhac'] ?? '',
                    ],
                    'NMua'    => [
                        'Ten'       => $options['DLHDon']['NDHDon']['NMua']['Ten'] ?? '',
                        'MST'       => $options['DLHDon']['NDHDon']['NMua']['MST'] ?? '',
                        'DChi'      => $options['DLHDon']['NDHDon']['NMua']['DChi'] ?? '',
                        // Thông tin dưới Không bắt buộc
                        'HVTNNHang' => $options['DLHDon']['NDHDon']['NMua']['HVTNNHang'] ?? '',
                        'TTKhac'    => $options['DLHDon']['NDHDon']['NMua']['TTKhac'] ?? '',
                    ],
                    'DSHHDVu' => [
                        'HHDVu' => $options['DLHDon']['NDHDon']['DSHHDVu']['HHDVu'] ?? '',
                    ],
                ],
                'TTKhac'  => $options['DLHDon']['TTKhac'] ?? '',
            ],
            'DSCKS'  => [
                'NBan'     => [],
                'NMua'     => [],
                'CCKSKhac' => [],
            ],
        ];

        return [
            'TTChung' => $commonGeneralInfos,
            'DLieu'   => [
                'HDon' => $hdonDatas,
            ],
        ];
    }

    /**
     * Tờ khai đăng kí sử dụng/thay đổi
     */
    protected static function prepareDeclarationData(array $options): array
    {
        $generalInfos = self::getCommonGeneralInfo('100', $options);

        $hthDons = [
            'CMa'  => $options['CMa'],
            'KCMa' => $options['KCMa'],
        ];

        if (isset($options['CMTMTTien'])) {
            $hthDons['CMTMTTien'] = 1;
        }

        return [
            'TTChung' => $generalInfos,
            'DLieu'   => [
                'TKhai' => [
                    'DLTKhai' => [
                        'TTChung' => [
                            'PBan'  => self::$version,
                            'MSo'   => '01/ĐKTĐ-HĐĐT',//220
                            'Ten'   => 'Đăng ký/thay đổi thông tin sử dụng hóa đơn điện tử',
                            'HThuc' => $options['HThuc'], //1: Đăng ký mới, 2:Thay đổi thông tin

                            'TNNT' => $options['TNNT'] ?? '',
                            'MST'  => $options['MST'] ?? '',

                            'CQTQLy'  => $options['CQTQLy'],
                            'MCQTQLy' => $options['MCQTQLy'],
                            'NLHe'    => $options['NLHe'],
                            'DCLHe'   => $options['DCLHe'],
                            'DCTDTu'  => $options['DCTDTu'],
                            'DTLHe'   => $options['DTLHe'],
                            'DDanh'   => $options['DDanh'],

                            'NLap' => now()->format('Y-m-d'),
                        ],
                        'NDTKhai' => [
                            'HTHDon'     => $hthDons,
                            'HTGDLHDDT'  => [
                                'NNTDBKKhan'  => $options['NNTDBKKhan'] ?? '0',
                                'NNTKTDNUBND' => $options['NNTKTDNUBND'] ?? '0',
                                'CDLTTDCQT'   => $options['CDLTTDCQT'] ?? '0',
                                'CDLQTCTN'    => $options['CDLQTCTN'] ?? '1',
                            ],
                            'PThuc'      => [
                                'CDDu'   => '1',
                                'CBTHop' => '0',
                            ],
                            'LHDSDung'   => [
                                //hỏi lại cái này
                                'HDGTGT'     => $options['HDGTGT'] ?? '0',
                                'HDBHang'    => $options['HDBHang'] ?? '1',
                                'HDBTSCong'  => $options['HDBTSCong'] ?? '0',
                                'HDBHDTQGia' => $options['HDBHDTQGia'] ?? '0',
                                'HDKhac'     => $options['HDKhac'] ?? '0',
                                'CTu'        => $options['CTu'] ?? '0',
                            ],
                            'DSCTSSDung' => [
                                //chứa thông tin chứng thư số sử dụng để sign
                                'CTS' => $options['CTS'],
                            ],
                        ],
                    ],
                    'DSCKS'   => [
                        'NNT'      => [],
                        'CCKSKhac' => [],
                    ],
                ],
            ],
        ];
    }

    /**
     * Thông báo HD sai sót
     */
    protected static function prepareErrorNotification(array $options): array
    {
        $generalInfos = self::getCommonGeneralInfo('300', $options);

        $version = '2.0.1';

        return [
            'TTChung' => $generalInfos,
            'DLieu'   => [
                'TBao' => [
                    'DLTBao' => [
                        'PBan' => $version,
                        'MSo'  => '04/SS-HĐĐT',
                        'Ten'  => 'THÔNG BÁO HÓA ĐƠN ĐIỆN TỬ CÓ SAI SÓT',
                        'Loai' => '1',// 1: Thông báo hủy/giải trình của NNT, 2: Thông báo hủy/giải trình của NNT theo thông báo của CQT

                        //						'So'      => '',//Bắt buộc khi loại là 2
                        //						'NTBCCQT' => '',//Bắt buộc khi loại là 2

                        'MCQT' => $options['MCQT'],
                        'TCQT' => $options['TCQT'],

                        'TNNT'   => $options['TNNT'] ?? '',
                        'MST'    => $options['MST'] ?? '',

                        //						'MDVQHNSach' => '',//Bắt buộc (Đối với đơn vị bán tài sản công không có Mã số thuế )
                        'DDanh'  => $options['DDanh'],
                        'NTBao'  => date('Y-m-d'),
                        'DSHDon' => [
                            'HDon' => $options['HDon'],
                        ],
                    ],
                    'DSCKS'  => [
                        'NNT'      => [],
                        'CCKSKhac' => [],
                    ],
                ],
            ],
        ];
    }

    /**
     * Bảng tổng hợp
     */
    protected static function prepareSummaryData(array $options): array
    {
        $generalInfos = self::getCommonGeneralInfo('400', $options);

        return [
            'TTChung' => $generalInfos,
            'DLieu'   => [
                'BTHDLieu' => [
                    'DLBTHop' => [
                        'TTChung'    => [
                            'PBan' => self::$version,
                            'MSo'  => '01/TH-HĐĐT',//Phụ lục VIII
                            'Ten'  => 'Bảng dữ liệu tổng hợp',

                            'SBTHDLieu' => $options['SBTHDLieu'] ?? 1,
                            'LKDLieu'   => $options['LKDLieu'] ?? 'T',//Phụ luc VII: T,Q,N
                            'KDLieu'    => $options['KDLieu'] ?? 'T',//Phụ luc VII: T,Q,N
                            'LDau'      => $options['KDLieu'] ?? 1,//1 lần đầu, 0 bổ sung
                            'BSLThu'    => $options['BSLThu'] ?? '', // Bắt buộc (Đối với trường hợp  LDau = 0)
                            'NLap'      => $options['NLap'] ?? now()->format('Y-m-d'),

                            'TNNT' => $options['TNNT'] ?? '',
                            'MST'  => $options['MST'] ?? '',

                            'DVTTe' => $options['DVTTe'] ?? 'VND', //Đơn vị tiền tệ

                            'HDDIn' => 0,// Số (0: Hóa đơn điện tử, 1: Hóa đơn đặt in)
                            'LHHoa' => 9,// Số (1: Xăng dầu, 2: Vận tải hàng không, 9: Khác)
                        ],
                        'NDBTHDLieu' => [
                            'DSDLieu' => [
                                'DLieu' => [
                                    'KHMSHDon' => $options['KHMSHDon'] ?? '',//Phụ lục 2
                                    'KHHDon'   => $options['KHHDon'] ?? '',
                                    'SHDon'    => $options['SHDon'] ?? '',
                                    'NLap'     => $options['NLap'] ?? '',
                                    'TNMua'    => $options['TNMua'] ?? '',
                                    'MSTNMua'  => $options['MSTNMua'] ?? '',

                                    //Bắt buộc (Đối với Loại hàng hóa, dịch vụ kinh doanh là 1- Xăng dầu)
                                    'MHHDVu'   => $options['MHHDVu'] ?? '',
                                    'THHDVu'   => $options['THHDVu'] ?? '',
                                    'DVTinh'   => $options['DVTinh'] ?? '',
                                    'SLuong'   => $options['SLuong'] ?? '',
                                    //							'MKHang' => $options['KHMSHDon'],// Không bắt buộc

                                    'TTCThue'      => $options['TTCThue'] ?? '',
                                    'TSuat'        => $options['TSuat'] ?? '',
                                    'TgTThue'      => $options['TgTThue'] ?? '',
                                    'TTPhi'        => $options['TTPhi'] ?? '',//Bắt buộc (Nêu có)
                                    'TgTTToan'     => $options['TgTTToan'] ?? '',
                                    'TGia'         => $options['TGia'] ?? '',//Bắt buộc (Trừ trường hợp Đơn vị tiền tệ là VND)
                                    'TThai'        => $options['TThai'] ?? '',//Số (Chi tiết tại Phụ lục IX kèm theo Quyết định số 1450/QĐ-TCT ngày 7/10/2021)
                                    //Bắt buộc (Đối với trường hợp điều chỉnh, thay thế cho hóa đơn có Ký hiệu mẫu số hóa đơn, Ký hiệu hóa đơn, Số hóa đơn)
                                    'LHDCLQuan'    => $options['LHDCLQuan'] ?? '',
                                    'KHMSHDCLQuan' => $options['KHMSHDCLQuan'] ?? '',
                                    'KHHDCLQuan'   => $options['KHHDCLQuan'] ?? '',
                                    'SHDCLQuan'    => $options['SHDCLQuan'] ?? '',
                                    'LKDLDChinh'   => $options['LKDLDChinh'] ?? '',
                                    'KDLDChinh'    => $options['KDLDChinh'] ?? '',
                                    //Bắt buộc (đối với trường hợp giải trình theo thông báo của CQT)
                                    'STBao'        => $options['STBao'] ?? '',
                                    'NTBao'        => $options['NTBao'] ?? '',
                                    'GChu'         => $options['GChu'] ?? '',// Không bắt buộc
                                ],
                            ],
                        ],
                    ],
                    'DSCKS'   => [
                        'NNT'      => [],
                        'CCKSKhac' => [],
                    ],
                ],
            ],
        ];
    }

    /**
     * Chứng từ khấu trừ thuế TNCN
     */
    protected static function preparePersonalInvoice(array $options): array
    {
        $generalInfos = self::getCommonGeneralInfo('400', $options);

        return [
            'TTChung' => $generalInfos,
            'DLieu'   => [
                'CTu' => [
                    'DLCTu' => [
                        'TTChung' => [
                            'PBan'  => self::$version,
                            'THDon' => $options['THDon'],
                            'MSCTu' => $options['MSCTu'],
                            'KHCTu' => $options['KHCTu'],
                            'SCTu'  => $options['SCTu'],
                            'NLap'  => $options['NLap'],
                        ],
                        'NDCTu'   => [
                            'TCTTNhap'  => [
                                'Ten'     => $options['TCTTNhap']['Ten'],
                                'MST'     => $options['TCTTNhap']['MST'],
                                'DChi'    => $options['TCTTNhap']['DChi'],
                                'SDThoai' => $options['TCTTNhap']['SDThoai'] ?? '', //Không bắt buộc
                            ],
                            'NNT'       => [
                                'Ten'   => $options['NNT']['Ten'] ?? '', //Không bắt buộc
                                'MST'   => $options['NNT']['MST'] ?? '', //Không bắt buộc
                                'DChi'  => $options['NNT']['DChi'] ?? '', //Không bắt buộc
                                'QTich' => $options['NNT']['QTich'] ?? '', //Không bắt buộc

                                'CNCTru' => $options['NNT']['CNCTru'], //Số (0: Cá nhân không cư trú, 1: Cá nhân cư trú)

                                'CMND'    => $options['NNT']['CMND'] ?? '', //Bắt buộc (Đối với trường hợp không có MST)
                                'NgCCMND' => $options['NNT']['NgCCMND'] ?? '', //Bắt buộc (Đối với trường hợp không có MST)
                                'NCCMND'  => $options['NNT']['NCCMND'] ?? '', //Bắt buộc (Đối với trường hợp không có MST)

                                'SDThoai' => $options['NNT']['SDThoai'] ?? '', //Không bắt buộc
                                'DCTDTu'  => $options['NNT']['DCTDTu'] ?? '', //Không bắt buộc
                            ],
                            'TTNCNKTru' => [
                                'KTNhap' => $options['TTNCNKTru']['KTNhap'],
                                'TThang' => $options['TTNCNKTru']['TThang'],//Từ tháng (Tháng bắt đầu trả thu nhập)
                                'DThang' => $options['TTNCNKTru']['DThang'],//Đến Tháng (Tháng cuối cùng trả thu nhập)
                                'Nam'    => $options['TTNCNKTru']['Nam'],//Năm (Thời điểm trả thu nhập)
                                'BHiem'  => $options['TTNCNKTru']['BHiem'],//Khoản đóng bảo hiểm bắt buộc

                                'TTNCThue' => round($options['TTNCNKTru']['TTNCThue'], 6),
                                'TTNTThue' => round($options['TTNCNKTru']['TTNTThue'], 6),
                                'SThue'    => round($options['TTNCNKTru']['SThue'], 6),

                                'STNCDNhan' => $options['TTNCNKTru']['STNCDNhan'] ?? '', //Số thu nhập còn được nhận (Không bắt buộc)
                            ],
                        ],
                    ],
                    'DSCKS' => [
                        'TCTTNhap' => [],
                        'CCKSKhac' => [],
                    ],
                ],
            ],
        ];
    }
}