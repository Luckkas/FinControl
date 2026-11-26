<?php

use Adianti\Database\TRecord;

class Categoria extends TRecord
{
    const TABLENAME = 'categorias';
    const PRIMARYKEY = 'id_categ';
    const IDPOLICY = 'serial';

    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('nome_categ');
        parent::addAttribute('criado_em');
        parent::addAttribute('deletado_em');
    }
}