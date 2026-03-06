<?php
require_once 'connect.php';
session_start();
if (!isset($_SESSION['username'])) {
    header('location: index.php');
    exit();
}

// 1. FETCH THE EXISTING DATA
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $db->prepare("SELECT * FROM income_expense_items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        die("Record not found.");
    }
}

// 2. HANDLE THE UPDATE REQUEST
if (isset($_POST['updateBtn'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $cost = $_POST['cost'];
    $type = $_POST['sign'];

    $update_query = "UPDATE income_expense_items SET Title = ?, cost_profit = ?, expense_income = ? WHERE id = ?";
    $stmt = $db->prepare($update_query);

    if ($stmt->execute([$title, $cost, $type, $id])) {
        header("Location: main.php?state=succ"); // Redirect back to main page
        exit();
    } else {
        echo "Error updating record.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Finance Record</title>
    <style>
        body {
            font-family: sans-serif;
            background-color: #9738ca;
            display: flex;
            justify-content: center;
            padding: 50px;
        }

        .edit-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            width: 100%;
            max-width: 400px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .save-btn {
            width: 100%;
            padding: 10px;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .cancel-link {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #666;
            text-decoration: none;
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

    <div class="edit-container">
        <div class="form-wrapper">
            <div class="logout">
                <a href="logout.php">logout</a>
            </div>
            <h2>Edit Record</h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?= $item['id'] ?>">

                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($item['Title']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Type</label>
                    <select name="sign">
                        <option value="expense" <?= $item['expense_income'] == 'expense' ? 'selected' : '' ?>>Expense</option>
                        <option value="income" <?= $item['expense_income'] == 'income' ? 'selected' : '' ?>>Income</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Amount</label>
                    <input type="number" name="cost" value="<?= htmlspecialchars($item['cost_profit']) ?>" required>
                </div>

                <button type="submit" name="updateBtn" class="save-btn">Update Record</button>
                <a href="main.php" class="cancel-link">Cancel</a>
            </form>
        </div>

</body>

</html>