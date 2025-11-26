<?php
require_once '../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $conn = getDBConnection();
        
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);
            
            if ($stmt->execute()) {
                $success = 'Registration successful! Redirecting to login...';
                header("refresh:2;url=login.php");
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration | Event Decoration Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        /* Background Gradient */
        body {
            background: linear-gradient(135deg, #ff512f, #dd2476);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Card Style */
        .register-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            padding: 40px;
            color: #fff;
            width: 100%;
            max-width: 450px;
        }

        .register-card h2 {
            font-weight: 700;
            margin-bottom: 20px;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.15);
            border: none;
            border-radius: 10px;
            color: #fff;
        }

        .form-control:focus {
            box-shadow: 0 0 0 2px #ff9f68;
            background-color: rgba(255, 255, 255, 0.25);
        }

        label {
            font-weight: 500;
        }

        /* Button Gradient */
        .btn-gradient {
            background: linear-gradient(90deg, #ff512f, #dd2476);
            border: none;
            color: white;
            font-weight: 600;
            border-radius: 10px;
            padding: 10px;
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            background: linear-gradient(90deg, #dd2476, #ff512f);
            transform: scale(1.03);
        }

        .alert {
            border-radius: 10px;
        }

        a {
            color: #ffb4a2;
            text-decoration: none;
        }

        a:hover {
            color: #fff;
            text-decoration: underline;
        }

        .footer-text {
            font-size: 0.9rem;
            margin-top: 15px;
            color: #eee;
        }

        @media (max-width: 768px) {
            .register-card {
                margin: 20px;
                padding: 30px;
            }
        }
    </style>
</head>
<body>

    <div class="register-card">
        <h2 class="text-center"><i class="fas fa-user-plus"></i> Register</h2>
        <p class="text-center text-light mb-4">Create your account to book stunning event decorations!</p>

        <?php if ($error): ?>
            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success text-center"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" name="name" placeholder="Enter your full name" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control" name="email" placeholder="yourname@email.com" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="tel" class="form-control" name="phone" placeholder="Optional">
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" placeholder="Minimum 6 characters" required minlength="6">
            </div>

            <div class="mb-4">
                <label class="form-label">Confirm Password</label>
                <input type="password" class="form-control" name="confirm_password" placeholder="Re-enter your password" required>
            </div>

            <button type="submit" class="btn btn-gradient w-100">Register</button>
        </form>

        <div class="text-center footer-text">
            <p class="mt-3">Already have an account? <a href="login.php">Login here</a></p>
            <p><a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Home</a></p>
        </div>
    </div>

</body>
</html>
