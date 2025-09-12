<?php
session_start();
include 'includes/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get house ID from URL
$house_id = isset($_GET['house_id']) ? (int)$_GET['house_id'] : 1;

// Fetch house details
$stmt = $pdo->prepare("SELECT house_number, status, rent_amount, water_bill, deposit FROM houses WHERE id = ?");
$stmt->execute([$house_id]);
$house = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$house) {
    die("House not found.");
}

$house_number = $house['house_number'];
$status = $house['status'];
$rent_amount = $house['rent_amount'] ?? 0;
$water_bill = $house['water_bill'] ?? 0;
$deposit = $house['deposit'] ?? 0;

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['status'])) {
    $new_status = $_POST['status'];
    $color = ($new_status == 'occupied') ? 'green' : (($new_status == 'vacant') ? 'red' : 'orange');
    $stmt = $pdo->prepare("UPDATE houses SET status = ?, color_indicator = ? WHERE id = ?");
    $stmt->execute([$new_status, $color, $house_id]);

    // Log the action
    $stmt = $pdo->prepare("INSERT INTO action_logs (user_id, action_type, details) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], 'update_status', "Updated status of house $house_number to $new_status"]);

    header("Location: house_dashboard.php?house_id=$house_id");
    exit;
}

// Handle agreed amounts update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agreed_rent']) && isset($_POST['agreed_water']) && isset($_POST['agreed_deposit'])) {
    $agreed_rent = floatval($_POST['agreed_rent']);
    $agreed_water = floatval($_POST['agreed_water']);
    $agreed_deposit = floatval($_POST['agreed_deposit']);

    $stmt = $pdo->prepare("UPDATE houses SET rent_amount = ?, water_bill = ?, deposit = ? WHERE id = ?");
    $stmt->execute([$agreed_rent, $agreed_water, $agreed_deposit, $house_id]);

    // Log the action
    $stmt = $pdo->prepare("INSERT INTO action_logs (user_id, action_type, details) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], 'update_agreed_amounts', "Updated agreed amounts for house $house_number: Rent KSH $agreed_rent, Water KSH $agreed_water, Deposit KSH $agreed_deposit"]);

    header("Location: house_dashboard.php?house_id=$house_id");
    exit;
}

// Handle monthly collection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['collected_rent']) && isset($_POST['collected_water']) && isset($_POST['collection_date'])) {
    $collected_rent = floatval($_POST['collected_rent']);
    $collected_water = floatval($_POST['collected_water']);
    $collection_date = $_POST['collection_date'];
    $total = $collected_rent + $collected_water;

    $stmt = $pdo->prepare("INSERT INTO payments (house_id, date, water_bill, rent, total, paid_by_user_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$house_id, $collection_date, $collected_water, $collected_rent, $total, $_SESSION['user_id']]);

    // Log the action
    $stmt = $pdo->prepare("INSERT INTO action_logs (user_id, action_type, details) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], 'record_collection', "Recorded collection for house $house_number: Rent KSH $collected_rent, Water KSH $collected_water, Date $collection_date"]);

    header("Location: house_dashboard.php?house_id=$house_id");
    exit;
}

// Handle payment update (existing functionality)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deposit']) && isset($_POST['rent']) && isset($_POST['water_bill'])) {
    $deposit = floatval($_POST['deposit']);
    $rent = floatval($_POST['rent']);
    $water_bill = floatval($_POST['water_bill']);
    $total = $rent + $water_bill;

    $stmt = $pdo->prepare("UPDATE houses SET deposit = ?, rent_amount = ?, water_bill = ? WHERE id = ?");
    $stmt->execute([$deposit, $rent, $water_bill, $house_id]);

    $stmt = $pdo->prepare("INSERT INTO payments (house_id, date, water_bill, rent, total, paid_by_user_id) VALUES (?, CURDATE(), ?, ?, ?, ?)");
    $stmt->execute([$house_id, $water_bill, $rent, $total, $_SESSION['user_id']]);

    // Log the action
    $stmt = $pdo->prepare("INSERT INTO action_logs (user_id, action_type, details) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], 'record_payment', "Recorded payment for house $house_number: Rent KSH $rent, Water KSH $water_bill, Total KSH $total"]);

    header("Location: house_dashboard.php?house_id=$house_id");
    exit;
}

// Handle renovation update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['renovation_type']) && isset($_POST['renovation_amount'])) {
    $type = $_POST['renovation_type'];
    $amount = floatval($_POST['renovation_amount']);

    $stmt = $pdo->prepare("INSERT INTO renovations (house_id, date, type, amount, recorded_by_user_id) VALUES (?, CURDATE(), ?, ?, ?)");
    $stmt->execute([$house_id, $type, $amount, $_SESSION['user_id']]);

    // Log the action
    $stmt = $pdo->prepare("INSERT INTO action_logs (user_id, action_type, details) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], 'record_renovation', "Recorded renovation for house $house_number: $type, KSH $amount"]);

    header("Location: house_dashboard.php?house_id=$house_id");
    exit;
}

// Fetch payment history
$stmt = $pdo->prepare("SELECT date, water_bill, rent, total FROM payments WHERE house_id = ? ORDER BY date DESC");
$stmt->execute([$house_id]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch renovation history
$stmt = $pdo->prepare("SELECT date, type, amount FROM renovations WHERE house_id = ? ORDER BY date DESC");
$stmt->execute([$house_id]);
$renovations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Determine background color with proper nesting
$background_color = ($status == 'occupied') ? '#90EE90' : (($status == 'vacant') ? '#ff6347' : '#ffa500');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>House <?php echo htmlspecialchars($house_number); ?> Dashboard - Rental Management System</title>
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
            max-width: 800px;
            margin: 0 auto;
        }
        .status-section {
            margin-bottom: 20px;
            background-color: <?php echo $background_color; ?>;
            padding: 15px;
            border-radius: 5px;
            color: #ffffff;
        }
        .status-section select {
            padding: 10px;
            border-radius: 5px;
            border: 2px solid #ffffff;
            background-color: inherit;
            color: #ffffff;
            font-weight: bold;
        }
        .status-section button {
            padding: 10px 20px;
            background-color: #ffffff;
            border: none;
            border-radius: 5px;
            color: #90EE90;
            cursor: pointer;
        }
        .status-section button:hover {
            background-color: #d3f9d3;
        }
        .agreed-amounts-section, .payment-section, .collection-section, .renovation-section {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .agreed-amounts-section input, .payment-section input, .collection-section input {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #90EE90;
            border-radius: 5px;
        }
        .agreed-amounts-section button, .payment-section button, .collection-section button, .renovation-section button {
            padding: 10px 20px;
            background-color: #90EE90;
            border: none;
            border-radius: 5px;
            color: #ffffff;
            cursor: pointer;
        }
        .agreed-amounts-section button:hover, .payment-section button:hover, .collection-section button:hover, .renovation-section button:hover {
            background-color: #78d578;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #90EE90;
            color: #ffffff;
        }
        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .nav-buttons button {
            padding: 10px 20px;
            background-color: #ffffff;
            border: 2px solid #90EE90;
            border-radius: 5px;
            color: #90EE90;
            font-weight: bold;
            cursor: pointer;
        }
        .nav-buttons button:hover {
            background-color: #d3f9d3;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>House <?php echo htmlspecialchars($house_number); ?> Dashboard</h1>
        <form action="home.php?logout=1" method="POST" style="display:inline;">
            <button type="submit">Logout</button>
        </form>
    </div>
    <div class="content">
        <div class="status-section">
            <h2>Status</h2>
            <form method="POST" action="">
                <select name="status" onchange="this.form.submit()">
                    <option value="occupied" <?php echo $status == 'occupied' ? 'selected' : ''; ?>>Occupied</option>
                    <option value="vacant" <?php echo $status == 'vacant' ? 'selected' : ''; ?>>Vacant</option>
                    <option value="staff" <?php echo $status == 'staff' ? 'selected' : ''; ?>>Staff</option>
                </select>
            </form>
        </div>

        <?php if ($status == 'occupied'): ?>
            <div class="agreed-amounts-section">
                <h2>Agreed Amounts</h2>
                <form method="POST" action="">
                    <input type="number" name="agreed_rent" step="0.01" placeholder="Agreed Rent (KSH)" value="<?php echo $rent_amount; ?>" required>
                    <input type="number" name="agreed_water" step="0.01" placeholder="Agreed Water Bill (KSH)" value="<?php echo $water_bill; ?>" required>
                    <input type="number" name="agreed_deposit" step="0.01" placeholder="Agreed Deposit (KSH)" value="<?php echo $deposit; ?>" required>
                    <button type="submit">Save Agreed Amounts</button>
                </form>
            </div>

            <div class="collection-section">
                <h2>Monthly Collection</h2>
                <form method="POST" action="">
                    <input type="number" name="collected_rent" step="0.01" placeholder="Collected Rent (KSH)" required>
                    <input type="number" name="collected_water" step="0.01" placeholder="Collected Water Bill (KSH)" required>
                    <input type="date" name="collection_date" value="<?php echo date('Y-m-d'); ?>" required>
                    <button type="submit">Record Collection</button>
                </form>
            </div>

            <div class="payment-section">
                <h2>Payment Details</h2>
                <form method="POST" action="">
                    <input type="number" name="deposit" step="0.01" placeholder="Deposit (KSH)" value="<?php echo $deposit; ?>" required>
                    <input type="number" name="rent" step="0.01" placeholder="Rent (KSH)" value="<?php echo $rent_amount; ?>" required>
                    <input type="number" name="water_bill" step="0.01" placeholder="Water Bill (KSH)" value="<?php echo $water_bill; ?>" required>
                    <button type="submit">Update Payment</button>
                </form>
                <h3>Rent Payment History</h3>
                <table>
                    <tr>
                        <th>Date</th>
                        <th>Water Bill (KSH)</th>
                        <th>Rent (KSH)</th>
                        <th>Total (KSH)</th>
                    </tr>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['date']); ?></td>
                            <td><?php echo number_format($payment['water_bill'], 2); ?></td>
                            <td><?php echo number_format($payment['rent'], 2); ?></td>
                            <td><?php echo number_format($payment['total'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>

        <div class="renovation-section">
            <h2>Renovation</h2>
            <form method="POST" action="">
                <input type="text" name="renovation_type" placeholder="Type of Renovation" required>
                <input type="number" name="renovation_amount" step="0.01" placeholder="Amount (KSH)" required>
                <button type="submit">Record Renovation</button>
            </form>
            <h3>Renovation History</h3>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Amount (KSH)</th>
                </tr>
                <?php foreach ($renovations as $renovation): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($renovation['date']); ?></td>
                        <td><?php echo htmlspecialchars($renovation['type']); ?></td>
                        <td><?php echo number_format($renovation['amount'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="nav-buttons">
            <a href="apartment_dashboard.php?apartment_id=<?php echo $apartment_id; ?>">
                <button>Back</button>
            </a>
            <form action="home.php?logout=1" method="POST" style="display:inline;">
                <button type="submit">Logout</button>
            </form>
        </div>
    </div>
</body>
</html>