<?php
// Include the database settings
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

// Fetch admin user_id from GET request
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Query to fetch admin details
$query = "SELECT * FROM users WHERE user_id = $user_id AND role = 'admin'";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
} else {
    die("Admin not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['Email']);

    // Update users table
    $updateUserQuery = "
        UPDATE users SET 
            email = '$email'
        WHERE user_id = $user_id AND role = 'admin'
    ";

    // Only update password if not empty
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $updateUserPasswordQuery = "UPDATE users SET password='$password' WHERE user_id='$user_id' AND role = 'admin'";
        $conn->query($updateUserPasswordQuery);
    }

    // Execute queries and redirect
    if ($conn->query($updateUserQuery)) {
        header("Location: ../Pages/admin/users.php?active_tab=admins");
        exit();
    } else {
        echo "Error updating admin: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Styles/common.css">
    <link rel="stylesheet" href="/Styles/forms.css">
    <title>Edit Admin</title>
    <style>
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 12px;
            align-items: center;
        }

        .form-row label {
            min-width: 110px;
            font-size: 0.96em;
            margin-right: 6px;
            white-space: nowrap;
        }

        .form-row input,
        .form-row select,
        .form-row textarea {
            min-width: 140px;
            flex: 1 1 140px;
            padding: 6px 8px;
            font-size: 1em;
        }

        form {
            background: #fafbfc;
            border-radius: 10px;
            box-shadow: 0 2px 16px rgba(60, 60, 60, 0.12);
            padding: 18px 24px;
            margin-top: 18px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        button[type="submit"],
        a[href$="users.php"] {
            margin-top: 14px;
            margin-right: 10px;
        }

        @media (max-width: 800px) {
            .form-row {
                flex-direction: column;
                gap: 6px;
            }

            form {
                padding: 12px;
            }
        }

        button {
            background-color: #1f2937;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: rgb(71, 82, 95);
        }

        a {
            margin-left: 15px;
            text-decoration: none;
            color: #1f2937;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        a:hover {
            color: rgb(71, 82, 95);
        }
    </style>
</head>

<body>
    <h1>Edit Admin</h1>
    <form method="POST">
        <div class="form-row">
            <label for="Email">Email:</label>
            <input type="email" id="Email" name="Email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
            <label for="admin_password">New Password:</label>
            <input type="password" id="admin_password" name="password" placeholder="Leave blank to keep current password">
        </div>
        <button type="submit">Update</button>
        <a href="../Pages/admin/users.php?active_tab=admins">Cancel</a>
    </form>
</body>
</html>