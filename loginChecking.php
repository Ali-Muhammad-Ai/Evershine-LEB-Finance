<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submitBtn'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        try {
            require_once "connect.php";

            // 1. Fixed column names and removed single quotes from column identifiers
            $sql = "SELECT * FROM `member_login` WHERE `member_username` = ? AND `member_password` = ?";

            $run = $db->prepare($sql);

            // 2. Fixed execute to pass an array
            $run->execute([$username, $password]);

            // 3. fetch() is better than fetchAll() for checking a single user
            $user = $run->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                session_start();
                $_SESSION['username'] = strtolower($username);
                $_SESSION['state'] = 'correct';
                echo $_SESSION['username'];
                header('Location: main.php');
                exit();
            } else {
                header('location: index.php?status=incorrect');
            }
        } catch (\Throwable $th) {
            // In production, don't show the full error to users
            echo "Database error: " . $th->getMessage();
        }
    }
}
