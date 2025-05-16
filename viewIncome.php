<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Please login to view this page.");
}


// Database connection
$conn = new mysqli('mysql', 'root', 'root', 'budgeting_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$user_id = $_SESSION['user_id'];

// Get filters
$month = $_GET['month'] ?? 'all';
$year = $_GET['year'] ?? 'all';

// Build dynamic WHERE clause
$where = "WHERE user_id = ?";
$params = [$user_id];
$types = "i";

if ($month !== 'all') {
    $where .= " AND MONTH(due_date) = ?";
    $params[] = intval($month);
    $types .= "i";
}

if ($year !== 'all') {
    $where .= " AND YEAR(due_date) = ?";
    $params[] = intval($year);
    $types .= "i";
}

// Prepare the SQL query to filter by the selected month and year
$sql = "SELECT income_id, income_source, income_amount, date_added 
        FROM Income 
        WHERE user_id = ? AND MONTH(date_added) = ? AND YEAR(date_added) = ?
        ORDER BY date_added ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $selected_month, $selected_year);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Income List</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="container mt-5">
<h1>Your Income
    <?php 
    if ($selected_month === "all" && $selected_year === "all") {
        echo "(All Time)";
    } elseif ($selected_month === "all") {
        echo "for Year $selected_year";
    } elseif ($selected_year === "all") {
        echo "for " . date('F', mktime(0, 0, 0, $selected_month, 10)) . " (All Years)";
    } else {
        echo "for " . date('F Y', strtotime("$selected_year-$selected_month-01"));
    }
    ?>
</h1>

  <?php if ($result->num_rows > 0): ?>
    <table class="table table-striped">
            <thead>
                <tr>
                    <th>Income ID</th>
                    <th>Source</th>
                    <th>Date</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total = 0;
                while ($row = $result->fetch_assoc()): 
                    $total += $row['income_amount'];
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['income_id']) ?></td>
                        <td><?= htmlspecialchars($row['income_source']) ?></td>
                        <td><?= date('M j, Y', strtotime($row['date_added'])) ?></td>
                        <td>$<?= number_format($row['income_amount'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <div class="d-flex justify-content-end mt-4" style="margin-right: 200px;" >
        <div><strong>Total Income:</strong> $<?= number_format($total, 2) ?></div>
        </div>

        <?php else: ?>
        <p>No income records found for the selected month.</p>
        <?php endif; ?>

        <!-- Centered Button (margin-top = 4) -->
        <div class="text-center mt-4">
            <a href='../front_end/home.html' class="btn btn-success">Go Back to Dashboard</a>
        </div>

        </body>
        </html>


<?php
$stmt->close();
$conn->close();
?>
