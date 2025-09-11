<?php
session_start();
include 'includes/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get apartment ID from URL
$apartment_id = isset($_GET['apartment_id']) ? (int)$_GET['apartment_id'] : 1;

// Fetch apartment name and house data
$stmt = $pdo->prepare("SELECT name FROM apartments WHERE id = ?");
$stmt->execute([$apartment_id]);
$apartment = $stmt->fetch(PDO::FETCH_ASSOC);
$apartment_name = $apartment ? $apartment['name'] : "Unknown Apartment";

// Log if apartment not found (for debugging)
if (!$apartment) {
    $stmt = $pdo->prepare("INSERT INTO action_logs (user_id, action_type, details) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], 'error', "Unknown apartment_id: $apartment_id"]);
}

$stmt = $pdo->prepare("SELECT house_number, status FROM houses WHERE apartment_id = ? ORDER BY house_number");
$stmt->execute([$apartment_id]);
$houses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate house status counts
$occupied_count = $vacant_count = $staff_count = 0;
foreach ($houses as $house) {
    if ($house['status'] == 'occupied') $occupied_count++;
    elseif ($house['status'] == 'vacant') $vacant_count++;
    elseif ($house['status'] == 'staff') $staff_count++;
}

// Fetch total water bill and rent
$stmt = $pdo->prepare("
    SELECT SUM(water_bill) as total_water, SUM(rent_amount) as total_rent 
    FROM houses 
    WHERE apartment_id = ?
");
$stmt->execute([$apartment_id]);
$totals = $stmt->fetch(PDO::FETCH_ASSOC);
$total_water = $totals['total_water'] ?? 0;
$total_rent = $totals['total_rent'] ?? 0;
$total_amount = $total_water + $total_rent;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($apartment_name); ?> Dashboard - Rental Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #333;
        }
        .header {
            background-color: #90EE90;
            color: #ffffff;
            text-align: center;
            padding: 20px;
        }
        .content {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }
        .stat-card {
            background-color: #90EE90;
            color: #ffffff;
            padding: 10px;
            border-radius: 5px;
            width: 30%;
            text-align: center;
        }
        .house-list {
            margin-bottom: 20px;
        }
        .house-list ul {
            list-style-type: none;
            padding: 0;
        }
        .house-list li {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            background-color: #f9f9f9;
            margin-bottom: 5px;
            border-radius: 5px;
        }
        .house-list button {
            background-color: #90EE90;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            color: #ffffff;
            cursor: pointer;
        }
        .house-list button:hover {
            background-color: #78d578;
        }
        .totals {
            background-color: #90EE90;
            color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .totals button {
            background-color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            color: #90EE90;
            font-weight: bold;
            cursor: pointer;
        }
        .totals button:hover {
            background-color: #d3f9d3;
        }
        .logout-btn {
            background-color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            color: #90EE90;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .logout-btn:hover {
            background-color: #d3f9d3;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php echo htmlspecialchars($apartment_name); ?> Dashboard</h1>
        <form action="home.php?logout=1" method="POST" style="display:inline;">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>
    <div class="content">
        <div class="stats">
            <div class="stat-card">Occupied: <?php echo $occupied_count; ?></div>
            <div class="stat-card">Vacant: <?php echo $vacant_count; ?></div>
            <div class="stat-card">Staff: <?php echo $staff_count; ?></div>
        </div>
        <div class="house-list">
            <h2>Houses</h2>
            <ul>
                <?php foreach ($houses as $house): ?>
                    <li>
                        <?php echo htmlspecialchars($house['house_number']); ?>
                        <a href="house_dashboard.php?house_id=<?php echo $house['id']; ?>">
                            <button>View</button>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="totals">
            <h2>Monthly Totals (All Time for Now)</h2>
            <p>Total Water Bill: KSH <?php echo number_format($total_water, 2); ?></p>
            <p>Total Rent: KSH <?php echo number_format($total_rent, 2); ?></p>
            <p>Total Amount: KSH <?php echo number_format($total_amount, 2); ?></p>
            <button>Download PDF</button>
        </div>
    </div>
</body>
</html>