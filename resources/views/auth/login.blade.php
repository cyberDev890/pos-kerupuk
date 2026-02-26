<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Page</title>

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('adminlte') }}/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('adminlte') }}/dist/css/adminlte.min.css">
    <style>
        #global-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        .modern-loader {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            position: relative;
            animation: rotate_loader 1.2s linear infinite;
        }
        .modern-loader::before, .modern-loader::after {
            content: "";
            box-sizing: border-box;
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 5px solid #007bff;
            animation: prixRetriever 2s linear infinite;
        }
        .modern-loader::after {
            border-color: #ff3d00;
            animation: prixRetriever 2s linear infinite, rotate_loader 0.5s linear infinite reverse;
            inset: 6px;
        }
        @keyframes rotate_loader {
            0% { transform: rotate(0deg) }
            100% { transform: rotate(360deg) }
        }
        @keyframes prixRetriever {
            0% { clip-path: polygon(50% 50%, 0 0, 0 0, 0 0, 0 0, 0 0) }
            25% { clip-path: polygon(50% 50%, 0 0, 100% 0, 100% 0, 100% 0, 100% 0) }
            50% { clip-path: polygon(50% 50%, 0 0, 100% 0, 100% 100%, 100% 100%, 100% 100%) }
            75% { clip-path: polygon(50% 50%, 0 0, 100% 0, 100% 100%, 0 100%, 0 100%) }
            100% { clip-path: polygon(50% 50%, 0 0, 100% 0, 100% 100%, 0 100%, 0 0) }
        }
    </style>
</head>

<body class="hold-transition login-page">
    <div id="global-loader">
        <div class="modern-loader"></div>
        <div class="loading-text mt-4 font-weight-bold" style="color: #222; text-shadow: 0 0 10px white;">Sedang Memproses...</div>
    </div>
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="/" class="h1">{{ env('APP_NAME') }}</a>
            </div>
            <div class="card-body">
                <p class="login-box-msg">Sign in to start your session</p>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="post">
                    @csrf
                    <div class="input-group mb-3">
                        <input type="text" name="login" class="form-control" placeholder="Email / Nama User" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>

                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">Login</button>
                        </div>
                    </div>
                </form>

                <div class="social-auth-links text-center mt-2 mb-3">
                    <a href="#" class="btn btn-block btn-danger">
                        <i class="fab fa-google-plus mr-2"></i> Login with Google
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('adminlte') }}/plugins/jquery/jquery.min.js"></script>
    <script src="{{ asset('adminlte') }}/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('adminlte') }}/dist/js/adminlte.min.js"></script>
    <script>
        $(function() {
            $('form').on('submit', function() {
                if (this.checkValidity()) {
                    $('#global-loader').css('display', 'flex');
                }
            });
        });
    </script>
</body>

</html>
