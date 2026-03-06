<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('location: index.php');
    exit();
}
require_once 'connect.php';

$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('F');
$selectedYear  = isset($_GET['year']) ? $_GET['year'] : date('Y');

/* Fetch data for selected month and year */
$sql = "SELECT * FROM income_expense_items 
        WHERE Month = :month AND year = :year 
        ORDER BY id ASC";
$stmt = $db->prepare($sql);
$stmt->execute([
    'month' => $selectedMonth,
    'year'  => $selectedYear
]);
$run = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Get months for dropdown */
$monthQuery = $db->query("SELECT DISTINCT Month FROM income_expense_items");
$months = $monthQuery->fetchAll(PDO::FETCH_COLUMN);

/* Get years for dropdown */
$yearQuery = $db->query("SELECT DISTINCT year FROM income_expense_items ORDER BY year DESC");
$years = $yearQuery->fetchAll(PDO::FETCH_COLUMN);

$totalExpense = 0;
$totalIncome  = 0;
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Receipts & Payments</title>

    <style>
        body {
            font-family: Calibri, Arial;
            background: #f2f2f2;
        }

        .container {
            width: 950px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1,
        h2,
        h3,
        h4 {
            text-align: center;
            margin: 3px 0;
        }

        .filter-section {
            margin: 25px 0;
            display: flex;
            justify-content: space-between;
        }

        select,
        button {
            padding: 8px 12px;
            font-size: 14px;
        }

        button {
            background: #217346;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
        }

        th {
            background: #e6e6e6;
        }

        .section-title {
            font-weight: bold;
            background: #f9f9f9;
        }

        .amount {
            text-align: right;
        }

        .total-row {
            font-weight: bold;
            background: #f2f2f2;
        }

        .border-top {
            border-top: 3px solid black;
        }

        .border-bottom {
            border-bottom: 3px solid black;
        }

        .border-inline {
            border-inline: 3px solid black;
        }

        .skip-row {
            color: #f2f2f2;
        }

        .title-center {
            text-align: center;
            font-size: 1.4rem;
        }
        .logout{
            text-align: right;
        }

        .logout a {
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            background-color: red;
            color: black;
        }
    </style>
</head>

<body>
    <div class="form-wrapper">
        
        <div class="container">
        <div class="logout">
            <a href="logout.php">logout</a>
        </div>

        <h1>COUNCIL FOR GULSHAN</h1>
        <h2>AGA KHAN EDUCATION BOARD FOR GULSHAN</h2>
        <h3>EVERSHINE AREA COMMITTEE</h3>
        <h4>RECEIPTS & PAYMENTS</h4>
        <h4>FOR THE MONTH OF <?= htmlspecialchars($selectedMonth) ?> <?= htmlspecialchars($selectedYear) ?></h4>

        <div class="filter-section">

            <form method="GET">

                <label>Select Month:</label>
                <select name="month">
                    <?php foreach ($months as $month): ?>
                        <option value="<?= $month ?>" <?= $month == $selectedMonth ? 'selected' : '' ?>>
                            <?= $month ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Select Year:</label>
                <select name="year">
                    <?php foreach ($years as $year): ?>
                        <option value="<?= $year ?>" <?= $year == $selectedYear ? 'selected' : '' ?>>
                            <?= $year ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Filter</button>

            </form>

            <?php if (strtolower($_SESSION['username']) == 'ali muhammad' || 'moinuddin') { ?>

                <form action="download_excel.php" method="GET">
                    <input type="hidden" name="month" value="<?= $selectedMonth ?>">
                    <input type="hidden" name="year" value="<?= $selectedYear ?>">
                    <button type="submit">Download Excel</button>
                </form>

            <?php } ?>

        </div>

        <table>

            <tr class="section-title">
                <td>Description</td>
                <td width="200">Amount (Rs)</td>
            </tr>

            <tr class="section-title title-center">
                <td colspan="2">Receipts / Events Fees</td>
            </tr>

            <?php
            foreach ($run as $data):
                if ($data['expense_income'] == 'income'):

                    $amount = $data['cost_profit'] > 0 ? number_format($data['cost_profit'], 2) : '-';
                    $totalIncome += $data['cost_profit'];
            ?>

                    <tr>
                        <td><?= $data['portfolio_name'] ?> (<?= $data['Title'] ?>)</td>
                        <td class="amount"><?= $amount ?></td>
                    </tr>

            <?php
                endif;
            endforeach;
            ?>

            <tr class="total-row">
                <td>Total Receipts</td>
                <td class="amount"><?= number_format($totalIncome, 2) ?></td>
            </tr>

            <tr class="section-title title-center">
                <td colspan="2">Payments</td>
            </tr>

            <?php
            foreach ($run as $data):
                if ($data['expense_income'] == 'expense'):

                    $amount = $data['cost_profit'] > 0 ? number_format($data['cost_profit'], 2) : '-';
                    $totalExpense += $data['cost_profit'];
            ?>

                    <tr>
                        <td><?= $data['portfolio_name'] ?> (<?= $data['Title'] ?>)</td>
                        <td class="amount"><?= $amount ?></td>
                    </tr>

            <?php
                endif;
            endforeach;
            ?>

            <tr class="total-row">
                <td>Total Payments</td>
                <td class="amount"><?= number_format($totalExpense, 2) ?></td>
            </tr>

            <tr class="total-row border-top border-inline">
                <td>Saving / (Excess Payments)</td>
                <td class="amount"><?= number_format($totalIncome - $totalExpense, 2) ?></td>
            </tr>

            <tr class="total-row border-inline skip-row">
                <td>Skipped Row</td>
                <td>Skipped Row</td>
            </tr>

            <tr class="total-row border-inline">
                <td>Opening Balance</td>

                <?php
                $incrementation = 1;
                foreach ($run as $data) {
                    if ($incrementation == 1) {
                ?>
                        <td class="amount">Rs. <?= $data['cost_profit'] + $data['money_left'] ?></td>
                <?php
                        $incrementation++;
                    }
                }
                ?>

            </tr>

            <tr class="total-row border-bottom border-inline">
                <td>Cash In Hand</td>
                <td class="amount">Rs. <?= isset($data['money_left']) ? $data['money_left'] : 0 ?></td>
            </tr>

        </table>

    </div>

</body>

</html>