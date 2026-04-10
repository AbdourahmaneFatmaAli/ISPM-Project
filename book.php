<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'includes/header.php';

// Get available services
$stmt = $pdo->query("SELECT * FROM Services ORDER BY name ASC");
$services = $stmt->fetchAll();
?>

<div class="row min-vh-100 align-items-center justify-content-center animate__animated animate__fadeIn pb-5">
    <div class="col-lg-10 col-xl-8">
        <div class="card border-0 Modern-Glass-Card shadow-2xl overflow-hidden">
            <div class="row g-0">
                <!-- Left Sidebar: Info -->
                <div class="col-md-4 bg-primary bg-opacity-10 p-4 p-lg-5 text-center border-end border-white border-opacity-10 d-none d-md-block">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-4 shadow-lg" style="width: 70px; height: 70px;">
                        <i class="fa-solid fa-calendar-plus text-white fs-3"></i>
                    </div>
                    <h4 class="fw-bolder text-white mb-4">Book Service</h4>
                    <p class="text-muted small mb-5">Reserve your spot in the digital queue in three simple steps.</p>
                    
                    <div class="text-start mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary rounded-circle me-3 flex-shrink-0" style="width: 10px; height: 10px;"></div>
                            <span class="small text-white opacity-75">Instant Confirmation</span>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary rounded-circle me-3 flex-shrink-0" style="width: 10px; height: 10px;"></div>
                            <span class="small text-white opacity-75">Live Queue Updates</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary rounded-circle me-3 flex-shrink-0" style="width: 10px; height: 10px;"></div>
                            <span class="small text-white opacity-75">Smart Arrival Alerts</span>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar: Form -->
                <div class="col-md-8 p-4 p-lg-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bolder text-white mb-0">New Appointment</h3>
                        <a href="dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                            <i class="fa-solid fa-xmark small me-1"></i> Cancel
                        </a>
                    </div>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert bg-danger bg-opacity-10 text-danger border-0 small mb-4 animate__animated animate__shakeX">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($_GET['error']) ?>
                        </div>
                    <?php endif; ?>

                    <form action="api/appointments/create.php" method="POST" id="bookingForm">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <!-- Step 1: Service -->
                        <div class="mb-4 animate__animated animate__fadeInUp">
                            <label class="form-label text-muted small fw-bold text-uppercase">1. Choose Service</label>
                            <select name="service_id" class="form-select form-select-lg" required id="serviceSelect">
                                <option value="">Select a Department...</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?= $service['id'] ?>" data-description="<?= htmlspecialchars($service['description'] ?? 'Standard department service.') ?>">
                                        <?= htmlspecialchars($service['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-primary small mt-2" id="serviceDescription"></div>
                        </div>

                        <!-- Step 2: Schedule -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6 animate__animated animate__fadeInUp animate__delay-1s">
                                <label class="form-label text-muted small fw-bold text-uppercase">2. Select Date</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="fa-regular fa-calendar"></i></span>
                                    <input type="date" name="date" class="form-control border-start-0" required
                                           min="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d', strtotime('+30 days')) ?>"
                                           id="dateInput">
                                </div>
                            </div>
                            <div class="col-md-6 animate__animated animate__fadeInUp animate__delay-1s">
                                <label class="form-label text-muted small fw-bold text-uppercase">3. Pick Time</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="fa-regular fa-clock"></i></span>
                                    <input type="time" name="time" class="form-control border-start-0" required 
                                           min="08:00" max="17:00" step="1800" id="timeInput">
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Notes -->
                        <div class="mb-5 animate__animated animate__fadeInUp animate__delay-2s">
                            <label class="form-label text-muted small fw-bold text-uppercase">Additional Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Tell us if you have special requirements..." maxlength="500"></textarea>
                        </div>

                        <div class="animate__animated animate__fadeInUp animate__delay-2s">
                            <button type="submit" class="btn btn-primary w-100 py-3 rounded-4 shadow-lg mb-3">
                                CONFIRM APPOINTMENT <i class="fa-solid fa-check-circle ms-2"></i>
                            </button>
                            <p class="text-center text-muted smaller mb-0">
                                <i class="fa-solid fa-info-circle me-1"></i> You can cancel or reschedule up to 1 hour before.
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Logic for dynamic form guidance
    document.getElementById('serviceSelect').addEventListener('change', function () {
        const description = this.options[this.selectedIndex].dataset.description;
        const descDiv = document.getElementById('serviceDescription');
        descDiv.innerHTML = description ? `<i class="fa-solid fa-circle-info me-2"></i>${description}` : '';
    });

    document.getElementById('dateInput').addEventListener('change', function () {
        const selectedDate = new Date(this.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const timeInput = document.getElementById('timeInput');

        if (selectedDate.getTime() === today.getTime()) {
            const now = new Date();
            const minTime = String(now.getHours() + 1).padStart(2, '0') + ':00';
            timeInput.min = minTime;
        } else {
            timeInput.min = '08:00';
        }
    });

    document.getElementById('bookingForm').addEventListener('submit', function (e) {
        const time = document.getElementById('timeInput').value.replace(':', '');
        if (time < '0800' || time > '1700') {
            e.preventDefault();
            alert('Please select a time within office hours (8:00 AM - 5:00 PM)');
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>