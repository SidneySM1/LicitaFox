<?php
// models/Municipio.php

class Municipio extends TRecord
{
    const TABLENAME  = 'municipios';
    const PRIMARYKEY = 'id_municipio';
    const IDPOLICY   = 'max'; // {max, serial}

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome_municipio');
        parent::addAttribute('id_estado');
    }
}
?>