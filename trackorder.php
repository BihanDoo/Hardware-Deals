<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Track Your Order | Hardware Deals</title>
  <link rel="stylesheet" href="track.css" />
  <link rel="stylesheet" href="style.css">
  <style>
    body {
  margin: 0;
  font-family: 'Segoe UI', sans-serif;
  background: #0f1724;
  color: #fff;
}
.title-header-thing {
  background-color: #1e293b;
}
.footer {
  background-color: #1e293b;
}
.footer a {
  color: #ff6600;}
.footer a:hover {
  text-decoration: underline;
color: #3b82f6;}


  </style>
</head>
<body>
  <header class="title-header-thing">
    <div class="logo-title">
      <img src="logo.png" alt="Logo" class="logo">
      <h1 class="title" onclick="window.location.href='index.html'">Hardware Deals.lk</h1>
    </div>

    


  </header>

  <section class="track-section">
    <div class="track-card">
      <h2>Track Your Order</h2>
      <p>Enter your Order ID below to check the latest status.</p>

      <form id="trackForm">
        <input type="text" id="orderId" placeholder="Enter your Order ID" required />
        <button type="submit">Track</button>
      </form>

      <div id="result" class="result hidden">
        <h3>Order Status</h3>
        <ul class="progress">
          <li class="step done">Order Placed</li>
          <li class="step done">Processing</li>
          <li class="step active">Shipped</li>
          <li class="step">Delivered</li>
        </ul>

        <div class="status-card">
          <p><strong>Order ID:</strong> #HD20251008</p>
          <p><strong>Estimated Delivery:</strong> October 12, 2025</p>
          <p><strong>Current Status:</strong> In Transit</p>
        </div>
      </div>
    </div>
  </section>

  <footer class="footer" >
    <div class="footer-inside">
      <div class="footr2" >
        <h3>Hardware Deals.lk</h3>
        <p>Your trusted source for hardware tools and rentals in Sri Lanka.</p>
        <p>&copy; 2024 HardwareDeals.lk</p>
      </div>


      
      <div class="footr2">
        <h3>Contact Us</h3>
        <p>Email: <a href="mailto:info@hardwaredeals.lk">info@hardwaredeals.lk</a></p>
        <p>Phone: <a href="tel:+94111234567">011 1 1234567</a></p>
        <p><a href="about.html">About Us</a></p>
        <p><a href="terms.html">Terms & Conditions</a></p>
      </div>
    </div>
    
  </footer>

  <script>
    const form = document.getElementById('trackForm');
    const result = document.getElementById('result');

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      // Dummy logic – In real version, fetch status from backend
      result.classList.remove('hidden');
    });
  </script>
</body>
</html>
