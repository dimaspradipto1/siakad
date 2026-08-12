<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Login - SIAKAD SD Negeri 007 Sekupang</title>
    <meta name="description" content="Sistem Informasi Akademik SD Negeri 007 Sekupang - Login">
    <meta name="keywords" content="SIAKAD, SD Negeri 007, Sekupang, Akademik">

    @php
        $schoolLogoUrl = ($schoolProfile && $schoolProfile->logo_sekolah) 
            ? asset($schoolProfile->logo_sekolah) 
            : asset('assets/img/logo.png');
        $schoolFaviconUrl = ($schoolProfile && $schoolProfile->logo_sekolah) 
            ? asset($schoolProfile->logo_sekolah) 
            : asset('assets/img/favicon.png');
        $schoolName = $schoolProfile->nama_sekolah ?? 'SD NEGERI 007 SEKUPANG';
    @endphp

    <!-- Favicons -->
    <link href="{{ $schoolFaviconUrl }}" rel="icon">
    <link href="{{ $schoolFaviconUrl }}" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0d2a6e 0%, #1a4fad 40%, #1e6fb5 70%, #0d9fd8 100%);
            position: relative;
            overflow: hidden;
        }

        /* Animated background blobs */
        body::before {
            content: '';
            position: fixed;
            width: 600px;
            height: 600px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
            top: -200px;
            right: -150px;
            animation: float 8s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: fixed;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
            bottom: -100px;
            left: -100px;
            animation: float 10s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(5deg); }
        }

        .login-wrapper {
            width: 100%;
            max-width: 460px;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        /* Logo / Brand */
        .brand-section {
            text-align: center;
            margin-bottom: 28px;
            animation: slideDown 0.6s ease-out;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .brand-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #f5a623, #e8821a);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            box-shadow: 0 8px 32px rgba(245, 166, 35, 0.4);
        }

        .brand-icon i {
            font-size: 40px;
            color: #fff;
        }

        .brand-title {
            font-size: 26px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 2px;
            margin-bottom: 4px;
        }

        .brand-subtitle {
            font-size: 12px;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.75);
            letter-spacing: 0.5px;
        }

        /* Card */
        .login-card {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 20px;
            padding: 40px 36px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.6s ease-out 0.1s both;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card h4 {
            font-size: 20px;
            font-weight: 700;
            color: #0d2a6e;
            margin-bottom: 6px;
            text-align: center;
        }

        .login-card p {
            font-size: 13px;
            color: #6b7280;
            text-align: center;
            margin-bottom: 28px;
        }

        /* Alert */
        .alert {
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
        }

        /* Form group */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 16px;
            transition: color 0.2s;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: #1f2937;
            background: #f9fafb;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }

        .form-control:focus {
            border-color: #1a4fad;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(26, 79, 173, 0.1);
        }

        .form-control:focus + .input-icon,
        .input-wrapper:focus-within .input-icon {
            color: #1a4fad;
        }

        .form-control.is-invalid {
            border-color: #ef4444;
        }

        .error-text {
            font-size: 12px;
            color: #ef4444;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Toggle password */
        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #9ca3af;
            font-size: 16px;
            background: none;
            border: none;
            padding: 0;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: #1a4fad;
        }

        /* Remember me */
        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }

        .remember-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #1a4fad;
            cursor: pointer;
        }

        .remember-row label {
            font-size: 13px;
            color: #6b7280;
            cursor: pointer;
        }

        /* Submit button */
        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #1a4fad, #0d9fd8);
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
            box-shadow: 0 4px 15px rgba(26, 79, 173, 0.4);
            letter-spacing: 0.3px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26, 79, 173, 0.5);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* Loading spinner inside button */
        .spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255,255,255,0.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Divider */
        .divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 24px 0 16px;
        }

        .footer-text {
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }

        .footer-text strong {
            color: #0d2a6e;
        }

        /* Roles badge strip */
        .roles-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: center;
            margin-top: 20px;
            animation: fadeIn 0.8s ease-out 0.4s both;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .role-badge {
            background: rgba(255, 255, 255, 0.15);
            color: rgba(255, 255, 255, 0.85);
            font-size: 11px;
            font-weight: 500;
            padding: 4px 10px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(4px);
        }
    </style>
</head>

<body>
    <div class="login-wrapper">

        <!-- Brand / Logo -->
        <div class="brand-section">
            <div class="brand-icon">
                <img src="{{ $schoolLogoUrl }}" alt="Logo" style="max-height: 60px; max-width: 60px; object-fit: contain;">
            </div>
            <div class="brand-title">SIAKAD</div>
            <div class="brand-subtitle">{{ strtoupper($schoolName) }}</div>
            <div class="brand-subtitle">Sistem Informasi Akademik</div>
        </div>

        <!-- Login Card -->
        <div class="login-card">
            <h4>Masuk ke Akun Anda</h4>
            <p>Masukkan email &amp; password untuk mengakses sistem</p>

            {{-- Flash messages --}}
            @if (session('success'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->has('email') || $errors->has('password'))
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    {{ $errors->first('email') ?: $errors->first('password') }}
                </div>
            @endif

            <form id="loginForm" method="POST" action="{{ route('loginProses') }}" novalidate>
                @csrf

                <!-- Username/Email -->
                <div class="form-group">
                    <label for="login">Email atau Username</label>
                    <div class="input-wrapper">
                        <input
                            type="text"
                            id="login"
                            name="login"
                            class="form-control @error('login') is-invalid @enderror"
                            value="{{ old('login') }}"
                            placeholder="Email atau Username"
                            autocomplete="username"
                            required
                        >
                        <i class="bi bi-person input-icon"></i>
                    </div>
                    @error('login')
                        <div class="error-text"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >
                        <i class="bi bi-lock input-icon"></i>
                        <button type="button" class="toggle-password" id="togglePwd" title="Tampilkan/sembunyikan password">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="error-text"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember me -->
                <div class="remember-row">
                    <input type="checkbox" id="remember" name="remember" value="1">
                    <label for="remember">Ingat saya</label>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn-login" id="btnLogin">
                    <span class="spinner" id="loginSpinner"></span>
                    <span id="btnText">Masuk</span>
                </button>

            </form>

            <hr class="divider">
            <div class="footer-text">
                &copy; {{ date('Y') }} <strong>SD Negeri 007 Sekupang</strong> — Hak cipta dilindungi
            </div>
        </div>

        <!-- Role info strip -->
        <div class="roles-strip">
            <span class="role-badge"><i class="bi bi-shield-check"></i> Admin</span>
            <span class="role-badge"><i class="bi bi-person-badge"></i> Guru</span>
            <span class="role-badge"><i class="bi bi-people"></i> Wali Kelas</span>
            <span class="role-badge"><i class="bi bi-building"></i> Kepala Sekolah</span>
            <span class="role-badge"><i class="bi bi-person"></i> Siswa</span>
            <span class="role-badge"><i class="bi bi-heart"></i> Orang Tua</span>
        </div>

    </div>

    <script>
        // Toggle password visibility
        const togglePwd = document.getElementById('togglePwd');
        const pwdInput  = document.getElementById('password');
        const eyeIcon   = document.getElementById('eyeIcon');

        togglePwd.addEventListener('click', () => {
            const isHidden = pwdInput.type === 'password';
            pwdInput.type  = isHidden ? 'text' : 'password';
            eyeIcon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
        });

        // Loading state on submit
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            const btn     = document.getElementById('btnLogin');
            const spinner = document.getElementById('loginSpinner');
            const text    = document.getElementById('btnText');

            btn.disabled       = true;
            spinner.style.display = 'inline-block';
            text.textContent   = 'Memproses...';
        });
    </script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    @include('sweetalert::alert')
</body>

</html>