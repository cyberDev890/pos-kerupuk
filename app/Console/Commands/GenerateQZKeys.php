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
        $this->info("Generating QZ Tray Certificate with SAN & Extensions...");

        // Ensure directory exists
        if (!file_exists(storage_path('app/qz'))) {
            mkdir(storage_path('app/qz'), 0755, true);
        }

        $domain = "jayaabadi.rayhn.my.id";
        $configFile = storage_path('app/qz/openssl.cnf');

        // 1. Create OpenSSL Config File
        $configContent = <<<EOL
[req]
default_bits = 2048
prompt = no
default_md = sha256
distinguished_name = dn
req_extensions = req_ext
x509_extensions = v3_ca

[dn]
C = ID
ST = Jawa Timur
L = Jember
O = Jaya Abadi POS
OU = POS System
CN = $domain
emailAddress = system@jayaabadi.com

[req_ext]
subjectAltName = @alt_names

[v3_ca]
subjectKeyIdentifier = hash
authorityKeyIdentifier = keyid:always,issuer
basicConstraints = critical, CA:TRUE
keyUsage = critical, digitalSignature, cRLSign, keyCertSign
subjectAltName = @alt_names

[alt_names]
DNS.1 = $domain
DNS.2 = localhost
IP.1 = 127.0.0.1
EOL;
        file_put_contents($configFile, $configContent);

        // 2. Generate Private Key
        $privKey = openssl_pkey_new([
            "config" => $configFile,
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);
        openssl_pkey_export($privKey, $privKeyPem, null, ["config" => $configFile]);
        file_put_contents(storage_path('app/qz/private-key.pem'), $privKeyPem);
        $this->info("Generated private-key.pem");

        // 3. Generate CSR & Self-Signed Cert using Config
        $csr = openssl_csr_new([], $privKey, ["config" => $configFile]); // DN comes from config
        
        // Sign with extensions
        $cert = openssl_csr_sign($csr, null, $privKey, 3650, ["config" => $configFile, "digest_alg" => "sha256"]);
        openssl_x509_export($cert, $certPem);
        
        file_put_contents(storage_path('app/qz/digital-certificate.txt'), $certPem);
        file_put_contents(storage_path('app/qz/root-ca.crt'), $certPem);
        
        // Verify output
        $details = openssl_x509_parse($certPem);
        $this->info("Certificate Generated for CN: " . $details['subject']['CN']);
        $this->info("Extensions present: " . implode(", ", array_keys($details['extensions'] ?? [])));
        
        $this->info("Done! Please RE-INSTALL 'root-ca.crt' on the client machine.");
    }
}
