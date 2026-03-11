<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('location: index.php');
    exit();
}

$user    = strtolower(trim($_SESSION['username'] ?? ''));
$isAdmin = in_array($user, ['ali muhammad', 'moinuddin', 'haris asim']);

if (!$isAdmin) {
    header('location: main.php');
    exit();
}

require_once 'connect.php';

$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('F');
$selectedYear  = isset($_GET['year'])  ? $_GET['year']  : date('Y');

$sql = "SELECT * FROM income_expense_items
        WHERE Month = :month AND year = :year
        ORDER BY id ASC";
$stmt = $db->prepare($sql);
$stmt->execute(['month' => $selectedMonth, 'year' => $selectedYear]);
$run = $stmt->fetchAll(PDO::FETCH_ASSOC);

$monthQuery = $db->query("SELECT DISTINCT Month FROM income_expense_items");
$months     = $monthQuery->fetchAll(PDO::FETCH_COLUMN);

$yearQuery = $db->query("SELECT DISTINCT year FROM income_expense_items ORDER BY year DESC");
$years     = $yearQuery->fetchAll(PDO::FETCH_COLUMN);

$totalExpense = 0;
$totalIncome  = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel — Evershine Finance</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --purple:      #7c3aed;
            --purple-mid:  #9333ea;
            --purple-lite: #a855f7;
            --dark:        #0f0a1e;
            --card:        #1a1035;
            --card2:       #221544;
            --border:      rgba(168,85,247,0.18);
            --text:        #f3e8ff;
            --muted:       #a78bca;
            --green:       #10b981;
            --red:         #ef4444;
            --gold:        #f59e0b;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--dark); color: var(--text); min-height: 100vh;
        }
        body::before {
            content: ''; position: fixed; inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 10%, rgba(124,58,237,0.2) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 80%, rgba(147,51,234,0.13) 0%, transparent 55%);
            pointer-events: none; z-index: 0;
        }

        /* TOP BAR */
        .top-bar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 1.2rem 2.5rem;
            background: rgba(26,16,53,0.85);
            border-bottom: 1px solid var(--border);
            backdrop-filter: blur(12px);
            position: sticky; top: 0; z-index: 100;
        }
        .brand {
            font-family: 'Syne', sans-serif; font-size: 1.1rem;
            font-weight: 800; letter-spacing: 0.06em; text-transform: uppercase;
            color: var(--purple-lite);
        }
        .brand span { color: var(--text); }
        .top-actions { display: flex; gap: 8px; }
        .btn-top {
            font-family: 'DM Sans', sans-serif; font-size: 0.8rem; font-weight: 500;
            padding: 7px 18px; border-radius: 8px;
            border: 1px solid var(--border);
            background: rgba(124,58,237,0.1);
            color: var(--muted); cursor: pointer; text-decoration: none; transition: all 0.2s;
        }
        .btn-top:hover  { background: rgba(124,58,237,0.25); color: var(--text); }
        .btn-top.logout { border-color: rgba(239,68,68,0.3); background: rgba(239,68,68,0.08); color: #fca5a5; }
        .btn-top.logout:hover { background: rgba(239,68,68,0.22); color: #fff; }
        .btn-top.back   { border-color: rgba(168,85,247,0.3); color: var(--purple-lite); }

        /* PAGE */
        .page-wrap { position: relative; z-index: 1; width: 100%; padding: 2.5rem 2.5rem 5rem; }

        /* HERO */
        .hero { text-align: center; margin-bottom: 2.5rem; }
        .hero-eyebrow {
            font-size: 0.7rem; font-weight: 600; letter-spacing: 0.22em;
            text-transform: uppercase; color: var(--purple-lite); margin-bottom: 0.5rem;
        }
        .hero h1 {
            font-family: 'Syne', sans-serif; font-size: clamp(1.8rem, 4vw, 2.6rem);
            font-weight: 800;
            background: linear-gradient(135deg, #f3e8ff 0%, #c084fc 55%, #a855f7 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; margin-bottom: 0.3rem;
        }
        .hero p { color: var(--muted); font-size: 0.85rem; }

        /* CARD */
        .card {
            background: var(--card); border: 1px solid var(--border);
            border-radius: 18px; padding: 1.75rem 2rem;
            margin-bottom: 1.5rem; position: relative; overflow: hidden; width: 100%;
        }
        .card::before {
            content:''; position:absolute; top:0; left:0; right:0; height:2px;
            background: linear-gradient(90deg, transparent, var(--purple-lite), transparent);
        }
        .card-title {
            font-family: 'Syne', sans-serif; font-size: 0.72rem; font-weight: 700;
            letter-spacing: 0.15em; text-transform: uppercase;
            color: var(--purple-lite); margin-bottom: 1.2rem;
            display: flex; align-items: center; gap: 7px;
        }

        /* FILTER ROW */
        .filter-row { display: flex; align-items: flex-end; gap: 1rem; flex-wrap: wrap; }
        .field { display: flex; flex-direction: column; gap: 6px; }
        .field label {
            font-size: 0.7rem; font-weight: 600; letter-spacing: 0.08em;
            text-transform: uppercase; color: var(--muted);
        }
        .field select {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(168,85,247,0.2);
            border-radius: 10px; padding: 10px 14px;
            color: var(--text); font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem; outline: none;
            transition: border-color 0.2s; -webkit-appearance: none; min-width: 150px;
        }
        .field select:focus { border-color: var(--purple-lite); }
        .field select option { background: #1a1035; }

        .btn-filter {
            padding: 10px 24px; height: 41px;
            background: linear-gradient(135deg, #4c1d95, var(--purple));
            border: 1px solid rgba(168,85,247,0.3);
            border-radius: 10px; color: #fff;
            font-family: 'DM Sans', sans-serif; font-size: 0.88rem;
            font-weight: 600; cursor: pointer; transition: all 0.2s;
        }
        .btn-filter:hover { background: linear-gradient(135deg, var(--purple), var(--purple-lite)); }

        .btn-excel {
            padding: 10px 24px; height: 41px;
            background: linear-gradient(135deg, #064e3b, #065f46);
            border: 1px solid rgba(16,185,129,0.3);
            border-radius: 10px; color: #6ee7b7;
            font-family: 'DM Sans', sans-serif; font-size: 0.88rem;
            font-weight: 600; cursor: pointer; transition: all 0.2s;
        }
        .btn-excel:hover { background: linear-gradient(135deg, #065f46, #047857); }

        /* REPORT HEADING */
        .report-heading {
            text-align: center; margin-bottom: 1.5rem;
            padding-bottom: 1.2rem; border-bottom: 1px solid var(--border);
        }
        .report-heading h2 {
            font-family: 'Syne', sans-serif; font-size: 1rem; font-weight: 800;
            color: var(--text); letter-spacing: 0.05em; margin-bottom: 2px;
        }
        .report-heading h3 { font-size: 0.88rem; color: var(--muted); font-weight: 500; margin-bottom: 2px; }
        .report-heading h4 { font-size: 0.82rem; color: var(--muted); font-weight: 400; }
        .report-heading .period {
            display: inline-block; margin-top: 0.6rem;
            font-family: 'Syne', sans-serif; font-size: 0.72rem; font-weight: 700;
            letter-spacing: 0.14em; text-transform: uppercase;
            background: rgba(124,58,237,0.18); border: 1px solid rgba(168,85,247,0.3);
            color: var(--purple-lite); padding: 4px 14px; border-radius: 50px;
        }

        /* RECEIPTS & PAYMENTS TABLE */
        .rp-table { width: 100%; border-collapse: collapse; }
        .rp-table th, .rp-table td {
            padding: 10px 14px; font-size: 0.86rem;
            border: 1px solid rgba(168,85,247,0.12);
        }
        .rp-table th {
            font-family: 'Syne', sans-serif; font-size: 0.65rem; font-weight: 700;
            letter-spacing: 0.13em; text-transform: uppercase;
            background: rgba(124,58,237,0.18); color: var(--purple-lite);
            border-color: rgba(168,85,247,0.2);
        }
        .rp-table .section-heading td {
            text-align: center; font-family: 'Syne', sans-serif; font-size: 0.8rem;
            font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;
            background: rgba(124,58,237,0.1); color: var(--purple-lite);
            padding: 10px; border-top: 2px solid rgba(168,85,247,0.3);
        }
        .rp-table .total-row td {
            font-weight: 600; background: rgba(168,85,247,0.07); color: var(--text);
        }
        .rp-table .amount-cell { text-align: right; font-family: 'Syne', sans-serif; font-weight: 600; }
        .rp-table .income-amt  { color: #34d399; }
        .rp-table .expense-amt { color: #f87171; }
        .rp-table .saving-row td { font-weight: 700; background: rgba(124,58,237,0.15); }
        .rp-table .saving-amt  { color: var(--gold); }
        .rp-table .cash-row td { font-weight: 700; background: rgba(245,158,11,0.08); }
        .rp-table .cash-amt    { color: #fcd34d; font-size: 1rem; }
        .rp-table .spacer td   { height: 8px; background: transparent; border: none; }
        .rp-table .balance-row td { background: rgba(168,85,247,0.06); }
        .rp-table tbody tr:hover { background: rgba(124,58,237,0.06); }

        @media (max-width: 700px) {
            .page-wrap  { padding: 1.5rem 1rem 4rem; }
            .top-bar    { padding: 1rem 1.2rem; }
            .filter-row { flex-direction: column; align-items: stretch; }
            .btn-filter, .btn-excel { width: 100%; }
        }
    </style>
</head>
<body>

    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="brand">Evershine <span>Finance</span></div>
        <div class="top-actions">
            <a href="main.php" class="btn-top back">← Dashboard</a>
            <a href="logout.php" class="btn-top logout">Sign Out</a>
        </div>
    </div>

    <div class="page-wrap">

        <!-- HERO -->
        <div class="hero">
            <div class="hero-eyebrow">Admin Control Panel</div>
            <h1>Receipts &amp; Payments</h1>
            <p>Full financial overview for any month and year.</p>
        </div>

        <!-- FILTER CARD -->
        <div class="card">
            <div class="card-title">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                Select Period
            </div>
            <div class="filter-row">
                <form method="GET" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;">
                    <div class="field">
                        <label>Month</label>
                        <select name="month">
                            <?php foreach ($months as $m): ?>
                                <option value="<?= $m ?>" <?= $m === $selectedMonth ? 'selected' : '' ?>><?= $m ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Year</label>
                        <select name="year">
                            <?php foreach ($years as $y): ?>
                                <option value="<?= $y ?>" <?= $y == $selectedYear ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-filter">Apply Filter</button>
                </form>

                <form action="download_excel.php" method="GET" style="margin-left:auto;">
                    <input type="hidden" name="month" value="<?= $selectedMonth ?>">
                    <input type="hidden" name="year"  value="<?= $selectedYear ?>">
                    <button type="submit" class="btn-excel">⬇ Download Excel</button>
                </form>
            </div>
        </div>

        <!-- REPORT CARD -->
        <div class="card">
            <div class="report-heading">
                <h2>COUNCIL FOR GULSHAN</h2>
                <h3>AGA KHAN EDUCATION BOARD FOR GULSHAN</h3>
                <h4>EVERSHINE AREA COMMITTEE</h4>
                <div class="period">Receipts &amp; Payments — <?= htmlspecialchars($selectedMonth) ?> <?= htmlspecialchars($selectedYear) ?></div>
            </div>

            <table class="rp-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="width:220px;text-align:right;">Amount (Rs)</th>
                    </tr>
                </thead>
                <tbody>

                    <!-- RECEIPTS HEADING -->
                    <tr class="section-heading">
                        <td colspan="2">Receipts / Events Fees</td>
                    </tr>

                    <?php
                    foreach ($run as $data):
                        if ($data['expense_income'] === 'income'):
                            $amount = $data['cost_profit'] > 0 ? number_format($data['cost_profit'], 2) : '-';
                            $totalIncome += $data['cost_profit'];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($data['portfolio_name']) ?> (<?= htmlspecialchars($data['Title']) ?>)</td>
                        <td class="amount-cell income-amt"><?= $amount ?></td>
                    </tr>
                    <?php endif; endforeach; ?>

                    <tr class="total-row">
                        <td>Total Receipts</td>
                        <td class="amount-cell income-amt"><?= number_format($totalIncome, 2) ?></td>
                    </tr>

                    <!-- PAYMENTS HEADING -->
                    <tr class="section-heading">
                        <td colspan="2">Payments</td>
                    </tr>

                    <?php
                    foreach ($run as $data):
                        if ($data['expense_income'] === 'expense'):
                            $amount = $data['cost_profit'] > 0 ? number_format($data['cost_profit'], 2) : '-';
                            $totalExpense += $data['cost_profit'];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($data['portfolio_name']) ?> (<?= htmlspecialchars($data['Title']) ?>)</td>
                        <td class="amount-cell expense-amt"><?= $amount ?></td>
                    </tr>
                    <?php endif; endforeach; ?>

                    <tr class="total-row">
                        <td>Total Payments</td>
                        <td class="amount-cell expense-amt"><?= number_format($totalExpense, 2) ?></td>
                    </tr>

                    <!-- SAVING -->
                    <tr class="saving-row">
                        <td>Saving / (Excess Payments)</td>
                        <td class="amount-cell saving-amt"><?= number_format($totalIncome - $totalExpense, 2) ?></td>
                    </tr>

                    <tr class="spacer"><td colspan="2"></td></tr>

                    <!-- OPENING BALANCE -->
                    <tr class="balance-row">
                        <td>Opening Balance</td>
                        <td class="amount-cell" style="color:var(--muted);">
                            Rs. <?php
                                if (!empty($run)) {
                                    $first = $run[0];
                                    echo number_format($first['cost_profit'] + $first['money_left'], 2);
                                } else { echo '0.00'; }
                            ?>
                        </td>
                    </tr>

                    <!-- CASH IN HAND -->
                    <tr class="cash-row">
                        <td>Cash In Hand</td>
                        <td class="amount-cell cash-amt">
                            Rs. <?= !empty($run) ? number_format(end($run)['money_left'], 2) : '0.00' ?>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

    </div><!-- /page-wrap -->
</body>
</html>