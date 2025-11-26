<?php
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $package_name = trim($_POST['package_name']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    
    // Handle image upload
    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $image_name = uniqid() . '.' . $ext;
            $upload_path = '../uploads/' . $image_name;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_name = '';
            }
        }
    }
    
    if (empty($package_name) || empty($category) || $price <= 0) {
        $error = 'Please fill all required fields with valid data';
    } else {
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("INSERT INTO decoration_packages (package_name, category, description, price, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssds", $package_name, $category, $description, $price, $image_name);
        
        if ($stmt->execute()) {
            $success = 'Decoration package added successfully!';
            header("refresh:2;url=view-packages.php");
        } else {
            $error = 'Failed to add package. Please try again.';
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Decoration Package</title>
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
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="add-package.php">
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
                <h2 class="mb-4">Add New Decoration Package</h2>

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
                                    <input type="text" class="form-control" name="package_name" placeholder="e.g., Wedding Stage Decoration Premium" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Category *</label>
                                    <select class="form-control" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="Wedding">Wedding</option>
                                        <option value="Birthday">Birthday</option>
                                        <option value="Engagement">Engagement</option>
                                        <option value="Anniversary">Anniversary</option>
                                        <option value="Baby Shower">Baby Shower</option>
                                        <option value="Housewarming">Housewarming</option>
                                        <option value="Corporate Event">Corporate Event</option>
                                        <option value="Reception">Reception</option>
                                        <option value="Mehendi/Sangeet">Mehendi/Sangeet</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description *</label>
                                <textarea class="form-control" name="description" rows="6" placeholder="Describe what's included in this package..." required></textarea>
                                <small class="text-muted">List all items and services included in this decoration package</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Price (₹) *</label>
                                    <input type="number" class="form-control" name="price" min="0" step="0.01" placeholder="e.g., 15000" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Package Image</label>
                                    <input type="file" class="form-control" name="image" accept="image/*" id="imageInput">
                                    <img id="imagePreview" class="image-preview" src="" alt="Preview">
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Package
                                </button>
                                <a href="view-packages.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Example Section -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Example Package</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Package Name:</strong> Wedding Stage Decoration - Premium Package</p>
                        <p><strong>Category:</strong> Wedding</p>
                        <p><strong>Description:</strong></p>
                        <pre class="bg-light p-3">Book our premium wedding stage decoration service for your special day! 

Package Includes:
✓ Grand Stage Setup with Floral Backdrop
✓ Bride & Groom Seating Arrangement
✓ Fresh Flower Decoration (Roses, Orchids, Lilies)
✓ LED Lighting & Fairy Lights
✓ Side Pillars & Draping
✓ Stage Carpet & Walkway
✓ 4-Hour Setup Time
✓ Professional Decoration Team

After booking, our team will contact you to:
- Confirm your wedding date & venue
- Discuss color preferences & theme
- Schedule site visit
- Finalize decoration design</pre>
                        <p><strong>Price:</strong> ₹25,000</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image preview
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