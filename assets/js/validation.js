/* ============================================================
   Tuition Finder - client side form validation
   ------------------------------------------------------------
   This gives the user instant feedback. It is only a convenience:
   every rule here is checked again on the server in PHP, because
   JavaScript can be disabled or bypassed.
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {
    var forms = document.querySelectorAll('form[data-validate]');

    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!validateForm(form)) {
                event.preventDefault();
            }
        });

        // Clear the error as soon as the user edits the field again.
        form.querySelectorAll('input, select, textarea').forEach(function (field) {
            field.addEventListener('input', function () {
                clearError(field);
            });
        });
    });

    // Live character counter for the description box.
    var description = document.getElementById('description');
    if (description) {
        var counter = document.createElement('small');
        counter.className = 'muted';
        description.parentNode.insertBefore(counter, description.nextSibling);

        var update = function () {
            counter.textContent = description.value.length + ' / 1000 characters';
        };
        description.addEventListener('input', update);
        update();
    }
});

function showError(field, message) {
    clearError(field);
    field.classList.add('invalid');
    var note = document.createElement('div');
    note.className = 'field-error';
    note.textContent = message;
    field.parentNode.insertBefore(note, field.nextSibling);
}

function clearError(field) {
    field.classList.remove('invalid');
    var next = field.nextSibling;
    if (next && next.className === 'field-error') {
        next.parentNode.removeChild(next);
    }
}

function validateForm(form) {
    var ok = true;
    var type = form.getAttribute('data-validate');

    var check = function (id, test, message) {
        var field = form.querySelector('#' + id);
        if (!field) { return; }
        if (!test(field.value.trim())) {
            showError(field, message);
            ok = false;
        }
    };

    if (type === 'register') {
        check('full_name', function (v) { return v.length >= 3; },
              'Full name must be at least 3 characters.');
        check('email', function (v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); },
              'Please enter a valid email address.');
        check('phone', function (v) { return /^01[3-9]\d{8}$/.test(v); },
              'Enter an 11 digit number starting with 01.');
        check('password', function (v) { return v.length >= 8 && /[A-Za-z]/.test(v) && /\d/.test(v); },
              'At least 8 characters with letters and digits.');

        var pass = form.querySelector('#password');
        var conf = form.querySelector('#confirm_password');
        if (pass && conf && pass.value !== conf.value) {
            showError(conf, 'Passwords do not match.');
            ok = false;
        }
    }

    if (type === 'login') {
        check('email', function (v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); },
              'Please enter a valid email address.');
        check('password', function (v) { return v.length > 0; },
              'Password is required.');
    }

    if (type === 'post') {
        check('title', function (v) { return v.length >= 5; },
              'Title must be at least 5 characters.');
        check('class_level', function (v) { return v !== ''; }, 'Please select a class level.');
        check('subject', function (v) { return v !== ''; }, 'Please select a subject.');
        check('area', function (v) { return v.length >= 2; }, 'Please enter the area.');
        check('salary', function (v) {
            var n = parseInt(v, 10);
            return /^\d+$/.test(v) && n >= 500 && n <= 100000;
        }, 'Salary must be between 500 and 100000.');
        check('days_per_week', function (v) {
            var n = parseInt(v, 10);
            return /^\d+$/.test(v) && n >= 1 && n <= 7;
        }, 'Days per week must be between 1 and 7.');
        check('description', function (v) { return v.length <= 1000; },
              'Description cannot exceed 1000 characters.');
    }

    return ok;
}
