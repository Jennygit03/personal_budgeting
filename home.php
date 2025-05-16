<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    header("Location: signin.html");
    exit();
}

// Database connection
$conn = new mysqli('mysql', 'root', 'root', 'budgeting_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Get user info
$stmt = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

// Get total income & expenses
$sql = "
SELECT 
  (SELECT IFNULL(SUM(income_amount),0) FROM Income WHERE user_id = ?) AS total_income,
  (SELECT IFNULL(SUM(transaction_amount),0) FROM Transactions WHERE user_id = ?) AS total_expenses
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$stmt->bind_result($income, $expenses);
$stmt->fetch();
$stmt->close();
$conn->close();

// Default values if no data yet
$income = $income ?? 0;
$expenses = $expenses ?? 0;
?>

<!-- start HTML -->
<!DOCTYPE html>
<html>
<head>
  <title>Budget Dashboard</title>
</head>
<body>
  <h1>Welcome, <?php echo htmlspecialchars($username); ?></h1>
  <p>Total Income: $<?php echo number_format($income, 2); ?></p>
  <p>Total Expenses: $<?php echo number_format($expenses, 2); ?></p>
</body>
</html>
