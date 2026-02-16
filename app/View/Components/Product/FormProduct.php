<?php

namespace App\View\Components\Product;

use App\Models\Kategori;
use App\Models\Product;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FormProduct extends Component
{
    /**
     * Create a new component instance.
     */

    public $id, $nama_produk, $harga_jual, $harga_jual_besar, $harga_beli, $stok, $stok_gudang, $stok_min, $is_active, $kategori_id, $kategori, $unit_id, $units;
    public function __construct($id=null)
    {
        //
        $this->kategori = Kategori::all();
        $this->units = \App\Models\Unit::all();
        if($id){
            $product = Product::find($id);
            $this->id = $product->id;
            $this->nama_produk = $product->nama_produk;
            $this->unit_id = $product->unit_id;
            $this->harga_jual = $product->harga_jual;
            $this->harga_jual_besar = $product->harga_jual_besar;
            $this->harga_beli = $product->harga_beli;

            $isi = $product->unit ? $product->unit->isi : 1;
            $this->stok = $product->stok;
            $this->stok_gudang = $product->stok_gudang / $isi;
            $this->stok_min = $product->stok_min;

            $this->is_active = $product->is_active;
            $this->kategori_id = $product->kategori_id;
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.product.form-product');
    }
}
