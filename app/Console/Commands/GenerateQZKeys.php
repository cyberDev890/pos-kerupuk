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
        $this->info("Generating Single Self-Signed Certificate for QZ Tray...");

        // Ensure directory exists
        if (!file_exists(storage_path('app/qz'))) {
            mkdir(storage_path('app/qz'), 0755, true);
        }

        // 1. Generate Private Key
        $privKey = openssl_pkey_new([
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);
        openssl_pkey_export($privKey, $privKeyPem);
        file_put_contents(storage_path('app/qz/private-key.pem'), $privKeyPem);
        $this->info("Generated private-key.pem");

        // 2. Generate Self-Signed Certificate (Acts as Root AND App Cert)
        $dn = [
            "countryName" => "ID",
            "stateOrProvinceName" => "Jawa Timur",
            "localityName" => "Jember",
            "organizationName" => "Jaya Abadi POS",
            "organizationalUnitName" => "POS System",
            "commonName" => "jayaabadi.rayhn.my.id", // MATCH DOMAIN
            "emailAddress" => "system@jayaabadi.com"
        ];
        $csr = openssl_csr_new($dn, $privKey);
        $cert = openssl_csr_sign($csr, null, $privKey, 3650); // 10 Years
        openssl_x509_export($cert, $certPem);
        
        file_put_contents(storage_path('app/qz/digital-certificate.txt'), $certPem);
        
        // Copy to root-ca.crt for the setup page download link (same file)
        file_put_contents(storage_path('app/qz/root-ca.crt'), $certPem);
        
        $this->info("Generated digital-certificate.txt & root-ca.crt");
        $this->info("Done! Please re-install 'root-ca.crt' on the client machine.");
    }
}
