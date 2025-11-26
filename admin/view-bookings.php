<?php
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();

// Handle status update
if (isset($_POST['update_status'])) {
    $booking_id = (int)$_POST['booking_id'];
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $booking_id);
    
    if ($stmt->execute()) {
        $message = 'Booking status updated successfully!';
    }
}

// Fetch all bookings
$result = $conn->query("
    SELECT b.id, b.function_date, b.function_time, b.venue_address, b.contact_phone, 
           b.additional_notes, b.booking_date, b.status,
           u.name as user_name, u.email as user_email, u.phone as user_phone,
           p.package_name, p.category, p.price
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN decoration_packages p ON b.package_id = p.id
    ORDER BY b.booking_date DESC
");

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Bookings</title>
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
            <div class="col-md-2 sidebar">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
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
                        <a class="nav-link active" href="view-bookings.php">
                            <i class="fas fa-ticket-alt"></i> View Bookings
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-md-10 p-4">
                <h2 class="mb-4">All Bookings</h2>

                <?php if (isset($message)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <?php if ($result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Customer Details</th>
                                            <th>Package</th>
                                            <th>Function Details</th>
                                            <th>Price</th>
                                            <th>Booked On</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($booking = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong>#<?php echo $booking['id']; ?></strong></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($booking['user_name']); ?></strong><br>
                                                    <small>📧 <?php echo htmlspecialchars($booking['user_email']); ?></small><br>
                                                    <small>📞 <?php echo htmlspecialchars($booking['contact_phone']); ?></small>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($booking['package_name']); ?></strong><br>
                                                    <span class="badge bg-info"><?php echo htmlspecialchars($booking['category']); ?></span>
                                                </td>
                                                <td>
                                                    <strong>Date:</strong> <?php echo date('M d, Y', strtotime($booking['function_date'])); ?><br>
                                                    <strong>Time:</strong> <?php echo date('h:i A', strtotime($booking['function_time'])); ?><br>
                                                    <strong>Venue:</strong> <small><?php echo htmlspecialchars(substr($booking['venue_address'], 0, 50)) . '...'; ?></small>
                                                    <?php if($booking['additional_notes']): ?>
                                                        <br><small class="text-muted"><i class="fas fa-comment"></i> <?php echo htmlspecialchars(substr($booking['additional_notes'], 0, 30)) . '...'; ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><strong class="text-success">₹<?php echo number_format($booking['price'], 2); ?></strong></td>
                                                <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                                <td>
                                                    <?php
                                                    $badge = 'secondary';
                                                    if ($booking['status'] == 'confirmed') $badge = 'success';
                                                    elseif ($booking['status'] == 'completed') $badge = 'primary';
                                                    elseif ($booking['status'] == 'cancelled') $badge = 'danger';
                                                    elseif ($booking['status'] == 'pending') $badge = 'warning';
                                                    ?>
                                                    <span class="badge bg-<?php echo $badge; ?>"><?php echo ucfirst($booking['status']); ?></span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#statusModal<?php echo $booking['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    
                                                    <!-- Status Update Modal -->
                                                    <div class="modal fade" id="statusModal<?php echo $booking['id']; ?>" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Update Booking Status</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form method="POST" action="">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Status</label>
                                                                            <select class="form-control" name="status" required>
                                                                                <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                                                <option value="confirmed" <?php echo $booking['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                                                <option value="completed" <?php echo $booking['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                                                <option value="cancelled" <?php echo $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No bookings found yet.
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