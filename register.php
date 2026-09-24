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

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name && $phone && $password) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            $error = "Phone number is already registered.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)");
            $ins->execute([$name, $phone, $email ?: null, $hash]);

            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            redirect('account/index.php');
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="bg-gray-50 min-h-screen py-16 flex items-center justify-center">
    <div class="bg-white p-8 sm:p-10 rounded-2xl shadow-sm border border-gray-100 max-w-md w-full mx-4">
        <h1 class="text-3xl font-bold text-pcwBlack mb-2 text-center">Create Account</h1>
        <p class="text-gray-500 text-sm text-center mb-8">Join Pal Chasme Wale today.</p>

        <?php if($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm"><?php echo e($error); ?></div>
        <?php endif; ?>

        <form method="post" action="register.php" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Full Name *</label>
                <input type="text" name="name" required class="w-full border-2 border-gray-200 rounded-lg p-2.5 outline-none focus:border-pcwRed transition-colors">
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Phone Number *</label>
                <input type="text" name="phone" required class="w-full border-2 border-gray-200 rounded-lg p-2.5 outline-none focus:border-pcwRed transition-colors">
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Email Address</label>
                <input type="email" name="email" class="w-full border-2 border-gray-200 rounded-lg p-2.5 outline-none focus:border-pcwRed transition-colors">
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Password *</label>
                <input type="password" name="password" required class="w-full border-2 border-gray-200 rounded-lg p-2.5 outline-none focus:border-pcwRed transition-colors">
            </div>
            <button type="submit" class="w-full bg-pcwRed hover:bg-red-800 text-white font-bold py-3 rounded-lg transition-colors shadow-md mt-4">
                Register
            </button>
        </form>
        <p class="text-center text-sm text-gray-600 mt-6">
            Already have an account? <a href="login.php" class="text-pcwRed hover:underline font-bold">Login</a>
        </p>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>