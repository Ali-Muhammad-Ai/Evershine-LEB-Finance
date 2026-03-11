<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evershine Finance — Login</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        /* animated background blobs */
        body::before, body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
            filter: blur(80px);
        }
        body::before {
            width: 600px; height: 600px;
            background: rgba(124,58,237,0.18);
            top: -150px; left: -150px;
            animation: blobMove1 12s ease-in-out infinite alternate;
        }
        body::after {
            width: 500px; height: 500px;
            background: rgba(147,51,234,0.13);
            bottom: -120px; right: -120px;
            animation: blobMove2 14s ease-in-out infinite alternate;
        }
        @keyframes blobMove1 {
            from { transform: translate(0,0) scale(1); }
            to   { transform: translate(60px, 40px) scale(1.1); }
        }
        @keyframes blobMove2 {
            from { transform: translate(0,0) scale(1); }
            to   { transform: translate(-50px,-30px) scale(1.08); }
        }

        /* ── TOAST ── */
        .toast {
            position: fixed; top: 1.5rem; left: 50%;
            transform: translateX(-50%) translateY(-20px);
            padding: 12px 30px; border-radius: 50px;
            font-size: 0.88rem; font-weight: 500;
            z-index: 9999; opacity: 0; pointer-events: none;
            white-space: nowrap;
            background: linear-gradient(135deg,#7f1d1d,#991b1b);
            border: 1px solid rgba(239,68,68,0.4);
            color: #fca5a5;
            <?php
            $status = $_GET['status'] ?? '';
            if ($status === 'incorrect') echo 'animation: toastAnim 4s forwards;';
            ?>
        }
        @keyframes toastAnim {
            0%   { opacity:0; transform: translateX(-50%) translateY(-20px); }
            12%  { opacity:1; transform: translateX(-50%) translateY(0); }
            80%  { opacity:1; transform: translateX(-50%) translateY(0); }
            100% { opacity:0; transform: translateX(-50%) translateY(-20px); }
        }

        /* ── WRAPPER ── */
        .login-wrap {
            position: relative; z-index: 1;
            width: 100%; max-width: 440px;
        }

        /* ── CARD ── */
        .login-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 2.75rem 2.5rem;
            position: relative; overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,0.4), 0 0 0 1px rgba(168,85,247,0.08);
        }
        .login-card::before {
            content:''; position:absolute; top:0; left:0; right:0; height:2px;
            background: linear-gradient(90deg, transparent, var(--purple-lite), transparent);
        }

        /* ── HEADER ── */
        .login-header { text-align: center; margin-bottom: 2.2rem; }
        .login-logo {
            display: inline-flex; align-items: center; justify-content: center;
            width: 56px; height: 56px; border-radius: 16px;
            background: linear-gradient(135deg, var(--purple), var(--purple-mid));
            box-shadow: 0 8px 24px rgba(124,58,237,0.45);
            margin-bottom: 1.2rem;
        }
        .login-logo svg { width: 26px; height: 26px; }
        .login-eyebrow {
            font-size: 0.68rem; font-weight: 600; letter-spacing: 0.22em;
            text-transform: uppercase; color: var(--purple-lite); margin-bottom: 0.4rem;
        }
        .login-header h1 {
            font-family: 'Syne', sans-serif; font-size: 1.9rem; font-weight: 800;
            background: linear-gradient(135deg, #f3e8ff 0%, #c084fc 55%, #a855f7 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; margin-bottom: 0.4rem;
        }
        .login-header p { font-size: 0.85rem; color: var(--muted); }

        /* ── FORM ── */
        .field { margin-bottom: 1.2rem; }
        .field label {
            display: block; font-size: 0.7rem; font-weight: 600;
            letter-spacing: 0.08em; text-transform: uppercase;
            color: var(--muted); margin-bottom: 7px;
        }
        .input-wrap {
            position: relative;
        }
        .input-wrap svg {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            width: 16px; height: 16px; color: var(--muted); pointer-events: none;
            transition: color 0.2s;
        }
        .input-wrap input {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(168,85,247,0.2);
            border-radius: 12px;
            padding: 12px 14px 12px 42px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.92rem;
            outline: none;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        }
        .input-wrap input::placeholder { color: rgba(167,139,202,0.5); }
        .input-wrap input:focus {
            border-color: var(--purple-lite);
            background: rgba(168,85,247,0.06);
            box-shadow: 0 0 0 3px rgba(168,85,247,0.12);
        }
        .input-wrap input:focus + svg,
        .input-wrap:focus-within svg { color: var(--purple-lite); }

        /* put icon after input in DOM but position it via flex trick — simpler: keep icon before input */

        .btn-login {
            width: 100%; padding: 13px; margin-top: 0.4rem;
            background: linear-gradient(135deg, var(--purple), var(--purple-mid));
            border: none; border-radius: 12px; color: #fff;
            font-family: 'Syne', sans-serif; font-size: 0.95rem;
            font-weight: 700; letter-spacing: 0.05em; cursor: pointer;
            transition: all 0.25s;
            box-shadow: 0 4px 18px rgba(124,58,237,0.4);
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(124,58,237,0.55);
        }
        .btn-login:active { transform: translateY(0); }

        /* ── FOOTER LINKS ── */
        .login-footer {
            margin-top: 1.8rem; text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
        }
        .login-footer p { font-size: 0.82rem; color: var(--muted); }
        .login-footer a {
            color: var(--purple-lite); text-decoration: none; font-weight: 500;
            transition: color 0.2s;
        }
        .login-footer a:hover { color: var(--text); }

        /* ── BRAND STRIP ── */
        .brand-strip {
            text-align: center; margin-bottom: 1.8rem;
        }
        .brand-name {
            font-family: 'Syne', sans-serif; font-size: 0.95rem; font-weight: 800;
            letter-spacing: 0.08em; text-transform: uppercase;
            color: var(--purple-lite);
        }
        .brand-name span { color: var(--text); }

        @media (max-width: 480px) {
            .login-card { padding: 2rem 1.5rem; border-radius: 18px; }
            body { padding: 1rem; align-items: flex-start; padding-top: 2rem; }
        }
    </style>
</head>
<body>

    <!-- Error toast -->
    <div class="toast">✕ &nbsp; Incorrect username or password</div>

    <div class="login-wrap">

        <!-- Brand -->
        <div class="brand-strip">
            <div class="brand-name">Evershine <span>Finance</span></div>
        </div>

        <div class="login-card">

            <!-- Header -->
            <div class="login-header">
                <div class="login-logo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="14" rx="2"/>
                        <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                        <line x1="12" y1="12" x2="12" y2="16"/>
                        <line x1="10" y1="14" x2="14" y2="14"/>
                    </svg>
                </div>
                <div class="login-eyebrow">Evershine Area Committee</div>
                <h1>Member Login</h1>
                <p>Sign in to manage your portfolio finances</p>
            </div>

            <!-- Form -->
            <form action="loginChecking.php" method="POST">

                <div class="field">
                    <label>Username</label>
                    <div class="input-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <input type="text" name="username" placeholder="Enter your username" required autocomplete="username">
                    </div>
                </div>

                <div class="field">
                    <label>Password</label>
                    <div class="input-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input type="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                    </div>
                </div>

                <button type="submit" name="submitBtn" class="btn-login">Sign In →</button>

            </form>

            <!-- Footer -->
            <div class="login-footer">
                <p>Need access? <a href="message.php">Contact the admin</a></p>
            </div>

        </div>
    </div>

</body>
</html>