<?php
class Estado extends TRecord
{
    const TABLENAME  = 'estados';
    const PRIMARYKEY = 'id_estado';
    const IDPOLICY   = 'max'; // {max, serial}

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id_estado');
        parent::addAttribute('nome_estado');
        parent::addAttribute('sigla_estado');
    }
}
?>