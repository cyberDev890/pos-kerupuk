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
        $user = auth()->user();
        $role = $user ? $user->role : 'guest';

        $allRoutes = [
            [
                'label' => 'Dashboard',
                'icon' => 'fas fa-laptop',
                'route_name' => 'dashboard',
                'route_active' => 'dashboard',
                'is_dropdown' => false,
                'allowed_roles' => ['admin', 'karyawan'],
                'permission' => 'dashboard',
            ],
            [
                'label' => 'Data User',
                'icon' => 'fas fa-users',
                'route_name' => 'users.index',
                'route_active' => 'users.*',
                'is_dropdown' => false,
                'allowed_roles' => ['admin'],
                'permission' => 'users',
            ],
            [
                'label' => 'Master Data',
                'icon' => 'fas fa-database',
                'route_active' => 'master-data.*',
                'is_dropdown' => true,
                'allowed_roles' => ['admin'],
                'permission' => 'master-data',
                'dropdown' => [
                    [
                        'label' => 'Produk',
                        'route_name' => 'master-data.product.index',
                        'route_active' => 'master-data.product.*',
                        'permission' => 'master-data.product',
                    ],
                    [
                        'label' => 'Satuan',
                        'route_name' => 'master-data.unit.index',
                        'route_active' => 'master-data.unit.*',
                        'permission' => 'master-data.unit',
                    ],
                    [
                        'label' => 'Suplier',
                        'route_name' => 'master-data.supplier.index',
                        'route_active' => 'master-data.supplier.*',
                        'permission' => 'master-data.supplier',
                    ],
                    [
                        'label' => 'Pelanggan',
                        'route_name' => 'master-data.customer.index',
                        'route_active' => 'master-data.customer.*',
                        'permission' => 'master-data.customer',
                    ],
                ],
            ],
            [
                'label' => 'Mutasi Stok',
                'icon' => 'fas fa-exchange-alt',
                'route_name' => 'stock.mutation.index',
                'route_active' => 'stock.mutation.*',
                'is_dropdown' => false,
                'allowed_roles' => ['admin'],
                'permission' => 'stock-mutation',
            ],
            [
                'label' => 'Transaksi Pembelian',
                'icon' => 'fas fa-shopping-cart',
                'route_active' => 'transaction.purchase.*',
                'is_dropdown' => true,
                'allowed_roles' => ['admin'],
                'permission' => 'transaction.purchase.index',
                'dropdown' => [
                    [
                        'label' => 'Pembelian Baru',
                        'route_name' => 'transaction.purchase.create',
                        'route_active' => 'transaction.purchase.create',
                        'allowed_roles' => ['admin'],
                        'permission' => 'transaction.purchase.create',
                    ],
                    [
                        'label' => 'Riwayat Pembelian',
                        'route_name' => 'transaction.purchase.index',
                        'route_active' => 'transaction.purchase.index',
                        'allowed_roles' => ['admin'],
                        'permission' => 'transaction.purchase.index',
                    ],
                    [
                        'label' => 'Saldo Awal Hutang',
                        'route_name' => 'transaction.purchase.opening-balance',
                        'route_active' => 'transaction.purchase.opening-balance',
                        'allowed_roles' => ['admin'],
                        'permission' => 'transaction.purchase.create',
                    ],
                ],
            ],
            [
                'label' => 'Transaksi Penjualan',
                'icon' => 'fas fa-cash-register',
                'route_active' => 'transaction.sales.*',
                'is_dropdown' => true,
                'allowed_roles' => ['admin', 'karyawan'],
                'permission' => 'transaction.sales.index',
                'dropdown' => [
                    [
                        'label' => 'Penjualan (Kasir)',
                        'route_name' => 'transaction.sales.create',
                        'route_active' => 'transaction.sales.create',
                        'allowed_roles' => ['admin', 'karyawan'],
                        'permission' => 'transaction.sales.create',
                    ],
                    [
                        'label' => 'Riwayat Penjualan',
                        'route_name' => 'transaction.sales.index',
                        'route_active' => 'transaction.sales.index',
                        'allowed_roles' => ['admin', 'karyawan'],
                        'permission' => 'transaction.sales.index',
                    ],
                    [
                        'label' => 'Saldo Awal Piutang',
                        'route_name' => 'receivable.opening-balance',
                        'route_active' => 'receivable.opening-balance',
                        'allowed_roles' => ['admin'],
                        'permission' => 'transaction.sales.create',
                    ],
                ],
            ],
            [
                'label' => 'Retur Barang',
                'icon' => 'fas fa-undo',
                'route_name' => '',
                'route_active' => 'return.*',
                'is_dropdown' => true,
                'allowed_roles' => ['admin', 'karyawan'],
                'permission' => 'return',
                'dropdown' => [
                    [
                        'label' => 'Retur Baru',
                        'route_name' => 'return.create',
                        'route_active' => 'return.create',
                        'allowed_roles' => ['admin', 'karyawan'],
                        'permission' => 'return.create',
                    ],
                    [
                        'label' => 'Riwayat Retur',
                        'route_name' => 'return.index',
                        'route_active' => 'return.index',
                        'allowed_roles' => ['admin', 'karyawan'],
                        'permission' => 'return.index',
                    ],
                ]
            ],
            [
                'label' => 'Piutang Pelanggan',
                'icon' => 'fas fa-file-invoice-dollar',
                'route_name' => 'receivable.index',
                'route_active' => 'receivable.*',
                'is_dropdown' => false,
                'allowed_roles' => ['admin'],
                'permission' => 'receivable',
            ],
            [
                'label' => 'Hutang Suplier',
                'icon' => 'fas fa-file-invoice-dollar',
                'route_name' => 'payable.index',
                'route_active' => 'payable.*',
                'is_dropdown' => false,
                'allowed_roles' => ['admin'],
                'permission' => 'payable',
            ],
            [
                'label' => 'Laporan',
                'icon' => 'fas fa-chart-line',
                'route_active' => 'report.*',
                'is_dropdown' => true,
                'allowed_roles' => ['admin'],
                'permission' => 'report',
                'dropdown' => [
                    [
                        'label' => 'Laporan Laba Rugi',
                        'route_name' => 'report.profit-loss',
                        'route_active' => 'report.profit-loss',
                        'permission' => 'report.profit-loss',
                    ],
                ],
            ],
            [
                'label' => 'Anggaran',
                'icon' => 'fas fa-money-bill-wave',
                'route_active' => 'budget.*',
                'is_dropdown' => true,
                'allowed_roles' => ['admin'],
                'permission' => 'budget',
                'dropdown' => [
                    [
                        'label' => 'Gaji Karyawan',
                        'route_name' => 'budget.salary.index',
                        'route_active' => 'budget.salary.*',
                        'permission' => 'budget.salary',
                    ],
                    [
                        'label' => 'Biaya Operasional',
                        'route_name' => 'budget.operational.index',
                        'route_active' => 'budget.operational.*',
                        'permission' => 'budget.operational',
                    ],
                ],
            ],
        ];

        $this->routes = $this->filterRoutes($allRoutes, $user);
    }

    private function filterRoutes($routes, $user)
    {
        $filtered = [];
        foreach ($routes as $route) {
            // hasPermission returns true for admins or if permissions array contains the key
            if (isset($route['permission']) && !$user->hasPermission($route['permission'])) {
                continue;
            }

            // Fallback to role-based if permission not checked or to double secure

            // If it has a dropdown, filter the dropdown items
            if (isset($route['dropdown'])) {
                $route['dropdown'] = $this->filterRoutes($route['dropdown'], $user);
                // If no dropdown items remain, skip this parent menu
                if (empty($route['dropdown'])) {
                    continue;
                }
            }

            $filtered[] = $route;
        }
        return $filtered;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.admin.aside');
    }
}
