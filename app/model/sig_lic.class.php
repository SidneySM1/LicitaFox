<?php 

class sig_lic extends TRecord
{
    const TABLENAME = 'sig_lic'; // Nome da tabela no banco de dados
    const PRIMARYKEY= 'seq';         // Chave primária da tabela
    const IDPOLICY =  'serial';     // Política de ID (serial, auto, max, etc.)

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);

        // Adicionando atributos correspondentes às colunas da tabela
        parent::addAttribute('seq');
        parent::addAttribute('cod');
        parent::addAttribute('org');
        parent::addAttribute('est');

    }
}

?>