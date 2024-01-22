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

        // Define colunas da datagrid
        $this->datagrid->addColumn(new TDataGridColumn('titulo', 'Título', 'left', '20%'));
        $this->datagrid->addColumn(new TDataGridColumn('municipio', 'Municipio', 'center', '10%'));
        $this->datagrid->addColumn(new TDataGridColumn('tipo', 'Tipo', 'center', '10%'));
        $this->datagrid->addColumn(new TDataGridColumn('objeto', 'Objeto', 'left', '40%'));
        $this->datagrid->addColumn(new TDataGridColumn('abertura', 'Abertura', 'center', '10%'));
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
                        'id_licitacao' => $object->id_licitacao, 
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
        $external_link_action = new TDataGridAction([$this, 'onlinkExterno'], ['linkExterno' => '{linkExterno}']);
        $external_link_action->setLabel('Acessar portal');
        $external_link_action->setImage('fa:external-link-alt green');
        // Cria a ação de remover
        $remove_action = new TDataGridAction([$this, 'onRemove'], ['id_licitacao' => '{id_licitacao}']);
        $remove_action->setLabel('Remover');
        $remove_action->setImage('fa:trash red');
        // Cria a ação de baixar
        $comment_action = new TDataGridAction([$this, 'onInputDialog'], ['id_licitacao' => '{id_licitacao}']);
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
        
        
        $this->datagrid->enableSearch($input_search, 'id_licitacao, titulo, objeto, municipio, tipo, status');
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

        // Primeira consulta para obter os IDs das licitações do usuário
        $criteriaUserLicitacoes = new TCriteria;
        $criteriaUserLicitacoes->add(new TFilter('user_id', '=', $userId));

        $repositoryUserLicitacoes = new TRepository('LicitacoesUser');
        $userLicitacoes = $repositoryUserLicitacoes->load($criteriaUserLicitacoes);

        // MONTAR ARRAY DE IDS DAS LICITACOES DO USUARIO
        $licitacaoIds = array();
        if ($userLicitacoes) {
            foreach ($userLicitacoes as $userLicitacao) {
                $licitacaoIds[] = $userLicitacao->licitacao_id;
            }
        }

        // Verifica se há IDs para buscar
        if (count($licitacaoIds) == 0) {
            throw new Exception("Nenhuma licitação encontrada para o usuário.");
        }

        // Segunda consulta para buscar as licitações em MinhasLicitacoes
        $criteriaMinhasLicitacoes = new TCriteria;
        $criteriaMinhasLicitacoes->add(new TFilter('id_licitacao', 'IN', $licitacaoIds));

        $repositoryMinhasLicitacoes = new TRepository('MinhasLicitacoes');
        $minhasLicitacoes = $repositoryMinhasLicitacoes->load($criteriaMinhasLicitacoes);

        $this->datagrid->clear();

        if ($minhasLicitacoes) {
            foreach ($minhasLicitacoes as $minhaLicitacao) {
                if ($minhaLicitacao->status >= 0){$this->datagrid->addItem($minhaLicitacao);}
            }
        }

        TTransaction::close();
    } catch (Exception $e) {
        new TMessage('error', $e->getMessage());
        TTransaction::rollback();
    }
    $this->loaded = true;
}

    // Implementar o método para exibir detalhes aqui
    public function onView($param)
    {
        $id_licitacao = $param['id_licitacao'];
        TTransaction::open('licitacoes');
        

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
    new TQuestion("Deseja realmente remover a licitação {$param['id_licitacao']}?", $action);
}

public function DeleteLicitacao($param)
{
    try {
        TTransaction::open('licitacoes');

        // Verifica se a licitação existe
        $licitacao = new MinhasLicitacoes($param['id_licitacao']);
        if ($licitacao) {
            // Insere a licitação na tabela de removidos
            $licitacaoRemovida = new MinhasLicitacoesRemovidas();
            $licitacaoRemovida->fromArray($licitacao->toArray());
            $licitacaoRemovida->store();

            // Remove a licitação da tabela original
            $licitacao->delete();

            TTransaction::close();

            // Mensagem de confirmação
            new TMessage('info', "Licitação {$param['id_licitacao']} removida com sucesso e adicionada a lista de removidas.");
        } else {
            throw new Exception("Licitação não encontrada");
        }
    } catch (Exception $e) {
        TTransaction::rollback();
        new TMessage('error', $e->getMessage());
    }
    $this->onReload();
}
public function onLinkExterno($param)
{
    TScript::create('window.open("'.$param['linkExterno'].'","_blank")');
    
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
            
            if (!isset($param['id_licitacao'])) {
                throw new Exception("ID da licitação não definido.");
            }

            TTransaction::open('licitacoes');
            $licitacao = MinhasLicitacoes::find($param['id_licitacao']);
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
        
        $id_licitacao = $param['id_licitacao'];

        $comments_container = new TElement('div');
        $comments_container->{'style'} = 'height:200px; overflow:auto; border:1px solid gray; padding:10px; margin-bottom:10px;';
    
        try
        {
            TTransaction::open('licitacoes');

            // Carrega os comentários existentes
            $comentarios = Observacao::where('id_licitacao', '=', $id_licitacao)->load();
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
        $form->addAction('Adicionar OBS', $action_save_comment, 'fa:save green');
        
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
}
?>