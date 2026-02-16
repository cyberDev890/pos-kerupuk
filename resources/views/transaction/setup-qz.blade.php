@extends('layouts.app')
@section('content_title', 'Setup Silent Printing (QZ Tray)')
@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Panduan Install Sertifikat QZ Tray</h3>
            </div>
            <div class="card-body">
                <p class="lead">Agar printer bisa mencetak langsung (Silent Print) tanpa muncul popup "Allow" terus-menerus, Anda perlu menginstal Sertifikat Otoritas (CA) di komputer kasir.</p>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Sertifikat ini hanya perlu diinstal <strong>sekali saja</strong> di setiap laptop/komputer yang digunakan untuk kasir.
                </div>

                <h4>Langkah 1: Download Sertifikat</h4>
                <p>Klik tombol di bawah ini untuk mendownload file sertifikat (<code>.crt</code>).</p>
                <div class="text-center mb-4">
                    <a href="{{ route('transaction.qz.download-ca') }}" class="btn btn-lg btn-success">
                        <i class="fas fa-download"></i> Download Sertifikat CA
                    </a>
                </div>

                <h4>Langkah 2: Install Sertifikat</h4>
                <ol>
                    <li>Buka file <code>JayaAbadi-POS-RootCA.crt</code> yang baru saja didownload.</li>
                    <li>Akan muncul jendela sertifikat, klik tombol <strong>Install Certificate...</strong></li>
                    <li>Pilih <strong>Local Machine</strong> (jika diminta), lalu klik Next.</li>
                    <li>Pilih opsi <strong>Place all certificates in the following store</strong>.</li>
                    <li>Klik <strong>Browse...</strong> dan pilih folder <strong>Trusted Root Certification Authorities</strong>. <span class="text-danger">(Penting!)</span></li>
                    <li>Klik OK, Next, dan Finish.</li>
                    <li>Jika muncul peringatan keamanan, klik <strong>Yes</strong>.</li>
                </ol>

                <h4>Langkah 3: Test Print</h4>
                <p>Setelah install, coba klik tombol test di bawah ini. Seharusnya tidak muncul popup "Allow" lagi, atau Anda bisa mencentang "Remember this decision" dan tombol Allow akan aktif.</p>
                <div class="text-center">
                    <button class="btn btn-primary" onclick="testPrint()">
                        <i class="fas fa-print"></i> Test Print Struk
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function testPrint() {
        Swal.fire({ title: 'Mencoba Print...', didOpen: () => Swal.showLoading() });
        
        window.connectToQZ()
            .then(() => qz.printers.find("pos_printer"))
            .then(printer => {
                let config = qz.configs.create(printer);
                let data = [
                    'Test Print QZ Tray\n',
                    'Silent Printing OK!\n',
                    '------------------\n',
                    '\n'
                ];
                return qz.print(config, data);
            })
            .then(() => {
                Swal.fire('Berhasil', 'Printer merespon!', 'success');
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Gagal', err.message, 'error');
            });
    }
</script>
@endsection
