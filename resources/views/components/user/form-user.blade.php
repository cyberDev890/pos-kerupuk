<div>
    <button type="button" class="btn {{ $id ? 'btn-warning' : 'btn-primary' }}" data-toggle="modal"
        data-target="#formUser{{ $id ?? '' }}">
        @if ($id)
            <i class="fas fa-edit"></i>
        @else
            User Baru
        @endif
    </button>

    <div class="modal fade" id="formUser{{ $id ?? '' }}">
        <form action="{{ route('users.index') }}" method="POST">
            @csrf
            <input type="hidden" name="id" value="{{ $id ?? '' }}">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">{{ $id ? 'Form Edit Users' : 'Form Tambah Users' }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <div class="form-group my-1">
                            <label for=""> Email</label>
                            <input type="email" class="form-control" name="email" id="email"
                                value="{{ $id ? $email : old('email') }}">

                        </div>
                        <div class="form-group my-1">
                            <label for=""> nama</label>
                            <input type="name" class="form-control" name="name" id="name"
                                value="{{ $id ? $name : old('name') }}">
                        </div>

                        <div class="form-group my-1">
                            <label for="role"> Role</label>
                            <select name="role" id="role-{{ $id }}" class="form-control role-select">
                                <option value="admin" {{ $role == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="karyawan" {{ $role == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                            </select>
                        </div>

                        <div id="permissions-section-{{ $id }}"
                            class="permissions-section {{ $role == 'admin' ? 'd-none' : '' }}">
                            <hr>
                            <label class="mt-2 text-primary font-weight-bold"><i class="fas fa-key mr-1"></i> Hak Akses Menu</label>
                            <div class="border rounded p-3 bg-light shadow-sm" style="max-height: 400px; overflow-y: auto;">
                                @php
                                    $menuGroups = [
                                        [
                                            'header' => 'Dashboard',
                                            'permission' => 'dashboard',
                                            'icon' => 'fas fa-laptop',
                                            'items' => [
                                                'dashboard.view-omzet' => 'Lihat Omzet & Chart',
                                            ],
                                        ],
                                        [
                                            'header' => 'Data User',
                                            'permission' => 'users',
                                            'icon' => 'fas fa-users',
                                        ],
                                        [
                                            'header' => 'Master Data',
                                            'permission' => 'master-data',
                                            'icon' => 'fas fa-database',
                                            'items' => [
                                                'master-data.product' => 'Produk',
                                                'master-data.unit' => 'Satuan',
                                                'master-data.supplier' => 'Suplier',
                                                'master-data.customer' => 'Pelanggan',
                                            ],
                                        ],
                                        [
                                            'header' => 'Mutasi Stok',
                                            'permission' => 'stock-mutation',
                                            'icon' => 'fas fa-exchange-alt',
                                        ],
                                        [
                                            'header' => 'Transaksi Pembelian',
                                            'permission' => 'transaction.purchase.index',
                                            'icon' => 'fas fa-shopping-cart',
                                            'items' => [
                                                'transaction.purchase.create' => 'Pembelian Baru & Saldo Awal',
                                                'transaction.purchase.index' => 'Riwayat Pembelian',
                                            ],
                                        ],
                                        [
                                            'header' => 'Transaksi Penjualan',
                                            'permission' => 'transaction.sales.index',
                                            'icon' => 'fas fa-cash-register',
                                            'items' => [
                                                'transaction.sales.create' => 'Kasir & Saldo Awal',
                                                'transaction.sales.index' => 'Riwayat Penjualan',
                                            ],
                                        ],
                                        [
                                            'header' => 'Retur Barang',
                                            'permission' => 'return',
                                            'icon' => 'fas fa-undo',
                                            'items' => [
                                                'return.create' => 'Retur Baru',
                                                'return.index' => 'Riwayat Retur',
                                            ],
                                        ],
                                        [
                                            'header' => 'Piutang Pelanggan',
                                            'permission' => 'receivable',
                                            'icon' => 'fas fa-file-invoice-dollar',
                                        ],
                                        [
                                            'header' => 'Hutang Suplier',
                                            'permission' => 'payable',
                                            'icon' => 'fas fa-file-invoice-dollar',
                                        ],
                                        [
                                            'header' => 'Laporan',
                                            'permission' => 'report',
                                            'icon' => 'fas fa-chart-line',
                                            'items' => [
                                                'report.profit-loss' => 'Laporan Laba Rugi',
                                            ],
                                        ],
                                        [
                                            'header' => 'Anggaran',
                                            'permission' => 'budget',
                                            'icon' => 'fas fa-money-bill-wave',
                                            'items' => [
                                                'budget.salary' => 'Gaji Karyawan',
                                                'budget.operational' => 'Biaya Operasional',
                                            ],
                                        ],
                                    ];
                                @endphp

                                @foreach ($menuGroups as $group)
                                    <div class="permission-group mb-3 card card-outline card-primary shadow-none">
                                        <div class="card-header p-2">
                                            <div class="custom-control custom-checkbox ml-1">
                                                <input class="custom-control-input parent-checkbox" type="checkbox"
                                                    id="perm-{{ $group['permission'] }}-{{ $id }}"
                                                    name="permissions[]" value="{{ $group['permission'] }}"
                                                    data-group="{{ $group['permission'] }}"
                                                    {{ in_array($group['permission'], $permissions ?? []) ? 'checked' : '' }}>
                                                <label for="perm-{{ $group['permission'] }}-{{ $id }}"
                                                    class="custom-control-label">
                                                    <i class="{{ $group['icon'] }} mr-1 text-primary" style="width: 20px"></i>
                                                    {{ $group['header'] }}
                                                </label>
                                            </div>
                                        </div>

                                        @if (isset($group['items']))
                                            <div class="card-body p-2 bg-white">
                                                <div class="row no-gutters">
                                                    @foreach ($group['items'] as $key => $label)
                                                        <div class="col-12 col-sm-6 col-md-4">
                                                            <div class="custom-control custom-checkbox ml-3 py-1">
                                                                <input class="custom-control-input child-checkbox"
                                                                    type="checkbox" id="perm-{{ $key }}-{{ $id }}"
                                                                    name="permissions[]" value="{{ $key }}"
                                                                    data-parent="{{ $group['permission'] }}"
                                                                    {{ in_array($key, $permissions ?? []) ? 'checked' : '' }}>
                                                                <label for="perm-{{ $key }}-{{ $id }}"
                                                                    class="custom-control-label font-weight-normal text-sm">{{ $label }}</label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle mr-1"></i> Admin memiliki akses penuh secara default.</small>
                        </div>

                        <script>
                            $(document).ready(function() {
                                // Toggle section visibility based on role
                                $('#role-{{ $id }}').on('change', function() {
                                    if ($(this).val() === 'karyawan') {
                                        $('#permissions-section-{{ $id }}').fadeOut().removeClass('d-none').fadeIn();
                                    } else {
                                        $('#permissions-section-{{ $id }}').fadeOut();
                                    }
                                });

                                // Auto check/uncheck children when parent is toggled
                                $('.parent-checkbox').on('change', function() {
                                    let group = $(this).data('group');
                                    let isChecked = $(this).is(':checked');
                                    $(`input[data-parent="${group}"]`).prop('checked', isChecked);
                                });

                                // Auto check parent if any child is checked
                                $('.child-checkbox').on('change', function() {
                                    let parentId = $(this).data('parent');
                                    if ($(this).is(':checked')) {
                                        $(`input[data-group="${parentId}"]`).prop('checked', true);
                                    }
                                });
                            });
                        </script>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </form>
    </div>
</div>
