<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('location: index.php');
    exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitBtn'])) {

    $portfolio = $_POST['portfolio_name'];
    $month_input = $_POST['month'];
    $title = $_POST['title'];
    $sign = $_POST['sign'];
    $cost = floatval($_POST['cost']);

    try {
        require_once "connect.php";

        // 1️⃣ Convert month input (2026-03 → March)
        list($year, $month_num) = explode('-', $month_input);
        $dateObj = DateTime::createFromFormat('!m', $month_num);
        $month_name = $dateObj->format("F");

        // 2️⃣ Get LAST balance from entire table (GLOBAL balance)
        $stmt = $db->query("
            SELECT money_left 
            FROM income_expense_items 
            ORDER BY id DESC 
            LIMIT 1
        ");

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // If table empty → start at 0
        $last_balance = ($row && $row['money_left'] !== null)
            ? floatval($row['money_left'])
            : 0;

        // 3️⃣ Calculate new balance
        if ($sign === "expense") {
            $new_balance = $last_balance - $cost;
        } else {
            $new_balance = $last_balance + $cost;
        }

        // 4️⃣ Insert new row with updated balance
        $sql = "INSERT INTO income_expense_items 
                (portfolio_name, Month, year, Title, expense_income, cost_profit, money_left) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $insert = $db->prepare($sql);
        $insert->execute([
            $portfolio,
            $month_name,
            $year,
            $title,
            $sign,
            $cost,
            $new_balance
        ]);

        // 5️⃣ Redirect back to main page
        header("Location: main.php?state=succ");
        exit();
    } catch (Exception $e) {
        error_log($e->getMessage());
        header("Location: main.php?state=err");
        exit();
    }
}
