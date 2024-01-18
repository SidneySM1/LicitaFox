<?php

class GETmodelo extends TPage {
    private $form, $datagrid, $pageNavigation, $loaded=false;
    private int $limit = 50;
    private $panel;

    public function __construct()
    {
        parent::__construct();
        $formData = TSession::getValue('form_data');
        
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
            '11' => 'Compras BR',
            '9999' => 'Demais Portais'
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
        $this->form->setData( TSession::getValue('form_data'));

        $expandir = $this->form->addExpandButton('Expandir', '', false);
        //$expandir->start_hidden = true;
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';

        $colunaObjeto = new TDataGridColumn('objeto', 'Objeto', 'left', '50%');
        $colunaObjeto->setTransformer(function ($value, $object, $row) {
                        return $this->truncarTexto($value); // garantir que o $value é uma string, não um objeto
                    });
        //$col_id_licitacao    = new TDataGridColumn('id_licitacao', 'Id', 'right', '10%');
        $col_titulo  = new TDataGridColumn('titulo', 'Titulo', 'left', '20%');
        $col_municipio = new TDataGridColumn('municipio', 'Cidade', 'center', '10%');
        $col_data = new TDataGridColumn('abertura', 'Abertura', 'center','10%');

        $col_titulo->setTransformer(function($value, $object, $row) {
            TTransaction::open('licitacoes');
            
            // Verifica se a licitação está na lista de licitações ativas
            $minhaLicitacao = MinhasLicitacoes::where('id_licitacao', '=', $object->id_licitacao)->first();
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
        $action1 = new TDataGridAction([$this, 'onView'],     ['titulo'=>'{titulo}','orgao' => '{orgao}',  'objeto' => '{objeto}', 'linkExterno' => '{linkExterno}'] + (array)TSession::getValue('form_data'), $formData);
        //$action2 = new TDataGridAction([$this, 'onDelete'],   ['id_licitacao' => '{id_licitacao}' ] );
        $action2 = new TDataGridAction([$this, 'onDelete'], [
            'id_licitacao' => '{id_licitacao}',
            'titulo' => '{titulo}',
            'municipio_IBGE' => '{municipio_IBGE}',
            'uf' => '{uf}',
            'orgao' => '{orgao}',
            'abertura_datetime' => '{abertura_datetime}',
            'objeto' => '{objeto}',
            'link' => '{link}',
            'linkExterno' => '{linkExterno}',
            'municipio' => '{municipio}',
            'abertura' => '{abertura}',
            'aberturaComHora' => '{aberturaComHora}',
            'id_tipo' => '{id_tipo}',
            'tipo' => '{tipo}',
            'valor' => '{valor}',
            'id_portal' => '{id_portal}',
        ]+ (array)TSession::getValue('form_data'), $formData);
        $action3 = new TDataGridAction([$this, 'onInsert'], [
            'id_licitacao' => '{id_licitacao}',
            'titulo' => '{titulo}',
            'municipio_IBGE' => '{municipio_IBGE}',
            'uf' => '{uf}',
            'orgao' => '{orgao}',
            'abertura_datetime' => '{abertura_datetime}',
            'objeto' => '{objeto}',
            'link' => '{link}',
            'linkExterno' => '{linkExterno}',
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

        $action4 = new TDataGridAction([$this, 'onlinkExterno'],   ['linkExterno' => '{linkExterno}' ] );

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
        $this->pageNavigation->setAction(new TAction([$this, 'onReload'], ['page' => '{page}'] + (array)TSession::getValue('form_data'), $formData));
        $this->pageNavigation->setLimit($this->limit);
        
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form);
        $vbox->add($this->panel = TPanelGroup::pack('', $this->datagrid, $this->pageNavigation)); 
        $this->panel->style = 'display:none;';      
        parent::add($vbox);
        
    }
    function onReload($param = NULL){
        $data = $this->form->getData();
        $this->panel->style = '';

        // Verifica se a chamada veio da paginação
        if(isset($param['page'])) {
            // Mescla parametros
            $formData = TSession::getValue('form_data');
            $param = array_merge((array)$formData, (array)$param);
        } else {
            // Não existe page(chamou via form), então salva os dados do formulário
            TSession::setValue('form_data', $param);
        }
        
        //var_dump($param);
        $token = '986259b8ae392b22491634a213258539';
        $token = 'abcdefabcdefabcdefabcdefabcdef97';
        $parametros = $this->montarParametrosAPI($param);
        //var_dump($parametros);
        if (isset($param['page'])){
            $apiUrl = "https://alertalicitacao.com.br/api/v1/licitacoesAbertas/?pagina={$param['page']}&token={$token}&{$parametros}";
        } else {
            $apiUrl = "https://alertalicitacao.com.br/api/v1/licitacoesAbertas/?&token={$token}&{$parametros}";
        }
        //var_dump($apiUrl);
        //var_dump($parametros);
        try{
            //$this->form->validate();
            $result = AdiantiHttpClient::request($apiUrl, 'GET');
            
            $this->pageNavigation->setCount($result['totalLicitacoes']);
            $this->pageNavigation->setProperties($param);
            

            if ($result)
            {
                TTransaction::open('licitacoes'); // Abre uma transação com o banco

                foreach ($result['licitacoes'] as $licitacao) {
                    
                    // Convertendo o item para objeto para manipulação
                    $licitacaoObj = (object) $licitacao;

                    // Se não, verifica se está na lista de licitações removidas
                    $licitacaoRemovida = MinhasLicitacoesRemovidas::where('id_licitacao', '=', $licitacaoObj->id_licitacao)->first();
                    if ($licitacaoRemovida) {
                        //var_dump($licitacaoObj->titulo);
                        //$licitacaoObj->titulo .= " <span style='color:red;'>(REMOVIDO!!)</span>";

                        // NÂO IRA APARECER
                    } else {
                        $this->datagrid->addItem($licitacaoObj);
                    }
                    
                }

                TTransaction::close(); // Fecha a transação
            }

        }catch (Exception $e)
        {
            new TMessage('API', $e->getMessage());
        }
        $this->form->setData($data);
    }
    /*
    function show()
    {
        $this->onReload();
        parent::show();
    }
    */

    public function montarParametrosAPI($param)
    {
        $formData = TSession::getValue('form_data');
        $param = array_merge((array)$formData, (array)$param);

        $parametros = [];
        if (!empty($param['uf'])) {
            $parametros[] = 'uf=' . urlencode($param['uf']);
        } 
        if (!empty($param['palavra_chave'])) {
            $parametros[] = 'palavra_chave=' . urlencode($param['palavra_chave']);
        }
        if (!empty($param['modalidade'])) {
            $parametros[] = 'modalidade=' . urlencode($param['modalidade']);
        }
        if (!empty($param['id_portal'])) {
            $parametros[] = 'id_portal=' . urlencode($param['id_portal']);
        }
        if (!empty($param['municipio_ibge'])) {
            $parametros[] = 'municipio_ibge=' . urlencode($param['municipio_ibge']);
        }
        if (!empty($param['data_insercao'])) {
            $parametros[] = 'data_insercao=' . urlencode($param['data_insercao']);
        }
        if (!empty($param['data_abertura'])) {
            $parametros[] = 'data_inicio=' . urlencode($param['data_abertura']);
        }
        if (!empty($param['page'])) {
            $parametros[] = 'pagina=' . urlencode($param['page']);
        }
        
        return implode('&', $parametros);
    }

    public function truncarTexto($texto, $limite = 140)
    {
        $texto = strip_tags($texto); 
        if (mb_strlen($texto, 'UTF-8') > $limite) {
            return mb_substr($texto, 0, $limite, 'UTF-8') . '...';
        }
        return $texto;
    }
    
    public function onLinkExterno($param)
    {
       TScript::create('window.open("'.$param['linkExterno'].'","_blank")');
       $this->onReload((array)TSession::getValue('form_data'));
    }

    public function onView($param)
    {
        $titulo = $param['titulo'];
        $orgao = $param['orgao'];
        $objeto = $param['objeto'];
        $link = $param['linkExterno'];

        $botaoLink = new TElement('a');
        $botaoLink->href = $link;
        $botaoLink->target = '_blank';
        $botaoLink->class = 'btn btn-primary';
        $botaoLink->add('Acessar Portal');

        new TMessage('info', "<h4>$titulo</h4> <br> Orgão responsavel: <b>$orgao</b> <br> Objeto : <b>$objeto</b><br> $botaoLink");
        $this->onReload((array)TSession::getValue('form_data'));
    }
    
    public function onDelete($param)
    {
        try {
            TTransaction::open('licitacoes');

            // Busca pela licitação a ser removida (pode não existir)
            $licitacao = MinhasLicitacoes::find($param['id_licitacao']);
            
            // Mesmo se não existir na tabela MinhasLicitacoes, cria uma nova entrada em MinhasLicitacoesRemovidas
            $licitacaoRemovida = new MinhasLicitacoesRemovidas();
            $licitacaoRemovida->fromArray($param); // Assume que $param contém todas as informações necessárias
            $licitacaoRemovida->store();

            // Se a licitação existir, remove-a da tabela MinhasLicitacoes
            if ($licitacao) {
                $licitacao->delete();
            } 
            
            TTransaction::close();

            new TMessage('info', "Licitação removida. {$param['id_licitacao']} foi adicionada à lista de licitações removidas.");

        } catch (Exception $e) {
            TTransaction::rollback();
            new TMessage('error', $e->getMessage());
        }
        
        // Recarrega os dados da sessão após a operação
        $this->onReload((array)TSession::getValue('form_data'));
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
            $criteria->add(new TFilter('licitacao_id', '=', $param['id_licitacao']));
        
            $repository = new TRepository('LicitacoesUser');
            $duplicatas = $repository->load($criteria);
        
            if (count($duplicatas) > 0) {
                throw new Exception("Já existe uma licitação com este ID para o usuário.");
            }
        
            $licitacaoUser = new LicitacoesUser();
            $licitacaoUser->user_id = $userid;
            $licitacaoUser->licitacao_id = $param['id_licitacao'];
            $licitacaoUser->status = 0;
            $licitacaoUser->store();

            TTransaction::close();

            new TMessage('info', "Licitação {$param['id_licitacao']} inserida com sucesso.");
        } catch (Exception $e) {
            TTransaction::rollback();
            new TMessage('error', $e->getMessage());
        }
        $this->onReload((array)TSession::getValue('form_data'));
    }   
}

