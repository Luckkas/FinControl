<?php

use Adianti\Database\TRecord;

class Cliente extends TRecord
{
    const TABLENAME = 'clientes';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'serial';

    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('nome_cliente');
        parent::addAttribute('email_cliente');
        parent::addAttribute('status');
    }
}