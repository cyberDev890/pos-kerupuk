<?php

namespace App\View\Components\Admin;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Aside extends Component
{
    /**
     * Create a new component instance.
     */
    public $routes;
    public function __construct()
    {
        //
        $this->routes = [
            [
                'label' => 'Dashboard',
                'icon' => 'fas fa-laptop',
                'route_name' => 'dashboard',
                'route_active' => 'dashboard',
                'is_dropdown' => false

            ],
            [
                'label' => 'Data User',
                'icon' => 'fas fa-users',
                'route_name' => 'users.index',
                'route_active' => 'users.*',
                'is_dropdown' => false

            ],
            /*
            [
                'label' => 'Penerimaan Barang',
                'icon' => 'fas fa-truck-loading',
                'route_name' => 'dashboard',
                'route_active' => 'dashboard',
                'is_dropdown' => false

            ],
            [
                'label' => 'Laporan',
                'icon' => 'fas fa-chart-line',
                'route_active' => 'master-data.*',
                'is_dropdown' => true,
                'dropdown' => [
                    [
                        'label' => 'Kategori',
                        'route_name' => 'master-data.kategori.index',
                        'route_active' => 'master-data.kategori.*',
                    ],
                    [
                        'label' => 'Produk',
                        'route_name' => 'master-data.product.index',
                        'route_active' => 'master-data.product.*',
                    ],
                ],
            ],
            */
            [
                'label' => 'Master Data',
                'icon' => 'fas fa-database',
                'route_active' => 'master-data.*',
                'is_dropdown' => true,
                'dropdown' => [
                    [
                        'label' => 'Kategori',
                        'route_name' => 'master-data.kategori.index',
                        'route_active' => 'master-data.kategori.*',
                    ],
                    [
                        'label' => 'Produk',
                        'route_name' => 'master-data.product.index',
                        'route_active' => 'master-data.product.*',
                    ],
                    [
                        'label' => 'Satuan',
                        'route_name' => 'master-data.unit.index',
                        'route_active' => 'master-data.unit.*',
                    ],
                    [
                        'label' => 'Suplier',
                        'route_name' => 'master-data.supplier.index',
                        'route_active' => 'master-data.supplier.*',
                    ],
                    [
                        'label' => 'Pelanggan',
                        'route_name' => 'master-data.customer.index',
                        'route_active' => 'master-data.customer.*',
                    ],
                ],
            ],
            [
                'label' => 'Mutasi Stok',
                'icon' => 'fas fa-exchange-alt',
                'route_name' => 'stock.mutation.index', 
                'route_active' => 'stock.mutation.*',
                'is_dropdown' => false
            ],
            [
                'label' => 'Transaksi',
                'icon' => 'fas fa-shopping-cart',
                'route_name' => '',
                'route_active' => 'transaction.*',
                'is_dropdown' => true,
                'dropdown' => [
                    [
                        'label' => 'Pembelian Baru',
                        'route_name' => 'transaction.purchase.create',
                        'route_active' => 'transaction.purchase.create',
                    ],
                    [
                        'label' => 'Riwayat Pembelian',
                        'route_name' => 'transaction.purchase.index',
                        'route_active' => 'transaction.purchase.index',
                    ],
                    [
                        'label' => 'Penjualan (Kasir)',
                        'route_name' => 'transaction.sales.create', // Direct to POS
                        'route_active' => 'transaction.sales.create',
                    ],
                    [
                        'label' => 'Riwayat Penjualan',
                        'route_name' => 'transaction.sales.index',
                        'route_active' => 'transaction.sales.index',
                    ],
                ],
            ],
            [
                'label' => 'Retur Barang',
                'icon' => 'fas fa-undo',
                'route_name' => '',
                'route_active' => 'return.*',
                'is_dropdown' => true,
                'dropdown' => [
                    [
                        'label' => 'Retur Baru',
                        'route_name' => 'return.create',
                        'route_active' => 'return.create',
                    ],
                    [
                        'label' => 'Riwayat Retur',
                        'route_name' => 'return.index',
                        'route_active' => 'return.index',
                    ],
                ]
            ],
            [
                'label' => 'Piutang Pelanggan',
                'icon' => 'fas fa-file-invoice-dollar',
                'route_name' => 'receivable.index',
                'route_active' => 'receivable.*',
                'is_dropdown' => false
            ],
            [
                'label' => 'Laporan',
                'icon' => 'fas fa-chart-line',
                'route_active' => 'report.*',
                'is_dropdown' => true,
                'dropdown' => [
                    [
                        'label' => 'Laporan Laba Rugi',
                        'route_name' => 'report.profit-loss',
                        'route_active' => 'report.profit-loss',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.admin.aside');
    }
}
