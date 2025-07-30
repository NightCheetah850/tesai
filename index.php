<?php
// Koneksi ke Supabase
$supabase_url = 'https://your-project-id.supabase.co';
$supabase_key = 'your-anon-key';
$supabase_table = 'users';

// Proses form saat disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (!empty($email) && !empty($password)) {
        // Simpan data ke Supabase menggunakan REST API
        $data = [
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $ch = curl_init("$supabase_url/rest/v1/$supabase_table");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $supabase_key,
            'Authorization: Bearer ' . $supabase_key,
            'Content-Type: application/json',
            'Prefer: return=minimal'
        ]);
        
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpcode >= 200 && $httpcode < 300) {
            $success_message = "Registrasi berhasil! Silakan login.";
        } else {
            $error_message = "Terjadi kesalahan saat menyimpan data: " . $response;
        }
        
        // Simpan email di cookie jika remember me dicentang
        if ($remember) {
            setcookie('rhama_email', $email, time() + (86400 * 30), "/"); // 30 hari
        } else {
            setcookie('rhama_email', '', time() - 3600, "/");
        }
    } else {
        $error_message = "Email dan password harus diisi!";
    }
}

// Ambil email dari cookie jika ada
$saved_email = $_COOKIE['rhama_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RHAMA AI - Sign In</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #0f1b3a, #2a0b3a, #001c20);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            padding: 20px;
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 1200px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 1s ease;
        }
        
        .header h1 {
            font-size: 4.5rem;
            font-weight: 800;
            color: white;
            letter-spacing: 3px;
            margin-bottom: 10px;
            text-shadow: 0 2px 15px rgba(0,0,0,0.4);
            background: linear-gradient(to right, #ff7e5f, #feb47b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .header h2 {
            font-size: 2rem;
            font-weight: 300;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 20px;
            letter-spacing: 1px;
        }
        
        .logo-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .logo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00c9ff, #92fe9d);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 30px rgba(0, 201, 255, 0.5);
            animation: pulse 2s infinite;
        }
        
        .logo i {
            font-size: 4rem;
            color: #0f1b3a;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(0, 201, 255, 0.7); }
            70% { box-shadow: 0 0 0 20px rgba(0, 201, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 201, 255, 0); }
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            animation: fadeInUp 1s ease;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(30deg);
            z-index: -1;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h3 {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
        }
        
        .input-group {
            margin-bottom: 25px;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }
        
        .input-container {
            position: relative;
        }
        
        .input-container input {
            width: 100%;
            padding: 14px 20px;
            padding-right: 50px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.08);
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .input-container input:focus {
            outline: none;
            border-color: rgba(0, 201, 255, 0.6);
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 0 3px rgba(0, 201, 255, 0.2);
        }
        
        .input-container i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .input-container i:hover {
            color: #00c9ff;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .remember-me input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #00c9ff;
        }
        
        .remember-me label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
            cursor: pointer;
        }
        
        .forgot-password a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color 0.3s ease;
            font-size: 0.95rem;
        }
        
        .forgot-password a:hover {
            color: #00c9ff;
            text-decoration: underline;
        }
        
        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(to right, #00c9ff, #92fe9d);
            border: none;
            border-radius: 12px;
            color: #0f1b3a;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0, 201, 255, 0.5);
        }
        
        .login-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, #92fe9d, #00c9ff);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }
        
        .login-btn:hover::after {
            opacity: 1;
        }
        
        .signup-link {
            text-align: center;
            margin-top: 20px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.95rem;
        }
        
        .signup-link a {
            color: #92fe9d;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .signup-link a:hover {
            text-decoration: underline;
            color: #00c9ff;
        }
        
        .social-icons {
            display: flex;
            justify-content: center;
            gap: 25px;
            margin-top: 30px;
        }
        
        .social-icons a {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 20px;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        
        .social-icons a:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.3);
        }
        
        .instagram:hover {
            background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888) !important;
        }
        
        .facebook:hover {
            background: #3b5998 !important;
        }
        
        .linkedin:hover {
            background: #0077b5 !important;
        }
        
        .download-section {
            text-align: center;
            margin-top: 30px;
            animation: fadeIn 1.5s ease;
        }
        
        .download-btn {
            padding: 14px 50px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            letter-spacing: 1px;
            background: rgba(255, 255, 255, 0.15);
            color: white;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .download-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            background: rgba(255, 255, 255, 0.25);
        }
        
        .download-btn::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to right,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.1) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            transform: rotate(30deg);
            transition: all 0.6s ease;
        }
        
        .download-btn:hover::after {
            transform: rotate(30deg) translate(10%, 10%);
        }
        
        .download-text {
            color: rgba(255, 255, 255, 0.8);
            margin-top: 15px;
            font-size: 1rem;
        }
        
        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            animation: fadeIn 0.5s ease;
        }
        
        .success {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid rgba(40, 167, 69, 0.3);
            color: #28a745;
        }
        
        .error {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #dc3545;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        @media (max-width: 768px) {
            .header h1 {
                font-size: 3.5rem;
            }
            
            .header h2 {
                font-size: 1.6rem;
            }
            
            .logo {
                width: 100px;
                height: 100px;
            }
            
            .logo i {
                font-size: 3rem;
            }
            
            .login-container {
                padding: 30px 20px;
            }
            
            .remember-forgot {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .download-btn {
                padding: 12px 40px;
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-container">
                <div class="logo">
                    <i class="fas fa-robot"></i>
                </div>
            </div>
            <h1>RHAMA AI</h1>
            <h2>YOUR PERSONAL ASSISTANT</h2>
        </div>
        
        <div class="login-container">
            <div class="login-header">
                <h3>Sign In</h3>
                <p>Access your RHAMA AI account</p>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="message success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="message error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form id="loginForm" method="POST">
                <div class="input-group">
                    <label for="email">Email</label>
                    <div class="input-container">
                        <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($saved_email); ?>" required>
                    </div>
                </div>
                
                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="input-container">
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <i class="fas fa-eye" id="togglePassword"></i>
                    </div>
                </div>
                
                <div class="remember-forgot">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember" <?php echo !empty($saved_email) ? 'checked' : ''; ?>>
                        <label for="remember">Remember me</label>
                    </div>
                    <div class="forgot-password">
                        <a href="#">Forgot Password?</a>
                    </div>
                </div>
                
                <button type="submit" class="login-btn">SIGN IN</button>
                
                <div class="signup-link">
                    Don't have an account? <a href="#">Sign Up</a>
                </div>
            </form>
            
            <div class="social-icons">
                <a href="https://www.instagram.com/rhamaardian._/" class="instagram" target="_blank">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="https://www.facebook.com/luiz.vanrhama/" class="facebook" target="_blank">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://www.linkedin.com/in/rhama-ardian-syahputra-652465270" class="linkedin" target="_blank">
                    <i class="fab fa-linkedin-in"></i>
                </a>
            </div>
        </div>
        
        <div class="download-section">
            <button class="download-btn">
                <i class="fas fa-download"></i> DOWNLOAD APP
            </button>
            <p class="download-text">Get the full experience with our mobile app</p>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
        
        // Form submission animation
        const loginForm = document.getElementById('loginForm');
        loginForm.addEventListener('submit', function() {
            const btn = this.querySelector('.login-btn');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> SIGNING IN...';
            btn.disabled = true;
        });
        
        // Download button animation
        const downloadBtn = document.querySelector('.download-btn');
        downloadBtn.addEventListener('mouseenter', () => {
            downloadBtn.style.transform = 'translateY(-3px)';
            downloadBtn.style.boxShadow = '0 6px 20px rgba(0,0,0,0.3)';
        });
        
        downloadBtn.addEventListener('mouseleave', () => {
            downloadBtn.style.transform = 'translateY(0)';
            downloadBtn.style.boxShadow = '0 4px 15px rgba(0,0,0,0.1)';
        });
        
        // Background animation enhancement
        document.body.addEventListener('mousemove', (e) => {
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            document.body.style.backgroundPosition = `${x * 100}% ${y * 100}%`;
        });
    </script>
</body>
</html>
