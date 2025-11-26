<?php
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();

// Get statistics
$stats = [];

$result = $conn->query("SELECT COUNT(*) as total FROM decoration_packages WHERE status = 'active'");
$stats['total_packages'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status != 'cancelled'");
$stats['total_bookings'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'");
$stats['pending_bookings'] = $result->fetch_assoc()['total'];

// Recent bookings
$recent_bookings = $conn->query("
    SELECT b.booking_date, b.function_date, b.status,
           u.name as user_name, p.package_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN decoration_packages p ON b.package_id = p.id
    ORDER BY b.booking_date DESC
    LIMIT 5
");

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-palette"></i> Event Decoration Admin
            </a>
            <div class="navbar-nav ms-auto">
                <span class="nav-link text-white">
                    <i class="fas fa-user"></i> <?php echo $_SESSION['admin_username']; ?>
                </span>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="add-package.php">
                            <i class="fas fa-plus-circle"></i> Add Package
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view-packages.php">
                            <i class="fas fa-box"></i> View Packages
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view-users.php">
                            <i class="fas fa-users"></i> View Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view-bookings.php">
                            <i class="fas fa-ticket-alt"></i> View Bookings
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <h2 class="mb-4">Dashboard</h2>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card dashboard-card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Packages</h6>
                                        <h2><?php echo $stats['total_packages']; ?></h2>
                                    </div>
                                    <i class="fas fa-box stats-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Users</h6>
                                        <h2><?php echo $stats['total_users']; ?></h2>
                                    </div>
                                    <i class="fas fa-users stats-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card text-white bg-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Bookings</h6>
                                        <h2><?php echo $stats['total_bookings']; ?></h2>
                                    </div>
                                    <i class="fas fa-ticket-alt stats-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card text-white bg-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Pending Bookings</h6>
                                        <h2><?php echo $stats['pending_bookings']; ?></h2>
                                    </div>
                                    <i class="fas fa-clock stats-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Bookings</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($recent_bookings->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Package</th>
                                            <th>Function Date</th>
                                            <th>Booking Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($booking = $recent_bookings->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                                <td><?php echo htmlspecialchars($booking['package_name']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($booking['function_date'])); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                                <td>
                                                    <?php
                                                    $badge = 'secondary';
                                                    if ($booking['status'] == 'confirmed') $badge = 'success';
                                                    elseif ($booking['status'] == 'pending') $badge = 'warning';
                                                    elseif ($booking['status'] == 'cancelled') $badge = 'danger';
                                                    ?>
                                                    <span class="badge bg-<?php echo $badge; ?>"><?php echo ucfirst($booking['status']); ?></span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <a href="view-bookings.php" class="btn btn-primary btn-sm">View All Bookings</a>
                        <?php else: ?>
                            <p class="text-muted">No bookings yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>