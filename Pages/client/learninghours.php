<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance</title>
    <link rel="stylesheet" href="/Styles/common.css" />
    <link rel="stylesheet" href="/Styles/attendance.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .attendance-table th,
        .attendance-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .attendance-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .attendance-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .attendance-table tr:hover {
            background-color: #f1f1f1;
        }

        .status-attended {
            color: green;
            font-weight: bold;
        }

        .status-missed {
            color: red;
            font-weight: bold;
        }

        .status-replacement {
            color: orange;
            font-weight: bold;
        }

        .summary-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .summary-title {
            font-size: 18px;
            margin-bottom: 15px;
            color: #333;
        }

        .summary-value {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <?php include("../includes/Aside_Nav_Student.php"); ?>
        <main class="main-content">
            <?php include("../includes/Top_Nav_Bar_Student.php"); ?>

            <!-- Attendance Content -->
            <div class="content-container">
                <div class="summary-card">
                    <div class="summary-title">Total Hours Attended</div>
                    <div class="summary-value" id="total-hours">0.0 hours</div>
                </div>

                <div class="summary-card">
                    <div class="summary-title">Hours Remaining</div>
                    <div class="summary-value" id="remaining-hours">0.0 hours</div>
                </div>

                <h2>Attendance Records</h2>
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Course</th>
                            <th>Scheduled Time</th>
                            <th>Attendance Time</th>
                            <th>Hours Attended</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="attendance-data">
                        <!-- Data will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <script type="module" src="../../scripts/common.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // In a real application, you would get the student ID from the session
            // For this example, we'll use student_id = 1 (Wei Jie Tan)
            const studentId = 1;

            // Fetch attendance data from the server
            fetchAttendanceData(studentId);
        });

        function fetchAttendanceData(studentId) {
            // In a real application, you would make an AJAX call to your server
            // For this example, we'll use mock data that matches the SQL dump

            // This would be replaced with actual API call:
            // fetch(`/api/attendance?student_id=${studentId}`)
            //     .then(response => response.json())
            //     .then(data => displayAttendanceData(data));

            // Mock data based on the SQL dump for student_id = 1
            const mockData = [
                {
                    record_id: 1,
                    course: "IGCSE Math Prep",
                    timetable_datetime: "2023-11-01 16:00:00",
                    attendance_datetime: "2023-11-01 16:05:00",
                    hours_attended: 2.0,
                    hours_remaining: 18.0,
                    status: "attended"
                }
                // Add more records if needed
            ];

            displayAttendanceData(mockData);
        }

        function displayAttendanceData(data) {
            const tableBody = document.getElementById('attendance-data');
            let totalHours = 0;
            let remainingHours = 0;

            if (data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6">No attendance records found</td></tr>';
                return;
            }

            tableBody.innerHTML = '';

            data.forEach(record => {
                const row = document.createElement('tr');

                // Format date
                const date = new Date(record.timetable_datetime);
                const formattedDate = date.toLocaleDateString();
                const formattedTime = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                // Format attendance time if exists
                let attendanceTime = 'N/A';
                if (record.attendance_datetime) {
                    const attendDate = new Date(record.attendance_datetime);
                    attendanceTime = attendDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }

                // Determine status class
                let statusClass = '';
                if (record.status === 'attended') statusClass = 'status-attended';
                else if (record.status === 'missed') statusClass = 'status-missed';
                else if (record.status === 'replacement_booked') statusClass = 'status-replacement';

                // Capitalize first letter of status
                const formattedStatus = record.status.charAt(0).toUpperCase() + record.status.slice(1);

                row.innerHTML = `
                    <td>${formattedDate}</td>
                    <td>${record.course}</td>
                    <td>${formattedTime}</td>
                    <td>${attendanceTime}</td>
                    <td>${record.hours_attended} hours</td>
                    <td class="${statusClass}">${formattedStatus}</td>
                `;

                tableBody.appendChild(row);

                // Calculate totals
                totalHours += parseFloat(record.hours_attended);
                remainingHours = record.hours_remaining; // Will take last record's remaining hours
            });

            // Update summary cards
            document.getElementById('total-hours').textContent = `${totalHours.toFixed(1)} hours`;
            document.getElementById('remaining-hours').textContent = `${remainingHours.toFixed(1)} hours`;
        }
    </script>
</body>

</html>