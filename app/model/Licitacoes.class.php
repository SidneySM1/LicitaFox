<?php 

class Licitacoes extends TRecord
{
    const TABLENAME = 'licitacoes'; // Nome da tabela no banco de dados
    const PRIMARYKEY= 'id';         // Chave primária da tabela
    const IDPOLICY =  'serial';     // Política de ID (serial, auto, max, etc.)

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);

        // Adicionando atributos correspondentes às colunas da tabela
        parent::addAttribute('id');
        parent::addAttribute('identificador');
        parent::addAttribute('titulo');
        parent::addAttribute('municipio_estado');
        parent::addAttribute('municipio');
        parent::addAttribute('estado');
        parent::addAttribute('modalidade');
        parent::addAttribute('modalidade_id');
        parent::addAttribute('abertura');
        parent::addAttribute('orgao');
        parent::addAttribute('objeto');
        parent::addAttribute('site_original');
        parent::addAttribute('publicado_em');
        parent::addAttribute('portal');
        parent::addAttribute('portal_id');
    }
}

?>