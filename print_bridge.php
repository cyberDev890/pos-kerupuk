<?php
/**
 * LOCAL PRINT BRIDGE (PHP)
 * Jalankan ini di laptop yang terhubung ke Printer dengan perintah:
 * php -S localhost:8080 print_bridge.php
 */

// 1. Handle CORS (supaya browser diizinkan kirim data ke localhost)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// 2. Baca data dari Browser
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (isset($data['data'])) {
        $rawCommands = base64_decode($data['data']);
        
        try {
            // NAMA PRINTER: Sesuaikan dengan 'Share Name' yang Mas buat di Windows
            $printerName = "RP58_Printer"; 
            
            // Kirim langsung ke printer via Windows Connector
            $fp = fopen("//localhost/" . $printerName, "wb");
            if (!$fp) {
                http_response_code(500);
                echo "Gagal membuka printer: $printerName. Pastikan sudah di-Share.";
                exit;
            }
            fwrite($fp, $rawCommands);
            fclose($fp);

            echo "Berhasil mencetak ke $printerName!";
        } catch (Exception $e) {
            http_response_code(500);
            echo "Error: " . $e->getMessage();
        }
    } else {
        http_response_code(400);
        echo "Data tidak ditemukan.";
    }
    exit;
}

echo "Local Print Bridge is Running on http://localhost:8080";
