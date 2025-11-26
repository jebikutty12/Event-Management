<?php
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: view-events.php');
    exit();
}

$event_id = (int)$_GET['id'];
$conn = getDBConnection();

$error = '';
$success = '';

// Get event details
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: view-events.php');
    exit();
}

$event = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = trim($_POST['location']);
    $category = trim($_POST['category']);
    $total_seats = (int)$_POST['total_seats'];
    
    // Calculate available seats based on bookings
    $booked_seats = $event['total_seats'] - $event['available_seats'];
    $available_seats = $total_seats - $booked_seats;
    
    // Handle image upload
    $image_name = $event['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // Delete old image
            if ($image_name && file_exists('../uploads/' . $image_name)) {
                unlink('../uploads/' . $image_name);
            }
            
            $image_name = uniqid() . '.' . $ext;
            $upload_path = '../uploads/' . $image_name;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_name = $event['image'];
            }
        }
    }
    
    if (empty($title) || empty($event_date) || empty($location) || $total_seats < 1) {
        $error = 'Please fill all required fields';
    } elseif ($available_seats < 0) {
        $error = 'Total seats cannot be less than already booked seats (' . $booked_seats . ')';
    } else {
        $stmt = $conn->prepare("UPDATE events SET title=?, description=?, event_date=?, event_time=?, location=?, category=?, total_seats=?, available_seats=?, image=? WHERE id=?");
        $stmt->bind_param("ssssssissi", $title, $description, $event_date, $event_time, $location, $category, $total_seats, $available_seats, $image_name, $event_id);
        
        if ($stmt->execute()) {
            $success = 'Event updated successfully!';
            header("refresh:2;url=view-events.php");
        } else {
            $error = 'Failed to update event. Please try again.';
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
    <title>Edit Event</title>
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
                        <a class="nav-link active" href="view-events.php">
                            <i class="fas fa-calendar"></i> View Events
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
                <h2 class="mb-4">Edit Event</h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Event Title *</label>
                                    <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Category *</label>
                                    <select class="form-control" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="Conference" <?php echo $event['category'] == 'Conference' ? 'selected' : ''; ?>>Conference</option>
                                        <option value="Workshop" <?php echo $event['category'] == 'Workshop' ? 'selected' : ''; ?>>Workshop</option>
                                        <option value="Seminar" <?php echo $event['category'] == 'Seminar' ? 'selected' : ''; ?>>Seminar</option>
                                        <option value="Concert" <?php echo $event['category'] == 'Concert' ? 'selected' : ''; ?>>Concert</option>
                                        <option value="Sports" <?php echo $event['category'] == 'Sports' ? 'selected' : ''; ?>>Sports</option>
                                        <option value="Exhibition" <?php echo $event['category'] == 'Exhibition' ? 'selected' : ''; ?>>Exhibition</option>
                                        <option value="Other" <?php echo $event['category'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($event['description']); ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Event Date *</label>
                                    <input type="date" class="form-control" name="event_date" value="<?php echo $event['event_date']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Event Time *</label>
                                    <input type="time" class="form-control" name="event_time" value="<?php echo $event['event_time']; ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Location *</label>
                                    <input type="text" class="form-control" name="location" value="<?php echo htmlspecialchars($event['location']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Total Seats *</label>
                                    <input type="number" class="form-control" name="total_seats" value="<?php echo $event['total_seats']; ?>" min="<?php echo $event['total_seats'] - $event['available_seats']; ?>" required>
                                    <small class="text-muted">Booked: <?php echo $event['total_seats'] - $event['available_seats']; ?> seats</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Event Image</label>
                                <?php if($event['image']): ?>
                                    <div class="mb-2">
                                        <img src="../uploads/<?php echo htmlspecialchars($event['image']); ?>" class="image-preview" style="display:block;" alt="Current Image">
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" name="image" accept="image/*" id="imageInput">
                                <img id="imagePreview" class="image-preview" src="" alt="Preview">
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Event
                                </button>
                                <a href="view-events.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>