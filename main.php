<?php
require_once 'connect.php';
session_start();
if (!isset($_SESSION['username'])) {
    header('location: index.php');
    exit();
}

$user    = strtolower(trim($_SESSION['username'] ?? ''));
$isAdmin = in_array($user, ['ali muhammad', 'moinuddin', 'haris asim']);

$portfolioMap = [
    'english language leap'                 => 'English Language Leap',
    'parwaaz'                               => 'Parwaaz',
    'institute of professional development' => 'Ipd',
    'quality schooling program'             => 'Quality Schooling Program',
    'career and scholarship program'        => 'Career and Scholarship',
    'stem and robotics'                     => 'Stem and Robotics',
];

$lockedPortfolio        = $portfolioMap[$user] ?? null;
$lockedPortfolioDisplay = $lockedPortfolio ?? ucwords($user);

// Always default to current month on first load
$selectedDate = (isset($_GET['date']) && !empty($_GET['date'])) ? $_GET['date'] : date('Y-m');
list($year, $month_num) = explode('-', $selectedDate);
$month = date("F", mktime(0, 0, 0, (int)$month_num, 10));

if ($isAdmin) {
    $stmt = $db->prepare(
        'SELECT id, Month, Title, year, portfolio_name, expense_income, cost_profit, money_left
         FROM income_expense_items WHERE Month = ? AND year = ? ORDER BY id ASC'
    );
    $stmt->execute([$month, $year]);
} else {
    $filterPortfolio = $lockedPortfolio ?? 'other';
    $stmt = $db->prepare(
        'SELECT id, Month, Title, year, portfolio_name, expense_income, cost_profit, money_left
         FROM income_expense_items WHERE portfolio_name = ? AND Month = ? AND year = ? ORDER BY id ASC'
    );
    $stmt->execute([$filterPortfolio, $month, $year]);
}

$results      = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalIncome  = 0;
$totalExpense = 0;
foreach ($results as $row) {
    if ($row['expense_income'] === 'income')  $totalIncome  += $row['cost_profit'];
    if ($row['expense_income'] === 'expense') $totalExpense += $row['cost_profit'];
}
$balance    = $totalIncome - $totalExpense;
$cashInHand = !empty($results) ? end($results)['money_left'] : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evershine Finance</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --purple: #7c3aed;
            --purple-mid: #9333ea;
            --purple-lite: #a855f7;
            --dark: #0f0a1e;
            --card: #1a1035;
            --card2: #221544;
            --border: rgba(168, 85, 247, 0.18);
            --text: #f3e8ff;
            --muted: #a78bca;
            --green: #10b981;
            --red: #ef4444;
            --gold: #f59e0b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--dark);
            color: var(--text);
            min-height: 100vh;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 10%, rgba(124, 58, 237, 0.2) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 80%, rgba(147, 51, 234, 0.13) 0%, transparent 55%);
            pointer-events: none;
            z-index: 0;
        }

        .page-wrap {
            position: relative;
            z-index: 1;
            width: 100%;
            padding: 0 0 5rem;
        }

        /* ── TOP BAR ── */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 2.5rem;
            background: rgba(26, 16, 53, 0.85);
            border-bottom: 1px solid var(--border);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .brand {
            font-family: 'Syne', sans-serif;
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--purple-lite);
        }

        .brand span {
            color: var(--text);
        }

        .top-actions {
            display: flex;
            gap: 8px;
        }

        .btn-top {
            font-family: 'DM Sans', sans-serif;
            font-size: 0.8rem;
            font-weight: 500;
            padding: 7px 18px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: rgba(124, 58, 237, 0.1);
            color: var(--muted);
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-top:hover {
            background: rgba(124, 58, 237, 0.25);
            color: var(--text);
        }

        .btn-top.admin {
            border-color: rgba(245, 158, 11, 0.35);
            background: rgba(245, 158, 11, 0.08);
            color: #fcd34d;
        }

        .btn-top.admin:hover {
            background: rgba(245, 158, 11, 0.2);
        }

        .btn-top.logout {
            border-color: rgba(239, 68, 68, 0.3);
            background: rgba(239, 68, 68, 0.08);
            color: #fca5a5;
        }

        .btn-top.logout:hover {
            background: rgba(239, 68, 68, 0.22);
            color: #fff;
        }

        /* ── HERO ── */
        .hero {
            text-align: center;
            padding: 3rem 2rem 2.5rem;
            background: linear-gradient(180deg, rgba(124, 58, 237, 0.1) 0%, transparent 100%);
            border-bottom: 1px solid var(--border);
        }

        .hero-eyebrow {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--purple-lite);
            margin-bottom: 0.75rem;
        }

        .hero h1 {
            font-family: 'Syne', sans-serif;
            font-size: clamp(2.2rem, 5vw, 3.2rem);
            font-weight: 800;
            line-height: 1.1;
            background: linear-gradient(135deg, #f3e8ff 0%, #c084fc 55%, #a855f7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.6rem;
        }

        .hero p {
            color: var(--muted);
            font-size: 0.9rem;
        }

        /* ── TOAST ── */
        .toast {
            position: fixed;
            top: 1.5rem;
            left: 50%;
            transform: translateX(-50%) translateY(-20px);
            padding: 11px 28px;
            border-radius: 50px;
            font-size: 0.88rem;
            font-weight: 500;
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            white-space: nowrap;
        }

        .toast.success {
            background: linear-gradient(135deg, #064e3b, #065f46);
            border: 1px solid rgba(16, 185, 129, 0.4);
            color: #6ee7b7;
            <?php if (($_GET['state'] ?? '') === 'succ') echo 'animation: toastAnim 3.5s forwards;'; ?>
        }

        .toast.error {
            background: linear-gradient(135deg, #7f1d1d, #991b1b);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fca5a5;
            <?php if (($_GET['state'] ?? '') === 'err') echo 'animation: toastAnim 3.5s forwards;'; ?>
        }

        @keyframes toastAnim {
            0% {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }

            10% {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }

            85% {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }

            100% {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
        }

        /* ── INNER CONTENT ── */
        .inner {
            width: 100%;
            padding: 2rem 2.5rem;
        }

        /* ── CARD ── */
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 1.75rem 2rem;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
            width: 100%;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--purple-lite), transparent);
        }

        .card-title {
            font-family: 'Syne', sans-serif;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--purple-lite);
            margin-bottom: 1.4rem;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        /* ── FORM ── */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .form-grid .full {
            grid-column: 1 / -1;
        }

        .form-grid .half {
            grid-column: span 1;
        }

        .field label {
            display: block;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .field input,
        .field select {
            width: 100%;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(168, 85, 247, 0.2);
            border-radius: 10px;
            padding: 10px 14px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            outline: none;
            transition: border-color 0.2s, background 0.2s;
            -webkit-appearance: none;
        }

        .field input:focus,
        .field select:focus {
            border-color: var(--purple-lite);
            background: rgba(168, 85, 247, 0.07);
        }

        .field select option {
            background: #1a1035;
            color: var(--text);
        }

        .field input[readonly] {
            background: rgba(168, 85, 247, 0.1);
            border-color: rgba(168, 85, 247, 0.4);
            color: var(--purple-lite);
            cursor: not-allowed;
            font-weight: 600;
        }

        .btn-submit {
            width: 100%;
            margin-top: 1.1rem;
            padding: 13px;
            background: linear-gradient(135deg, var(--purple), var(--purple-mid));
            border: none;
            border-radius: 12px;
            color: #fff;
            font-family: 'Syne', sans-serif;
            font-size: 0.92rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            cursor: pointer;
            transition: all 0.25s;
            box-shadow: 0 4px 18px rgba(124, 58, 237, 0.35);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 26px rgba(124, 58, 237, 0.5);
        }

        /* ── DIVIDER ── */
        .section-divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 2rem 0 1.5rem;
        }

        .section-divider::before,
        .section-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .section-divider span {
            font-family: 'Syne', sans-serif;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--muted);
            white-space: nowrap;
        }

        /* ── FILTER ── */
        .filter-row {
            display: flex;
            align-items: flex-end;
            gap: 1rem;
        }

        .filter-row .field {
            flex: 1;
            max-width: 320px;
        }

        .btn-filter {
            padding: 10px 28px;
            height: 41px;
            background: linear-gradient(135deg, #4c1d95, var(--purple));
            border: 1px solid rgba(168, 85, 247, 0.3);
            border-radius: 10px;
            color: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-filter:hover {
            background: linear-gradient(135deg, var(--purple), var(--purple-lite));
        }

        /* ── ADMIN STATS (only shown to admins) ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
            width: 100%;
        }

        .stat-card {
            background: var(--card2);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.3rem 1rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .stat-card.income::after {
            background: var(--green);
        }

        .stat-card.expense::after {
            background: var(--red);
        }

        .stat-card.net::after {
            background: #6366f1;
        }

        .stat-card.cash::after {
            background: var(--gold);
        }

        .stat-label {
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .stat-value {
            font-family: 'Syne', sans-serif;
            font-size: 1.15rem;
            font-weight: 800;
        }

        .stat-card.income .stat-value {
            color: #34d399;
        }

        .stat-card.expense .stat-value {
            color: #f87171;
        }

        .stat-card.net .stat-value {
            color: #818cf8;
        }

        .stat-card.cash .stat-value {
            color: #fcd34d;
        }

        /* ── TABLE ── */
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .month-badge {
            font-family: 'Syne', sans-serif;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            background: rgba(124, 58, 237, 0.18);
            border: 1px solid rgba(168, 85, 247, 0.3);
            color: var(--purple-lite);
            padding: 5px 14px;
            border-radius: 50px;
        }

        .record-count {
            font-size: 0.78rem;
            color: var(--muted);
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
            border-radius: 14px;
            border: 1px solid var(--border);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: rgba(124, 58, 237, 0.18);
        }

        th {
            font-family: 'Syne', sans-serif;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.13em;
            text-transform: uppercase;
            color: var(--purple-lite);
            padding: 13px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        td {
            padding: 12px 16px;
            font-size: 0.86rem;
            border-bottom: 1px solid rgba(168, 85, 247, 0.06);
        }

        tbody tr {
            transition: background 0.15s;
            animation: rowIn 0.3s ease both;
        }

        tbody tr:hover {
            background: rgba(124, 58, 237, 0.08);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:nth-child(1) {
            animation-delay: .04s;
        }

        tbody tr:nth-child(2) {
            animation-delay: .08s;
        }

        tbody tr:nth-child(3) {
            animation-delay: .12s;
        }

        tbody tr:nth-child(4) {
            animation-delay: .16s;
        }

        tbody tr:nth-child(n+5) {
            animation-delay: .20s;
        }

        @keyframes rowIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .portfolio-pill {
            display: inline-block;
            font-size: 0.68rem;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 50px;
            background: rgba(124, 58, 237, 0.18);
            border: 1px solid rgba(168, 85, 247, 0.25);
            color: var(--purple-lite);
            white-space: nowrap;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 50px;
        }

        .badge.income {
            background: rgba(16, 185, 129, 0.14);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.22);
        }

        .badge.expense {
            background: rgba(239, 68, 68, 0.14);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.22);
        }

        .badge::before {
            content: '';
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: currentColor;
        }

        .amount {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 0.88rem;
        }

        .amount.income {
            color: #34d399;
        }

        .amount.expense {
            color: #f87171;
        }

        .action-cell {
            display: flex;
            gap: 5px;
        }

        .btn-edit,
        .btn-delete {
            padding: 5px 13px;
            border-radius: 7px;
            font-size: 0.72rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.18s;
            border: 1px solid transparent;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            background: none;
            white-space: nowrap;
        }

        .btn-edit {
            border-color: rgba(99, 102, 241, 0.3);
            color: #a5b4fc;
        }

        .btn-edit:hover {
            background: rgba(99, 102, 241, 0.22);
            color: #fff;
        }

        .btn-delete {
            border-color: rgba(239, 68, 68, 0.28);
            color: #fca5a5;
        }

        .btn-delete:hover {
            background: rgba(239, 68, 68, 0.22);
            color: #fff;
        }

        .empty-state {
            padding: 4rem 1rem;
            text-align: center;
        }

        .empty-icon {
            font-size: 2.5rem;
            opacity: 0.3;
            margin-bottom: 0.8rem;
        }

        .empty-state p {
            color: var(--muted);
            font-size: 0.9rem;
        }

        input[type="month"]::-webkit-calendar-picker-indicator {
            filter: invert(0.5) sepia(1) saturate(5) hue-rotate(220deg);
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .inner {
                padding: 1.5rem 1rem;
            }

            .top-bar {
                padding: 1rem 1.2rem;
            }

            .form-grid {
                grid-template-columns: 1fr 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }

            .filter-row {
                flex-direction: column;
            }

            .filter-row .field {
                max-width: 100%;
            }

            .btn-filter {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <div class="toast success">✓ &nbsp; Entry saved successfully</div>
    <div class="toast error">✕ &nbsp; Failed to save entry</div>

    <div class="page-wrap">

        <!-- TOP BAR -->
        <div class="top-bar">
            <div class="brand">Evershine <span>Finance</span></div>
            <div class="top-actions">
                <?php if ($isAdmin): ?>
                    <a href="excel_present.php" class="btn-top admin">⚙ Admin Panel</a>
                <?php endif; ?>
                <a href="logout.php" class="btn-top logout">Sign Out</a>
            </div>
        </div>

        <!-- HERO -->
        <div class="hero">
            <div class="hero-eyebrow">Evershine Area Committee</div>
            <h1>Finance Manager</h1>
            <p>Welcome back, <strong><?= htmlspecialchars(ucwords($_SESSION['username'])) ?></strong>
                <?php if ($isAdmin): ?>
                    &nbsp;·&nbsp; <span style="color:#fcd34d;font-size:0.8rem;font-weight:600;">Admin</span>
                <?php endif; ?>
            </p>
        </div>

        <div class="inner">

            <!-- ADD ENTRY FORM -->
            <div class="card">
                <div class="card-title">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Add New Entry
                </div>
                <form action="request.php" method="POST">
                    <div class="form-grid">

                        <div class="field half">
                            <label>Portfolio</label>
                            <?php if ($isAdmin): ?>
                                <select name="portfolio_name" required>
                                    <option value="" disabled selected>Choose portfolio</option>
                                    <option value="Parwaaz">Parwaaz</option>
                                    <option value="Ipd">IPD</option>
                                    <option value="Career and Scholarship">Career &amp; Scholarship</option>
                                    <option value="Stem and Robotics">Stem &amp; Robotics</option>
                                    <option value="English Language Leap">English Language Leap</option>
                                    <option value="Quality Schooling Program">Quality Schooling Program</option>
                                    <option value="Donation">Donation</option>
                                    <option value="other">Other</option>
                                </select>
                            <?php else: ?>
                                <input type="text" value="<?= htmlspecialchars($lockedPortfolioDisplay) ?>" readonly>
                                <input type="hidden" name="portfolio_name" value="<?= htmlspecialchars($lockedPortfolioDisplay) ?>">
                            <?php endif; ?>
                        </div>

                        <div class="field half">
                            <label>Month</label>
                            <input type="month" name="month" value="<?= date('Y-m') ?>" required />
                        </div>

                        <div class="field half">
                            <label>Type</label>
                            <select name="sign" required>
                                <option value="" disabled selected>Select type</option>
                                <option value="income">Income</option>
                                <option value="expense">Expense</option>
                            </select>
                        </div>

                        <div class="field full">
                            <label>Title / Description</label>
                            <input type="text" name="title" placeholder="e.g. Event registration fee" required />
                        </div>

                        <div class="field half">
                            <label>Amount (Rs)</label>
                            <input type="number" name="cost" placeholder="0" min="0" required />
                        </div>

                    </div>
                    <button name="submitBtn" type="submit" class="btn-submit">Submit Entry →</button>
                </form>
            </div>

            <!-- DIVIDER -->
            <div class="section-divider"><span>Records</span></div>

            <!-- FILTER -->
            <div class="card">
                <div class="card-title">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
                    </svg>
                    Filter by Period
                </div>
                <form method="GET" class="filter-row">
                    <div class="field">
                        <label>Month &amp; Year</label>
                        <input type="month" name="date" value="<?= htmlspecialchars($selectedDate) ?>" required>
                    </div>
                    <button type="submit" class="btn-filter">Update View</button>
                </form>
            </div>

            <?php if (!empty($results)): ?>

                <!-- ADMIN-ONLY STATS -->
                <?php if ($isAdmin): ?>
                    <div class="stats-grid">
                        <div class="stat-card income">
                            <div class="stat-label">Total Income</div>
                            <div class="stat-value">Rs <?= number_format($totalIncome) ?></div>
                        </div>
                        <div class="stat-card expense">
                            <div class="stat-label">Total Expense</div>
                            <div class="stat-value">Rs <?= number_format($totalExpense) ?></div>
                        </div>
                        <div class="stat-card net">
                            <div class="stat-label">Net Saving</div>
                            <div class="stat-value"><?= $balance < 0 ? '-' : '' ?>Rs <?= number_format(abs($balance)) ?></div>
                        </div>
                        <div class="stat-card cash">
                            <div class="stat-label">Cash In Hand</div>
                            <div class="stat-value">Rs <?= number_format($cashInHand) ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- TABLE HEADER -->
                <div class="table-header">
                    <span class="month-badge"><?= htmlspecialchars($month) ?> <?= htmlspecialchars($year) ?></span>
                    <span class="record-count"><?= count($results) ?> record<?= count($results) !== 1 ? 's' : '' ?></span>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <?php if ($isAdmin): ?><th>Portfolio</th><?php endif; ?>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <?php if ($isAdmin): ?><th>Balance</th><?php endif; ?>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $data): ?>
                                <tr>
                                    <?php if ($isAdmin): ?>
                                        <td><span class="portfolio-pill"><?= htmlspecialchars($data['portfolio_name']) ?></span></td>
                                    <?php endif; ?>
                                    <td><?= htmlspecialchars($data['Title']) ?></td>
                                    <td><span class="badge <?= $data['expense_income'] ?>"><?= ucfirst($data['expense_income']) ?></span></td>
                                    <td><span class="amount <?= $data['expense_income'] ?>">Rs <?= number_format($data['cost_profit']) ?></span></td>
                                    <?php if ($isAdmin): ?>
                                        <td style="color:var(--muted);font-size:0.82rem;">Rs <?= number_format($data['money_left']) ?></td>
                                    <?php endif; ?>
                                    <td>
                                        <div class="action-cell">
                                            <a href="data_edit.php?id=<?= $data['id'] ?>" class="btn-edit">Edit</a>
                                            <a href="delete.php?id=<?= $data['id'] ?>" class="btn-delete"
                                                onclick="return confirm('Delete this entry? All balances after it will be recalculated.')">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <p>No records found for <strong><?= htmlspecialchars($month) ?> <?= htmlspecialchars($year) ?></strong></p>
                </div>
            <?php endif; ?>

        </div><!-- /inner -->
    </div><!-- /page-wrap -->
</body>

</html>