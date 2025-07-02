<flux:modal name="edit-profile" variant="flyout" wire:model="isOpen">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Update profile</flux:heading>
            <flux:text class="mt-2">Make changes to your personal details.</flux:text>
        </div>
        <flux:input wire:model="form.fullname" label="Nombre completo" />
        <flux:input wire:model="form.document" label="Documento" />
        <flux:input wire:model="form.cellphone"	 label="Celular" />
        <flux:input wire:model="form.email" label="Email" />
        <flux:input wire:model="form.address" label="Dirección" />

        <div class="flex gap-2">
            <flux:spacer />
            <flux:button wire:click="$set('isOpen',false)">Cancelar</flux:button>
            <flux:button wire:click.prevent="store()" wire:loading.attr="disabled" wire:target="store" variant="primary">Registrar</flux:button>
        </div>
    </div>
</flux:modal>
