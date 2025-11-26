<?php
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();

// Fetch all users with booking count
$result = $conn->query("
    SELECT u.*, COUNT(b.id) as total_bookings
    FROM users u
    LEFT JOIN bookings b ON u.id = b.user_id AND b.status = 'confirmed'
    GROUP BY u.id
    ORDER BY u.created_at DESC
");

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-calendar-alt"></i> EventHub Admin
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
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="add-event.php">
                            <i class="fas fa-plus-circle"></i> Add Event
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view-events.php">
                            <i class="fas fa-calendar"></i> View Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="view-users.php">
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
                <h2 class="mb-4">Registered Users</h2>

                <div class="card">
                    <div class="card-body">
                        <?php if ($result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Total Bookings</th>
                                            <th>Registered On</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($user = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $user['id']; ?></td>
                                                <td><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td><?php echo $user['phone'] ? htmlspecialchars($user['phone']) : '-'; ?></td>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo $user['total_bookings']; ?></span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No users registered yet.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>