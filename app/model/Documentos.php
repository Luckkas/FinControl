<?php

use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRecord;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Widget\Util\TCalendar;
use Dom\Document;

class Documentos extends TRecord
{
    const TABLENAME = 'documentos';
    const PRIMARYKEY = 'id_doc';
    const IDPOLICY = 'serial';

    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('nome_arquivo');
        parent::addAttribute('arquivo_fisico');
        parent::addAttribute('caminho_garage');
        parent::addAttribute('data_upload');
    }

    /**
     * * Retorna todos os documentos. funcao ja abre transacao com o banco
     */
    public static function getDocumentos()
    {
        try
        {
            TTransaction::open('conexao');

            $objDoc = self::where('id_doc', 'IS NOT', NULL )->load();

            TTransaction::close();

            return $objDoc;

        } catch (Exception $e) {
            throw new Exception('Ocorreu algum erro: ' . $e->getMessage());
            TTransaction::rollback();
        }

    }
}