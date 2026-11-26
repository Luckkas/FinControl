<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TTransaction;
use Adianti\Widget\Container\THBox;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Wrapper\TDBUniqueSearch;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

class TesteDatagrid extends TPage
{
    public $form;
    public $datagrid;

    public function __construct()
    {
        parent::__construct();
        $this->form = new BootstrapFormBuilder('teste_datagrid_form');
        $this->form->setFormTitle('Teste de categoria');

        $criteria = new TCriteria();
        $criteria->add(new TFilter('status', '=', 'Y'));

        $bar_pesquisa = new TDBUniqueSearch('bar_pesquisa', 'conexao', 'Cliente', 'id', 'nome_cliente', 'nome_cliente', $criteria);
        $bar_pesquisa->style = 'width: 100%';

        $btnpes = new TButton('btnpes');
        $btnpes->class = 'btn btn-primary';
        $btnpes->setLabel('Buscar');
        $btnpes->setImage('fa:search');
        $btnpes->setAction(new TAction([$this, 'onReload']));

        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);

        $id_cliente = new TDataGridColumn('id', 'Id', 'center', '10%');
        $nome_cliente = new TDataGridColumn('nome_cliente', 'Nome', 'left', '30%');
        $email_cliente = new TDataGridColumn('email_cliente', 'Email', 'left', '60%');

        $this->datagrid->addColumn($id_cliente);
        $this->datagrid->addColumn($nome_cliente);
        $this->datagrid->addColumn($email_cliente);

        $this->datagrid->createModel();
        
        $this->form->addFields([$bar_pesquisa], [$btnpes])->layout = ['col-sm-10', 'col-sm-2'];

        $this->form->addFields([$this->datagrid]);

        parent::add($this->form);
    }


    public function onReload($param = NULL){
         
        try
        {
            $data = $this->form->getData();

            $this->datagrid->clear();

            TTransaction::open('conexao');

            $query = Cliente::where('status', '=', 'Y');

            if (!empty($data->bar_pesquisa)) {
                $query = Cliente::where('status', '=', 'Y')->where('id', '=', $data->bar_pesquisa);
            }

            $clientes = $query->load();

            if ($clientes) {
                foreach($clientes as $cliente) {
                    $this->datagrid->addItem($cliente);
                }
            }
            

            TTransaction::close();
        } catch(Exception $e){
            TTransaction::rollback();
            new TMessage('error', $e->getMessage());
        }

    }

    public function show(){
        if (empty($_GET['method'])) {
            $this->onReload();
        }
        parent::show();
    }
}