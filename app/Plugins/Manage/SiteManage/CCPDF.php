<?php

namespace App\Plugins\Manage\SiteManage;

use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * PDF 出力クラス
 * TCODF <- Fpdi <- このクラス で継承している。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 * @package Controller
 */
class CCPDF extends Fpdi
{
    public function __construct($orientation, $unit, $format, $unicode, $encoding, $diskcache)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache);
    }

    // Page footer
    public function footer()
    {
        //Go to 1.5 cm from bottom
        $this->SetY(-15);

        //Select font
        $this->SetFont('ipaexg', '', 8);

        // Page number(PageNo() だと、目次は最後のページ数になるので、getAliasNumPage() で、挿入したページの数を印字する)
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().' / '.$this->getAliasNbPages(), 'T', false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
