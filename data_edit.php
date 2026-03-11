<?php
require_once 'connect.php';
session_start();
if (!isset($_SESSION['username'])) {
    header('location: index.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $db->prepare("SELECT * FROM income_expense_items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) die("Record not found.");
}

if (isset($_POST['updateBtn'])) {
    $id    = $_POST['id'];
    $title = $_POST['title'];
    $cost  = $_POST['cost'];
    $type  = $_POST['sign'];

    $stmt = $db->prepare("UPDATE income_expense_items SET Title = ?, cost_profit = ?, expense_income = ? WHERE id = ?");
    if ($stmt->execute([$title, $cost, $type, $id])) {
        header("Location: main.php?state=succ");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Record — Evershine Finance</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --purple:      #7c3aed;
            --purple-mid:  #9333ea;
            --purple-lite: #a855f7;
            --dark:        #0f0a1e;
            --card:        #1a1035;
            --border:      rgba(168,85,247,0.18);
            --text:        #f3e8ff;
            --muted:       #a78bca;
            --red:         #ef4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--dark);
            color: var(--text);
            min-height: 100vh;
        }
        body::before {
            content: '';
            position: fixed; inset: 0;
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
        .btn-top {
            font-family: 'DM Sans', sans-serif; font-size: 0.8rem; font-weight: 500;
            padding: 7px 18px; border-radius: 8px;
            border: 1px solid rgba(239,68,68,0.3);
            background: rgba(239,68,68,0.08);
            color: #fca5a5; cursor: pointer; text-decoration: none; transition: all 0.2s;
        }
        .btn-top:hover { background: rgba(239,68,68,0.22); color: #fff; }

        /* PAGE */
        .page-wrap {
            position: relative; z-index: 1;
            width: 100%; padding: 3rem 2.5rem 5rem;
            display: flex; justify-content: center;
        }

        .edit-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            max-width: 560px;
            position: relative; overflow: hidden;
        }
        .edit-card::before {
            content:''; position:absolute; top:0; left:0; right:0; height:2px;
            background: linear-gradient(90deg, transparent, var(--purple-lite), transparent);
        }

        .edit-header {
            margin-bottom: 2rem;
        }
        .edit-eyebrow {
            font-size: 0.7rem; font-weight: 600; letter-spacing: 0.2em;
            text-transform: uppercase; color: var(--purple-lite); margin-bottom: 0.4rem;
        }
        .edit-header h2 {
            font-family: 'Syne', sans-serif; font-size: 1.7rem; font-weight: 800;
            background: linear-gradient(135deg, #f3e8ff 0%, #c084fc 55%, #a855f7 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .field { margin-bottom: 1.2rem; }
        .field label {
            display: block; font-size: 0.7rem; font-weight: 600;
            letter-spacing: 0.08em; text-transform: uppercase;
            color: var(--muted); margin-bottom: 6px;
        }
        .field input, .field select {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(168,85,247,0.2);
            border-radius: 10px; padding: 11px 14px;
            color: var(--text); font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem; outline: none;
            transition: border-color 0.2s, background 0.2s;
            -webkit-appearance: none;
        }
        .field input:focus, .field select:focus {
            border-color: var(--purple-lite);
            background: rgba(168,85,247,0.07);
        }
        .field select option { background: #1a1035; color: var(--text); }

        .btn-save {
            width: 100%; padding: 13px; margin-top: 0.5rem;
            background: linear-gradient(135deg, var(--purple), var(--purple-mid));
            border: none; border-radius: 12px; color: #fff;
            font-family: 'Syne', sans-serif; font-size: 0.92rem;
            font-weight: 700; letter-spacing: 0.05em; cursor: pointer;
            transition: all 0.25s; box-shadow: 0 4px 18px rgba(124,58,237,0.35);
        }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 8px 26px rgba(124,58,237,0.5); }

        .cancel-link {
            display: block; text-align: center; margin-top: 1rem;
            color: var(--muted); text-decoration: none; font-size: 0.85rem;
            transition: color 0.2s;
        }
        .cancel-link:hover { color: var(--text); }

        @media (max-width: 600px) {
            .page-wrap  { padding: 2rem 1rem 4rem; }
            .edit-card  { padding: 1.75rem 1.5rem; }
            .top-bar    { padding: 1rem 1.2rem; }
        }
    </style>
</head>
<body>

    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="brand">Evershine <span>Finance</span></div>
        <a href="logout.php" class="btn-top">Sign Out</a>
    </div>

    <div class="page-wrap">
        <div class="edit-card">
            <div class="edit-header">
                <div class="edit-eyebrow">Evershine Area Committee</div>
                <h2>Edit Record</h2>
            </div>

            <form method="POST">
                <input type="hidden" name="id" value="<?= $item['id'] ?>">

                <div class="field">
                    <label>Title</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($item['Title']) ?>" required>
                </div>

                <div class="field">
                    <label>Type</label>
                    <select name="sign">
                        <option value="expense" <?= $item['expense_income'] === 'expense' ? 'selected' : '' ?>>Expense</option>
                        <option value="income"  <?= $item['expense_income'] === 'income'  ? 'selected' : '' ?>>Income</option>
                    </select>
                </div>

                <div class="field">
                    <label>Amount (Rs)</label>
                    <input type="number" name="cost" value="<?= htmlspecialchars($item['cost_profit']) ?>" required>
                </div>

                <button type="submit" name="updateBtn" class="btn-save">Save Changes →</button>
                <a href="main.php" class="cancel-link">← Cancel, go back</a>
            </form>
        </div>
    </div>

</body>
</html>