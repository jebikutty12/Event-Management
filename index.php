<?php
require_once 'config/database.php';
// session_start();
$conn = getDBConnection();

// Fetch active decoration packages
$sql = "SELECT * FROM decoration_packages WHERE status = 'active' ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Event Decoration Services</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f9f9f9;
      font-family: 'Poppins', sans-serif;
    }

    /* Navbar */
    .navbar {
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    /* Hero Section */
    .hero-section {
      background: url('assets/images/event-bg.jpeg') center center/cover no-repeat;
      color: white;
      text-align: center;
      position: relative;
      padding: 130px 0;
    }

    .hero-section::before {
      content: "";
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.6);
    }

    .hero-section .container {
      position: relative;
      z-index: 2;
    }

    .hero-section h1 {
      font-size: 3.2rem;
      font-weight: 700;
      letter-spacing: 1px;
    }

    .hero-section p.lead {
      font-size: 1.2rem;
    }

    .hero-section .btn {
      font-size: 1rem;
      padding: 12px 30px;
      font-weight: 600;
      border-radius: 50px;
    }

    /* Packages */
    .event-card {
      border: none;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .event-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    }

    .event-card img {
      height: 220px;
      object-fit: cover;
    }

    .btn-primary {
      background: linear-gradient(90deg, #ff512f, #dd2476);
      border: none;
    }

    .btn-primary:hover {
      background: linear-gradient(90deg, #dd2476, #ff512f);
    }

    /* Services */
    .services-section {
      background-color: #fff;
      padding: 80px 0;
    }

    .service-box {
      text-align: center;
      padding: 30px;
      border-radius: 12px;
      transition: all 0.3s ease;
      background: #fefefe;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .service-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }

    .service-box i {
      font-size: 40px;
      color: #ff512f;
      margin-bottom: 15px;
    }

    /* Testimonials */
    .testimonial-section {
      background: linear-gradient(135deg, #ff512f, #dd2476);
      color: white;
      padding: 80px 0;
    }

    .testimonial {
      background: rgba(255,255,255,0.1);
      border-radius: 12px;
      padding: 25px;
      margin: 10px;
    }

    /* Footer */
    footer {
      background: #111;
      color: #aaa;
      padding: 40px 0;
    }

    footer a {
      color: #ff512f;
      text-decoration: none;
    }

    footer a:hover {
      color: white;
    }

    @media (max-width:768px){
      .hero-section h1 { font-size: 2.2rem; }
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-palette me-2 text-warning"></i>Event Decoration</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="nav-link" href="user/my-bookings.php"><i class="fas fa-calendar-check"></i> My Bookings</a></li>
          <li class="nav-item"><a class="nav-link" href="user/logout.php"><i class="fas fa-sign-out-alt"></i> Logout (<?= htmlspecialchars($_SESSION['user_name']); ?>)</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="user/login.php"><i class="fas fa-user"></i> Login</a></li>
          <li class="nav-item"><a class="nav-link" href="user/register.php"><i class="fas fa-user-plus"></i> Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Hero -->
<section class="hero-section">
  <div class="container">
    <h1>Transform Your Events into Magical Experiences</h1>
    <p class="lead">Luxury Decorations for Weddings, Birthdays, Corporate Functions & More</p>
    <a href="#packages" class="btn btn-primary mt-3">Explore Packages <i class="fas fa-arrow-right"></i></a>
  </div>
</section>

<!-- Packages -->
<section id="packages" class="py-5">
  <div class="container">
    <h2 class="text-center mb-5 fw-bold text-uppercase">Our Decoration Packages</h2>
    <div class="row">
      <?php if ($result->num_rows > 0): ?>
        <?php while($package = $result->fetch_assoc()): ?>
          <div class="col-md-4 mb-4">
            <div class="card event-card h-100">
              <?php if($package['image']): ?>
                <img src="uploads/<?= htmlspecialchars($package['image']); ?>" alt="Package Image">
              <?php else: ?>
                <div class="bg-secondary d-flex align-items-center justify-content-center" style="height:220px;">
                  <i class="fas fa-image fa-3x text-white"></i>
                </div>
              <?php endif; ?>
              <div class="card-body">
                <span class="badge bg-info mb-2"><?= htmlspecialchars($package['category']); ?></span>
                <h5 class="card-title"><?= htmlspecialchars($package['package_name']); ?></h5>
                <p class="card-text"><?= substr(htmlspecialchars($package['description']), 0, 120) . '...'; ?></p>
                <div class="mt-3">
                  <h4 class="text-primary fw-bold">₹<?= number_format($package['price'], 2); ?></h4>
                </div>
                <a href="user/book-package.php?id=<?= $package['id']; ?>" class="btn btn-primary w-100 mt-3">Book Now</a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="col-12 text-center">
          <div class="alert alert-info"><i class="fas fa-info-circle"></i> No decoration packages available at the moment.</div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Services -->
<section class="services-section">
  <div class="container">
    <h2 class="text-center fw-bold mb-5 text-uppercase">Our Services</h2>
    <div class="row g-4">
      <div class="col-md-3">
        <div class="service-box">
          <i class="fas fa-ring"></i>
          <h5>Wedding Decor</h5>
          <p>Stunning wedding stages, floral designs, lighting, and venue theming for your dream day.</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="service-box">
          <i class="fas fa-birthday-cake"></i>
          <h5>Birthday Parties</h5>
          <p>Creative birthday setups with balloons, backdrops, themes, and personalized décor.</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="service-box">
          <i class="fas fa-building"></i>
          <h5>Corporate Events</h5>
          <p>Elegant setups for conferences, meetings, and brand events with professional lighting.</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="service-box">
          <i class="fas fa-heart"></i>
          <h5>Anniversaries</h5>
          <p>Romantic decoration setups with candles, lights, and flowers for your special moments.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Testimonials -->
<section class="testimonial-section text-center">
  <div class="container">
    <h2 class="fw-bold text-uppercase mb-4">What Our Clients Say</h2>
    <div class="row justify-content-center">
      <div class="col-md-4 testimonial">
        <p>"Absolutely beautiful setup! They made our wedding unforgettable. Highly recommend!"</p>
        <h6>- Priya Sharma</h6>
      </div>
      <div class="col-md-4 testimonial">
        <p>"Professional and creative team! The birthday decorations were amazing and on-time."</p>
        <h6>- Arjun Mehta</h6>
      </div>
      <div class="col-md-4 testimonial">
        <p>"Our corporate gala looked stunning thanks to their elegant décor and coordination."</p>
        <h6>- Ananya Gupta</h6>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer>
  <div class="container text-center">
    <p>&copy; 2025 Event Decoration Services. All rights reserved.</p>
    <p>Made with ❤️ by <a href="#">YourTeamName</a></p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
