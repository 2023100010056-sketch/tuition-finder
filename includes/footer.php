</main>

<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <a class="logo footer-logo" href="index.php">
                <span class="logo-mark" aria-hidden="true">TF</span>
                <span class="logo-text">Tuition<strong>Finder</strong></span>
            </a>
            <p class="muted">
                A tuition board for guardians, students and tutors.
                Post what you need, find who you need.
            </p>
        </div>

        <div>
            <h4>Explore</h4>
            <ul class="footer-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="browse.php">Browse tuitions</a></li>
                <li><a href="post_create.php">Post a tuition</a></li>
            </ul>
        </div>

        <div>
            <h4>Popular subjects</h4>
            <ul class="footer-links">
                <li><a href="browse.php?subject=Mathematics">Mathematics</a></li>
                <li><a href="browse.php?subject=English">English</a></li>
                <li><a href="browse.php?subject=Physics">Physics</a></li>
                <li><a href="browse.php?subject=ICT">ICT</a></li>
            </ul>
        </div>

        <div>
            <h4>About</h4>
            <p class="muted">
                Built as a group project for CSE 471 &ndash; Web and Internet Programming,
                using HTML, CSS, JavaScript, PHP and MySQL.
            </p>
        </div>
    </div>

    <div class="container footer-bottom">
        <p class="muted">&copy; <?= date('Y') ?> Tuition Finder. Academic project &mdash; not a commercial service.</p>
    </div>
</footer>

<script src="assets/js/validation.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
