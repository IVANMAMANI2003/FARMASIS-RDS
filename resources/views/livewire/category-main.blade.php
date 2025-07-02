<div>
    <div class="mx-6 mb-4">
        <h2 class="text-3xl font-bold text-gray-800 dark:text-white">Categorias</h2>
        <div class="border-b-2 border-indigo-600 w-60 mt-2"></div>
    </div>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl border-1 sm:rounded-lg px-4 py-4 dark:bg-gray-800/50 dark:bg-gradient-to-bl">
        <div class="flex items-center justify-between dark:text-gray-400">
            <!--Input de busqueda   -->
            <div class="flex items-center p-2 rounded-md flex-1">
                <label class="w-full relative text-gray-400 focus-within:text-gray-600 block">
                    <svg class="pointer-events-none w-8 h-8 absolute top-1/2 transform -translate-y-1/2 left-3" viewBox="0 0 25 25"  fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" class="w-6 h-6"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <x-input type="text" wire:model.live="search" class="w-full block pl-14" placeholder="Buscar elemento..."/>
                </label>
            </div>
            <!--Boton nuevo   -->
            <div class="lg:ml-40 ml-10 space-x-8">
                <flux:modal.trigger>
                    <flux:button wire:click="create()" variant="primary" icon="plus" class="cursor-pointer" class="bg-indigo-700 hover:bg-indigo-500">Nuevo</flux:button>
                </flux:modal.trigger>
                @include('livewire.category-create')
            </div>
        </div>
        <!--Tabla lista de items   -->
        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
            <table class="w-full divide-y divide-gray-200 table-auto">
              <thead class="bg-indigo-500 text-white">
                <tr class="text-left text-xs font-bold  uppercase">
                  <td scope="col" class="px-6 py-3">ID</td>
                  <td scope="col" class="px-6 py-3">Nombre categoría</td>
                  <td scope="col" class="px-6 py-3 text-center">Opciones</td>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:text-gray-400">
                @foreach($categories as $item)
                <tr class="text-sm font-medium text-gray-900">
                  <td class="px-6 py-4">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-500 text-white">
                      {{$item->id}}
                    </span>
                  </td>
                  <td class="px-6 py-4 dark:text-gray-400">{{$item->name}}</td>
                  <td class="px-6 py-4 flex justify-end gap-1">
                    <button wire:click="edit({{$item}})" class="bg-blue-700 text-white rounded-full px-2 cursor-pointer hover:bg-blue-500"> <!-- Usamos metodos magicos -->
                        <i class="fas fa-edit"></i>
                    <button>
                    <button wire:click="$dispatch('deleteItem',{{$item->id}})" class="bg-red-700 text-white rounded-full px-2 cursor-pointer hover:bg-red-500"> <!-- Usamos metodos magicos -->
                        <i class="fas fa-trash"></i>
                    <button>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
        </div>
        @if(!$categories->count())
            No existe ningun registro conincidente
        @endif
        @if($categories->hasPages())
        <div class="px-6 py-3">
            {{$categories->links()}}
        </div>
        @endif

        </div>
      </div>

      <!--Scripts - Sweetalert   -->
      @push('js')
        <script>
          Livewire.on('deleteItem',id=>{
            Swal.fire({
              title: 'Are you sure?',
              text: "You won't be able to revert this!",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Yes, delete it!'
              }).then((result) => {
                if (result.isConfirmed) {
                    //console.log(id);
                    //alert(id);
                    Livewire.dispatch('delItem',{category:id});
                    Swal.fire(
                        'Deleted!',
                        'Your file has been deleted.',
                        'success'
                    )

                }
              })
          });
        </script>
      @endpush
</div>
