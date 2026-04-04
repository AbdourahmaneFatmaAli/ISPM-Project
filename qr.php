<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';
require_once 'includes/header.php';

if(!isset($_GET['id'])) {
    die("Invalid request");
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT a.*, s.name as service_name FROM Appointments a JOIN Services s ON a.service_id = s.id WHERE a.id = ? AND a.user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$appointment = $stmt->fetch();

if(!$appointment) {
    die("Appointment not found");
}

$qr_url = "api/qr/generate.php?data=" . urlencode($appointment['qr_code']);
?>
<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 text-center p-4">
            <h3 class="mb-4">Check-in QR Code</h3>
            
            <!-- Dynamic JS QR Container -->
            <div id="qr-canvas" class="mb-4 d-flex justify-content-center"></div>

            <h5><?= htmlspecialchars($appointment['service_name']) ?></h5>
            <p class="text-muted"><i class="fa-regular fa-calendar me-2"></i><?= $appointment['date'] ?> at <?= $appointment['time'] ?></p>
            <hr>
            <p class="small text-muted mb-3">Present this QR code to the staff or scan it at the kiosk to check-in for your appointment.</p>
            
            <div class="d-flex flex-column gap-2 mt-2">
                <button id="downloadBtn" class="btn btn-primary w-100"><i class="fa-solid fa-download me-2"></i> Download QR Code</button>
                <a href="dashboard.php" class="btn btn-outline-secondary w-100">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<!-- Modern QR Code Styling Library -->
<script type="text/javascript" src="https://unpkg.com/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>
<script>
    const qrCode = new QRCodeStyling({
        width: 250,
        height: 250,
        type: "canvas",
        data: "<?= addslashes(htmlspecialchars($appointment['qr_code'])) ?>",
        image: "/DQSSA/assets/images/qr_logo.png",
        margin: 10,
        dotsOptions: {
            color: "#0d6efd", // Primary brand color
            type: "rounded"
        },
        backgroundOptions: {
            color: "#ffffff",
        },
        imageOptions: {
            crossOrigin: "anonymous",
            margin: 5,
            imageSize: 0.4
        },
        cornersSquareOptions: {
            type: "extra-rounded",
            color: "#0a58ca"
        }
    });

    // Render it to the canvas container
    qrCode.append(document.getElementById("qr-canvas"));

    // Handle Download button click
    document.getElementById("downloadBtn").addEventListener("click", () => {
        qrCode.download({ name: "DQSSA_Checkin_QR", extension: "png" });
    });
</script>

<?php require_once 'includes/footer.php'; ?>
