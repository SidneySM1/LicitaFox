<?php
$zsuper;
class GETmodelo extends TPage {
    private $form, $datagrid, $pageNavigation, $loaded=false;
    private int $limit = 20;
    private $panel;
    private $data2;



    public function __construct()
    {
        parent::__construct();
        //$formData = TSession::getValue('form_data');
        $formData = $this->data;
        
        $this->form = new BootstrapFormBuilder('form_localidades');
        $this->form->setFormTitle('Busca');

        //Definindo os obj para o form
        $uf = new TCombo('uf');
        TTransaction::open('localidades');
        $estados = Estado::getObjects();
        $items = [];
        foreach ($estados as $obj) {
            $items[$obj->sigla_estado] = $obj->nome_estado;
        }
        asort($items);
        $uf->addItems($items);
        $uf->setSize('90%');
        $uf->addValidation('UF', new TRequiredValidator);
        TTransaction::close();

        $data_insercao = new TDate('data_insercao');
        $data_insercao->setMask('yyyy-mm-dd');
        $data_insercao->setSize('75%');

        $data_abertura = new TDate('data_abertura');
        $data_abertura->setMask('yyyy-mm-dd');
        $data_abertura->setSize('75%');

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
        $this->form->addfields([new TLabel('UF:')], [$uf]);
        $this->form->addFields([new TLabel('Data-Captura:')], [$data_insercao],[new Tlabel('Data-Prazo')], [$data_abertura]);
        $this->form->addFields([new TLabel('Modalidade:')], [$modalidade]);
        $this->form->addFields([new TLabel('Portal:')], [$portal]);
        $this->form->addfields([new TLabel('Palavra-chave:')], [$palavra_chave]);
        
        $this->form->addAction('Buscar', new TAction([$this, 'onReload']), 'fa:search blue');
        //$this->form->setData( TSession::getValue('form_data'));
        $this->form->setData($this->data);

        $expandir = $this->form->addExpandButton('Expandir', '', false);
        //$expandir->start_hidden = true;
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';

        $colunaObjeto = new TDataGridColumn('objeto', 'Objeto', 'left', '50%');
        $colunaObjeto->setTransformer(function ($value, $object, $row) {
                        return $this->truncarTexto($value); // garantir que o $value é uma string, não um objeto
                    });
        //$col_identificador    = new TDataGridColumn('identificador', 'Id', 'right', '10%');
        $col_titulo  = new TDataGridColumn('titulo', 'Titulo', 'left', '20%');
        $col_municipio = new TDataGridColumn('municipio', 'Cidade', 'center', '10%');
        $col_data = new TDataGridColumn('abertura', 'Abertura', 'center','10%');

        $col_titulo->setTransformer(function($value, $object, $row) {
            TTransaction::open('licitacoes');
            
            // Verifica se a licitação está na lista de licitações ativas
            $minhaLicitacao = MinhasLicitacoes::where('id_licitacao', '=', $object->identificador)->first();
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

        // ações em grupo
        $action1 = new TDataGridAction([$this, 'onView'],     ['titulo'=>'{titulo}','orgao' => '{orgao}',  'objeto' => '{objeto}', 'site_original' => '{site_original}'] + (array)TSession::getValue('form_data'), $formData);
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
        ]+ (array)TSession::getValue('form_data'), $formData);
        $action3 = new TDataGridAction([$this, 'onInsert'], [
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
        ]+ (array)TSession::getValue('form_data'), $formData);        
                
        $action1->setLabel('Ver info');
        $action1->setImage('fa:search #7C93CF');
        
        $action2->setLabel('Descartar licitação');
        $action2->setImage('far:trash-alt red');
        
        $action3->setLabel('Inserir licitação');
        $action3->setImage('far:hand-pointer green');

        $action4 = new TDataGridAction([$this, 'onsite_original'],   ['site_original' => '{site_original}' ] );

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
        $this->datagrid->clear();
        
        //$this->pageNavigation->setAction(new TAction([$this, 'onReload'], $formData));

        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        $this->pageNavigation->style = 'padding-top:0px; margin-right:10px;';
        
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form);
        $vbox->add($this->panel = TPanelGroup::pack('', $this->datagrid, $this->pageNavigation)); 
        $this->panel->style = 'display:none;';      
        parent::add($vbox);
        
    }
    
    function onReload($param = NULL)
    {
        $this->panel->style = '';
        // Coletando dados do formulário
        $this->data = $this->form->getData();
        $data = $this->data;
        try
        {
            // open a transaction with database 'samples'
            TTransaction::open('licitacoesdb');
            
            // creates a repository for Customer
            $repository = new TRepository('licitacoes');
            $limit = 10;
            
            // creates a criteria
            $criteria = new TCriteria;
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            // Adicionando filtros conforme os dados do formulário
            if (!empty($data->uf)) {
                $criteria->add(new TFilter('estado', 'like', $data->uf));
            }
            if (!empty($data->data_insercao)) {
                $publicado_em = "$data->data_insercao %";
                $criteria->add(new TFilter('publicado_em', 'like', $publicado_em));
            }
            if (!empty($data->data_abertura)) {
                $abertura = "$data->data_abertura %";
                $criteria->add(new TFilter('abertura', 'like', $abertura));
            }
            if (!empty($data->modalidade)) {
                $criteria->add(new TFilter('modalidade_id', '=', $data->modalidade));
            }
            if (!empty($data->id_portal)) {
                $criteria->add(new TFilter('portal_id', '=', $data->id_portal));
            }
            if (!empty($data->palavra_chave)) {
                $criteria->add(new TFilter('objeto', 'like', "%{$data->palavra_chave}%"));
            }
            
            // load the objects according to criteria
            $objects = $repository->load($criteria);
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }
            
            // reset the criteria for record count
            //$criteria->resetProperties();
            $count= $repository->count($criteria);
            $this->pageNavigation->enableCounters();
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
            
            TTransaction::close();
            $this->loaded = true;
            // Recarregar os dados do formulário
            $this->form->setData($this->data);
        }
        catch (Exception $e) 
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    function show()
    {
        $this->onReload();
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

            $licitacao = new MinhasLicitacoes();
            $licitacao->fromArray($param);
            $licitacao->store();

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
}

