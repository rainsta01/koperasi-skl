<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: ../admin");
    } else if ($_SESSION['role'] == 'user') {
        header("Location: ../user");
    }
    exit();
}

include '../config.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, nama, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row && password_verify($password, $row['password'])) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $row['id'];
        $_SESSION['nama'] = $row['nama'];
        $_SESSION['role'] = $row['role'];

        if ($row['role'] == 'admin') {
            header("Location: ../admin");
        } else {
            header("Location: ../user");
        }
        exit();
    } else {
        $error = "Login gagal! Cek username atau password.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Login Koperasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bubblegum+Sans&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .floating-shape {
            position: absolute;
            pointer-events: none;
            transition: all 0.3s ease;
        }
        .shape-hover:hover .floating-shape {
            transform: scale(1.2) rotate(10deg);
        }
        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(15px, -15px) rotate(180deg); }
            100% { transform: translate(0, 0) rotate(360deg); }
        }
        .animate-float {
            animation: float 8s infinite ease-in-out;
        }
        .flying-icon {
            position: absolute;
            color: #6366f1;
            opacity: 0;
            z-index: 1;
            filter: drop-shadow(0 0 2px rgba(99, 102, 241, 0.3));
        }
        .icon-trail {
            position: absolute;
            background: linear-gradient(90deg, #e0e7ff 0%, transparent 100%);
            height: 2px;
            width: 50px;
            opacity: 0.5;
            transform-origin: left;
        }
        @keyframes flyAcross1 {
            0% { transform: translate(-100vw, 20vh) rotate(10deg); opacity: 0; }
            20% { opacity: 1; }
            80% { opacity: 1; }
            100% { transform: translate(100vw, 0vh) rotate(10deg); opacity: 0; }
        }
        @keyframes flyAcross2 {
            0% { transform: translate(-100vw, 50vh) rotate(-5deg); opacity: 0; }
            20% { opacity: 1; }
            80% { opacity: 1; }
            100% { transform: translate(100vw, 30vh) rotate(-5deg); opacity: 0; }
        }
        @keyframes flyAcross3 {
            0% { transform: translate(100vw, 70vh) rotate(-15deg); opacity: 0; }
            20% { opacity: 1; }
            80% { opacity: 1; }
            100% { transform: translate(-100vw, 40vh) rotate(-15deg); opacity: 0; }
        }
        .fly1 { animation: flyAcross1 15s infinite ease-in-out; }
        .fly2 { animation: flyAcross2 20s infinite ease-in-out 5s; }
        .fly3 { animation: flyAcross3 18s infinite ease-in-out 10s; }

        /* Animasi untuk karakter O */
        .character-o {
            position: relative;
            display: inline-block;
            color: #93c5fd;
            text-shadow: 0 0 3px rgba(147, 197, 253, 0.5);
            background: linear-gradient(135deg, #dbeafe, #93c5fd);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: bold;
        }
        .eyes {
            position: absolute;
            width: 4px;
            height: 4px;
            background: #3b82f6;
            border-radius: 50%;
            top: 8px;
            box-shadow: 0 0 2px rgba(59, 130, 246, 0.5);
        }
        .left-eye { left: 8px; }
        .right-eye { right: 8px; }
        @keyframes blink {
            0%, 100% { transform: scaleY(1); }
            50% { transform: scaleY(0.1); }
        }
        .eyes {
            animation: blink 3s infinite;
        }
        .mouth {
            position: absolute;
            width: 10px;
            height: 6px;
            border-bottom: 2px solid #3b82f6;
            border-radius: 50%;
            bottom: 6px;
            left: 50%;
            transform: translateX(-50%);
        }

        /* Animasi untuk karakter I Pixar */
        .pixar-i {
            position: relative;
            display: inline-block;
            transform-origin: bottom;
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0) rotate(0); }
            50% { transform: translateY(-10px) rotate(5deg); }
        }
        .pixar-i {
            animation: bounce 2s infinite;
        }
        .lamp-head {
            position: absolute;
            width: 0;
            height: 0;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            border-left: 15px solid transparent;
            border-right: 15px solid transparent;
            border-top: 20px solid rgba(255, 215, 0, 0.3);
            filter: drop-shadow(0 0 5px #ffd700);
        }
        .lamp-head::after {
            content: '';
            position: absolute;
            top: -20px;
            left: -5px;
            width: 10px;
            height: 10px;
            background: #ffd700;
            border-radius: 50%;
            box-shadow: 0 0 15px 5px #ffd700;
        }
    </style>
</head>

<body class="bg-gray-100 overflow-hidden">
    <div class="flying-icon text-3xl fly1"><i class="fas fa-pencil text-indigo-500"></i><div class="icon-trail"></div></div>
    <div class="flying-icon text-4xl fly2"><i class="fas fa-ruler text-pink-500"></i><div class="icon-trail"></div></div>
    <div class="flying-icon text-2xl fly3"><i class="fas fa-book text-yellow-500"></i><div class="icon-trail"></div></div>
    
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative shape-hover" x-data="{ shapes: ['square', 'circle', 'triangle'] }">
        <div class="floating-shape w-16 h-16 bg-indigo-200 rounded-lg animate-float" style="top: 20%; left: 20%; animation-delay: 0s;"></div>
        <div class="floating-shape w-12 h-12 bg-pink-200 rounded-full animate-float" style="top: 60%; right: 25%; animation-delay: 1s;"></div>
        <div class="floating-shape animate-float" style="top: 30%; right: 20%; animation-delay: 2s;">
            <div class="w-16 h-16 bg-yellow-200" style="clip-path: polygon(50% 0%, 0% 100%, 100% 100%);"></div>
        </div>
        <div class="floating-shape w-10 h-10 bg-green-200 rounded-lg animate-float" style="bottom: 20%; left: 30%; animation-delay: 3s;"></div>
        
        <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-lg relative z-10 backdrop-blur-sm bg-opacity-90">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">

                    L<span class="character-o">o<div class="eyes left-eye"></div><div class="eyes right-eye"></div><div class="mouth"></div></span>gin K<span class="character-o">o<div class="eyes left-eye"></div><div class="eyes right-eye"></div><div class="mouth"></div></span>peras<span class="pixar-i">i<div class="lamp-head"></div></span>
                </h2>
                <?php if (isset($error)) echo "<p class='mt-2 text-center text-sm text-red-600'>$error</p>"; ?>
            </div>
            <form class="mt-8 space-y-6" method="POST">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="username" class="sr-only">Username</label>
                        <input id="username" name="username" type="text" required 
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                            placeholder="Username">
                    </div>
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input id="password" name="password" type="password" required 
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                            placeholder="Password">
                    </div>
                </div>

                <div>
                    <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </span>

                        Login
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
