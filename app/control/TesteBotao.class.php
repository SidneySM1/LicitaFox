<?php

class TesteBotao extends TPage {
    private $form, $datagrid, $pageNavigation, $loaded;
    private $limit;


    public function __construct()
    {
        parent::__construct();

        $this->form = new TQuickForm('form_localidades');
        $this->form->class = 'tform';

        // Criando o campo de seleção de UF
        $uf = new TCombo('uf');
        $uf->setChangeFunction(new TAction(array($this, 'onMudaUF')));

        // Carregando as UFs do banco de dados
        TTransaction::open('localidades'); // Substitua 'sqlite' pelo nome da sua conexão configurada
        $estados = Estado::getObjects(); // Estado é o modelo do Adianti que representa a tabela de estados

        $items = array();
        foreach ($estados as $obj) {
            $items[$obj->id_estado] = $obj->nome_estado;
        }
        $uf->addItems($items);
        TTransaction::close();

        $municipio = new TCombo('municipio');

        $this->form->addQuickField('UF', $uf);
        $this->form->addQuickField('Município', $municipio);
        $this->add($this->form);
    }

    public static function onMudaUF($param)
    {
        try {
            $municipios = array();
            TTransaction::open('localidades'); 

            $criteria = new TCriteria;
            $criteria->add(new TFilter('id_estado', '=', $param['uf']));
            $repository = new TRepository('Municipio');
            $objects = $repository->load($criteria);

            if ($objects) {
                foreach ($objects as $obj) {
                    $municipios[$obj->id_municipio] = $obj->nome_municipio;
                }
            }

            TTransaction::close();

            TCombo::reload('form_localidades', 'municipio', $municipios);
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }
}
