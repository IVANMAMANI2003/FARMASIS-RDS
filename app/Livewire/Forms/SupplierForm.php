<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class SupplierForm extends Form{
    #[Validate('required|min:5')]
    public $fullname,$document,$cellphone;
    public $email,$address;
}
