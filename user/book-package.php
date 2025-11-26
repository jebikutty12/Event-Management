<?php
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if (!isset($_GET['id'])) {
    header('Location: ../index.php');
    exit();
}

$package_id = (int)$_GET['id'];
$conn = getDBConnection();

// Get package details
$stmt = $conn->prepare("SELECT * FROM decoration_packages WHERE id = ? AND status = 'active'");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ../index.php');
    exit();
}

$package = $result->fetch_assoc();

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $function_date = $_POST['function_date'];
    $function_time = $_POST['function_time'];
    $venue_address = trim($_POST['venue_address']);
    $contact_phone = trim($_POST['contact_phone']);
    $additional_notes = trim($_POST['additional_notes']);
    
    if (empty($function_date) || empty($function_time) || empty($venue_address) || empty($contact_phone)) {
        $error = 'Please fill all required fields';
    } elseif (strtotime($function_date) < strtotime('today')) {
        $error = 'Function date cannot be in the past';
    } else {
        // Insert booking
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, package_id, function_date, function_time, venue_address, contact_phone, additional_notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssss", $_SESSION['user_id'], $package_id, $function_date, $function_time, $venue_address, $contact_phone, $additional_notes);
        
        if ($stmt->execute()) {
            $success = 'Booking request submitted successfully! Our team will contact you soon to confirm details.';
            header("refresh:3;url=my-bookings.php");
        } else {
            $error = 'Booking failed. Please try again.';
        }
    }
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Decoration Package</title>
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
                <a class="nav-link" href="my-bookings.php">My Bookings</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-7">
                <div class="card">
                    <div class="card-body">
                        <?php if($package['image']): ?>
                            <img src="../uploads/<?php echo htmlspecialchars($package['image']); ?>" class="img-fluid mb-4 rounded" alt="Package Image">
                        <?php endif; ?>
                        
                        <span class="badge bg-info mb-3"><?php echo htmlspecialchars($package['category']); ?></span>
                        <h2><?php echo htmlspecialchars($package['package_name']); ?></h2>
                        
                        <div class="alert alert-success">
                            <h4 class="mb-0">₹<?php echo number_format($package['price'], 2); ?></h4>
                        </div>
                        
                        <h5>Package Details:</h5>
                        <p class="lead" style="white-space: pre-line;"><?php echo htmlspecialchars($package['description']); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Book This Package</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php else: ?>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label class="form-label">Your Function Date *</label>
                                    <input type="date" class="form-control" name="function_date" min="<?php echo date('Y-m-d'); ?>" required>
                                    <small class="text-muted">When is your event/function?</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Function Time *</label>
                                    <input type="time" class="form-control" name="function_time" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Venue Address *</label>
                                    <textarea class="form-control" name="venue_address" rows="3" placeholder="Enter complete venue address where decoration is needed" required></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Contact Phone *</label>
                                    <input type="tel" class="form-control" name="contact_phone" placeholder="10-digit mobile number" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Additional Requirements</label>
                                    <textarea class="form-control" name="additional_notes" rows="3" placeholder="Any special requests, color preferences, theme details, etc."></textarea>
                                </div>
                                
                                <div class="booking-summary mb-3">
                                    <h6>Booking Summary</h6>
                                    <p class="mb-1"><strong>Name:</strong> <?php echo $_SESSION['user_name']; ?></p>
                                    <p class="mb-1"><strong>Email:</strong> <?php echo $_SESSION['user_email']; ?></p>
                                    <p class="mb-1"><strong>Package:</strong> <?php echo $package['package_name']; ?></p>
                                    <p class="mb-0"><strong>Price:</strong> ₹<?php echo number_format($package['price'], 2); ?></p>
                                </div>
                                
                                <div class="alert alert-info">
                                    <small><i class="fas fa-info-circle"></i> Our team will contact you within 24 hours to confirm the booking and discuss decoration details.</small>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-check"></i> Confirm Booking
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <a href="../index.php" class="btn btn-outline-secondary w-100 mt-3">Back to Packages</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>