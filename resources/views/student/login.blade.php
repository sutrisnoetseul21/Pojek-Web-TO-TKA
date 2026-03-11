@extends('student.layouts.app')

@section('title', 'Login Peserta')
@section('subtitle', 'Login Tryout')

@section('content')
    <div class="login-container">
        <div class="login-card">
            <h2 class="login-title">Selamat Datang</h2>
            <p class="login-subtitle">Silakan login dengan menggunakan username dan password yang anda miliki</p>

            @if ($errors->any())
                <div class="error-box">
                    <span class="text-red-700">{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('tryout.login') }}" method="POST">
                @csrf

                <div class="input-group">
                    <div class="input-icon">
                        <svg style="width:20px;height:20px;color:#6b7280;" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <input type="text" id="username" name="username" class="input-with-icon" placeholder="Username"
                        value="{{ old('username') }}" autofocus required>
                </div>

                <div class="input-group">
                    <div class="input-icon">
                        <svg style="width:20px;height:20px;color:#6b7280;" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input type="password" id="password" name="password" class="input-with-icon" placeholder="Password"
                        required>
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <svg id="eyeIcon" style="width:20px;height:20px;color:#10b981;" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>

                <button type="submit" class="btn-login">
                    Login
                </button>
            </form>
        </div>
    </div>

    @push('styles')
        <style>
            .login-container {
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: calc(100vh - 200px);
                padding: 2rem 1rem;
            }

            .login-card {
                background: white;
                border-radius: 0.5rem;
                padding: 2.5rem;
                width: 100%;
                max-width: 400px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            }

            .login-title {
                font-size: 1.5rem;
                font-weight: 700;
                color: #1f2937;
                margin-bottom: 0.5rem;
            }

            .login-subtitle {
                font-size: 0.875rem;
                color: #6b7280;
                margin-bottom: 2rem;
                line-height: 1.5;
            }

            .input-group {
                position: relative;
                margin-bottom: 1rem;
            }

            .input-icon {
                position: absolute;
                left: 1rem;
                top: 50%;
                transform: translateY(-50%);
                pointer-events: none;
            }

            .input-with-icon {
                width: 100%;
                padding: 0.875rem 1rem 0.875rem 3rem;
                border: none;
                border-bottom: 2px solid #e5e7eb;
                font-size: 1rem;
                transition: border-color 0.3s;
                background: transparent;
            }

            .input-with-icon:focus {
                outline: none;
                border-bottom-color: #3b82f6;
            }

            .input-with-icon::placeholder {
                color: #9ca3af;
            }

            .toggle-password {
                position: absolute;
                right: 1rem;
                top: 50%;
                transform: translateY(-50%);
                background: none;
                border: none;
                cursor: pointer;
                padding: 0;
            }

            .btn-login {
                width: 100%;
                padding: 1rem;
                background: linear-gradient(90deg, #3b82f6 0%, #60a5fa 100%);
                color: white;
                border: none;
                border-radius: 2rem;
                font-size: 1rem;
                font-weight: 600;
                cursor: pointer;
                margin-top: 1.5rem;
                transition: all 0.3s;
            }

            .btn-login:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
            }

            .error-box {
                background: #fef2f2;
                border-left: 4px solid #ef4444;
                padding: 0.75rem 1rem;
                margin-bottom: 1.5rem;
                border-radius: 0.25rem;
                font-size: 0.875rem;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function togglePassword() {
                const passwordInput = document.getElementById('password');
                const eyeIcon = document.getElementById('eyeIcon');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                } else {
                    passwordInput.type = 'password';
                }
            }
        </script>
    @endpush
@endsection