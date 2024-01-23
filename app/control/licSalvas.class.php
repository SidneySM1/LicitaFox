<?php
class licSalvas extends TPage
{
    protected $datagrid; // listing
    protected $loaded;
    

    //use Adianti\base\AdiantiStandardListTrait;

    public function __construct()
    {
        parent::__construct();
        // Cria a datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';

        $col_status = new TDataGridColumn('status', 'Status', 'center', '10%');
        $col_abertura = new TDataGridColumn('abertura', 'Abertura', 'center', '10%');
        $col_abertura->setTransformer(function($value, $object, $row) {
            $rest = substr($value, -19, 10);    
            return $rest; 
        });
        // Define colunas da datagrid
        $this->datagrid->addColumn(new TDataGridColumn('titulo', 'Título', 'left', '10%'));
        $this->datagrid->addColumn(new TDataGridColumn('municipio', 'Municipio', 'center', '10%'));
        $this->datagrid->addColumn(new TDataGridColumn('modalidade', 'Tipo', 'center', '10%'));
        $this->datagrid->addColumn(new TDataGridColumn('objeto', 'Objeto', 'left', '50%'));
        $this->datagrid->addColumn($col_abertura);
        $this->datagrid->addColumn($col_status);

        // Definição dos status
        $statuses = [
            0 => (object)['id' => 0, 'name' => 'Adicionado', 'color' => '#525252'],
            1 => (object)['id' => 1, 'name' => 'Participando', 'color' => '#FFC107'],
            2 => (object)['id' => 2, 'name' => 'Aprovado', 'color' => '#28A745'],
            3 => (object)['id' => 3, 'name' => 'Rejeitado', 'color' => '#DC3545'],
            
        ];
        $col_status->setTransformer( function($value, $object, $row, $cell) use ($statuses) {
            if (isset($statuses[$value])) {
                $status = $statuses[$value];
                $cell->href = '#';
                $dropdown = new TDropDown($status->name, '');
                $dropdown->getButton()->style = 'color:white; border-radius:5px; background:'.$status->color;
                
                foreach ($statuses as $statusOption) {
                    $params = [
                        'identificador' => $object->identificador, 
                        'status_id' => $statusOption->id, 
                    ];
                
                    $dropdown->addAction($statusOption->name, new TAction([$this, 'changeStatus'], $params), 'fas:circle '.$statusOption->color);
                }
                
                return $dropdown;
            } else {
                return 'Status Unknown'; 
            }
        });

        // Cria uma instância de TDataGridActionGroup
        // Cria a ação de acessar link externo
        $external_link_action = new TDataGridAction([$this, 'onsite_original'],   ['site_original' => '{site_original}' ]);
        $external_link_action->setLabel('Acessar portal');
        $external_link_action->setImage('fa:external-link-alt green');
        // Cria a ação de remover
        $remove_action = new TDataGridAction([$this, 'onRemove'], ['identificador' => '{identificador}']);
        $remove_action->setLabel('Remover');
        $remove_action->setImage('fa:trash red');
        // Cria a ação de baixar
        $comment_action = new TDataGridAction([$this, 'onInputDialog'], ['identificador' => '{identificador}']);
        $comment_action->setLabel('Observações');
        $comment_action->setImage('fa:comments blue');
        // Adiciona as ações ao grupo de ações da DataGrid
        $action_group = new TDataGridActionGroup('Ações', 'fa:th');
        $action_group->addHeader('Opções disponiveis');
        $action_group->addAction($comment_action);
        $action_group->addAction($external_link_action);
        $action_group->addHeader('Remoção');
        $action_group->addAction($remove_action);
        
        // Adiciona o grupo de ações à DataGrid
        $this->datagrid->addActionGroup($action_group);

        // Configurações da datagrid
        $this->datagrid->createModel();

        $panel = new TPanelGroup('Minhas licitações');
        $panel->add( $this->datagrid );
        $panel->addFooter('footer');
        
        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction( 'PDF', new TAction([$this, 'exportAsPDF'], ['register_state' => 'false']), 'far:file-pdf red' );
        $dropdown->addAction( 'CSV', new TAction([$this, 'exportAsCSV'], ['register_state' => 'false']), 'fa:table blue' );
        
        $input_search = new TEntry('input_search');
        $input_search->placeholder = _t('Search');
        $input_search->setSize('100%');
        
        $this->datagrid->enableSearch($input_search, 'identificador, titulo, objeto, municipio, tipo, status');
        $panel->addHeaderWidget($input_search);
        
        $panel->addHeaderWidget( $dropdown );
        
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($panel);
        parent::add($vbox);
        
    }

    public function onReload($param = NULL)
{
    try {
        TTransaction::open('licitacoes'); 

        $userId = TSession::getValue('userid');

        // Primeira consulta para obter as licitações do usuário com status
        $criteriaUserLicitacoes = new TCriteria;
        $criteriaUserLicitacoes->add(new TFilter('user_id', '=', $userId));

        $repositoryUserLicitacoes = new TRepository('LicitacoesUser');
        $userLicitacoes = $repositoryUserLicitacoes->load($criteriaUserLicitacoes);

        // Montar array de licitações do usuário com status
        $licitacaoInfo = array();
        if ($userLicitacoes) {
            foreach ($userLicitacoes as $userLicitacao) {
                $licitacaoInfo[] = array(
                    'id' => $userLicitacao->licitacao_id,
                    'status' => $userLicitacao->status
                );
            }
        }

        // Verifica se há licitações para buscar
        if (count($licitacaoInfo) == 0) {
            throw new Exception("Nenhuma licitação encontrada para o usuário.");
        }
        $licitacaoIds = array_column($licitacaoInfo, 'id');

        TTransaction::close();

        TTransaction::open('licitacoesdb'); 
        // Segunda consulta para buscar os dados das licitacoes conforme os ID's que o usuario tem
        $criteriaMinhasLicitacoes = new TCriteria;
        $criteriaMinhasLicitacoes->add(new TFilter('identificador', 'IN', $licitacaoIds));

        $repositoryMinhasLicitacoes = new TRepository('licitacoes');
        $minhasLicitacoes = $repositoryMinhasLicitacoes->load($criteriaMinhasLicitacoes);

        TTransaction::Close();
        $this->datagrid->clear();

        if ($minhasLicitacoes) {
            foreach ($minhasLicitacoes as $minhaLicitacao) {
                foreach ($licitacaoInfo as $info) {
                    if ($info['id'] == $minhaLicitacao->identificador) {
                        $minhaLicitacao->status = $info['status']; // Adiciona o status ao objeto da licitação
                        break;
                    }
                }

                if ($minhaLicitacao->status >= 0) {
                    $this->datagrid->addItem($minhaLicitacao);
                }
            }
        }

        
    } catch (Exception $e) {
        new TMessage('error', $e->getMessage());
        TTransaction::rollback();
    }
    $this->loaded = true;
}

    // Implementar o método para exibir detalhes aqui
    public function onView($param)
    {
       
    }

    public function show()
    {
        if (!$this->loaded) {
            $this->onReload(func_get_args());
        }
        parent::show();
    }




    public function onRemove($param)
{
    // Define a ação de confirmação
    $action = new TAction([$this, 'DeleteLicitacao']);
    $action->setParameters($param);  // passa o parâmetro adiante

    // Mostra uma caixa de diálogo ao usuário
    new TQuestion("Deseja realmente remover a licitação {$param['identificador']}?", $action);
}

public function DeleteLicitacao($param)
{
    try {
        TTransaction::open('licitacoes');

        // Verifica se a licitação existe
        //$licitacao = new MinhasLicitacoes($param['identificador']);
        $licitacao = LicitacoesUser::where('licitacao_id', '=', $param['identificador'])
                                   ->where('user_id', '=', TSession::getValue('userid'))
                                   ->first();
        if ($licitacao) {
            // Insere a licitação na tabela de removidos
            //$licitacaoRemovida = new MinhasLicitacoesRemovidas();
            //$licitacaoRemovida->fromArray($licitacao->toArray());
            //$licitacaoRemovida->store();

            $licitacao = LicitacoesUser::where('licitacao_id', '=', $param['identificador'])
                                   ->where('user_id', '=', TSession::getValue('userid'))
                                   ->first();
            // Remove a licitação da tabela original
            $licitacao->status = -1;
            $licitacao->store();

            TTransaction::close();

            // Mensagem de confirmação
            new TMessage('info', "Licitação {$param['identificador']} removida com sucesso e adicionada a lista de removidas.");
        } else {
            throw new Exception("Licitação não encontrada");
        }
    } catch (Exception $e) {
        TTransaction::rollback();
        new TMessage('error', $e->getMessage());
    }
    $this->onReload();
}
    public function onsite_original($param)
        {
        TScript::create('window.open("'.$param['site_original'].'","_blank")');
        }
    public function onDownload($param)
    {
    
    }

    public function exportAsPDF($param)
        {
            try
            {
                // string with HTML contents
                $html = clone $this->datagrid;
                $contents = file_get_contents('app/resources/styles-print.html') . $html->getContents();
                
                // converts the HTML template into PDF
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($contents);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                
                $file = 'app/output/minhas-licitacoes.pdf';
                
                file_put_contents($file, $dompdf->output());
                
                $window = TWindow::create('Export', 0.8, 0.8);
                $object = new TElement('object');
                $object->data  = $file;
                $object->type  = 'application/pdf';
                $object->style = "width: 100%; height:calc(100% - 10px)";
                $object->add('O navegador não suporta a exibição deste conteúdo, <a style="color:#007bff;" target=_newwindow href="'.$object->data.'"> clique aqui para baixar</a>...');
                
                $window->add($object);
                $window->show();
            }
            catch (Exception $e)
            {
                new TMessage('error', $e->getMessage());
            }
        }

    public function exportAsCSV($param)
    {
        try
        {
            $data = $this->datagrid->getOutputData();
            
            if ($data)
            {
                $file    = 'app/output/minhas-licitacoes.csv';
                $handler = fopen($file, 'w');
                foreach ($data as $row)
                {
                    fputcsv($handler, $row);
                }
                
                fclose($handler);
                parent::openFile($file);
            }
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }


    public function changeStatus($param)
    {
        try
        {
            if (!isset($param['identificador'])) {
                throw new Exception("ID da licitação não definido.");
            }

            TTransaction::open('licitacoes');
            //$licitacao = LicitacoesUser::find($param['id_licitacao']);
            $licitacao = LicitacoesUser::where('licitacao_id', '=', $param['identificador'])
                                   ->where('user_id', '=', TSession::getValue('userid'))
                                   ->first();
            if ($licitacao) {
                $licitacao->status = $param['status_id'];
                $licitacao->store();
            } else {
                throw new Exception("Licitação não encontrada.");
            }
            TTransaction::close();
            
            $this->onReload($param);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }

    public static function onInputDialog($param)
    {
        $form = new BootstrapFormBuilder('input_form');
        
        $id_licitacao = $param['identificador'];

        $comments_container = new TElement('div');
        $comments_container->{'style'} = 'height:200px; overflow:auto; border:1px solid gray; padding:10px; margin-bottom:10px;';
        try
        {
            TTransaction::open('licitacoes');

            // Carrega os comentários existentes
            //$comentarios = Observacao::where('id_licitacao', '=', $id_licitacao)->load();
            $comentarios = Observacao::where('id_licitacao', '=', $param['identificador'])
                                   ->where('id_user', '=', TSession::getValue('userid'))
                                   //->where('user_id', '=', TSession::getValue('userid'), TExpression::OR_OPERATOR)
                                   ->load();
            // Lista os comentários existentes
            foreach ($comentarios as $comentario) {
                $comment_div = new TElement('div');
                $comment_div->{'style'} = 'margin-bottom: 10px;';
    
                // Destacar se for o usuário logado
                if ($comentario->username == TSession::getValue('username')) {
                    $comment_div->{'style'} .= 'background-color: lightblue;'; 
                }
    
                $comment_text = "<b>{$comentario->username}:</b> {$comentario->comentario}";
                $comment_div->add($comment_text);
                $comments_container->add($comment_div);
            }

            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
        $form->addContent([$comments_container]); // Adiciona o container de comentários ao formulário

        // Campo para adicionar novo comentário
        $new_comment = new TText('new_comment');
        $form->addFields([new TLabel('Nova OBS:')], [$new_comment]);
        
        // Ação para salvar o novo comentário
        // Ação para salvar o novo comentário
        $action_save_comment = new TAction([__CLASS__, 'onSaveComment']);
        $action_save_comment->setParameter('id_licitacao', $id_licitacao); // Passando id_licitacao como parâmetro
        $form->addAction('Adicionar Comentário', $action_save_comment, 'fa:comment gray');
        
        // Mostra o diálogo de entrada
        new TInputDialog('Observações para Licitação ' . $id_licitacao, $form);
    }
    
    
    public static function onSaveComment($param)
    {
        try
        {
            TTransaction::open('licitacoes');
            $comment = new Observacao();
            $comment->id_licitacao = $param['id_licitacao'];
            $comment->username = TSession::getValue('username'); // ou outro método para obter o nome do usuário
            $comment->id_user = TSession::getValue('userid'); // ou outro método para obter o nome do usuário
            $comment->comentario = $param['new_comment']; // O valor deve vir do campo de entrada na modal
            $comment->store();
            
            TTransaction::close();
            // Dá feedback ao usuário ou atualiza a lista de comentários
            new TMessage('info', 'Comentário adicionado com sucesso!');
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    public function truncarTexto($texto, $limite = 140)
    {
        $texto = strip_tags($texto); 
        if (mb_strlen($texto, 'UTF-8') > $limite) {
            return mb_substr($texto, 0, $limite, 'UTF-8') . '...';
        }
        return $texto;
    }
}
?>