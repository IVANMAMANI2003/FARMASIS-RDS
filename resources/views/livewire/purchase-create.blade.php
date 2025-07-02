<div>
    <div>Registro de compras</div>
    <div class="grid grid-cols-2 gap-3">
        <div class="relative p-3 rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            <flux:input icon="magnifying-glass" placeholder="Buscar producto"/>
        </div>
        <div class="relative p-3 rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20 -z-10"/>
            <div class="flex gap-2">
                <flux:input label="Nro. Comprobante"/>
                <flux:input type="date" label="Date" />
            </div>
            <div class="mt-2">
                <flux:select placeholder="Selecciones proveedor" class="z-10">
                    <flux:select.option>Photography</flux:select.option>
                    <flux:select.option>Design services</flux:select.option>
                    <flux:select.option>Web development</flux:select.option>
                    <flux:select.option>Accounting</flux:select.option>
                    <flux:select.option>Legal services</flux:select.option>
                    <flux:select.option>Consulting</flux:select.option>
                    <flux:select.option>Other</flux:select.option>
                </flux:select>
            </div>
        </div>
    </div>
    <div class="relative mt-3 p-3 rounded-xl border border-neutral-200 dark:border-neutral-700">
        <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        <table>
            <tr>
                <th>Descripción</td>
                <th>Cant.</th>
                <th>Precio</th>
                <th>Venc.</th>
                <th>SubTotal</th>
                <th>opt</th>
            </tr>
            <tr>
                <td scope="col" class="py-1"><flux:input value="Paracetamol"/></td>
                <td><flux:input value="3"/></td>
                <td><flux:input value="0.50"/></td>
                <td><flux:input type="date"/></td>
                <td><flux:input value="1.50"/></td>
                <td><flux:button variant="danger" icon="trash"></flux:button></td>
            </tr>
            <tr>
                <td><flux:input value="Paracetamol"/></td>
                <td><flux:input value="3"/></td>
                <td><flux:input value="0.50"/></td>
                <td><flux:input type="date"/></td>
                <td><flux:input value="1.50"/></td>
                <td><flux:button variant="danger" icon="trash" class="rounded-full"></flux:button></td>
            </tr>
        </table>
    </div>
    <div class="flex mt-3 justify-end gap-2">
        <flux:button>Cancelar</flux:button>
        <flux:button variant="primary">Registrar</flux:button>
    </div>

</div>

