<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'includes/header.php';

$stmt = $pdo->prepare("SELECT a.*, s.name as service_name FROM Appointments a JOIN Services s ON a.service_id = s.id WHERE a.user_id = ? ORDER BY a.date DESC, a.time DESC");
$stmt->execute([$_SESSION['user_id']]);
$appointments = $stmt->fetchAll();
?>

<div class="row animate__animated animate__fadeIn">
    <!-- Header -->
    <div class="col-12 mb-5 d-flex justify-content-between align-items-end">
        <div>
            <h6 class="text-primary fw-bold text-uppercase mb-1 letter-spacing-lg">SCHEDULE</h6>
            <h2 class="fw-bolder mb-0">My Appointments</h2>
        </div>
        <a href="book.php" class="btn btn-primary shadow-lg animate__animated animate__pulse animate__infinite animate__slow">
            <i class="fa-solid fa-plus me-2"></i> Book New
        </a>
    </div>

    <!-- Appointment Grid -->
    <div class="row g-4">
        <?php if(empty($appointments)): ?>
            <div class="col-12 text-center py-5">
                <div class="bg-surface p-5 rounded-4 border border-white border-opacity-5">
                    <i class="fa-solid fa-calendar-xmark text-muted fs-1 mb-3"></i>
                    <h5 class="text-muted mb-3">No Appointments Found</h5>
                    <p class="text-muted small">You haven't booked any services yet.</p>
                    <a href="book.php" class="btn btn-outline-primary mt-2">Book Your First Service</a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach($appointments as $appt): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 hover-lift overflow-hidden">
                        <div class="card-header bg-transparent border-white border-opacity-5 d-flex justify-content-between align-items-start pt-4 px-4">
                            <span class="badge rounded-pill bg-white bg-opacity-5 border border-white border-opacity-10 text-muted px-3 py-2">
                                <i class="fa-solid fa-hashtag me-1 small"></i><?= $appt['id'] ?>
                            </span>
                            
                            <?php if($appt['status'] == 'booked'): ?>
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">Confirmed</span>
                            <?php elseif($appt['status'] == 'checked-in'): ?>
                                <span class="badge bg-success bg-opacity-10 text-success fw-bold animate__animated animate__pulse animate__infinite">Live</span>
                            <?php elseif($appt['status'] == 'missed'): ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger fw-bold">Missed</span>
                            <?php else: ?>
                                <span class="badge bg-secondary bg-opacity-10 text-muted fw-bold">Completed</span>
                            <?php endif; ?>
                        </div>

                        <div class="card-body p-4">
                            <h4 class="fw-bold text-white mb-3"><?= htmlspecialchars($appt['service_name']) ?></h4>
                            
                            <div class="d-flex flex-column gap-2 mb-4">
                                <div class="d-flex align-items-center text-muted small">
                                    <i class="fa-regular fa-calendar-days text-primary me-3 fs-6"></i>
                                    <span><?= date('D, M j, Y', strtotime($appt['date'])) ?></span>
                                </div>
                                <div class="d-flex align-items-center text-muted small">
                                    <i class="fa-regular fa-clock text-primary me-3 fs-6"></i>
                                    <span><?= date('h:i A', strtotime($appt['time'])) ?></span>
                                </div>
                            </div>
                            
                            <!-- Action Area -->
                            <div class="mt-auto">
                                <?php if($appt['status'] == 'booked'): ?>
                                    <div class="d-grid">
                                        <a href="qr.php?id=<?= $appt['id'] ?>" class="btn btn-outline-primary rounded-4 py-2 border-primary border-opacity-25">
                                            <i class="fa-solid fa-qrcode me-2"></i> View QR Check-in
                                        </a>
                                    </div>
                                <?php elseif($appt['status'] == 'missed'): ?>
                                    <div class="d-grid">
                                        <a href="qr.php?id=<?= $appt['id'] ?>" class="btn btn-danger rounded-4 py-2 bg-opacity-75">
                                            <i class="fa-solid fa-redo me-2"></i> Re-join Queue
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="bg-white bg-opacity-5 rounded-4 p-3 text-center">
                                        <span class="small text-muted fw-bold">
                                            <i class="fa-solid fa-circle-check me-2"></i> Session Finished
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="mt-5 pt-5 text-center text-muted small pb-4 border-top border-white border-opacity-5">
    Need help? Contact support or check the <a href="#" class="text-primary text-decoration-none fw-bold">FAQ</a>.
</div>

<?php require_once 'includes/footer.php'; ?>
