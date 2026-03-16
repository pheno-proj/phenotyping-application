<?php
session_start();
require_once 'db_connect.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login_register.php');
    exit;
}

$action = $_POST['action'] ?? '';

// LOGIN
if ($action === 'login') {
    $user_type = $_POST['user_type'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($user_type) || empty($email) || empty($password)) {
        $_SESSION['error'] = 'All fields are required';
        header('Location: login_register.php');
        exit;
    }

    try {
        // Determine which table to query
        $table = '';
        $id_field = '';
        
        switch ($user_type) {
            case 'admin':
                $table = 'admin';
                $id_field = 'admin_id';
                $query = "SELECT admin_id, username AS name, password FROM admin WHERE username = :email";
                break;
            case 'teacher':
                $table = 'teacher';
                $id_field = 'teacher_id';
                $query = "SELECT teacher_id, full_name AS name, password FROM teacher WHERE email = :email";
                break;
            case 'parent':
                $table = 'parent';
                $id_field = 'parent_id';
                $query = "SELECT parent_id, full_name AS name, password FROM parent WHERE email = :email";
                break;
            case 'counselor':
                $table = 'counselor';
                $id_field = 'counselor_id';
                $query = "SELECT counselor_id, full_name AS name, password FROM counselor WHERE email = :email";
                break;
            default:
                $_SESSION['error'] = 'Invalid user type';
                header('Location: login_register.php');
                exit;
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user[$id_field];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_type'] = $user_type;
            $_SESSION['role'] = $user_type; // New standardized role key
            
            // Redirect to appropriate dashboard
            header('Location: dashboard_' . $user_type . '.php');
            exit;
        } else {
            $_SESSION['error'] = 'Invalid email or password';
            header('Location: login_register.php');
            exit;
        }

    } catch (PDOException $e) {
        $_SESSION['error'] = 'Login failed. Please try again.';
        header('Location: login_register.php');
        exit;
    }
}

// REGISTER
elseif ($action === 'register') {
    $user_type = $_POST['user_type'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');

    // Validation
    if (empty($user_type) || empty($full_name) || empty($email) || empty($password) || empty($phone)) {
        $_SESSION['error'] = 'All fields are required';
        header('Location: login_register.php');
        exit;
    }

    if (strlen($full_name) < 3) {
        $_SESSION['error'] = 'Name must be at least 3 characters';
        header('Location: login_register.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email format';
        header('Location: login_register.php');
        exit;
    }

    if (!preg_match('/^(05)[0-9]{8}$/', $phone)) {
        $_SESSION['error'] = 'Invalid Saudi phone number! (Must start with 05 and be 10 digits)';
        header('Location: login_register.php');
        exit;
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = 'Password must be at least 8 characters';
        header('Location: login_register.php');
        exit;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Check if email already exists
        $check_query = '';
        switch ($user_type) {
            case 'teacher':
                $check_query = "SELECT teacher_id FROM teacher WHERE email = :email";
                break;
            case 'parent':
                $check_query = "SELECT parent_id FROM parent WHERE email = :email";
                break;
            case 'counselor':
                $check_query = "SELECT counselor_id FROM counselor WHERE email = :email";
                break;
            default:
                $_SESSION['error'] = 'Invalid user type';
                header('Location: login_register.php');
                exit;
        }

        $stmt = $pdo->prepare($check_query);
        $stmt->execute(['email' => $email]);
        
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Email already registered';
            header('Location: login_register.php');
            exit;
        }

        // Insert new user
        switch ($user_type) {
            case 'teacher':
                $specialization = $_POST['specialization'] ?? '';
                $insert_query = "INSERT INTO teacher (full_name, email, password, phone, specialization) 
                                VALUES (:full_name, :email, :password, :phone, :specialization)";
                $stmt = $pdo->prepare($insert_query);
                $stmt->execute([
                    'full_name' => $full_name,
                    'email' => $email,
                    'password' => $hashed_password,
                    'phone' => $phone,
                    'specialization' => $specialization
                ]);
                break;

            case 'parent':
                $address = $_POST['address'] ?? '';
                $insert_query = "INSERT INTO parent (full_name, email, password, phone, address) 
                                VALUES (:full_name, :email, :password, :phone, :address)";
                $stmt = $pdo->prepare($insert_query);
                $stmt->execute([
                    'full_name' => $full_name,
                    'email' => $email,
                    'password' => $hashed_password,
                    'phone' => $phone,
                    'address' => $address
                ]);
                break;

            case 'counselor':
                $insert_query = "INSERT INTO counselor (full_name, email, password, phone) 
                                VALUES (:full_name, :email, :password, :phone)";
                $stmt = $pdo->prepare($insert_query);
                $stmt->execute([
                    'full_name' => $full_name,
                    'email' => $email,
                    'password' => $hashed_password,
                    'phone' => $phone
                ]);
                break;
        }

        $_SESSION['success'] = 'Registration successful! Please login.';
        header('Location: login_register.php');
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = 'Registration failed. Please try again.';
        header('Location: login_register.php');
        exit;
    }
}

else {
    header('Location: login_register.php');
    exit;
}
?>
