<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'includes/header.php';

// Get available services
$stmt = $pdo->query("SELECT * FROM Services ORDER BY name ASC");
$services = $stmt->fetchAll();
?>

<div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h4 class="mb-0">
                    <i class="fa-solid fa-calendar-plus me-2"></i> Book an Appointment
                </h4>
            </div>
            <div class="card-body p-4">
                <!-- Success/Error Messages -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show fw-bold">
                        <i class="fa-solid fa-circle-check me-2"></i>
                        <?= htmlspecialchars($_GET['success']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show fw-bold">
                        <i class="fa-solid fa-circle-exclamation me-2"></i>
                        <?= htmlspecialchars($_GET['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Booking Instructions -->
                <div class="alert alert-info border-0 mb-4">
                    <h6 class="alert-heading fw-bold">
                        <i class="fas fa-info-circle me-2"></i>How to Book
                    </h6>
                    <ol class="mb-0 ps-3">
                        <li>Select the service you need</li>
                        <li>Choose your preferred date and time</li>
                        <li>Click "Confirm Booking" to complete</li>
                        <li>You'll receive a QR code to check in on the day</li>
                    </ol>
                </div>

                <!-- Booking Form -->
                <form action="api/appointments/create.php" method="POST" id="bookingForm">
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-concierge-bell me-2 text-primary"></i>Select Service
                        </label>
                        <select name="service_id" class="form-select form-select-lg" required id="serviceSelect">
                            <option value="">-- Choose a Service --</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?= $service['id'] ?>"
                                    data-description="<?= htmlspecialchars($service['description']) ?>">
                                    <?= htmlspecialchars($service['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text" id="serviceDescription"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold">
                                <i class="far fa-calendar me-2 text-primary"></i>Date
                            </label>
                            <input type="date" name="date" class="form-control form-control-lg" required
                                min="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d', strtotime('+30 days')) ?>"
                                id="dateInput">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>You can book up to 30 days in advance
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold">
                                <i class="far fa-clock me-2 text-primary"></i>Time
                            </label>
                            <input type="time" name="time" class="form-control form-control-lg" required min="08:00"
                                max="17:00" id="timeInput">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>Office hours: 8:00 AM - 5:00 PM
                            </div>
                        </div>
                    </div>

                    <!-- Optional: Notes field -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="far fa-comment me-2 text-primary"></i>Additional Notes (Optional)
                        </label>
                        <textarea name="notes" class="form-control" rows="3"
                            placeholder="Any special requests or information?" maxlength="500"></textarea>
                        <div class="form-text">Maximum 500 characters</div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg fw-bold">
                            <i class="fa-solid fa-check me-2"></i> Confirm Booking
                        </button>
                        <a href="dashboard.php" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                    </div>
                </form>

                <!-- Recent Bookings Info -->
                <div class="mt-4 pt-4 border-top">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-history me-2 text-muted"></i>After Booking
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="fas fa-envelope fa-2x text-primary mb-2"></i>
                                <p class="small mb-0 fw-bold">Email Confirmation</p>
                                <p class="small text-muted mb-0">Instant notification</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="fas fa-qrcode fa-2x text-success mb-2"></i>
                                <p class="small mb-0 fw-bold">QR Code Generated</p>
                                <p class="small text-muted mb-0">For check-in</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="fas fa-bell fa-2x text-warning mb-2"></i>
                                <p class="small mb-0 fw-bold">Reminder Alert</p>
                                <p class="small text-muted mb-0">30 mins before</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Show service description when selected
    document.getElementById('serviceSelect').addEventListener('change', function () {
        const description = this.options[this.selectedIndex].dataset.description;
        const descDiv = document.getElementById('serviceDescription');

        if (description) {
            descDiv.innerHTML = '<i class="fas fa-info-circle text-primary me-1"></i>' + description;
        } else {
            descDiv.innerHTML = '';
        }
    });

    // Prevent selecting past times for today
    document.getElementById('dateInput').addEventListener('change', function () {
        const selectedDate = new Date(this.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const timeInput = document.getElementById('timeInput');

        if (selectedDate.getTime() === today.getTime()) {
            // If today, set min time to current hour + 1
            const now = new Date();
            const minHour = now.getHours() + 1;
            const minTime = String(minHour).padStart(2, '0') + ':00';
            timeInput.min = minTime;
        } else {
            // For future dates, allow booking from 8 AM
            timeInput.min = '08:00';
        }
    });

    // Form validation
    document.getElementById('bookingForm').addEventListener('submit', function (e) {
        const date = document.getElementById('dateInput').value;
        const time = document.getElementById('timeInput').value;

        if (!date || !time) {
            e.preventDefault();
            alert('Please select both date and time');
            return false;
        }

        // Check if time is within office hours
        const selectedTime = time.replace(':', '');
        if (selectedTime < '0800' || selectedTime > '1700') {
            e.preventDefault();
            alert('Please select a time between 8:00 AM and 5:00 PM');
            return false;
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>