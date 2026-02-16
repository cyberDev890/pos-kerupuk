<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateQZKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qz:generate-keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Self-Signed CA and Keys for QZ Tray Silent Printing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Generating QZ Tray Keys...");

        // Ensure directory exists
        if (!file_exists(storage_path('app/qz'))) {
            mkdir(storage_path('app/qz'), 0755, true);
        }

        // 1. Generate Private Key for CA
        $caPrivKey = openssl_pkey_new([
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);
        openssl_pkey_export($caPrivKey, $caPrivKeyPem);
        file_put_contents(storage_path('app/qz/root-ca.key'), $caPrivKeyPem);
        $this->info("Generated root-ca.key");

        // 2. Generate Self-Signed CA Certificate
        $dn = [
            "countryName" => "ID",
            "stateOrProvinceName" => "Jawa Timur",
            "localityName" => "Jember",
            "organizationName" => "Jaya Abadi POS",
            "organizationalUnitName" => "IT Dept",
            "commonName" => "Jaya Abadi POS Root CA",
            "emailAddress" => "admin@jayaabadi.com"
        ];
        $csr = openssl_csr_new($dn, $caPrivKey);
        $caCert = openssl_csr_sign($csr, null, $caPrivKey, 3650); // 10 Years
        openssl_x509_export($caCert, $caCertPem);
        file_put_contents(storage_path('app/qz/root-ca.crt'), $caCertPem);
        $this->info("Generated root-ca.crt (Install this on Client PC!)");

        // 3. Generate Private Key for Application (to sign messages)
        $appPrivKey = openssl_pkey_new([
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);
        openssl_pkey_export($appPrivKey, $appPrivKeyPem);
        file_put_contents(storage_path('app/qz/private-key.pem'), $appPrivKeyPem);
        $this->info("Generated private-key.pem (Used by Laravel)");

        // 4. Generate Certificate for Application (Signed by our CA)
        $appDn = [
            "countryName" => "ID",
            "stateOrProvinceName" => "Jawa Timur",
            "localityName" => "Jember",
            "organizationName" => "Jaya Abadi POS",
            "organizationalUnitName" => "POS System",
            "commonName" => "Jaya Abadi POS", // Must match what QZ Tray expects or just be valid
            "emailAddress" => "system@jayaabadi.com"
        ];
        $appCsr = openssl_csr_new($appDn, $appPrivKey);
        $appCert = openssl_csr_sign($appCsr, $caCert, $caPrivKey, 3650);
        openssl_x509_export($appCert, $appCertPem);
        
        // QZ Tray expects the public certificate chain. 
        // We probably only need the app cert, but having the chain is good.
        // For simplicity, just the public cert.
        file_put_contents(storage_path('app/qz/digital-certificate.txt'), $appCertPem);
        $this->info("Generated digital-certificate.txt (Used by QZ Tray JS)");

        $this->info("All keys generated successfully in storage/app/qz/");
    }
}
