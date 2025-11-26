<?php
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$conn = getDBConnection();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $package_id = (int)$_GET['delete'];
    
    $stmt = $conn->prepare("SELECT image FROM decoration_packages WHERE id = ?");
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $package = $result->fetch_assoc();
        
        if ($package['image'] && file_exists('../uploads/' . $package['image'])) {
            unlink('../uploads/' . $package['image']);
        }
        
        $stmt = $conn->prepare("DELETE FROM decoration_packages WHERE id = ?");
        $stmt->bind_param("i", $package_id);
        $stmt->execute();
        
        $message = 'Package deleted successfully!';
    }
}

// Fetch all packages
$result = $conn->query("SELECT * FROM decoration_packages ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Packages</title>
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
                        <a class="nav-link active" href="view-packages.php">
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

            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>All Decoration Packages</h2>
                    <a href="add-package.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Package
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
                                            <th>Image</th>
                                            <th>Package Name</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($package = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $package['id']; ?></td>
                                                <td>
                                                    <?php if($package['image']): ?>
                                                        <img src="../uploads/<?php echo $package['image']; ?>" style="width: 50px; height: 50px; object-fit: cover;" class="rounded">
                                                    <?php else: ?>
                                                        <i class="fas fa-image fa-2x text-muted"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td><strong><?php echo htmlspecialchars($package['package_name']); ?></strong></td>
                                                <td><span class="badge bg-info"><?php echo htmlspecialchars($package['category']); ?></span></td>
                                                <td><strong>₹<?php echo number_format($package['price'], 2); ?></strong></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $package['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($package['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="table-actions">
                                                    <a href="edit-package.php?id=<?php echo $package['id']; ?>" 
                                                       class="btn btn-sm btn-warning" 
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?delete=<?php echo $package['id']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       title="Delete"
                                                       onclick="return confirm('Are you sure you want to delete this package?')">
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
                                <i class="fas fa-info-circle"></i> No packages found. 
                                <a href="add-package.php" class="alert-link">Add your first package</a>
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