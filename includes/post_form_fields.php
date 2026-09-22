<?php
/**
 * The shared input fields of the create and edit forms.
 * The including page must provide an $old array with the values.
 */
?>
<label for="title">Title</label>
<input type="text" id="title" name="title" value="<?= e($old['title']) ?>"
       placeholder="Math tutor needed for Class 9" required>

<label for="class_level">Class level</label>
<select id="class_level" name="class_level" required>
    <option value="">-- select --</option>
    <?php foreach (CLASS_LEVELS as $option): ?>
        <option value="<?= e($option) ?>" <?= $old['class_level'] === $option ? 'selected' : '' ?>><?= e($option) ?></option>
    <?php endforeach; ?>
</select>

<label for="subject">Subject</label>
<select id="subject" name="subject" required>
    <option value="">-- select --</option>
    <?php foreach (SUBJECTS as $option): ?>
        <option value="<?= e($option) ?>" <?= $old['subject'] === $option ? 'selected' : '' ?>><?= e($option) ?></option>
    <?php endforeach; ?>
</select>

<label for="area">Area</label>
<input type="text" id="area" name="area" value="<?= e($old['area']) ?>"
       placeholder="Mirpur, Dhaka" required>

<label for="salary">Salary (BDT per month)</label>
<input type="number" id="salary" name="salary" min="500" max="100000" step="100"
       value="<?= e($old['salary']) ?>" required>

<label for="days_per_week">Days per week</label>
<input type="number" id="days_per_week" name="days_per_week" min="1" max="7"
       value="<?= e($old['days_per_week']) ?>" required>

<label for="medium">Medium</label>
<select id="medium" name="medium" required>
    <?php foreach (MEDIUMS as $option): ?>
        <option value="<?= e($option) ?>" <?= $old['medium'] === $option ? 'selected' : '' ?>><?= e($option) ?></option>
    <?php endforeach; ?>
</select>

<label for="description">Description (optional)</label>
<textarea id="description" name="description" rows="4"
          placeholder="Timing, expectations, tutor preference..."><?= e($old['description']) ?></textarea>
