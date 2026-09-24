<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';

if (is_logged_in()) {
    redirect('account/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        die("CSRF verification failed.");
    }

    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($phone && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'active') {
                session_regenerate_id(true); // secure session fixation
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                redirect('account/index.php');
            } else {
                $error = "Account is inactive or banned.";
            }
        } else {
            $error = "Invalid phone number or password.";
        }
    } else {
        $error = "Please enter phone and password.";
    }
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="bg-gray-50 min-h-screen py-16 flex items-center justify-center">
    <div class="bg-white p-8 sm:p-10 rounded-2xl shadow-sm border border-gray-100 max-w-md w-full mx-4">
        <h1 class="text-3xl font-bold text-pcwBlack mb-2 text-center">Welcome Back</h1>
        <p class="text-gray-500 text-sm text-center mb-8">Login to your account.</p>

        <?php if($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm"><?php echo e($error); ?></div>
        <?php endif; ?>

        <form method="post" action="login.php" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Phone Number</label>
                <input type="text" name="phone" required class="w-full border-2 border-gray-200 rounded-lg p-2.5 outline-none focus:border-pcwRed transition-colors">
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full border-2 border-gray-200 rounded-lg p-2.5 outline-none focus:border-pcwRed transition-colors">
            </div>
            <button type="submit" class="w-full bg-pcwRed hover:bg-red-800 text-white font-bold py-3 rounded-lg transition-colors shadow-md mt-4">
                Login
            </button>
        </form>
        <p class="text-center text-sm text-gray-600 mt-6">
            New to Pal Chasme Wale? <a href="register.php" class="text-pcwRed hover:underline font-bold">Create an account</a>
        </p>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>