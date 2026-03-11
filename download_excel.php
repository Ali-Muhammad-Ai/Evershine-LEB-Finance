<?php
require_once 'connect.php';
session_start();
if (!isset($_SESSION['username'])) {
    header('location: index.php');
    exit();
}

$selectedMonth = $_GET['month'] ?? date('F');
$selectedYear  = $_GET['year']  ?? date('Y');

$sql = "SELECT * FROM income_expense_items
        WHERE Month = :month AND year = :year
        ORDER BY id ASC";
$stmt = $db->prepare($sql);
$stmt->execute(['month' => $selectedMonth, 'year' => $selectedYear]);
$run = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalIncome  = 0;
$totalExpense = 0;
$incomeRows   = [];
$expenseRows  = [];

foreach ($run as $d) {
    if ($d['expense_income'] === 'income') {
        $totalIncome += (float)$d['cost_profit'];
        $incomeRows[] = $d;
    } else {
        $totalExpense += (float)$d['cost_profit'];
        $expenseRows[] = $d;
    }
}
$saving         = $totalIncome - $totalExpense;
$openingBalance = !empty($run) ? (float)$run[0]['cost_profit'] + (float)$run[0]['money_left'] : 0;
$cashInHand     = !empty($run) ? (float)end($run)['money_left'] : 0;
$generatedOn    = date('d M Y');

$SS = []; $SI = [];
function ss(string $v): int {
    global $SS, $SI;
    if (!isset($SI[$v])) { $SI[$v] = count($SS); $SS[] = $v; }
    return $SI[$v];
}
function xe(string $s): string {
    return htmlspecialchars($s, ENT_XML1 | ENT_COMPAT, 'UTF-8');
}

$NAVY      = 'FF0D2137';
$BLUE      = 'FF1A4F8A';
$TEAL      = 'FF0E7C86';
$GREEN_BG  = 'FFE6F4EA';
$GREEN_BG2 = 'FFF1FAF2';
$RED_ACC   = 'FF8B1A1A';
$RED_BG    = 'FFFDF0F0';
$RED_BG2   = 'FFFDF6F6';
$TOTAL_BG  = 'FFD9E8F5';
$WHITE     = 'FFFFFFFF';
$LIGHT_GREY= 'FFF5F7FA';
$DARK_TEXT = 'FF0D1B2A';
$MUTED_TEXT= 'FF5A6A7A';
$BORDER_C  = 'FFB8CDD9';
$BORDER_S  = 'FF1A4F8A';
$GOLD      = 'FFFFD700';
$HDR_SUB   = 'FFAEC6E8';

$styleMap = [
    'hdrOrg'=>1,'hdrOrgC'=>29,'hdrTitle'=>2,'hdrSub'=>3,
    'colHdrL'=>4,'colHdrR'=>5,
    'secIncL'=>6,'secIncR'=>7,'secExpL'=>8,'secExpR'=>9,
    'dataIncEvenL'=>10,'dataIncEvenR'=>11,'dataIncOddL'=>12,'dataIncOddR'=>13,
    'dataExpEvenL'=>14,'dataExpEvenR'=>15,'dataExpOddL'=>16,'dataExpOddR'=>17,
    'totalIncL'=>18,'totalIncR'=>19,'totalExpL'=>20,'totalExpR'=>21,
    'savingL'=>22,'savingR'=>23,'summaryL'=>24,'summaryR'=>25,
    'italicL'=>26,'italicR'=>27,'spacer'=>28,'divider'=>30,'default'=>0,
];
function sId(string $n): int { global $styleMap; return $styleMap[$n] ?? 0; }

function sf(string $c): string {
    return '<patternFill patternType="solid"><fgColor rgb="'.$c.'"/><bgColor indexed="64"/></patternFill>';
}
function bd(string $s, string $c): string {
    $e='<color rgb="'.$c.'"/>';
    return "<left style=\"$s\">$e</left><right style=\"$s\">$e</right><top style=\"$s\">$e</top><bottom style=\"$s\">$e</bottom>";
}

$stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <numFmts count="1">
    <numFmt numFmtId="164" formatCode="#,##0.00"/>
  </numFmts>
  <fonts count="16">
    <font><sz val="11"/><color rgb="'.$DARK_TEXT.'"/><name val="Calibri"/></font>
    <font><sz val="14"/><b/><color rgb="'.$GOLD.'"/><name val="Calibri"/></font>
    <font><sz val="20"/><b/><color rgb="'.$WHITE.'"/><name val="Calibri"/></font>
    <font><sz val="12"/><b/><color rgb="'.$HDR_SUB.'"/><name val="Calibri"/></font>
    <font><sz val="11"/><b/><color rgb="'.$WHITE.'"/><name val="Calibri"/></font>
    <font><sz val="11"/><b/><color rgb="'.$WHITE.'"/><name val="Calibri"/></font>
    <font><sz val="11"/><b/><color rgb="'.$WHITE.'"/><name val="Calibri"/></font>
    <font><sz val="11"/><color rgb="'.$DARK_TEXT.'"/><name val="Calibri"/></font>
    <font><sz val="11"/><b/><color rgb="'.$BLUE.'"/><name val="Calibri"/></font>
    <font><sz val="11"/><b/><color rgb="'.$RED_ACC.'"/><name val="Calibri"/></font>
    <font><sz val="12"/><b/><color rgb="'.$WHITE.'"/><name val="Calibri"/></font>
    <font><sz val="11"/><b/><color rgb="'.$WHITE.'"/><name val="Calibri"/></font>
    <font><sz val="10"/><i/><color rgb="'.$MUTED_TEXT.'"/><name val="Calibri"/></font>
    <font><sz val="11"/><b/><color rgb="'.$WHITE.'"/><name val="Calibri"/></font>
    <font><sz val="13"/><b/><color rgb="'.$WHITE.'"/><name val="Calibri"/></font>
    <font><sz val="12"/><b/><color rgb="'.$GOLD.'"/><name val="Calibri"/></font>
  </fonts>
  <fills count="17">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill>'.sf($NAVY).'</fill>
    <fill>'.sf($BLUE).'</fill>
    <fill>'.sf($TEAL).'</fill>
    <fill>'.sf($RED_ACC).'</fill>
    <fill>'.sf($GREEN_BG).'</fill>
    <fill>'.sf($GREEN_BG2).'</fill>
    <fill>'.sf($RED_BG).'</fill>
    <fill>'.sf($RED_BG2).'</fill>
    <fill>'.sf($TOTAL_BG).'</fill>
    <fill>'.sf($BLUE).'</fill>
    <fill>'.sf($RED_ACC).'</fill>
    <fill>'.sf($NAVY).'</fill>
    <fill>'.sf($WHITE).'</fill>
    <fill>'.sf($LIGHT_GREY).'</fill>
    <fill>'.sf('FF0A5F67').'</fill>
  </fills>
  <borders count="6">
    <border><left/><right/><top/><bottom/><diagonal/></border>
    <border>'.bd('thin',   $BORDER_C).'<diagonal/></border>
    <border>'.bd('thin',   $BORDER_S).'<diagonal/></border>
    <border>'.bd('medium', $BLUE).'<diagonal/></border>
    <border>'.bd('medium', $NAVY).'<diagonal/></border>
    <border>'.bd('thin',   $TEAL).'<diagonal/></border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="31">
    <xf numFmtId="0"   fontId="0"  fillId="0"  borderId="0" xfId="0"/>
    <xf numFmtId="0"   fontId="1"  fillId="2"  borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0"   fontId="2"  fillId="2"  borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0"   fontId="3"  fillId="3"  borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0"   fontId="4"  fillId="3"  borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left"  vertical="center" indent="2"/></xf>
    <xf numFmtId="0"   fontId="4"  fillId="3"  borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center" indent="1"/></xf>
    <xf numFmtId="0"   fontId="5"  fillId="4"  borderId="5" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left"  vertical="center" indent="2"/></xf>
    <xf numFmtId="0"   fontId="5"  fillId="4"  borderId="5" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center" indent="1"/></xf>
    <xf numFmtId="0"   fontId="6"  fillId="5"  borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left"  vertical="center" indent="2"/></xf>
    <xf numFmtId="0"   fontId="6"  fillId="5"  borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center" indent="1"/></xf>
    <xf numFmtId="0"   fontId="7"  fillId="6"  borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left"  vertical="center" indent="3" wrapText="1"/></xf>
    <xf numFmtId="164" fontId="7"  fillId="6"  borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center" indent="1"/></xf>
    <xf numFmtId="0"   fontId="7"  fillId="7"  borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left"  vertical="center" indent="3" wrapText="1"/></xf>
    <xf numFmtId="164" fontId="7"  fillId="7"  borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center" indent="1"/></xf>
    <xf numFmtId="0"   fontId="7"  fillId="8"  borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left"  vertical="center" indent="3" wrapText="1"/></xf>
    <xf numFmtId="164" fontId="7"  fillId="8"  borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center" indent="1"/></xf>
    <xf numFmtId="0"   fontId="7"  fillId="9"  borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left"  vertical="center" indent="3" wrapText="1"/></xf>
    <xf numFmtId="164" fontId="7"  fillId="9"  borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center" indent="1"/></xf>
    <xf numFmtId="0"   fontId="8"  fillId="10" borderId="3" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left"  vertical="center" indent="2"/></xf>
    <xf numFmtId="164" fontId="8"  fillId="10" borderId="3" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center" indent="1"/></xf>
    <xf numFmtId="0"   fontId="9"  fillId="10" borderId="3" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left"  vertical="center" indent="2"/></xf>
    <xf numFmtId="164" fontId="9"  fillId="10" borderId="3" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center" indent="1"/></xf>
    <xf numFmtId="0"   fontId="10" fillId="11" borderId="4" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left"  vertical="center" indent="2"/></xf>
    <xf numFmtId="164" fontId="10" fillId="11" borderId="4" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center" indent="1"/></xf>
    <xf numFmtId="0"   fontId="11" fillId="13" borderId="4" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left"  vertical="center" indent="2"/></xf>
    <xf numFmtId="164" fontId="11" fillId="13" borderId="4" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center" indent="1"/></xf>
    <xf numFmtId="0"   fontId="12" fillId="14" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left"  vertical="center" indent="3"/></xf>
    <xf numFmtId="0"   fontId="12" fillId="14" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>
    <xf numFmtId="0"   fontId="0"  fillId="0"  borderId="0" xfId="0"/>
    <xf numFmtId="0"   fontId="15" fillId="2"  borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0"   fontId="0"  fillId="15" borderId="0" xfId="0" applyFill="1"/>
  </cellXfs>
</styleSheet>';

$cells = [];
$merges = [];
$r = 1;

$r++; // row 2 blank header top
$r++; // row 3
$cells[] = [$r, 'B', 's', 'COUNCIL FOR GULSHAN', 'hdrOrg'];
$cells[] = [$r, 'C', 's', '', 'hdrOrgC'];
$merges[] = "B{$r}:C{$r}";
$r++;

$cells[] = [$r, 'B', 's', 'AGA KHAN EDUCATION BOARD FOR GULSHAN', 'hdrOrg'];
$cells[] = [$r, 'C', 's', '', 'hdrOrgC'];
$merges[] = "B{$r}:C{$r}";
$r++;

$cells[] = [$r, 'B', 's', 'EVERSHINE AREA COMMITTEE', 'hdrOrg'];
$cells[] = [$r, 'C', 's', '', 'hdrOrgC'];
$merges[] = "B{$r}:C{$r}";
$r++;

$r++; // blank gap
$cells[] = [$r, 'B', 's', 'RECEIPTS & PAYMENTS', 'hdrTitle'];
$cells[] = [$r, 'C', 's', '', 'hdrTitle'];
$merges[] = "B{$r}:C{$r}";
$r++;

$cells[] = [$r, 'B', 's', strtoupper($selectedMonth).'   '.$selectedYear, 'hdrSub'];
$cells[] = [$r, 'C', 's', '', 'hdrSub'];
$merges[] = "B{$r}:C{$r}";
$r++;

$r++; // header bottom padding

$cells[] = [$r, 'B', 's', '', 'divider']; $cells[] = [$r, 'C', 's', '', 'divider']; $r++;

$cells[] = [$r, 'B', 's', 'Description',  'colHdrL'];
$cells[] = [$r, 'C', 's', 'Amount (Rs)',  'colHdrR'];
$r++;

// Income section
$cells[] = [$r, 'B', 's', "\xe2\x96\xb8  RECEIPTS / INCOME", 'secIncL'];
$cells[] = [$r, 'C', 's', '', 'secIncR'];
$r++;

if (!empty($incomeRows)) {
    foreach ($incomeRows as $i => $d) {
        $label = $d['portfolio_name'].'  —  '.$d['Title'];
        $sL = ($i%2===0) ? 'dataIncEvenL' : 'dataIncOddL';
        $sR = ($i%2===0) ? 'dataIncEvenR' : 'dataIncOddR';
        $cells[] = [$r, 'B', 's', $label,                  $sL];
        $cells[] = [$r, 'C', 'n', (float)$d['cost_profit'],$sR];
        $r++;
    }
} else {
    $cells[] = [$r,'B','s','No income was recorded for this month.','italicL'];
    $cells[] = [$r,'C','s','','italicR']; $r++;
}
$cells[] = [$r,'B','s','Total Receipts','totalIncL'];
$cells[] = [$r,'C','n',(float)$totalIncome,'totalIncR']; $r++;

$cells[] = [$r,'B','s','','divider']; $cells[] = [$r,'C','s','','divider']; $r++;

// Expense section
$cells[] = [$r, 'B', 's', "\xe2\x96\xb8  PAYMENTS / EXPENSES", 'secExpL'];
$cells[] = [$r, 'C', 's', '', 'secExpR'];
$r++;

if (!empty($expenseRows)) {
    foreach ($expenseRows as $i => $d) {
        $label = $d['portfolio_name'].'  —  '.$d['Title'];
        $sL = ($i%2===0) ? 'dataExpEvenL' : 'dataExpOddL';
        $sR = ($i%2===0) ? 'dataExpEvenR' : 'dataExpOddR';
        $cells[] = [$r,'B','s',$label,$sL];
        $cells[] = [$r,'C','n',(float)$d['cost_profit'],$sR];
        $r++;
    }
} else {
    $cells[] = [$r,'B','s','No expenses were recorded for this month.','italicL'];
    $cells[] = [$r,'C','s','','italicR']; $r++;
}
$cells[] = [$r,'B','s','Total Payments','totalExpL'];
$cells[] = [$r,'C','n',(float)$totalExpense,'totalExpR']; $r++;

$cells[] = [$r,'B','s','','divider']; $cells[] = [$r,'C','s','','divider']; $r++;

// Summary
$cells[] = [$r,'B','s','Saving / (Excess Payments)','savingL'];
$cells[] = [$r,'C','n',(float)$saving,'savingR']; $r++;
$cells[] = [$r,'B','s','Opening Balance','summaryL'];
$cells[] = [$r,'C','n',$openingBalance,'summaryR']; $r++;
$cells[] = [$r,'B','s','Cash In Hand','summaryL'];
$cells[] = [$r,'C','n',$cashInHand,'summaryR']; $r++;

$cells[] = [$r,'B','s','','divider']; $cells[] = [$r,'C','s','','divider']; $r++;

$cells[] = [$r,'B','s','Generated on: '.$generatedOn.'   ·   Evershine Area Committee Finance Report','italicL'];
$cells[] = [$r,'C','s','','italicR'];
$merges[] = "B{$r}:C{$r}";
$r++;

foreach ($cells as $c) { if ($c[2]==='s') ss($c[3]); }

$rowMap = [];
foreach ($cells as [$rowNum,$col,$type,$val,$style]) {
    $rowMap[$rowNum][] = [$col,$type,$val,$style];
}
ksort($rowMap);

$headerStart = 2; $headerEnd = 9;

$sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
           xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheetViews><sheetView workbookViewId="0" showGridLines="0"><selection activeCell="B2" sqref="B2"/></sheetView></sheetViews>
  <sheetFormatPr defaultRowHeight="22" customHeight="1"/>
  <cols>
    <col min="1" max="1" width="3"  customWidth="1"/>
    <col min="2" max="2" width="58" customWidth="1"/>
    <col min="3" max="3" width="22" customWidth="1"/>
    <col min="4" max="4" width="3"  customWidth="1"/>
  </cols>
  <sheetData>
    <row r="1" ht="8" customHeight="1"><c r="A1" s="28"/></row>'."\n";

$hRowHts = [2=>10, 3=>22, 4=>22, 5=>22, 6=>10, 7=>40, 8=>26, 9=>10];

foreach ($rowMap as $rowNum => $rowCells) {
    $ht = $hRowHts[$rowNum] ?? 22;
    $sheetXml .= '    <row r="'.$rowNum.'" ht="'.$ht.'" customHeight="1">'."\n";
    $sheetXml .= '      <c r="A'.$rowNum.'" s="'.sId('spacer').'"/>'."\n";
    foreach ($rowCells as [$col,$type,$val,$style]) {
        $ref = $col.$rowNum; $s = sId($style);
        if ($type==='s') {
            $idx = $SI[$val] ?? 0;
            $sheetXml .= '      <c r="'.$ref.'" t="s" s="'.$s.'"><v>'.$idx.'</v></c>'."\n";
        } else {
            $sheetXml .= '      <c r="'.$ref.'" s="'.$s.'"><v>'.$val.'</v></c>'."\n";
        }
    }
    $sheetXml .= '      <c r="D'.$rowNum.'" s="'.sId('spacer').'"/>'."\n";
    $sheetXml .= '    </row>'."\n";
}
$sheetXml .= '  </sheetData>'."\n";
if (!empty($merges)) {
    $sheetXml .= '  <mergeCells count="'.count($merges).'">'."\n";
    foreach ($merges as $m) { $sheetXml .= '    <mergeCell ref="'.$m.'"/>'."\n"; }
    $sheetXml .= '  </mergeCells>'."\n";
}
$sheetXml .= '  <pageMargins left="0.6" right="0.6" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>
  <pageSetup orientation="portrait" paperSize="9" fitToPage="1" fitToWidth="1" fitToHeight="0"/>
</worksheet>';

$ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="'.count($SS).'" uniqueCount="'.count($SS).'">'."\n";
foreach ($SS as $s) { $ssXml .= '  <si><t xml:space="preserve">'.xe($s).'</t></si>'."\n"; }
$ssXml .= '</sst>';

$contentTypes='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>';
$rels='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
$wbRels='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
$workbook='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Finance Report" sheetId="1" r:id="rId1"/></sheets></workbook>';

$tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_');
$zip = new ZipArchive();
$zip->open($tmpFile, ZipArchive::OVERWRITE);
$zip->addFromString('[Content_Types].xml', $contentTypes);
$zip->addFromString('_rels/.rels', $rels);
$zip->addFromString('xl/workbook.xml', $workbook);
$zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);
$zip->addFromString('xl/sharedStrings.xml', $ssXml);
$zip->addFromString('xl/styles.xml', $stylesXml);
$zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
$zip->close();

$filename = "Finance Report {$selectedMonth} {$selectedYear}.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Content-Length: '.filesize($tmpFile));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
readfile($tmpFile);
unlink($tmpFile);
exit;