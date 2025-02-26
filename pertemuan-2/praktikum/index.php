<?php
require 'vendor/autoload.php';

use Rakit\Validation\Validator;

session_start();

/**
 * Validate user input and return errors if any.
 *
 * @param array $data The form data.
 * @return array Validation errors (if any).
 */
function validateInput(array $data): array
{
    $validator = new Validator;
    $validation = $validator->make($data, [
        'name'     => 'required',
        'email'    => 'required|email',
        'password' => 'required|min:6',
    ]);

    $validation->validate();

    return $validation->fails() ? $validation->errors()->firstOfAll() : [];
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $errors = validateInput($_POST);

    if (empty($errors)) {
        $hashedPassword = password_hash($_POST["password"], PASSWORD_ARGON2I);

        $_SESSION["form_data"] = [
            "name"     => $_POST["name"],
            "email"    => $_POST["email"],
            "password" => $hashedPassword, // Store only the hashed password
        ];

        header("Location: " . $_SERVER["PHP_SELF"]);
        exit;
    }
}

// Get stored session data (if any) & clear it
$formData = $_SESSION["form_data"] ?? [];
unset($_SESSION["form_data"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Form</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
    <form method="POST" action="" class="w-full max-w-md p-6 bg-white rounded-lg shadow-md space-y-4">
        <h2 class="text-xl font-semibold text-center">Sign Up</h2>

        <?php if (!empty($errors)) : ?>
            <div class="p-3 bg-red-100 text-red-700 rounded-md">
                <strong>Errors:</strong>
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error) : ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div>
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($formData['name'] ?? '') ?>" placeholder="John Doe"
                   class="w-full mt-1 p-2 border rounded-md focus:ring-2 focus:ring-blue-500" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($formData['email'] ?? '') ?>" placeholder="john@example.com"
                   class="w-full mt-1 p-2 border rounded-md focus:ring-2 focus:ring-blue-500" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" name="password" placeholder="••••••••"
                   class="w-full mt-1 p-2 border rounded-md focus:ring-2 focus:ring-blue-500" required>
        </div>

        <button type="submit" class="w-full p-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Sign Up</button>

        <?php if (!empty($formData)) : ?>
            <div class="mt-4 p-3 bg-gray-200 text-sm rounded-md overflow-x-auto">
                <strong>Submitted Data:</strong>
                <pre><?= htmlspecialchars(print_r($formData, true)) ?></pre>
            </div>
        <?php endif; ?>
    </form>
</body>
</html>
