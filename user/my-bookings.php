<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();

// Handle cancellation
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $booking_id = (int)$_GET['cancel'];
    
    $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'");
    $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $message = 'Booking cancelled successfully!';
    } else {
        $error = 'Unable to cancel booking. It may already be confirmed.';
    }
}

// Fetch user bookings
$stmt = $conn->prepare("
    SELECT b.id, b.function_date, b.function_time, b.venue_address, b.contact_phone, 
           b.additional_notes, b.booking_date, b.status,
           p.package_name, p.category, p.price
    FROM bookings b
    JOIN decoration_packages p ON b.package_id = p.id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-palette"></i> Event Decoration Services
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../index.php">Home</a>
                <a class="nav-link active" href="my-bookings.php">My Bookings</a>
                <a class="nav-link" href="logout.php">Logout (<?php echo $_SESSION['user_name']; ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <h2 class="mb-4"><i class="fas fa-list"></i> My Bookings</h2>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($result->num_rows > 0): ?>
            <div class="row">
                <?php while($booking = $result->fetch_assoc()): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span class="badge bg-info"><?php echo htmlspecialchars($booking['category']); ?></span>
                                <?php
                                $status_class = 'secondary';
                                if ($booking['status'] == 'confirmed') $status_class = 'success';
                                elseif ($booking['status'] == 'completed') $status_class = 'primary';
                                elseif ($booking['status'] == 'cancelled') $status_class = 'danger';
                                elseif ($booking['status'] == 'pending') $status_class = 'warning';
                                ?>
                                <span class="badge bg-<?php echo $status_class; ?>"><?php echo ucfirst($booking['status']); ?></span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($booking['package_name']); ?></h5>
                                
                                <div class="mb-3">
                                    <p class="mb-2"><i class="fas fa-calendar text-primary"></i> <strong>Function Date:</strong> <?php echo date('M d, Y', strtotime($booking['function_date'])); ?></p>
                                    <p class="mb-2"><i class="fas fa-clock text-primary"></i> <strong>Time:</strong> <?php echo date('h:i A', strtotime($booking['function_time'])); ?></p>
                                    <p class="mb-2"><i class="fas fa-map-marker-alt text-primary"></i> <strong>Venue:</strong> <?php echo htmlspecialchars($booking['venue_address']); ?></p>
                                    <p class="mb-2"><i class="fas fa-phone text-primary"></i> <strong>Contact:</strong> <?php echo htmlspecialchars($booking['contact_phone']); ?></p>
                                    <?php if($booking['additional_notes']): ?>
                                        <p class="mb-2"><i class="fas fa-comment text-primary"></i> <strong>Notes:</strong> <?php echo htmlspecialchars($booking['additional_notes']); ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <hr>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="text-success mb-0">₹<?php echo number_format($booking['price'], 2); ?></h5>
                                        <small class="text-muted">Booked on: <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></small>
                                    </div>
                                    <?php if ($booking['status'] == 'pending' && strtotime($booking['function_date']) >= strtotime('today')): ?>
                                        <a href="?cancel=<?php echo $booking['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to cancel this booking?')">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> You haven't booked any decoration packages yet.
                <a href="../index.php" class="alert-link">Browse Packages</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>