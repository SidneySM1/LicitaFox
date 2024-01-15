<?php

class LicitacoesUser extends TRecord
{
    const TABLENAME = 'licitacoes_user';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'max';

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);

        parent::addAttribute('user_id');
        parent::addAttribute('licitacao_id');
        parent::addAttribute('status');
    }
}



?>