<?php
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $event_id = (int)$_GET['delete'];
    
    // Get image name
    $stmt = $conn->prepare("SELECT image FROM events WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
        
        // Delete image file
        if ($event['image'] && file_exists('../uploads/' . $event['image'])) {
            unlink('../uploads/' . $event['image']);
        }
        
        // Delete event
        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        
        $message = 'Event deleted successfully!';
    }
}

// Fetch all events
$result = $conn->query("SELECT * FROM events ORDER BY event_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Events</title>
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>All Events</h2>
                    <a href="add-event.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Event
                    </a>
                </div>

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
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Date & Time</th>
                                            <th>Location</th>
                                            <th>Seats</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($event = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $event['id']; ?></td>
                                                <td><strong><?php echo htmlspecialchars($event['title']); ?></strong></td>
                                                <td><span class="badge bg-info"><?php echo htmlspecialchars($event['category']); ?></span></td>
                                                <td>
                                                    <?php echo date('M d, Y', strtotime($event['event_date'])); ?><br>
                                                    <small class="text-muted"><?php echo date('h:i A', strtotime($event['event_time'])); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($event['location']); ?></td>
                                                <td>
                                                    <?php echo $event['available_seats']; ?> / <?php echo $event['total_seats']; ?>
                                                </td>
                                                <td class="table-actions">
                                                    <a href="edit-event.php?id=<?php echo $event['id']; ?>" 
                                                       class="btn btn-sm btn-warning" 
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?delete=<?php echo $event['id']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       title="Delete"
                                                       onclick="return confirm('Are you sure you want to delete this event?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No events found. 
                                <a href="add-event.php" class="alert-link">Add your first event</a>
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
<?php $conn->close(); ?>