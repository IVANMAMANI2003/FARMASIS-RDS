<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <h1 class="text-3xl border-b-2 border-indigo-400 w-60">Realizar venta</h1>
    <div class="p-4 relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        {{-- <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" /> --}}
        <div>
            <div class="flex gap-3">
                <flux:input wire:model="dni" label="Ingresa DNI del cliente" icon="credit-card" mask="99999999" placeholder="12345678" />
                <flux:button wire:click="buscarDNI()" icon="magnifying-glass" class="mt-6">Buscar</flux:button>
            </div>
            <div class="bg-indigo-100 text-gray-900 p-2 rounded-lg mt-2 text-xs">
                <p>Nombre completo: <span class="font-bold">{{$nombre}}</span></p>
            </div>
        </div>
        <div class="mt-6 grid grid-cols-2 gap-4">
            <div>
                <flux:input wire:model.live="search" id="search" label="Buscar producto" icon="cube" placeholder="Amoxicilina 500mg" />
                <div class="grid grid-cols-3 p-2 text-xs gap-2">
                    <div class="fondo">Nombre</div>
                    <div class="fondo">Cantidad</div>
                    <div class="fondo">Precio</div>
                    @foreach ($productos as $item)
                        <div wire:click="selecionarProducto({{$item}})" class="underline tabla cursor-pointer">{{$item->name}}</div>
                        <div class="tabla text-center">{{$item->quantity}}</div>
                        <div class="tabla text-right">S/. {{$item->price}}</div>
                    @endforeach
                </div>
            </div>
            <div>
                <h2 class="text-sm font-medium text-zinc-800 dark:text-white">Detalle de venta</h2>
                 @if(session('detalle'))
                    <div class="grid grid-cols-4 p-2 text-xs gap-2">
                        <div class="fondo">Nombre</div>
                        <div class="fondo">Cantidad</div>
                        <div class="fondo">Precio</div>
                        <div class="fondo">Subtotal</div>
                        @foreach (session('detalle') as $id => $details)
                            <div class="tabla flex gap-1">
                                <flux:icon.trash wire:click="deleteProducto({{$id}})" variant="mini" class="text-red-500 cursor-pointer"/>
                                {{$details['name']}}
                            </div>
                            <div class="tabla flex gap-2 justify-center">
                                <flux:icon.minus-circle wire:click="restarCantidad({{$id}})" variant="mini" class="cursor-pointer"/>
                                {{$details['quantity']}}
                                <flux:icon.plus-circle wire:click="sumarCantidad({{$id}})" variant="mini" class="cursor-pointer"/>
                            </div>
                            <div class="tabla text-right">S/. {{$details['price']}}</div>
                            <div class="tabla text-right">S/. {{$details['subtotal']}}</div>
                        @endforeach
                    </div>
                @else
                    <p>El Detalle esta vacio</p>
                @endif
                <div class="px-2">
                    <p class="text-right">Gravada: S/. {{number_format($gravada,2)}}</p>
                    <p class="text-right">IGV 18%: S/. {{number_format($igv,2)}}</p>
                    <p class="text-right">Total importe: S/. {{number_format($totalImporte,2)}}</p>
                </div>
            </div>
        </div>
        <div class="mt-6 text-center">
            <flux:button wire:click="guardarVenta()" variant="primary" class="cursor-pointer">Guardar venta</flux:button>
            <flux:button wire:click="imprimirVoucher()" variant="primary" class="cursor-pointer">Imprimir voucher</flux:button>
            <flux:button wire:click="nuevaVenta()" variant="primary" class="cursor-pointer">Nueva venta</flux:button>
        </div>
    </div>
    <script>
        // Escuchar evento de Livewire y hacer focus
        document.addEventListener('livewire:init', () => {
            Livewire.on('focus-search', () => {
                document.querySelector('#search').focus();
            });
        });
    </script>
</div>
