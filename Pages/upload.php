<?php
session_start();
require_once '../setting.php';

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$userId = $_SESSION['user_id'];
$response = ['success' => false, 'message' => 'Invalid action'];

// Create adminPics directory if it doesn't exist
$uploadDir = __DIR__ . '/admin/adminPics/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Handle different actions
$action = $_POST['action'] ?? '';
switch ($action) {
    case 'upload':
        // Handle profile picture upload
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_pic'];
            
            // Validate file type and size
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 2 * 1024 * 1024; // 2MB
            
            if (!in_array($file['type'], $allowedTypes)) {
                $response['message'] = 'Only JPG, PNG, and GIF images are allowed';
                break;
            }
            
            if ($file['size'] > $maxSize) {
                $response['message'] = 'Image size must be less than 2MB';
                break;
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'admin_' . $userId . '_' . time() . '.' . $extension;
            $filePath = $uploadDir . $filename;
            
            // Delete old profile picture if exists
            $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if ($user['profile_pic'] && file_exists($uploadDir . basename($user['profile_pic']))) {
                unlink($uploadDir . basename($user['profile_pic']));
            }
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                // Update database with relative path
                $relativePath = 'admin/adminPics/' . $filename;
                $updateStmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE user_id = ?");
                $updateStmt->bind_param("si", $relativePath, $userId);
                
                if ($updateStmt->execute()) {
                    $response = [
                        'success' => true,
                        'filePath' => $relativePath,
                        'message' => 'Profile picture updated successfully'
                    ];
                } else {
                    unlink($filePath); // Remove the uploaded file if DB update fails
                    $response['message'] = 'Database update failed';
                }
            } else {
                $response['message'] = 'Error uploading file';
            }
        } else {
            $response['message'] = 'No file uploaded or upload error';
        }
        break;
        
    case 'delete':
        // Handle profile picture deletion
        $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user['profile_pic']) {
            $filePath = $uploadDir . basename($user['profile_pic']);
            
            // Delete file if it exists
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Update database
            $updateStmt = $conn->prepare("UPDATE users SET profile_pic = NULL WHERE user_id = ?");
            if ($updateStmt->execute([$userId])) {
                $response = [
                    'success' => true,
                    'message' => 'Profile picture removed successfully'
                ];
            } else {
                $response['message'] = 'Database update failed';
            }
        } else {
            $response['message'] = 'No profile picture to remove';
        }
        break;
        
    case 'update_profile':
        // Handle email and password updates
        $email = $_POST['email'] ?? '';
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Invalid email address';
            break;
        }
        
        // Check if email is being changed
        $stmt = $conn->prepare("SELECT email, password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        $emailChanged = ($user['email'] !== $email);
        $passwordChanged = !empty($newPassword);
        
        // If either email or password is being changed, require current password
        if (($emailChanged || $passwordChanged) && empty($currentPassword)) {
            $response['message'] = 'Current password is required to make changes';
            break;
        }
        
        // Verify current password if provided
        if (!empty($currentPassword) && !password_verify($currentPassword, $user['password'])) {
            $response['message'] = 'Current password is incorrect';
            break;
        }
        
        // Prepare update query
        $updateFields = [];
        $params = [];
        $types = '';
        
        if ($emailChanged) {
            // Check if new email already exists
            $emailCheck = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $emailCheck->bind_param("si", $email, $userId);
            $emailCheck->execute();
            
            if ($emailCheck->get_result()->num_rows > 0) {
                $response['message'] = 'Email address already in use';
                break;
            }
            
            $updateFields[] = 'email = ?';
            $params[] = $email;
            $types .= 's';
        }
        
        if ($passwordChanged) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateFields[] = 'password = ?';
            $params[] = $hashedPassword;
            $types .= 's';
        }
        
        // Only proceed if there are changes
        if (!empty($updateFields)) {
            $params[] = $userId;
            $types .= 'i';
            
            $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE user_id = ?";
            $updateStmt = $conn->prepare($query);
            $updateStmt->bind_param($types, ...$params);
            
            if ($updateStmt->execute()) {
                $response = [
                    'success' => true,
                    'message' => 'Profile updated successfully',
                    'newEmail' => $emailChanged ? $email : null
                ];
                
                // Update session email if changed
                if ($emailChanged) {
                    $_SESSION['email'] = $email;
                }
            } else {
                $response['message'] = 'Database update failed';
            }
        } else {
            $response['message'] = 'No changes to update';
        }
        break;
        
    default:
        $response['message'] = 'Invalid action';
        break;
}

header('Content-Type: application/json');
echo json_encode($response);