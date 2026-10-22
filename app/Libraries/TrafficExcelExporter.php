<?php

namespace App\Libraries;

/** Small dependency-free XLSX writer for admin traffic reports. */
final class TrafficExcelExporter
{
    /** @param array<int, array{title:string, rows:array, widths:array, headerRows?:array, numericColumns?:array, filterHeaderRow?:int}> $sheets */
    public function generate(array $sheets): string
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('PHP ZipArchive extension is required to create XLSX files.');
        }
        $path = tempnam(sys_get_temp_dir(), 'lunarea-traffic-');
        if ($path === false) throw new \RuntimeException('Temporary workbook could not be created.');

        try {
            $zip = new \ZipArchive();
            if ($zip->open($path, \ZipArchive::OVERWRITE) !== true) throw new \RuntimeException('Workbook archive could not be opened.');
            $sheetCount = count($sheets);
            $zip->addFromString('[Content_Types].xml', $this->contentTypes($sheetCount));
            $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
            $zip->addFromString('xl/workbook.xml', $this->workbookXml($sheets));
            $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels($sheetCount));
            $zip->addFromString('xl/styles.xml', $this->stylesXml());
            foreach (array_values($sheets) as $index => $sheet) {
                $zip->addFromString('xl/worksheets/sheet' . ($index + 1) . '.xml', $this->sheetXml($sheet));
            }
            $zip->close();
            $content = file_get_contents($path);
            if ($content === false) throw new \RuntimeException('Workbook could not be read.');
            return $content;
        } finally {
            if (is_file($path)) @unlink($path);
        }
    }

    private function sheetXml(array $sheet): string
    {
        $rows = $sheet['rows'];
        $lastColumn = $this->columnName(max(1, count($sheet['widths'])));
        $lastRow = max(1, count($rows));
        $headerRows = $sheet['headerRows'] ?? [];
        $numericColumns = $sheet['numericColumns'] ?? [];
        $xmlRows = '';
        foreach ($rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 1;
            $cells = '';
            foreach (array_values($row) as $columnIndex => $value) {
                $reference = $this->columnName($columnIndex + 1) . $rowNumber;
                $style = $rowNumber === 1 ? 1 : (in_array($rowNumber, $headerRows, true) ? 3 : (($rowNumber % 2) === 0 ? 6 : 5));
                if (in_array($columnIndex + 1, $numericColumns, true) && is_numeric($value)) {
                    $style = $rowNumber === 1 ? 1 : (($rowNumber % 2) === 0 ? 7 : 4);
                    $cells .= '<c r="' . $reference . '" s="' . $style . '"><v>' . (0 + $value) . '</v></c>';
                } elseif ($value !== null && $value !== '') {
                    $text = htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                    $cells .= '<c r="' . $reference . '" t="inlineStr" s="' . $style . '"><is><t xml:space="preserve">' . $text . '</t></is></c>';
                } else {
                    $cells .= '<c r="' . $reference . '" s="' . $style . '"/>';
                }
            }
            $xmlRows .= '<row r="' . $rowNumber . '">' . $cells . '</row>';
        }

        $columns = '';
        foreach ($sheet['widths'] as $index => $width) {
            $column = $index + 1;
            $columns .= '<col min="' . $column . '" max="' . $column . '" width="' . (float) $width . '" customWidth="1"/>';
        }
        $views = '';
        $headerRow = (int) ($sheet['filterHeaderRow'] ?? 0);
        if ($headerRow > 0) {
            $views = '<sheetViews><sheetView workbookViewId="0"><pane ySplit="' . $headerRow . '" topLeftCell="A' . ($headerRow + 1) . '" activePane="bottomLeft" state="frozen"/><selection pane="bottomLeft" activeCell="A' . ($headerRow + 1) . '" sqref="A' . ($headerRow + 1) . '"/></sheetView></sheetViews>';
        }
        $filter = '';
        if ($headerRow > 0 && $lastRow > $headerRow) {
            $filter = '<autoFilter ref="A' . $headerRow . ':' . $lastColumn . $lastRow . '"/>';
        }
        $merges = $lastColumn !== 'A' && $lastRow >= 2
            ? '<mergeCells count="2"><mergeCell ref="A1:' . $lastColumn . '1"/><mergeCell ref="A2:' . $lastColumn . '2"/></mergeCells>'
            : '';
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<dimension ref="A1:' . $lastColumn . $lastRow . '"/>' . $views . '<cols>' . $columns . '</cols><sheetData>' . $xmlRows . '</sheetData>' . $filter . $merges . '</worksheet>';
    }

    private function workbookXml(array $sheets): string
    {
        $entries = '';
        foreach (array_values($sheets) as $index => $sheet) {
            $name = htmlspecialchars(substr((string) $sheet['title'], 0, 31), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $entries .= '<sheet name="' . $name . '" sheetId="' . ($index + 1) . '" r:id="rId' . ($index + 1) . '"/>';
        }
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>' . $entries . '</sheets></workbook>';
    }

    private function workbookRels(int $count): string
    {
        $rels = '';
        for ($i = 1; $i <= $count; $i++) {
            $rels .= '<Relationship Id="rId' . $i . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $i . '.xml"/>';
        }
        $rels .= '<Relationship Id="rId' . ($count + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $rels . '</Relationships>';
    }

    private function contentTypes(int $count): string
    {
        $overrides = '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        for ($i = 1; $i <= $count; $i++) {
            $overrides .= '<Override PartName="/xl/worksheets/sheet' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/>' . $overrides . '</Types>';
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="1"><numFmt numFmtId="164" formatCode="#,##0.0"/></numFmts>'
            . '<fonts count="4"><font><sz val="10"/><name val="Aptos"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="16"/><name val="Aptos Display"/></font><font><i/><color rgb="FF66737F"/><sz val="10"/><name val="Aptos"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="10"/><name val="Aptos"/></font></fonts>'
            . '<fills count="4"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF243B6B"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFAF7F2"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left/><right/><top/><bottom style="thin"><color rgb="FFDFE5EA"/></bottom><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="8"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="center"/></xf><xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="3" fillId="2" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="center"/></xf><xf numFmtId="3" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1"/><xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"/><xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0"/><xf numFmtId="3" fontId="0" fillId="3" borderId="1" xfId="0" applyNumberFormat="1"/></cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    }

    private function columnName(int $column): string
    {
        $name = '';
        while ($column > 0) {
            $remainder = ($column - 1) % 26;
            $name = chr(65 + $remainder) . $name;
            $column = intdiv($column - 1, 26);
        }
        return $name;
    }
}
