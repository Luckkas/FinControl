<?php

use Adianti\Control\TPage;
use Adianti\Control\TWindow;
use Adianti\Database\TTransaction;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Dialog\TMessage;

    class DocumentosService extends TPage
{

    public function onVisualizarDoc($param = null)
    {

        try
        {
            $id_doc = null;

            if (!empty($param['id_doc'])){
                $id_doc = $param['id_doc'];
            } else {
                throw new Exception('warning', 'Não foi possível visualizar o documento');
            }

            TTransaction::open('conexao');

            $objDoc = Documentos::where('id_doc', '=', $id_doc)->first();

            TTransaction::close();

            $nome_arquivo = $objDoc->nome_arquivo;
            $path = 'tmp/';

            $arquivo_fisico = $objDoc->arquivo_fisico;


            if (!is_dir($path)){
                mkdir($path, 0775, true);
            }


            file_put_contents($path . $nome_arquivo, $arquivo_fisico);
            

            $object = new TElement('object');
            $object->data  = $path . $nome_arquivo;
            $object->type  = 'application/pdf';
            $object->style = "width: 100%; height:calc(100% - 10px)";
            $object->add('O navegador não suporta a exibição deste conteúdo, <a style="color:#007bff;" target=_newwindow href="'.$object->data.'"> clique aqui para baixar</a>...');

            $window = TWindow::create('Visualizar Documento', '100%', '700px', null);
            $window->add($object);
            $window->show();


        } catch (Exception $e) {
            TTransaction::rollback();
            throw new Exception('Ocorre um erro: ' . $e->getMessage());
        }



    }
}