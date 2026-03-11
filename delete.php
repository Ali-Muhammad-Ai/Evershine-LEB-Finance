<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('location: index.php');
    exit();
}

require_once 'connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('location: main.php');
    exit();
}

$id = (int)$_GET['id'];

try {
    // 1. Get the row being deleted so we know its cost and type
    $stmt = $db->prepare("SELECT * FROM income_expense_items WHERE id = ?");
    $stmt->execute([$id]);
    $deleted_row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$deleted_row) {
        header('location: main.php?state=err');
        exit();
    }

    // 2. Delete the row
    $db->prepare("DELETE FROM income_expense_items WHERE id = ?")->execute([$id]);

    // 3. Fetch ALL remaining rows in order (by id ascending)
    $all_rows = $db->query("SELECT * FROM income_expense_items ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

    // 4. Recalculate money_left for every row from scratch
    $running_balance = 0;
    $update_stmt = $db->prepare("UPDATE income_expense_items SET money_left = ? WHERE id = ?");

    foreach ($all_rows as $row) {
        if ($row['expense_income'] === 'income') {
            $running_balance += $row['cost_profit'];
        } else {
            $running_balance -= $row['cost_profit'];
        }
        $update_stmt->execute([$running_balance, $row['id']]);
    }

    header('location: main.php?state=succ');
    exit();
} catch (Exception $e) {
    error_log($e->getMessage());
    header('location: main.php?state=err');
    exit();
}
