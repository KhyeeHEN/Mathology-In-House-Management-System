<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pages/setting.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = trim($_POST['course_name']);
    $level = trim($_POST['level']);
    $fee_amount = floatval($_POST['fee_amount']);
    $package_hours = intval($_POST['package_hours']);
    $time = trim($_POST['time']);

    // Insert into courses
    $stmt1 = $conn->prepare("INSERT INTO courses (course_name, level) VALUES (?, ?)");
    $stmt1->bind_param("ss", $course_name, $level);

    if ($stmt1->execute()) {
        $new_course_id = $stmt1->insert_id;

        // Insert initial fee for new course
        $stmt2 = $conn->prepare("
            INSERT INTO course_fees (course_id, fee_amount, package_hours, time)
            VALUES (?, ?, ?, ?)
        ");
        $stmt2->bind_param("idis", $new_course_id, $fee_amount, $package_hours, $time);

        if ($stmt2->execute()) {
            header("Location: ../Pages/admin/manage_fees.php?message=Course and fee added successfully");
            exit();
        } else {
            header("Location: ../Pages/admin/manage_fees.php?error=Course added, but fee failed");
            exit();
        }

    } else {
        header("Location: ../Pages/admin/manage_fees.php?error=Failed to add course");
        exit();
    }
} else {
    header("Location: ../Pages/admin/manage_fees.php?error=Invalid request");
    exit();
}
