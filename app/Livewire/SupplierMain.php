<?php

namespace App\Livewire;

use App\Livewire\Forms\SupplierForm;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierMain extends Component{
    use WithPagination;
    public $isOpen=false;
    public $search;
    public ?Supplier $supplier;
    public SupplierForm $form;

    public function render(){
        $proveedores=Supplier::where('fullname','LIKE','%'.$this->search.'%')->latest("id")->paginate(10);
        return view('livewire.supplier-main',compact('proveedores'));
    }

    public function create(){
        $this->form->reset();
        $this->isOpen=true;
    }

    public function store(){
        $this->validate();
        if (!isset($this->supplier->id)) {
            Supplier::create($this->form->all());
        }else{
            $this->supplier->update($this->form->all());
        }
       $this->reset();
    }

    public function edit(Supplier $item){
        $this->isOpen=true;
        $this->supplier=$item;
        $this->form->fill($item);
    }

    public function delete(Supplier $item){
        $item->delete();
    }

    public function updatingSearch(){
        $this->resetPage();
    }


}
