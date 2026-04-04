<?php
require_once '../../lib/phpqrcode.php';

if(isset($_GET['data'])) {
    $data = $_GET['data'];
    QRcode::png($data, false, QR_ECLEVEL_L, 8, 2);
}
