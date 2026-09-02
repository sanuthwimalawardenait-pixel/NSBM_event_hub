    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="navbar-brand" style="color: #ffffff; margin-bottom: 1rem;">
                        <div class="brand-icon">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <span>NSBM <span style="color: #4ade80;">EventHub</span></span>
                    </div>
                    <p style="font-size: 0.9rem; line-height: 1.6; margin-bottom: 1.25rem;">
                        The official event planning and scheduling system for NSBM Green University. Empowering university clubs, societies, and students to discover, organize, and participate in academic and extracurricular experiences.
                    </p>
                    <div style="display: flex; gap: 0.75rem;">
                        <a href="https://facebook.com" target="_blank" class="btn btn-secondary btn-icon" style="background: #1e293b; color: #fff; border-color: #334155;"><i class="bi bi-facebook"></i></a>
                        <a href="https://instagram.com" target="_blank" class="btn btn-secondary btn-icon" style="background: #1e293b; color: #fff; border-color: #334155;"><i class="bi bi-instagram"></i></a>
                        <a href="https://linkedin.com" target="_blank" class="btn btn-secondary btn-icon" style="background: #1e293b; color: #fff; border-color: #334155;"><i class="bi bi-linkedin"></i></a>
                        <a href="https://youtube.com" target="_blank" class="btn btn-secondary btn-icon" style="background: #1e293b; color: #fff; border-color: #334155;"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>

                <div>
                    <h4 class="footer-title">Quick Navigation</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo $baseUrl; ?>index.php">Home</a></li>
                        <li><a href="<?php echo $baseUrl; ?>events.php">Browse All Events</a></li>
                        <li><a href="<?php echo $baseUrl; ?>announcements.php">Announcements</a></li>
                        <li><a href="<?php echo $baseUrl; ?>schedule.php">Personal Schedule</a></li>
                        <li><a href="<?php echo $baseUrl; ?>login.php">Portal Sign In</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-title">Faculties & Clubs</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo $baseUrl; ?>events.php?category=1">Faculty of Computing</a></li>
                        <li><a href="<?php echo $baseUrl; ?>events.php?category=2">Faculty of Business</a></li>
                        <li><a href="<?php echo $baseUrl; ?>events.php?category=6">Faculty of Engineering</a></li>
                        <li><a href="<?php echo $baseUrl; ?>events.php?category=3">NSBM Sports Council</a></li>
                        <li><a href="<?php echo $baseUrl; ?>events.php?category=4">Music & Cultural Circle</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-title">Campus Contact</h4>
                    <ul class="footer-links" style="font-size: 0.875rem;">
                        <li style="display: flex; gap: 0.5rem; align-items: flex-start;">
                            <i class="bi bi-geo-alt-fill" style="color: var(--primary-light); margin-top: 0.2rem;"></i>
                            <span>NSBM Green University, Mahenwaththa, Pitipana, Homagama, Sri Lanka</span>
                        </li>
                        <li style="display: flex; gap: 0.5rem; align-items: center;">
                            <i class="bi bi-telephone-fill" style="color: var(--primary-light);"></i>
                            <span>+94 11 544 5000</span>
                        </li>
                        <li style="display: flex; gap: 0.5rem; align-items: center;">
                            <i class="bi bi-envelope-fill" style="color: var(--primary-light);"></i>
                            <span>inquiries@nsbm.ac.lk</span>
                        </li>
                        <li style="display: flex; gap: 0.5rem; align-items: center;">
                            <i class="bi bi-clock-fill" style="color: var(--primary-light);"></i>
                            <span>Mon - Fri: 8:30 AM - 5:00 PM</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div>
                    &copy; <?php echo date('Y'); ?> NSBM Green University. All rights reserved. NSBM EventHub System.
                </div>
                <div style="display: flex; gap: 1.5rem;">
                    <a href="<?php echo $baseUrl; ?>login.php" style="color: #94a3b8;">Admin Login</a>
                    <a href="#" style="color: #94a3b8;">Privacy Policy</a>
                    <a href="#" style="color: #94a3b8;">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="<?php echo $baseUrl; ?>assets/js/main.js"></script>
</body>
</html>
