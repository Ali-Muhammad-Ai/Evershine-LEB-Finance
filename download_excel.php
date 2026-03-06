<?php
require_once 'connect.php';
session_start();
if (!isset($_SESSION['username'])) {
    header('location: index.php');
    exit();
}

$selectedMonth = $_GET['month'] ?? date('F');
$selectedYear  = $_GET['year'] ?? date('Y');

$totalExpense = 0;
$totalIncome = 0;

$incomeFound = false;
$expenseFound = false;

$sql = "SELECT * FROM income_expense_items 
        WHERE Month = :month AND year = :year
        ORDER BY id ASC";

$stmt = $db->prepare($sql);
$stmt->execute([
    'month' => $selectedMonth,
    'year'  => $selectedYear
]);

$run = $stmt->fetchAll(PDO::FETCH_ASSOC);

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Monthly Finance of {$selectedMonth} {$selectedYear}.xls");

echo "<table border='1'>";

echo "<tr><td></td><td></td></tr>";

echo "<tr><td></td><th colspan='2'>COUNCIL FOR GULSHAN</th></tr>";
echo "<tr><td></td><th colspan='2'>AGA KHAN EDUCATION BOARD FOR GULSHAN</th></tr>";
echo "<tr><td></td><th colspan='2'>EVERSHINE AREA COMMITTEE</th></tr>";
echo "<tr><td></td><th colspan='2'>RECEIPTS & PAYMENTS - $selectedMonth $selectedYear</th></tr>";
echo "<tr><td></td><td colspan='2'></td></tr>";

echo "<tr><td></td><th>Description</th><th>Amount (Rs)</th></tr>";
echo "<tr><td></td><td colspan='2'><b>Income</b></td></tr>";

/* INCOME */
foreach ($run as $data) {

    if ($data['expense_income'] == 'income') {

        $incomeFound = true;
        $totalIncome += $data['cost_profit'];

        echo "<tr>
        <td></td>
        <td>{$data['portfolio_name']} ({$data['Title']})</td>
        <td>" . number_format($data['cost_profit'], 2) . "</td>
        </tr>";
    }
}

/* IF NO INCOME */
if (!$incomeFound) {

    echo "<tr>
    <td></td>
    <td colspan='2'><i>No Income Was Generated In This Month</i></td>
    </tr>";
}

echo "<tr><td></td><td colspan='2'></td></tr>";

echo "<tr><td></td><td><b>Total Income</b></td>
<td><b>" . number_format($totalIncome, 2) . "</b></td></tr>";

echo "<tr><td></td><td colspan='2'></td></tr>";
echo "<tr><td></td><td colspan='2'><b>Expenses</b></td></tr>";

/* EXPENSES */
foreach ($run as $data) {

    if ($data['expense_income'] == 'expense') {

        $expenseFound = true;
        $totalExpense += $data['cost_profit'];

        echo "<tr>
        <td></td>
        <td>{$data['portfolio_name']} ({$data['Title']})</td>
        <td>" . number_format($data['cost_profit'], 2) . "</td>
        </tr>";
    }
}

/* IF NO EXPENSE */
if (!$expenseFound) {

    echo "<tr>
    <td></td>
    <td colspan='2'><i>No Expenses Were Made This Month</i></td>
    </tr>";
}

echo "<tr><td></td><td colspan='2'></td></tr>";

echo "<tr>
<td></td>
<td><b>Total Expenses</b></td>
<td><b>" . number_format($totalExpense, 2) . "</b></td>
</tr>";

echo "<tr><td></td><td colspan='2'></td></tr>";

echo "<tr>
<td></td>
<td><b>Saving / (Excess Payments)</b></td>
<td><b>" . number_format($totalIncome - $totalExpense, 2) . "</b></td>
</tr>";

/* OPENING BALANCE + CASH */

if (!empty($run)) {

    $firstRecord = $run[0];

    $openingBalance = $firstRecord['cost_profit'] + $firstRecord['money_left'];
    $cashInHand = $firstRecord['money_left'];

    echo "<tr>
    <td></td>
    <td><b>Opening Balance</b></td>
    <td><b>Rs. " . number_format($openingBalance, 2) . "</b></td>
    </tr>";

    echo "<tr>
    <td></td>
    <td><b>Cash In Hand</b></td>
    <td><b>Rs. " . number_format($cashInHand, 2) . "</b></td>
    </tr>";
}

echo "</table>";
exit;
