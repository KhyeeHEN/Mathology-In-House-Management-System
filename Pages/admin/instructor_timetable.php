<?php
include '../setting.php';
session_start();

// Ensure admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $per_page;

// Handle search functionality
$search_query = trim($_GET['search'] ?? '');
$show_search_results = !empty($search_query);

if ($show_search_results) {
    $search_term = "%" . $conn->real_escape_string($search_query) . "%";
    $instructors = $conn->query("
        SELECT i.instructor_id, i.First_Name, i.Last_Name, i.School, i.Highest_Education,
               COUNT(it.id) as timetable_count
        FROM instructor i
        LEFT JOIN instructor_courses ic ON i.instructor_id = ic.instructor_id
        LEFT JOIN instructor_timetable it ON ic.instructor_course_id = it.instructor_course_id
        WHERE (i.instructor_id LIKE '$search_query' OR i.First_Name LIKE '$search_term' OR i.Last_Name LIKE '$search_term')
        GROUP BY i.instructor_id, i.First_Name, i.Last_Name, i.School, i.Highest_Education
        LIMIT $per_page OFFSET $offset
    ");
    $total_query = $conn->query("
        SELECT COUNT(DISTINCT i.instructor_id) as total
        FROM instructor i
        WHERE (i.instructor_id LIKE '$search_query' OR i.First_Name LIKE '$search_term' OR i.Last_Name LIKE '$search_term')
    ");
} else {
    $instructors = $conn->query("
        SELECT i.instructor_id, i.First_Name, i.Last_Name, i.School, i.Highest_Education,
               COUNT(it.id) as timetable_count
        FROM instructor i
        LEFT JOIN instructor_courses ic ON i.instructor_id = ic.instructor_id
        LEFT JOIN instructor_timetable it ON ic.instructor_course_id = it.instructor_course_id
        GROUP BY i.instructor_id, i.First_Name, i.Last_Name, i.School, i.Highest_Education
        LIMIT $per_page OFFSET $offset
    ");
    $total_query = $conn->query("SELECT COUNT(*) as total FROM instructor");
}

$total = $total_query->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Timetable Management</title>
    <link rel="stylesheet" href="/Styles/common.css">
    <link rel="stylesheet" href="/Styles/timtable.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .content {
            margin: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .entry-card { 
            background: white; 
            padding: 15px; 
            margin-bottom: 15px; 
            border-radius: 5px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid #4CAF50;
            transition: all 0.3s;
            overflow: hidden;
            cursor: pointer;
        }
        .entry-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .info-row { 
            display: flex; 
            gap: 10px; 
            margin-bottom: 8px;
            flex-wrap: wrap;
        }
        .info-label { 
            font-weight: bold; 
            min-width: 150px;
            color: #333;
        }
        .info-row div:last-child {
            flex: 1;
            min-width: 0;
        }
        .search-container {
            margin: 20px 0;
            display: flex;
            gap: 10px;
        }
        .search-container input {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-container button {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-success {
            background-color: #4CAF50;
            color: white;
        }
        .btn-primary {
            background-color: #2196F3;
            color: white;
        }
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            gap: 10px;
        }
        .pagination a {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .pagination a:hover {
            background-color: #3e8e41;
        }
        .pagination span {
            padding: 8px 15px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php require("../includes/Aside_Nav.php"); ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content">
                <div class="content-header">
                    <h1><i class="fas fa-calendar-alt"></i> Instructor Timetable Management</h1>
                </div>

                <!-- Search Section -->
                <div class="search-container">
                    <form method="GET" action="instructor_timetable.php">
                        <input type="text" name="search" placeholder="Search by ID or name..." 
                               value="<?= htmlspecialchars($search_query) ?>">
                        <button type="submit"><i class="fas fa-search"></i> Search</button>
                    </form>
                </div>

                <?php if ($instructors && $instructors->num_rows > 0): ?>
                    <?php while ($instructor = $instructors->fetch_assoc()): ?>
                        <div class="entry-card" 
                             onclick="window.location='instructor_timetable.php?instructor_id=<?= htmlspecialchars($instructor['instructor_id']) ?><?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?>'">
                            <div class="info-row">
                                <div class="info-label">Name:</div>
                                <div><?= htmlspecialchars($instructor['First_Name'] . ' ' . $instructor['Last_Name']) ?> (ID: <?= htmlspecialchars($instructor['instructor_id']) ?>)</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">School:</div>
                                <div><?= htmlspecialchars($instructor['School'] ?? 'Not specified') ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Education:</div>
                                <div><?= htmlspecialchars($instructor['Highest_Education'] ?? 'Not specified') ?></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Scheduled Sessions:</div>
                                <div><?= htmlspecialchars($instructor['timetable_count'] ?? 0) ?></div>
                            </div>
                            <div class="actions">
                                <a href="add_timetable_instructor.php?instructor_id=<?= htmlspecialchars($instructor['instructor_id']) ?>" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Add Timetable
                                </a>
                                <?php if ($instructor['timetable_count'] > 0): ?>
                                    <a href="reschedule_timetable_instructor.php?instructor_id=<?= htmlspecialchars($instructor['instructor_id']) ?>" class="btn btn-primary">
                                        <i class="fas fa-calendar-alt"></i> Reschedule Timetable
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>

                    <?php if ($show_search_results && isset($total_pages) && $total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?search=<?= urlencode($search_query) ?>&page=<?= $page - 1 ?>" class="btn btn-primary">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <span>
                                Page <?= $page ?> of <?= $total_pages ?>
                            </span>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?search=<?= urlencode($search_query) ?>&page=<?= $page + 1 ?>" class="btn btn-primary">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php elseif (!$show_search_results && isset($total_pages) && $total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?>" class="btn btn-primary">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <span>
                                Page <?= $page ?> of <?= $total_pages ?>
                            </span>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?= $page + 1 ?>" class="btn btn-primary">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-entries">
                        <i class="fas fa-info-circle"></i>
                        <p>No instructors found<?= $show_search_results ? ' for your search' : '' ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script type="module" src="/Scripts/common.js"></script>
</body>
</html>