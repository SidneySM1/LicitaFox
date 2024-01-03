<?php
class Observacao extends TRecord
{
    const TABLENAME = 'observacoes';
    const PRIMARYKEY = 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id_user');
        parent::addAttribute('id_licitacao');
        parent::addAttribute('username');
        parent::addAttribute('comentario');
        parent::addAttribute('created_at');
    }
}
?>