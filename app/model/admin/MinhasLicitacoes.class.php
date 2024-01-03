<?php 

class MinhasLicitacoes extends TRecord
{
    const TABLENAME = 'minhas_licitacoes';
    const PRIMARYKEY= 'id_licitacao';
    const IDPOLICY =  'max';

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);

        parent::addAttribute('id_licitacao');
        parent::addAttribute('titulo');
        parent::addAttribute('municipio_IBGE');
        parent::addAttribute('uf');
        parent::addAttribute('orgao');
        parent::addAttribute('abertura_datetime');
        parent::addAttribute('objeto');
        parent::addAttribute('link');
        parent::addAttribute('linkExterno');
        parent::addAttribute('municipio');
        parent::addAttribute('abertura');
        parent::addAttribute('aberturaComHora');
        parent::addAttribute('id_tipo');
        parent::addAttribute('tipo');
        parent::addAttribute('valor');
        parent::addAttribute('id_portal');
        parent::addAttribute('emailContato');
        parent::addAttribute('status');

    }
}



?>