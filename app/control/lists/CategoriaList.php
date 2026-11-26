<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Control\TWindow;
use Adianti\Database\TTransaction;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Dialog\TToast;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

class CategoriaList extends TPage
{
    private $datagrid;
    private $form;

    function __construct()
    {
        parent::__construct();

        $this->form = new BootstrapFormBuilder('Listar Categorias');
        $this->form->setFormTitle('Listar categorias');

        $id = new TEntry('id_categ');
        $nome_categoria = new TEntry('nome_categ');


        $id->setSize('10%');
        $nome_categoria->setSize('30%');


        $this->form->addFields([new TLabel('ID')], [$id]);
        $this->form->addFields([new TLabel('Nome')], [$nome_categoria]);
        $this->form->addAction('Buscar', new TAction(array($this, 'onBuscarCategorias')), 'fa:search');
        $this->form->addAction('Criar categoria', new TAction(array($this, 'onSaveCategoriaWindow')), 'fa:pen');


        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);


        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid('Categorias'));
        $this->datagrid->style = 'width: 100%';
        $id = new TDataGridColumn('id', 'ID', 'center', '5%');
        $nome = new TDataGridColumn('nome', 'Nome', 'left', '40%');

        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($nome);

        $this->datagrid->createModel();

        $container->add($this->datagrid);
        

        parent::add($container);

    }


  //-- Janela do cadastro de categorias
    public static function onSaveCategoriaWindow($param)
    {
        $form = new BootstrapFormBuilder('onSaveCategoriaForm');
        $nome = new TEntry('nome_categ');
        $nome->addValidation('Nome', new TRequiredValidator);
        $nome->setSize('50%');

        $form->addFields([new TLabel('Nome')], [$nome]);
        $form->addAction('Cadastrar', new TAction(array(__CLASS__, 'SaveCategoriaWindow')), 'fa:save');

        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add($form);

        $window = TWindow::create('Cadastro de Categoria', 0.5, 200);

        $window->add($container);
        $window->show();

    }


    public function onBuscarCategorias($param) 
    {
        new TMessage('info', 'Teste');
    }


    public static function SaveCategoriaWindow($param)
    {

        $dados = TForm::getFormByName('onSaveCategoriaForm');
        $dados->nome_categ;
        try
        {
        TTransaction::open('conexao');

        $categoria = new Categoria();
        $categoria->nome_categ = $dados->nome_categ;
        $categoria->criado_em = date('Y-m-d H:i:s');

        $categoria->store();

        TTransaction::close();
        
        TToast::show('success', 'Categoria Criada com sucesso! ', 'top right', 'far:check-circle');
        } catch(Exception $e)
        {
            TTransaction::rollback();
            new TMessage('error', $e->getMessage());
        }

    }
}