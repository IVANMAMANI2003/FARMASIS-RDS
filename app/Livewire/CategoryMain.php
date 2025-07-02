<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryMain extends Component{
    use WithPagination;
    public $search;
    public $isOpen=false;
    public $category;
    public $name;

    public function render(){
        $categories=Category::where('name','LIKE','%'.$this->search.'%')->paginate();
        return view('livewire.category-main',compact('categories'));
    }

    public function create(){
        $this->isOpen=true;
        $this->reset(['category','name']);
    }

    public function edit(Category $category){
        $this->category=$category;
        $this->name=$category->name;
        $this->isOpen=true;
    }

    public function store(){
        $this->validate([
            'name'=>'required'
        ]);
        if(!isset($this->category->id)){
            Category::create([
                'name'=>$this->name
            ]);
        }else{
            $this->category->update([
                'name'=>$this->name
            ]);
        }
        $this->reset(['isOpen','category']);
        //$this->emitTo('CategoryCrud','render');
        $this->dispatch('alert',message:'Registro creado satisfactoriamente');
    }

    #[On('delItem')]
    public function delete(Category $category){
        $category->delete();
    }

    public function updatingSearch(){
        $this->resetPage();
    }

}
