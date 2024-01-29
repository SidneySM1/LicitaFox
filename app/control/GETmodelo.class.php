<?php
$zsuper;
class GETmodelo extends TPage {
    private $form, $datagrid, $pageNavigation, $loaded=false;
    private int $limit = 10;
    private $panel;

    use ListTrait;

    private $database         = 'licitacoesdb';
    private $activeRecord     = 'licitacoes';
    private $applicationTitle = 'Busca de licitações';
    private $filterFormTitle  = 'Filtros';
    private $editForm         = 'Licitacoes';
    private $keyField         = 'identificador';
    private $fieldFocus       = 'input_quick_search';
    //private $limit;
    private $limit_padrao = 10; // 10, 15, 20, 50, 100
    private $qtd_filtros = 0;



    public function __construct()
    {
        parent::__construct();

        $this->form = new BootstrapFormBuilder('form_localidades');
        //$this->form->setFormTitle('Busca');

        //Definindo os obj para o form
        $uf = new TCombo('uf');
        TTransaction::open('localidades');
        $estados = Estado::getObjects();
        $items = [];
        foreach ($estados as $obj) {
            $items[$obj->sigla_estado] = "$obj->nome_estado - $obj->sigla_estado";
        }
        asort($items);
        $uf->addItems($items);
        TTransaction::close();
        $uf->setSize('75%');
        $uf->setChangeAction(new TAction([$this, 'onMudaUF']));
        
        // Combo de Municípios (inicialmente vazio)
        $municipios = new TCombo('mun');
        $municipios->addItems(['' => '']);
        $municipios->setSize('75%');
      

        $data_insercao = new TDate('data_insercao');
        $data_insercao->setMask('dd/mm/yyyy');
        $data_insercao->setSize('75%');

        $data_abertura = new TDate('data_abertura');
        $data_abertura->setMask('dd/mm/yyyy');
        $data_abertura->setSize('75%');

        $data_abertura_fim = new TDate('data_abertura_fim');
        $data_abertura_fim->setMask('dd/mm/yyyy');
        $data_abertura_fim->setSize('75%');

        $modalidade = new TCombo('modalidade');
        $modalidade->addItems([
            '0' => 'Sem modalidade definida',
            '1' => 'Convite',
            '2' => 'Concorrência',
            '3' => 'Leilão',
            '4' => 'Tomada de preços',
            '5' => 'Pregão eletrônico',
            '8' => 'Pregão presencial',
            '11' => 'Chamada/Chamamento público'
        ]);
        $modalidade->setSize('90%');

        $portal = new TCombo('id_portal');
        $portal->addItems([
            '9999' => 'Demais Portais (MUNICIPAIS,etc)',
            '1' => 'Compras Governamentais/ComprasNET',
            '2' => 'Licitações-E/Banco do Brasil',
            '3' => 'BEC-SP',
            '4' => 'Portal Nacional de Compras Públicas (PNCP)',
            '5' => 'PRODESP (imprensaoficial.com.br)',
            '6' => 'BLL Compras',
            '7' => 'BNC Compras',
            '8' => 'BBMNET',
            '9' => 'Portal de Compras Públicas',
            '10' => 'LicitaNET',
            '11' => 'Compras BR'
        ]);
        $portal->setSize('90%');

        $palavra_chave = new TEntry('palavra_chave');
        $palavra_chave->setSize('90%');

        // Adição de campos ao form
        $this->form->addFields([new TLabel('UF:')], [$uf],[new TLabel('Município:')], [$municipios]);
        $this->form->addFields([new TLabel('Abertura do processo (De - Até):')], [$data_abertura],[new Tlabel('Data-abertura até')], [$data_abertura_fim]);
        $this->form->addFields([new TLabel('Modalidade:')], [$modalidade]);
        $this->form->addFields([new TLabel('Portal:')], [$portal]);
        $this->form->addfields([new TLabel('Palavra-chave:')], [$palavra_chave],[new TLabel('Data-Captura:')], [$data_insercao]);
        //$this->form->addFields([new TLabel('Data-Captura:')], [$data_insercao]);

        
        /// MANTER DADOS NO FORM
        
        $data = TSession::getValue(__CLASS__.'_filter_data');
        if (!empty($data->uf)){
            TTransaction::open('localidades');
            $estadoId = $this->searchUf_Id($data->uf);
            $municipios2 = array();
            if ($estadoId) {
                $criteriaMunicipio = new TCriteria;
                $criteriaMunicipio->add(new TFilter('id_estado', '=', $estadoId));
                $repositoryMunicipio = new TRepository('Municipio');
                $objects = $repositoryMunicipio->load($criteriaMunicipio);
                
                if ($objects) {
                    foreach ($objects as $obj) {
                        $municipios2[$obj->nome_municipio] = $obj->nome_municipio;
                    }
                    $municipios2 = ['' => ''] + $municipios2;
                }
                $municipios->addItems($municipios2);
            }
            TTransaction::close();
        }
        
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        //$this->form->addAction('Limpar', new TAction([$this, 'onClear']), 'fa:times red');
        $expandir = $this->form->addExpandButton('Expandir', '', false);
        //$expandir->start_hidden = true;
        

        /// TABELA AQUI
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        $this->datagrid->datatable = 'true';


        $colunaObjeto = new TDataGridColumn('objeto', 'Objeto', 'left', '50%');
        $colunaObjeto->setTransformer(function ($value, $object, $row) {
                        return $this->truncarTexto($value);
                    });
        //$col_identificador    = new TDataGridColumn('identificador', 'Id', 'right', '10%');
        $col_titulo  = new TDataGridColumn('titulo', 'Titulo', 'left', '20%');
        $col_municipio = new TDataGridColumn('municipio', 'Cidade', 'center', '10%');
        $col_data = new TDataGridColumn('abertura', 'Abertura', 'center','10%');
        $col_data->setTransformer(function($value, $object, $row) {
            //$rest = substr($value, -19, 10); 
            $rest = TDate::date2br($value);
            return $rest; 
        });

        $col_titulo->setTransformer(function($value, $object, $row) {
            TTransaction::open('licitacoes');
            
            // Verifica se a licitação está na lista de licitações ativas
            //$minhaLicitacao = MinhasLicitacoes::where('id_licitacao', '=', $object->identificador)->first();
            $minhaLicitacao = LicitacoesUser::where('licitacao_id', '=', $object->identificador)
                                   ->where('user_id', '=', TSession::getValue('userid'))
                                   ->first();
            if ($minhaLicitacao) {
                // Se encontrada, adiciona um ícone de exclamação verde ao título para exibição
                $value .= " <span style='color:green;'>INSERIDA!</span>";
            } 
            TTransaction::close();
            return $value; // Retorna o título modificado apenas para exibição
        });
        $this->datagrid->addColumn($col_titulo);
        $this->datagrid->addColumn($col_municipio);
        $this->datagrid->addColumn($colunaObjeto);
        $this->datagrid->addColumn($col_data);
        

        $col_titulo->setAction(new TAction([$this, 'onReload']), ['order' => 'titulo']);
        $col_data->setAction(new TAction([$this, 'onReload']), ['order' => 'abertura']);

        // ações em grupo
        //$action1 = new TDataGridAction([$this, 'onView'],     ['titulo'=>'{titulo}','orgao' => '{orgao}',  'objeto' => '{objeto}', 'site_original' => '{site_original}'] + (array)TSession::getValue(__CLASS__.'_filter_data'), );
        $action1 = new TDataGridAction([$this, 'onView'],     ['titulo'=>'{titulo}','orgao' => '{orgao}',  'objeto' => '{objeto}', 'site_original' => '{site_original}']);

        //$action2 = new TDataGridAction([$this, 'onDelete'],   ['identificador' => '{identificador}' ] );
        $action2 = new TDataGridAction([$this, 'onDelete'], [
            'identificador' => '{identificador}',
            'titulo' => '{titulo}',
            'municipio_IBGE' => '{municipio_IBGE}',
            'uf' => '{uf}',
            'orgao' => '{orgao}',
            'abertura_datetime' => '{abertura_datetime}',
            'objeto' => '{objeto}',
            'link' => '{link}',
            'site_original' => '{site_original}',
            'municipio' => '{municipio}',
            'abertura' => '{abertura}',
            'aberturaComHora' => '{aberturaComHora}',
            'id_tipo' => '{id_tipo}',
            'tipo' => '{tipo}',
            'valor' => '{valor}',
            'id_portal' => '{id_portal}',
        ]+ (array)TSession::getValue(__CLASS__.'_filter_data'));

        $action3 = new TDataGridAction([$this, 'onInsert'], ['identificador' => '{identificador}']);        
                
        $action1->setLabel('Ver info');
        $action1->setImage('fa:search #7C93CF');
        
        $action2->setLabel('Descartar licitação');
        $action2->setImage('far:trash-alt red');
        
        $action3->setLabel('Inserir licitação');
        $action3->setImage('far:hand-pointer green');

        //$action4 = new TDataGridAction([$this, 'onsite_original'],   ['site_original' => '{site_original}' ]+ (array)TSession::getValue(__CLASS__.'_filter_data') );
        $action4 = new TDataGridAction([$this, 'onsite_original'],   ['site_original' => '{site_original}' ]);

        $action4->setLabel('Acessar portal');
        $action4->setImage('fa:external-link-alt green');

        $action_group = new TDataGridActionGroup('Actions ', 'fa:th');
        
        $action_group->addHeader('Opções disponiveis');
        $action_group->addAction($action1);
        $action_group->addAction($action4);
        $action_group->addHeader('Database');
        $action_group->addAction($action3);
        $action_group->addAction($action2);

        $this->datagrid->addActionGroup($action_group);

        $this->datagrid->createModel();
        //$this->datagrid->clear();
        
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        //$this->pageNavigation->style = 'padding-top:0px; margin-right:10px;';

        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form);
        $vbox->add($this->panel = TPanelGroup::pack('', $this->datagrid, $this->pageNavigation)); 
        $this->panel->style = 'display:none;'; 
        $this->onDropdownExport();     
        parent::add($vbox);
        TSession::setValue(__CLASS__.'_onReload', 1);
    }


    public function onClearSession()
    {
        // clear session filters
        TSession::setValue(__CLASS__.'_filter_estado',         NULL);
        TSession::setValue(__CLASS__.'_filter_municipio',      NULL);
        TSession::setValue(__CLASS__.'_filter_objeto',         NULL);
        TSession::setValue(__CLASS__.'_filter_abertura',       NULL);
        TSession::setValue(__CLASS__.'_filter_abertura_fim',       NULL);
        TSession::setValue(__CLASS__.'_filter_modalidade',     NULL);
        TSession::setValue(__CLASS__.'_filter_portal',         NULL);
        TSession::setValue(__CLASS__.'_filter_publicado',      NULL);

        TSession::setValue(__CLASS__.'_filter_data',           NULL);
        TSession::setValue(__CLASS__.'_filter_counter',        0);
    }
    
    function show()
    {
        $onReload = TSession::getValue(__CLASS__.'_onReload');
        if ($onReload = 1){
            $this->onReload();
            
        }
        parent::show();
    }
    

    public function truncarTexto($texto, $limite = 140)
    {
        $texto = strip_tags($texto); 
        if (mb_strlen($texto, 'UTF-8') > $limite) {
            return mb_substr($texto, 0, $limite, 'UTF-8') . '...';
        }
        return $texto;
    }
    
    public function onsite_original($param)
    {
       TScript::create('window.open("'.$param['site_original'].'","_blank")');
    }

    public function onView($param)
    {
        
        $titulo = $param['titulo'];
        $orgao = $param['orgao'];
        $objeto = $param['objeto'];
        $link = $param['site_original'];

        $botaoLink = new TElement('a');
        $botaoLink->href = $link;
        $botaoLink->target = '_blank';
        $botaoLink->class = 'btn btn-primary';
        $botaoLink->add('Acessar Portal');
        new TMessage('info', "<h4>$titulo</h4> <br> Orgão responsavel: <b>$orgao</b> <br> Objeto : <b>$objeto</b><br> $botaoLink");
        
    }
    
    public function onDelete($param)
    {
        try {
            TTransaction::open('licitacoes');

            // Busca pela licitação a ser removida (pode não existir)
            $licitacao = MinhasLicitacoes::find($param['identificador']);
            
            // Mesmo se não existir na tabela MinhasLicitacoes, cria uma nova entrada em MinhasLicitacoesRemovidas
            $licitacaoRemovida = new MinhasLicitacoesRemovidas();
            $licitacaoRemovida->fromArray($param); // Assume que $param contém todas as informações necessárias
            $licitacaoRemovida->store();

            // Se a licitação existir, remove-a da tabela MinhasLicitacoes
            if ($licitacao) {
                $licitacao->delete();
            } 
            
            TTransaction::close();

            new TMessage('info', "Licitação removida. {$param['identificador']} foi adicionada à lista de licitações removidas.");

        } catch (Exception $e) {
            TTransaction::rollback();
            new TMessage('error', $e->getMessage());
        }
        

    }

    public function onInsert($param)
    {
        try {
            TTransaction::open('licitacoes');

            //$licitacao = new MinhasLicitacoes();
            //$licitacao->fromArray($param);
            //$licitacao->store();

            $userid = TSession::getValue('userid');
            $criteria = new TCriteria;
            $criteria->add(new TFilter('user_id', '=', $userid));
            $criteria->add(new TFilter('licitacao_id', '=', $param['identificador']));
        
            $repository = new TRepository('LicitacoesUser');
            $duplicatas = $repository->load($criteria);
        
            if (count($duplicatas) > 0) {
                throw new Exception("Já existe uma licitação com este ID para o usuário.");
            }
        
            $licitacaoUser = new LicitacoesUser();
            $licitacaoUser->user_id = $userid;
            $licitacaoUser->licitacao_id = $param['identificador'];
            $licitacaoUser->status = 0;
            $licitacaoUser->store();

            TTransaction::close();

            new TMessage('info', "Licitação {$param['identificador']} inserida com sucesso.");
        } catch (Exception $e) {
            TTransaction::rollback();
            new TMessage('error', $e->getMessage());
        }
    }
    
    //////////////////////////////////////

    public function onReload($param = NULL)
    {
        try {
            $this->panel->style = '';
            TTransaction::open('licitacoesdb');

            $repository = new TRepository('licitacoes');

            $limit = $this->limit;

            // Cria um critério de seleção de dados
            $criteria = new TCriteria;
            $criteria->setProperty('limit', $limit);

            // atualiza ou recupera os parametros de paginação com dados da sessão
            $param = $this->keepNavigation($param);

            if (isset($param['offset'])) {
                $criteria->setProperty('offset', $param['offset']);
            }

            // Aplica filtros salvos na sessão
            if (TSession::getValue(__CLASS__.'_filter_estado')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_estado'));
            }
            if (TSession::getValue(__CLASS__.'_filter_municipio')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_municipio'));
            }
            if (TSession::getValue(__CLASS__.'_filter_objeto')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_objeto'));
            }
            if (TSession::getValue(__CLASS__.'_filter_abertura')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_abertura'));
            }
            if (TSession::getValue(__CLASS__.'_filter_abertura_fim')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_abertura_fim'));
            }
            if (TSession::getValue(__CLASS__.'_filter_publicado')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_publicado'));
            }
            if (TSession::getValue(__CLASS__.'_filter_portal')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_portal'));
            }
            if (TSession::getValue(__CLASS__.'_filter_modalidade')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_modalidade'));
            }
            $criteria->setProperty('order', 'abertura ASC');
            // Carrega os objetos conforme o critério
            $licitacoes = $repository->load($criteria);
            $this->datagrid->clear();

            if ($licitacoes) {
                foreach ($licitacoes as $licitacao) {
                    $this->datagrid->addItem($licitacao);
                }
            }

            $criteriaCount = new TCriteria;
            // Aplica os mesmos filtros para contar os registros
            if (TSession::getValue(__CLASS__.'_filter_estado')) {
                $criteriaCount->add(TSession::getValue(__CLASS__.'_filter_estado'));
            }
            // Aplica os mesmos filtros para contar os registros
            if (TSession::getValue(__CLASS__.'_filter_municipio')) {
                $criteriaCount->add(TSession::getValue(__CLASS__.'_filter_municipio'));
            }
            if (TSession::getValue(__CLASS__.'_filter_objeto')) {
                $criteriaCount->add(TSession::getValue(__CLASS__.'_filter_objeto'));
            }
            if (TSession::getValue(__CLASS__.'_filter_abertura')) {
                $criteriaCount->add(TSession::getValue(__CLASS__.'_filter_abertura'));
            }
            if (TSession::getValue(__CLASS__.'_filter_abertura_fim')) {
                $criteriaCount->add(TSession::getValue(__CLASS__.'_filter_abertura_fim'));
            }
            if (TSession::getValue(__CLASS__.'_filter_publicado')) {
                $criteriaCount->add(TSession::getValue(__CLASS__.'_filter_publicado'));
            }
            if (TSession::getValue(__CLASS__.'_filter_portal')) {
                $criteriaCount->add(TSession::getValue(__CLASS__.'_filter_portal'));
            }
            if (TSession::getValue(__CLASS__.'_filter_modalidade')) {
                $criteriaCount->add(TSession::getValue(__CLASS__.'_filter_modalidade'));
            }

            $count = $repository->count($criteriaCount);
            $this->pageNavigation->setCount($count);
            $this->pageNavigation->setProperties($param); // propriedades de navegação
            $this->pageNavigation->setLimit($limit); // registros por página

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    public function onSearch($param)
    {
        // Obtém os dados do formulário
        try{
            $this->form->validate();
        }catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
        $this->onClearSession();
        self::clearNavigation();
        $data = $this->form->getData();

        // Armazena os filtros na sessão
        if (isset($data->uf) AND $data->uf){
            TSession::setValue(__CLASS__.'_filter_estado', new TFilter('estado', 'like', $data->uf));
            $this->qtd_filtros++;
        }
        if (isset($data->mun) AND $data->mun){
            TSession::setValue(__CLASS__.'_filter_municipio', new TFilter('municipio', 'like', $data->mun));
            $this->qtd_filtros++;
        }
        if (isset($data->palavra_chave) AND $data->palavra_chave){
            TSession::setValue(__CLASS__.'_filter_objeto', new TFilter('objeto', 'like', "%{$data->palavra_chave}%"));
            $this->qtd_filtros++;
        }
        if (isset($data->data_abertura) AND $data->data_abertura){
            TSession::setValue(__CLASS__.'_filter_abertura', new TFilter('abertura', '>=', TDate::date2us($data->data_abertura) . ' 00:00' ));
            $this->qtd_filtros++;
        }
        if (isset($data->data_abertura_fim) AND $data->data_abertura_fim){
            TSession::setValue(__CLASS__.'_filter_abertura_fim', new TFilter('abertura', '<=', TDate::date2us($data->data_abertura_fim) . ' 23:59' ));
            $this->qtd_filtros++;
        }
        if (isset($data->data_insercao) AND $data->data_insercao){
            $data_insercao = TDate::date2us($data->data_insercao);
            TSession::setValue(__CLASS__.'_filter_publicado', new TFilter('publicado_em', 'like', "$data_insercao %"));
            $this->qtd_filtros++;
        }
        if (isset($data->modalidade) AND $data->modalidade) {
            TSession::setValue(__CLASS__.'_filter_modalidade', new TFilter('modalidade_id', '=', $data->modalidade));
        }
        if (isset($data->id_portal) AND $data->id_portal) {
            TSession::setValue(__CLASS__.'_filter_portal', new TFilter('portal_id', '=', $data->id_portal));
        }

        TSession::setValue(__CLASS__.'_filter_counter', $this->qtd_filtros);
        
        // MANTER DADOS NO FORM
        $this->form->setData($data);
        // E SESSÃO
        TSession::setValue(__CLASS__.'_filter_data', $data);
        $this->resetParamAndOnReload();
    }
    public static function onMudaUF($param)
    {
        try {
            TTransaction::open('localidades');

            // Primeiro, encontre o ID do estado com base na sigla
            $estadoId = null;
            if (!empty($param['uf'])) {
                $criteriaEstado = new TCriteria;
                $criteriaEstado->add(new TFilter('sigla_estado', '=', $param['uf']));
                $repositoryEstado = new TRepository('Estado');
                $estados = $repositoryEstado->load($criteriaEstado);

                if (count($estados) > 0) {
                    $estadoId = $estados[0]->id_estado; // Supondo que 'id' é o campo de identificação na tabela Estado

                }
            }

            // Em seguida, carregue os municípios com base no ID do estado
            $municipios = array();
            if ($estadoId) {
                $criteriaMunicipio = new TCriteria;
                $criteriaMunicipio->add(new TFilter('id_estado', '=', $estadoId));
                $repositoryMunicipio = new TRepository('Municipio');
                $objects = $repositoryMunicipio->load($criteriaMunicipio);
                

                if ($objects) {
                    foreach ($objects as $obj) {
                        $municipios[$obj->nome_municipio] = $obj->nome_municipio;
                    }
                    $municipios = ['' => ''] + $municipios;
                }
            }

            TTransaction::close();

            // Atualiza o combo de municípios
            TCombo::reload('form_localidades', 'mun', $municipios);
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }
    public function searchUf_Id($uf){
        if (!empty($uf)) {
            $criteriaEstado = new TCriteria;
            $criteriaEstado->add(new TFilter('sigla_estado', '=', $uf));
            $repositoryEstado = new TRepository('Estado');
            $estados = $repositoryEstado->load($criteriaEstado);

            if (count($estados) > 0) {
                $estadoId = $estados[0]->id_estado; // Supondo que 'id' é o campo de identificação na tabela Estado
                return $estadoId;
            }
            return '';
        }
    }
}

