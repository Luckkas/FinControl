<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Control\TWindow;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridActionGroup;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TFile;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;


class DocumentosList extends TPage
{
    private $form;
    private $datagrid;


    public function __construct()
    {
        parent::__construct();
        //=================================================================
        //--FORM

        $this->form = new BootstrapFormBuilder('doc_form');
        $this->form->setFormTitle('Documentos');

        $id_doc = new TEntry('idDocumentos');
        $id_doc->id = 'idDoc';

        $nomeDoc = new TEntry('nomeDocumentos');
        $nomeDoc->id = 'nomeDoc';

        $id_doc->setSize(300);
        $nomeDoc->setSize(400);

        $this->form->addFields([new TLabel('Id: ')], [$id_doc]);
        $this->form->addFields([new TLabel('Nome do documento: ')], [$nomeDoc]);

        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search');
        $this->form->addAction('Upload de documento', new TAction([$this, 'onUploadDocWindow']), 'fa:upload');


        
        //=================================================================
        //--DATAGRID

        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';

        $id_doc_column = new TDataGridColumn('id_doc', 'Id', 'center');
        $nome_doc = new TDataGridColumn('nome_arquivo', 'Nome do documento', 'left');
        $data_upload = new TDataGridColumn('data_upload', 'Data de upload', 'left');

        //=================================================================
        //-- VISUALIZAR DOCUMENTO

        $action_visualizar_doc = new TDataGridAction(['DocumentosService', 'onVisualizarDoc']);
        $action_visualizar_doc->setLabel('Visualizar documento');
        $action_visualizar_doc->setImage('fa:folder-open');
        $action_visualizar_doc->setField('id_doc');

        $action_group = new TDataGridActionGroup('Opções', 'fa:search');
        $action_group->addAction($action_visualizar_doc);
 
        //=================================================================
        //--ADICIONA NA DATAGRID

        $this->datagrid->addColumn($id_doc_column);
        $this->datagrid->addColumn($nome_doc);
        $this->datagrid->addColumn($data_upload);
         
        $this->datagrid->addActionGroup($action_group);

        $this->datagrid->createModel();


        //=================================================================
        //--ADICIONA OS ELEMENTOS NA TELA
        $container = new TVBox();
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($this->datagrid);

        parent::add($container);

        $this->onReload();
    }

    /**
     * onReload
     */

    public function onReload($param = null)
    {
        try
        {       
            $this->datagrid->clear();

            $objDoc = Documentos::getDocumentos();

            foreach($objDoc as $doc){
                $item = (object) $doc;
                $this->datagrid->addItem($item);
            }

        } catch (Exception $e) {
            throw new Exception('Ocorreu um erro: ' . $e->getMessage());
            TTransaction::rollback();
        }
    }



    /**
     * Janela para form de upload do arquivo
     */
    public function onUploadDocWindow($param) 
    {
        $form_upload = new BootstrapFormBuilder('upload_doc');        

        $upload_file = new TFile('nome_arquivo');
        $upload_file->setSize(300);


        $form_upload->addFields([new TLabel('Escolha o documento: ')], [$upload_file]);

        $form_upload->addAction('Upload', new TAction([$this, 'uploadDoc']), 'fa:upload');

        $container = new TVBox();
        $container->style = 'width: 100%';
        $container->add($form_upload);

        $window = TWindow::create('Upload de documentos', 0.5, null);
        $window->add($container);

        $window->show();
    }
    
    /**
     * Salva no banco o arquivo e depois exlcui da /tmp
     */
    public function uploadDoc($param) 
    {
        try
        {
           
            $path = 'tmp/' . $param['nome_arquivo']; // upload_file vem como o nome do documento

            $arquivo_fisico = file_get_contents($path);

            $documento = ['nome_arquivo' => $param['nome_arquivo'],
                          'arquivo_fisico' => $arquivo_fisico
            ];


            TTransaction::open('conexao');

            $objDoc = new Documentos();
            $objDoc->nome_arquivo = $documento['nome_arquivo'];
            $objDoc->arquivo_fisico = $documento['arquivo_fisico'];
            $objDoc->store();

            TTransaction::close();

            unlink($path); // Exclui o arquivo dps do store no banco

            $this->onReload();

        } catch (Exception $e){
            TTransaction::rollback();
            throw new Exception('Ocorreu um erro: ' . $e->getMessage());
        }
    }

    /**
     * 
     */

    public function onSearch($param)
    {

        try
        {

            $this->datagrid->clear();

            TTransaction::open('conexao');

            $id_doc = $param['idDocumentos'];
            $nome_doc = $param['nomeDocumentos'];

            $criteria = new TCriteria();

            if (isset($param)){
                if (!empty($id_doc)){
                    $criteria->add(new TFilter('id_doc', '=', $id_doc));
                }

                if (!empty($nome_doc)){
                    $criteria->add(new TFilter('nome_arquivo', 'LIKE', '%' . $nome_doc . '%'));
                }    
            }

            $repo = new TRepository('Documentos');
            
            $loads = $repo->load($criteria);

            
            TTransaction::close();

            if (empty($loads)) {
                new TMessage('warning', 'Nenhum documento encontrado');
            }


            foreach($loads as $load){
                $item = (object) $load;
                $this->datagrid->addItem($item);
            }

        } catch (Exception $e) {
           
            TTransaction::rollback();
            throw new Exception('Ocorreu um erro: ' . $e->getMessage());
        }
    }
}