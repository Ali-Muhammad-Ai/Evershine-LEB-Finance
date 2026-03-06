<?php
require_once 'connect.php';
session_start();
if (!isset($_SESSION['username'])) {
    header('location: index.php');
    exit();
}

// Initialize $results as an empty array to prevent foreach errors before search
$results = [];

if (isset($_GET['date']) && !empty($_GET['date'])) {
    $date = $_GET['date'];
    // Explode YYYY-MM
    list($year, $month_num) = explode('-', $date);

    // Fix: Convert month number to Full Month Name (e.g., "03" -> "March")
    $month = date("F", mktime(0, 0, 0, (int)$month_num, 10));

    $portfolio_name = '';
    // Determine portfolio based on session password
    $session_un = $_SESSION['username'] ?? '';

    switch ($session_un) {
        case 'english language leap':
            $portfolio_name = 'English Language Leap';
            break;
        case 'Parwaaz':
            $portfolio_name = 'Parwaaz';
            break;
        case 'institute of professional development':
            $portfolio_name = 'ipd';
            break;
        case 'quality schooling program':
            $portfolio_name = 'quality schooling program';
            break;
        case 'career and scholarship program':
            $portfolio_name = 'career and scholarship';
            break;
        case 'stem and robotics':
            $portfolio_name = 'Stem and Robotics';
            break;
        default:
            $portfolio_name = 'other';
            break;
    }

    $select_all_query = 'SELECT id, Month, Title, year, portfolio_name, expense_income, cost_profit, money_left 
                         FROM income_expense_items 
                         WHERE portfolio_name = ? AND Month = ? AND year = ?';

    $stmt = $db->prepare($select_all_query);
    // Fix: Pass parameters as an array
    $stmt->execute([$portfolio_name, $month, $year]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Manager</title>
    <style>
        /* General Body and Font Styles */
        body {
            font-family: "Inter", sans-serif;
            margin: 0;
            background-color: #f3f4f6;
        }

        .main-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem;
            min-height: 100vh;
            background-color: #9738ca;
        }

        .form-wrapper {
            width: 100%;
            max-width: 42rem;
            border-radius: 0.5rem;
            background-color: white;
            padding: 2.5rem;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .form-image {
            margin-bottom: 2rem;
            width: 100%;
            border-radius: 0.5rem;
        }

        .form-header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .form-title {
            font-size: 1.875rem;
            font-weight: 600;
            color: #1f2937;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #4b5563;
        }

        .form-input {
            width: 100%;
            border-radius: 0.375rem;
            border: 1px solid #d1d5db;
            padding: 0.75rem;
            box-sizing: border-box;
        }

        .submit-btn {
            width: 100%;
            border-radius: 0.375rem;
            background-color: #4f46e5;
            padding: 0.75rem;
            font-weight: 500;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 1rem;
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            max-width: 42rem;
            background: white;
            margin-top: 2rem;
            border-collapse: collapse;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .data-table th,
        .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .data-table th {
            background-color: #4f46e5;
            color: white;
        }

        .filter-section {
            margin-top: 2rem;
            background: white;
            padding: 1rem;
            border-radius: 8px;
        }

        .form-link {
            position: absolute;
            top: 10px;
            right: 10px;
            text-decoration: none;
            color: white;
            padding: 10px 20px;
            background: #de0e0e;
            border-radius: 5px;
            font-size: 0.75rem;
        }

        /* Fix date & month input clickable area */
        input[type="date"],
        input[type="month"] {
            position: relative;
            cursor: pointer;
        }

        /* Expand calendar icon click area */
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="month"]::-webkit-calendar-picker-indicator {
            position: absolute;
            right: 10px;
            width: 100%;
            height: 100%;
            cursor: pointer;
            opacity: 0;
        }

        /* Success/Error Message Logic */
        .success-message {
            position: absolute;
            /* top: 0; */
            z-index: 999;
            background-color: black;
            display: flex;
            justify-content: center;
            width: 100%;
        }

        .success {
            width: 50%;
            z-index: 1;
            position: absolute;
            margin-inline: auto;
            background-color: #0b83c0;
            text-align: center;
            padding: 20px 30px;
            border-radius: 10px;
            color: white;
            font-size: 1.5rem;
            display: none;
            opacity: 0;
            <?php if ($_GET['state'] == 'succ') {
                echo 'display: inline;animation: opacityMover 3s forwards;';
            } ?>
        }

        .incorrect {
            display: none;
            position: absolute;
            z-index: 2;
            width: 50%;
            margin-inline: auto;
            background-color: #c00b0b;
            text-align: center;
            padding: 20px 30px;
            border-radius: 10px;
            color: white;
            font-size: 1.5rem;
            opacity: 0;
            <?php if ($_GET['state'] == 'err') {
                echo 'animation: opacityMover 3s forwards;';
            } ?>
        }

        @keyframes opacityMover {
            0% {
                opacity: 0;
                transform: scale(0);
            }

            10% {
                opacity: 1;
                transform: scale(1);
            }

            90% {
                opacity: 1;
                transform: scale(1);
            }

            100% {
                opacity: 0;
                transform: scale(0);
            }
        }

        .filter-card {
            background: #ffffff;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            width: 100%;
            max-width: 42rem;
            margin: 2rem auto;
            border: 1px solid #e5e7eb;
        }

        .filter-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 1.25rem;
            color: #374151;
        }

        .filter-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .filter-icon {
            color: #6366f1;
        }

        .filter-form {
            display: flex;
            align-items: flex-end;
            gap: 1.5rem;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            flex-grow: 1;
        }

        .input-group label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .filter-form input[type="month"] {
            padding: 0.6rem 1rem;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-family: inherit;
            font-size: 0.95rem;
            color: #1f2937;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .filter-form input[type="month"]:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .btn-primary {
            background-color: #6366f1;
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
            height: 42px;
            /* Matches input height */
        }

        .btn-primary:hover {
            background-color: #4f46e5;
        }

        /* Responsive adjustment */
        @media (max-width: 640px) {
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
        }

        .edit-btn {
            padding: 10px 20px;
            background-color: #0b83c0;
            border-radius: 5px;
            text-decoration: none;
            color: white;
        }

        .logout a{
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            background-color: red;
            color: black;
        }
    </style>
</head>

<body>
    <div class="main-container">

        <div class="success-message">
            <p class="success">Successfully Added!</p>
            <p class="incorrect">Failed To Add Finance</p>
        </div>

        <div class="form-wrapper">
            <div class="logout">
                <a href="logout.php">logout</a>
            </div>
            <?php
            $user = $_SESSION['username'] ?? '';
            if ($user == 'moinuddin' || $user == 'ali muhammad'): ?>
                <a href='excel_present.php' class='form-link'>Admin Control Panel</a>
            <?php endif; ?>

            <img src="https://ucarecdn.com/72c83037-be6b-4303-81b3-64edb7b7aa24/donationform.png" alt="Header" class="form-image" />

            <form action="request.php" method="POST">
                <div class="form-header">
                    <h2 class="form-title">Finance Manager</h2>
                    <p>Add expenses and income entries below.</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Portfolio Name*</label>
                    <select name="portfolio_name" class="form-input" required>
                        <option value="" disabled selected>Choose Your Portfolio</option>
                        <option value="Parwaaz">Parwaaz</option>
                        <option value="Ipd">IPD</option>
                        <option value="Career and Scholarship">Career & Scholarship</option>
                        <option value="Stem and Robotics">Stem and Robotics</option>
                        <option value="English Language Leap">ELP</option>
                        <option value="Quality Schooling Program">QSP</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Date*</label>
                    <input type="month" name="month" value="<?php echo date('Y-m'); ?>" class="form-input" required />
                </div>

                <div class="form-group">
                    <label class="form-label">Title*</label>
                    <input type="text" name="title" class="form-input" required />
                </div>

                <div class="form-group">
                    <label class="form-label">Type*</label>
                    <select name="sign" class="form-input" required>
                        <option disabled selected>Choose Your Type</option>
                        <option value="expense">Expense</option>
                        <option value="income">Income</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Amount*</label>
                    <input type="number" name="cost" class="form-input" required />
                </div>

                <button name="submitBtn" type="submit" class="submit-btn">Submit Entry</button>
            </form>
        </div>

        <div class="filter-card">
            <div class="filter-header">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="filter-icon">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
                <h3>Filter Records</h3>
            </div>
            <form method="GET" class="filter-form">
                <div class="input-group">
                    <label for="date-filter">Select Period</label>
                    <input type="month" id="date-filter" name="date" required value="<?php echo $_GET['date'] ?? date('Y-m'); ?>">
                </div>
                <button type="submit" class="btn-primary">
                    Update View
                </button>
            </form>
        </div>

        <?php if (!empty($results)): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $data): ?>
                        <tr>
                            <td><?= htmlspecialchars($data['Title']) ?></td>
                            <td><?= htmlspecialchars($data['expense_income']) ?></td>
                            <td><?= htmlspecialchars($data['cost_profit']) ?></td>
                            <td>
                                <a href="data_edit.php?id=<?php echo $data['id']; ?>" class="edit-btn">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif (isset($_GET['date'])): ?>
            <p style="color: white; margin-top: 20px;">No records found for this period.</p>
        <?php endif; ?>

    </div>
</body>

</html>