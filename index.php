<?php
/**
 * Home page (landing page).
 *
 * Sections: hero with a quick search box, live statistics, popular
 * subject shortcuts, the latest tuition posts, how the site works,
 * why use it, and a call to action.
 */
require_once __DIR__ . '/includes/functions.php';

// ---- live statistics for the hero and the counter band -------------
$stats = $conn->query(
    'SELECT
        (SELECT COUNT(*) FROM tuition_posts WHERE status = "open")  AS open_posts,
        (SELECT COUNT(*) FROM tuition_posts)                        AS total_posts,
        (SELECT COUNT(*) FROM users WHERE role = "tutor")           AS tutors,
        (SELECT COUNT(DISTINCT area) FROM tuition_posts)            AS areas'
)->fetch_assoc();

// ---- latest six open posts -----------------------------------------
$stmt = $conn->prepare(
    'SELECT p.post_id, p.title, p.class_level, p.subject, p.area, p.salary,
            p.days_per_week, p.medium, p.created_at, u.full_name
     FROM tuition_posts p
     JOIN users u ON u.user_id = p.posted_by
     WHERE p.status = "open"
     ORDER BY p.created_at DESC
     LIMIT 6'
);
$stmt->execute();
$recent = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ---- subjects that actually have posts, for the shortcut chips -----
$popular = $conn->query(
    'SELECT subject, COUNT(*) AS total
     FROM tuition_posts
     WHERE status = "open"
     GROUP BY subject
     ORDER BY total DESC, subject ASC
     LIMIT 8'
)->fetch_all(MYSQLI_ASSOC);

$page_title = 'Home';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ============ HERO ============ -->
<section class="hero">
    <div class="container hero-inner">
        <div class="hero-text">
            <p class="eyebrow">Tuition board for Bangladesh</p>
            <h1>Find the right tuition.<br>Find the right tutor.</h1>
            <p class="lead">
                Guardians and students post the tuition they need. Tutors search by
                subject, class and area, then contact the poster directly &mdash;
                no middleman, no fee.
            </p>

            <form class="hero-search" method="get" action="browse.php">
                <div class="hero-search-field">
                    <label for="hero-keyword" class="sr-only">Subject or area</label>
                    <input type="text" id="hero-keyword" name="keyword"
                           placeholder="Try &quot;Mathematics&quot; or &quot;Mirpur&quot;">
                </div>
                <div class="hero-search-field">
                    <label for="hero-class" class="sr-only">Class</label>
                    <select id="hero-class" name="class_level">
                        <option value="">Any class</option>
                        <?php foreach (CLASS_LEVELS as $option): ?>
                            <option value="<?= e($option) ?>"><?= e($option) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Search tuitions</button>
            </form>

            <?php if ($popular): ?>
                <div class="chip-row">
                    <span class="muted">Popular:</span>
                    <?php foreach ($popular as $row): ?>
                        <a class="chip" href="browse.php?subject=<?= urlencode($row['subject']) ?>">
                            <?= e($row['subject']) ?> <span class="chip-count"><?= (int) $row['total'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <aside class="hero-panel">
            <h2>Why post here?</h2>
            <ul class="tick-list">
                <li>Your post reaches tutors searching in your own area</li>
                <li>You decide the salary, days and medium</li>
                <li>Contact details stay hidden from anonymous visitors</li>
                <li>Close the post with one click when a tutor is found</li>
            </ul>
            <?php if (is_logged_in()): ?>
                <a class="btn btn-primary btn-block" href="post_create.php">Post a tuition now</a>
            <?php else: ?>
                <a class="btn btn-primary btn-block" href="register.php">Create a free account</a>
                <p class="muted center">Already a member? <a href="login.php">Log in</a></p>
            <?php endif; ?>
        </aside>
    </div>
</section>

<!-- ============ STATS BAND ============ -->
<section class="stats-band">
    <div class="container stats-inner">
        <div>
            <span class="stat-value"><?= (int) $stats['open_posts'] ?></span>
            <span class="stat-label">Open tuitions</span>
        </div>
        <div>
            <span class="stat-value"><?= (int) $stats['total_posts'] ?></span>
            <span class="stat-label">Total posts</span>
        </div>
        <div>
            <span class="stat-value"><?= (int) $stats['tutors'] ?></span>
            <span class="stat-label">Registered tutors</span>
        </div>
        <div>
            <span class="stat-value"><?= (int) $stats['areas'] ?></span>
            <span class="stat-label">Areas covered</span>
        </div>
    </div>
</section>

<!-- ============ LATEST POSTS ============ -->
<section class="container section">
    <div class="section-head">
        <div>
            <h2>Latest tuitions</h2>
            <p class="muted">The most recent posts from guardians and students.</p>
        </div>
        <a class="btn" href="browse.php">View all &rarr;</a>
    </div>

    <?php if (!$recent): ?>
        <p class="empty">No tuition has been posted yet. Be the first to post one.</p>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($recent as $post): ?>
                <article class="card post-card">
                    <div class="post-card-top">
                        <span class="tag"><?= e($post['subject']) ?></span>
                        <span class="salary"><?= number_format((int) $post['salary']) ?> <small>BDT/mo</small></span>
                    </div>

                    <h3><a href="post_details.php?id=<?= (int) $post['post_id'] ?>"><?= e($post['title']) ?></a></h3>

                    <ul class="meta">
                        <li><strong>Class:</strong> <?= e($post['class_level']) ?></li>
                        <li><strong>Area:</strong> <?= e($post['area']) ?></li>
                        <li><strong>Days:</strong> <?= (int) $post['days_per_week'] ?> per week</li>
                        <li><strong>Medium:</strong> <?= e($post['medium']) ?></li>
                    </ul>

                    <div class="post-card-foot">
                        <span class="muted">by <?= e($post['full_name']) ?></span>
                        <a class="btn btn-small" href="post_details.php?id=<?= (int) $post['post_id'] ?>">Details</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- ============ HOW IT WORKS ============ -->
<section class="section-alt">
    <div class="container section">
        <div class="section-head center-head">
            <h2>How it works</h2>
            <p class="muted">Three steps, whether you are looking for a tutor or for a tuition.</p>
        </div>

        <div class="step-grid">
            <article class="step">
                <span class="step-number">1</span>
                <h3>Create an account</h3>
                <p>Register in a few seconds as a guardian, a student or a tutor. Your phone number stays private until someone logs in.</p>
            </article>
            <article class="step">
                <span class="step-number">2</span>
                <h3>Post or search</h3>
                <p>Guardians describe the tuition &mdash; class, subject, area, salary and days. Tutors filter the board to find what fits them.</p>
            </article>
            <article class="step">
                <span class="step-number">3</span>
                <h3>Contact and start</h3>
                <p>Talk directly over phone or email, agree on the schedule, and close the post once the tuition is filled.</p>
            </article>
        </div>
    </div>
</section>

<!-- ============ FEATURES ============ -->
<section class="container section">
    <div class="section-head center-head">
        <h2>Made for both sides</h2>
    </div>

    <div class="feature-grid">
        <article class="card feature">
            <h3>Filter that actually narrows</h3>
            <p>Search by keyword and stack filters for class, subject, medium and a maximum salary, so you only see tuitions worth your time.</p>
        </article>
        <article class="card feature">
            <h3>Contact details protected</h3>
            <p>Phone numbers and emails are shown only to logged-in members, so posts do not turn into a public contact list.</p>
        </article>
        <article class="card feature">
            <h3>You control your post</h3>
            <p>Edit the salary, change the days, or mark the tuition closed the moment you find the right tutor. Nobody else can touch your post.</p>
        </article>
        <article class="card feature">
            <h3>Built on secure foundations</h3>
            <p>Hashed passwords, prepared statements, escaped output and CSRF protected forms &mdash; security handled properly, not as an afterthought.</p>
        </article>
    </div>
</section>

<!-- ============ CALL TO ACTION ============ -->
<section class="cta">
    <div class="container cta-inner">
        <div>
            <h2>Have a tuition to fill?</h2>
            <p>Post it in under a minute and let the right tutor come to you.</p>
        </div>
        <div class="cta-actions">
            <?php if (is_logged_in()): ?>
                <a class="btn btn-light" href="post_create.php">Post a tuition</a>
                <a class="btn btn-ghost" href="browse.php">Browse the board</a>
            <?php else: ?>
                <a class="btn btn-light" href="register.php">Get started free</a>
                <a class="btn btn-ghost" href="browse.php">Browse the board</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
