<?php

// Access the session variables from header.php
$login_message = isset($_SESSION['user']) ? '' : 'You must be logged in to use all features of this site.';
?>

</div><!-- Close content div -->
<footer class="site-footer">
    
        <div class="footer-section">
            <h3>About FitBlitz</h3>
            <p>FitBlitz is your ultimate fitness companion, helping you achieve your health and wellness goals through community support and expert guidance.</p>
        </div>

        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="about.php">About Us</a></li>
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="privacy.php">Privacy Policy</a></li>
                <li><a href="terms.php">Terms of Service</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h3>Connect With Us</h3>
            <div class="social-links">
                <a href="#" class="social-icon"><i class="fab fa-facebook"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
            </div>
        </div>

        <div class="footer-section">
            <h3>Contact Info</h3>
            <ul class="contact-info">
                <li><i class="fas fa-phone"></i> +1 234 567 8900</li>
                <li><i class="fas fa-envelope"></i> info@fitblitz.com</li>
                <li><i class="fas fa-location-dot"></i> 123 Fitness Street, Gym City, SP 12345</li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; 2024 FitBlitz. All rights reserved.</p>
        <!-- <p class="login-status"><?php echo $login_message; ?></p> -->
    </div>
</footer>
</body>
</html>