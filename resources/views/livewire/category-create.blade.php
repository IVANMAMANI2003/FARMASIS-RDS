<flux:modal name="edit-profile" class="md:w-96" wire:model="isOpen">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Crear Categoria</flux:heading>
        </div>
        <flux:input wire:model="name" label="Nombre categoria" placeholder="Analgésicos" />
        <div class="flex gap-2">
            <flux:spacer />
            <flux:button wire:click="$set('isOpen',false)">Cancelar</flux:button>
            <flux:button wire:click.prevent="store()" wire:loading.attr="disabled" wire:target="store" variant="primary">Registrar</flux:button>
        </div>
    </div>
</flux:modal>
