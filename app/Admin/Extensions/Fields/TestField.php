<?php

namespace App\Admin\Extentions\Fields;

use Encore\Admin\Form\Field;

class TestField extends Field
{

    protected $view = 'admin.ckeditor';


    public function render()
    {


        return parent::render(); // TODO: Change the autogenerated stub
    }
}
