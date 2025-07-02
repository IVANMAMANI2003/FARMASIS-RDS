<?php

namespace App\Livewire;

use App\Models\Detail;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

class SalesMain extends Component{
    public $dni;
    public $nombre="SIN NOMBRE";
    public $search;
    public $detalle;
    public $gravada,$igv,$totalImporte;
    public $ventaid;

    public function mount(){
        $this->detalle=session()->get('detalle');
        $this->calcularTotalImporte();
    }

    public function render(){
        if (strlen($this->search)>0) {
            $productos=Product::where('name','LIKE','%'.$this->search.'%')->get();
        }else{
            $productos=[];
        }
        return view('livewire.sales-main',compact('productos'));
    }

    public function buscarDNI(){
        $response = Http::get('https://api.apis.net.pe/v1/dni?numero='.$this->dni);
        $datos=(object)json_decode($response);
        $this->nombre=$datos->nombre;
    }

    public function selecionarProducto(Product $producto){
        //$this->detalle=session()->get('detalle');
        if (!$this->detalle) {
            $this->detalle=[$producto->id=>[
                "name"=>$producto->name,
                "quantity"=>1,
                "price"=>$producto->price,
                "subtotal"=>$producto->price
            ]];
        }else{ //si el carro existe pero el producto no esta en el carro
            $this->detalle[$producto->id] = [
                "name"=>$producto->name,
                "quantity"=>1,
                "price"=>$producto->price,
                "subtotal"=>$producto->price
            ];
        }
        session()->put('detalle',$this->detalle);
        $this->dispatch('focus-search');
        $this->reset("search");
        $this->calcularTotalImporte();
    }

    public function deleteProducto($id){
        unset($this->detalle[$id]);
        session()->put('detalle',$this->detalle);
        $this->calcularTotalImporte();
    }

    public function sumarCantidad($id){
        if(isset($this->detalle[$id])){
            $this->detalle[$id]['quantity']++;
            $this->detalle[$id]['subtotal']=$this->detalle[$id]['subtotal']+$this->detalle[$id]['price'];
        }
        session()->put('detalle',$this->detalle);
        $this->calcularTotalImporte();
    }

    public function restarCantidad($id){
        if(isset($this->detalle[$id])){
            $this->detalle[$id]['quantity']--;
            $this->detalle[$id]['subtotal']=$this->detalle[$id]['subtotal']-$this->detalle[$id]['price'];
            if ($this->detalle[$id]['quantity']<1) {
                unset($this->detalle[$id]);
            }
        }
        session()->put('detalle',$this->detalle);
        $this->calcularTotalImporte();
    }

    public function calcularTotalImporte(){
        //$this->detalle=session()->get('detalle');
        $sesionitems=$this->detalle;
        $total=0;
        if($this->detalle){
            foreach ($sesionitems as $id => $item) {
                $total=$total+($item['price']*$item['quantity']);
            }
        }
        $this->gravada=round($total/1.18,2);
        $this->igv=$total-round($total/1.18,2);
        $this->totalImporte=$total;
    }

    public function guardarVenta(){
        DB::beginTransaction();
        try {
            // Crear la venta
            $venta = Sale::create([
                'date' => now(),
                'cliente'=> $this->nombre,
                'number' => $this->generarNumeroVenta(), // puedes crear este método si deseas generar números únicos
                'type' => 'BOLETA',
                'subtotal' => $this->gravada,
                'igv' => $this->igv,
                'total' => $this->totalImporte,
            ]);
            $this->ventaid=$venta->id;
            // Guardar los detalles de venta
            foreach ($this->detalle as $id => $item) {
                Detail::create([
                    'description' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'amount' => $item['subtotal'],
                    'sale_id' => $venta->id,
                    'product_id' => $id,
                ]);
            }
            DB::commit();
            $this->dispatch('alert',message:'Venta registrada satisfactoriamente');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('error',message:'Error al registrar la venta: '.$e->getMessage());
        }
    }

    public function generarNumeroVenta(){
        $lastSale = Sale::orderBy('id', 'desc')->first();
        if ($lastSale) {
            // extraer solo el número si el formato fuera tipo B001-00000005
            $lastNumber = intval(substr($lastSale->number, 5));
            $number = $lastNumber + 1;
        } else {
            $number = 1;
        }
        return 'B001-' . str_pad($number, 8, '0', STR_PAD_LEFT);
    }

    public function nuevaVenta(){
        //limpiar variables
        $this->reset(['nombre', 'detalle', 'gravada', 'igv', 'totalImporte']);
        session()->forget('detalle');
        //session()->flush();
    }

    public function imprimirVoucher(){
        if (!$this->ventaid) {
            $this->dispatch('error',message:'Guarde la venta para imprimir');
        } else {
            $printerIp = '192.168.0.101';
            $printerPort = 9100;

            // Buscar venta y detalles
            $venta = Sale::with('details', 'client')->findOrFail($this->ventaid);

            try {
                // Conectar con la impresora por IP y puerto
                $connector = new NetworkPrintConnector($printerIp,$printerPort);
                $printer = new Printer($connector);

                // Encabezado
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->text("MINIMARKET CAPIBARA\n");
                $printer->text("Jr. Huancané 1244\n");
                $printer->text("RUC: 12345678912\n");
                $printer->text("-----------------------------\n");
                $printer->text("BOLETA: {$venta->number}\n");
                $printer->text("Fecha: " . $venta->created_at->format('Y-m-d H:i:s') . "\n");
                $printer->text("Cliente: " . $venta->cliente . "\n");
                $printer->text(str_repeat("-", 45) . "\n");

                // Encabezado de columnas
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text(str_pad("Producto", 20));
                $printer->text(str_pad("Cant", 5, ' ', STR_PAD_LEFT));
                $printer->text(str_pad("P.U", 9, ' ', STR_PAD_LEFT));
                $printer->text(str_pad("Total", 13, ' ', STR_PAD_LEFT));
                $printer->text("\n");
                $printer->text(str_repeat("-", 45) . "\n");

                // Detalles de productos
                foreach ($venta->details as $item) {
                    $printer->text(str_pad(substr($item->description, 0, 20), 20));
                    $printer->text(str_pad($item->quantity, 5, ' ', STR_PAD_LEFT));
                    $printer->text(str_pad(number_format($item->price, 2), 9, ' ', STR_PAD_LEFT));
                    $printer->text(str_pad(number_format($item->amount, 2), 13, ' ', STR_PAD_LEFT));
                    $printer->text("\n");
                }

                // Línea divisoria
                $printer->text(str_repeat("-", 45) . "\n");

                // Totales
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->text(str_pad("Gravada: ", 32, ' ', STR_PAD_LEFT) . "S/" . str_pad(number_format($venta->subtotal, 2), 8, ' ', STR_PAD_LEFT) . "\n");
                $printer->text(str_pad("IGV: ", 32, ' ', STR_PAD_LEFT) . "S/" . str_pad(number_format($venta->igv, 2), 8, ' ', STR_PAD_LEFT) . "\n");
                $printer->text(str_pad("TOTAL: ", 32, ' ', STR_PAD_LEFT) . "S/" . str_pad(number_format($venta->total, 2), 8, ' ', STR_PAD_LEFT) . "\n");


                // Footer
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->text("\n");
                $printer->text("¡Gracias por su compra!\n");
                $printer->feed(3);
                $printer->cut();
                $printer->close();
                $this->dispatch('alert',message:'Voucher imprimido satisfactoriamente');
            } catch (\Exception $e) {
                // Manejo de error
                $this->dispatch('error',message:'Error al imprimir: ' . $e->getMessage());
            }
        }
    }
}
