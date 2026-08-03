<?php

namespace Tests\Unit\Utilities\Csv;

use App\Enums\CsvCharacterCode;
use App\Utilities\Csv\CsvUtils;
use PHPUnit\Framework\TestCase;

/**
 * CsvUtils のCSV出力補助を対象に、表計算ソフトで開くCSVの危険なセル無害化と文字コード別出力を守る。
 */
class CsvUtilsTest extends TestCase
{
    /**
     * CSVを表計算ソフトで開いた際に数式として扱われる先頭文字は、文字列として扱われるよう保護する。
     *
     * @dataProvider formulaValueProvider
     */
    public function testEscapeCsvFormulaPrefixesFormulaValues($value)
    {
        $this->assertSame("'" . $value, CsvUtils::escapeCsvFormula($value));
    }

    /**
     * 通常の文字列や文字列以外の値は、CSV出力時の既存の見え方を必要以上に変えない。
     *
     * @dataProvider safeValueProvider
     */
    public function testEscapeCsvFormulaKeepsSafeValues($value)
    {
        $this->assertSame($value, CsvUtils::escapeCsvFormula($value));
    }

    /**
     * 共通CSV生成を通る出力では、UTF-8とShift-JISのどちらでも危険なセルが無害化される。
     */
    public function testGetResponseCsvDataEscapesFormulaValuesForSupportedCharacterCodes()
    {
        $csv_array = [
            ['見出し'],
            ['=SUM(A1)'],
        ];

        $utf8_csv = CsvUtils::getResponseCsvData($csv_array, CsvCharacterCode::utf_8);
        $sjis_csv = CsvUtils::getResponseCsvData($csv_array, CsvCharacterCode::sjis_win);

        $this->assertStringStartsWith(CsvUtils::bom, $utf8_csv);
        $this->assertStringContainsString('"\'=SUM(A1)"', $utf8_csv);
        $this->assertStringContainsString('"\'=SUM(A1)"', mb_convert_encoding($sjis_csv, 'UTF-8', 'SJIS-win'));
    }

    /**
     * 危険なセル値を網羅するためのデータを返す。
     */
    public function formulaValueProvider()
    {
        return [
            'equal' => ['=SUM(A1)'],
            'plus' => ['+SUM(A1)'],
            'minus' => ['-SUM(A1)'],
            'atmark' => ['@SUM(A1)'],
            'tab' => ["\t=SUM(A1)"],
            'carriage_return' => ["\r=SUM(A1)"],
            'line_feed' => ["\n=SUM(A1)"],
        ];
    }

    /**
     * 無害な値や非文字列値を網羅するためのデータを返す。
     */
    public function safeValueProvider()
    {
        return [
            'text' => ['normal text'],
            'empty' => [''],
            'null' => [null],
            'integer' => [123],
            'negative_integer' => [-1],
            'float' => [1.5],
        ];
    }
}
