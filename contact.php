<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact The Ceylon Wedding Planner | Start Your Wedding Journey</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <meta name="description" content="Get in touch with The Ceylon Wedding Planner today to begin planning your dream wedding in Sri Lanka. We’re here to help you every step of the way." />
</head>
<body>
   <header>
        <div class="header-container">
            <div class="logo">
                <img src="image2.jpg" alt="Emerald Weddings">
            </div>
            <nav>
                <a href="index.html">Home</a>
                <a href="services.html">Services</a>
                <a href="gallery.html">Gallery</a>
                <a href="packages.php">Packages</a>
                <a href="contact.php" class="active">Contact</a>
                <a href="about.html">About Us</a>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            </nav>
        </div>
    </header>

<div style="
    background: linear-gradient(rgba(2, 21, 58, 0.5), rgba(2, 15, 42, 0.5)), url('assets/image/banner.jpg');
    background-position: center center;
    background-repeat: no-repeat;
    background-size: cover;
    background-attachment: fixed;
    padding: 150px 0 50px 0;
    border-radius: 10px;
    width: 100%;
    height: 70vh;
    color: white;
    
    text-align: center;
    font-size: 2.5em;
    font-weight: bold;
    text-shadow: 2px 2px 5px #000;">Contact Emerald Weddings
</div>


    <section class="contact-section">
        <div class="container">
            <h2 class="section-title">Get in Touch</h2>

            <!-- Thank You Message -->
            <p id="thankYouMessage" class="success-message" style="display: none;">
                Thank you! Your message has been sent successfully.
            </p>

            <form id="contactForm" method="POST" action="contact-submit.php">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" required placeholder="Your full name">
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required placeholder="example@email.com">
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" required pattern="^[0-9]{10}$" placeholder="07XXXXXXXX">
                </div>

                <div class="form-group">
                    <label for="message">Your Message *</label>
                    <textarea id="message" name="message" rows="5" required placeholder="How can we help you?"></textarea>
                </div>

                <button type="submit" class="btn send-btn">Send Message</button>
            </form>
        </div>
    </section>

    <footer>
        <div class="">
            <div class="footer-container">
                <div class="footer-column">
                    <h3>Emerald Weddings</h3>
                    <p>Premium wedding planning services creating unforgettable celebrations since 2010.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="index.html">Home</a></li>
                        <li><a href="services.html">Services</a></li>
                        <li><a href="gallery.html">Gallery</a></li>
                        <li><a href="packages.php">Packages</a></li>
                        <li><a href="contact.html">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contact Us</h3>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Wedding Lane, Colombo 05</p>
                    <p><i class="fas fa-phone"></i> +94 76 123 4567</p>
                    <p><i class="fas fa-envelope"></i> info@emeraldweddings.lk</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Emerald Weddings. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Thank You JS -->
    <script>
        document.getElementById("contactForm").addEventListener("submit", function(e) {
            e.preventDefault();
            
            // Normally submit to PHP backend here
            const form = this;

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            })
            .then(response => {
                if (response.ok) {
                    document.getElementById("thankYouMessage").style.display = "block";
                    form.reset();
                } else {
                    alert("Something went wrong. Please try again later.");
                }
            })
            .catch(() => {
                alert("Submission failed. Please check your internet or try again.");
            });
        });
    </script>
</body>
</html>
