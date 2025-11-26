<?php
require_once '../config/database.php';

if (!isset($_SESSION['admin_id']) || !isset($_GET['id'])) {
    header('Location: view-packages.php');
    exit();
}

$package_id = (int)$_GET['id'];
$conn = getDBConnection();

$error = '';
$success = '';

// Get package details
$stmt = $conn->prepare("SELECT * FROM decoration_packages WHERE id = ?");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: view-packages.php');
    exit();
}

$package = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $package_name = trim($_POST['package_name']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $status = $_POST['status'];
    
    $image_name = $package['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            if ($image_name && file_exists('../uploads/' . $image_name)) {
                unlink('../uploads/' . $image_name);
            }
            
            $image_name = uniqid() . '.' . $ext;
            $upload_path = '../uploads/' . $image_name;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_name = $package['image'];
            }
        }
    }
    
    if (empty($package_name) || empty($category) || $price <= 0) {
        $error = 'Please fill all required fields';
    } else {
        $stmt = $conn->prepare("UPDATE decoration_packages SET package_name=?, category=?, description=?, price=?, status=?, image=? WHERE id=?");
        $stmt->bind_param("sssdssi", $package_name, $category, $description, $price, $status, $image_name, $package_id);
        
        if ($stmt->execute()) {
            $success = 'Package updated successfully!';
            header("refresh:2;url=view-packages.php");
        } else {
            $error = 'Failed to update package.';
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
    <title>Edit Package</title>
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
                <h2 class="mb-4">Edit Decoration Package</h2>

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
                                    <label class="form-label">Package Name *</label>
                                    <input type="text" class="form-control" name="package_name" value="<?php echo htmlspecialchars($package['package_name']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Category *</label>
                                    <select class="form-control" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="Wedding" <?php echo $package['category'] == 'Wedding' ? 'selected' : ''; ?>>Wedding</option>
                                        <option value="Birthday" <?php echo $package['category'] == 'Birthday' ? 'selected' : ''; ?>>Birthday</option>
                                        <option value="Engagement" <?php echo $package['category'] == 'Engagement' ? 'selected' : ''; ?>>Engagement</option>
                                        <option value="Anniversary" <?php echo $package['category'] == 'Anniversary' ? 'selected' : ''; ?>>Anniversary</option>
                                        <option value="Baby Shower" <?php echo $package['category'] == 'Baby Shower' ? 'selected' : ''; ?>>Baby Shower</option>
                                        <option value="Housewarming" <?php echo $package['category'] == 'Housewarming' ? 'selected' : ''; ?>>Housewarming</option>
                                        <option value="Corporate Event" <?php echo $package['category'] == 'Corporate Event' ? 'selected' : ''; ?>>Corporate Event</option>
                                        <option value="Reception" <?php echo $package['category'] == 'Reception' ? 'selected' : ''; ?>>Reception</option>
                                        <option value="Mehendi/Sangeet" <?php echo $package['category'] == 'Mehendi/Sangeet' ? 'selected' : ''; ?>>Mehendi/Sangeet</option>
                                        <option value="Other" <?php echo $package['category'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description *</label>
                                <textarea class="form-control" name="description" rows="6" required><?php echo htmlspecialchars($package['description']); ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Price (₹) *</label>
                                    <input type="number" class="form-control" name="price" value="<?php echo $package['price']; ?>" min="0" step="0.01" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Status *</label>
                                    <select class="form-control" name="status" required>
                                        <option value="active" <?php echo $package['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $package['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Package Image</label>
                                    <?php if($package['image']): ?>
                                        <div class="mb-2">
                                            <img src="../uploads/<?php echo htmlspecialchars($package['image']); ?>" class="image-preview" style="display:block;" alt="Current Image">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" name="image" accept="image/*" id="imageInput">
                                    <img id="imagePreview" class="image-preview" src="" alt="Preview">
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Package
                                </button>
                                <a href="view-packages.php" class="btn btn-secondary">Cancel</a>
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