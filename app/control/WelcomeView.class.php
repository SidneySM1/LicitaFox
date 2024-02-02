<?php

class WelcomeView extends TPage
{
    private $formNoticias;
    private $formLicitacoes;

    public function __construct()
    {
        parent::__construct();

        $this->formNoticias = new BootstrapFormBuilder('form_noticias');
        $this->formNoticias->setFormTitle('Notícias sobre Licitações');
        $this->formNoticias->generateAria();

        $this->formLicitacoes = new BootstrapFormBuilder('form_licitacoes');
        $this->formLicitacoes->setFormTitle('Minhas Últimas Licitações');
        $this->formLicitacoes->generateAria();

        //parent::add($this->formNoticias);
        //parent::add($this->formLicitacoes);
        //
    }

    public function onReload()
    {
        try
        {
            /* 
            $apiKey = 'e16c0d9d5d3341b59ea58fc3982fd3b0';
            $apiUrl = "https://newsapi.org/v2/everything?q=licitacoes&from=2023-12-03&sortBy=publishedAt&apiKey={$apiKey}";

            $curl = curl_init($apiUrl);
            $headers = [
                'Content-Type: application/json',
                'User-Agent: micromoney',
            ];
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($curl);
            curl_close($curl);

            $noticias = json_decode($response);
            //var_dump($noticias);
            foreach ($noticias->articles as $article)
            {
                $this->formNoticias->addContent([new TLabel("Título: " . $article->title)]);
                $this->formNoticias->addContent([new TLabel("Descrição: " . $article->description)]);
                $urlButton = new TButton("urlButton");
                $urlButton->setProperty('onclick', "window.open('$article->url', '_blank')");
                $urlButton->setLabel("Abrir Link");
                $this->formNoticias->addContent([$urlButton]);
                $this->formNoticias->addContent([new TLabel("Descrição: " . $article->publishedAt)]);
                $this->formNoticias->addContent([new TElement('hr')]);
            }

            
            TTransaction::open('licitacoes');

            $repository = new TRepository('MinhasLicitacoes');

            $criteria = new TCriteria;
            $criteria->setProperty('order', 'abertura_datetime DESC');
            $criteria->setProperty('limit', 5);

            $objects = $repository->load($criteria); 

            //var_dump($objects);
            if ($objects) {
                
                foreach ($objects as $obj) {
                    
                    $this->formLicitacoes->addContent([new TLabel("Título: " . $obj->titulo)]);
                    $this->formLicitacoes->addContent([new TLabel("Descrição: " . $obj->objeto)]);
                    $urlButton = new TButton("urlButton");
                    $urlButton->setProperty('onclick', "window.open('$obj->linkExterno', '_blank')");
                    $urlButton->setLabel("Abrir Link");
                    $this->formLicitacoes->addContent([$urlButton]);
                    $this->formLicitacoes->addContent([new TLabel("Abertura: " . $obj->abertura)]);
                    $this->formLicitacoes->addContent([new TElement('hr')]);
                    
                }
            }

            TTransaction::close(); 
            */
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }

    function show()
    {
        $this->onReload();
        parent::show();
    }
}
?>

    <style>
        .col-0 {
            flex: 0 0 0%;
            max-width: 0%;
        }

        .filter-title {
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .main-row {
            &.no-menu {
                .menu {
                    transform: translate3d(-300px, 0, 0);
                }
            }
        }

        .col-menu {
            .menu-wrap {
                position: relative;
            }

            .menu {
                transition: 1s;
                position: absolute;
                width: 200px;
                left: 0;
                top: 0;
            }
        }

        .row-cards {
            &.is-moving {
                .card {
                    &.clone {
                        transition: 1s;
                    }

                    &:nth-child(1) {
                        position: absolute;
                        width: 100%;
                        height: 100%;
                        top: 0;
                        left: 0;
                    }

                    &:nth-child(2) {
                        opacity: 0;
                    }
                }
            }
        }

        .col-card {
            &__content {
                position: relative;
            }
        }

        .card {
            padding: 0;
            border: none;
            margin-bottom: 50px;
            box-shadow: 0 2px 14px 0 rgba(47, 60, 83, 0.16);
            position: relative;
            overflow: hidden;
            height: 300px;
            border-radius: 8px;

            .card-body {
                
                flex-direction: column;
                max-height: 400px; 
                overflow: hidden;
            }

            .card-text {
                flex-grow: 1; 
                font-size: 12px;
                line-height: 1.4;
                overflow: hidden;
            }

            .card-title {
                font-size: 14px;
                font-weight: 700;
                text-transform: uppercase;
            }

            .card-list {
                font-size: 12px;
                padding-left: 15px;
            }
        }

        header.tg-header {
            padding: 40px;
            margin-bottom: 40px;
            background: rgb(247, 89, 100);
            background: linear-gradient(90deg, rgba(247, 89, 100, 1) 0%, rgba(249, 148, 104, 1) 100%);
            color: #fff;
        }

        h1.tg-h1 {
            text-align: center;
            font-size: 20px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-weight: bold;
        }

        h2.tg-h2 {
            text-align: center;
            font-size: 12px;
            letter-spacing: 1.2px;
            font-weight: 300;
        }

        hr.tg-hr {
            margin: 0 auto;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        footer.tg-footer {
            text-align: center;
            padding-bottom: 50px;
        }

        .tg-link {
            display: inline-block;
            margin: 0 20px;
            text-align: center;
            color: #278fb2;
        }
        .img-container {
        position: relative;
        overflow: hidden;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
        }

        .img-container:hover img {
            transform: scale(1.1); 
            transition: transform 0.3s ease; 
        }
        .card:hover {
            transform: scale(1.1); 
            transition: transform 0.3s ease; 
        }
        .card-img-top {
            width: 100%;
            height: auto;
            max-height: 100px;
            object-fit: cover;
        }
        .cardLic{
            padding: 0;
            border: none;
            margin-bottom: 50px;
            box-shadow: 0 2px 14px 0 rgba(47, 60, 83, 0.16);
            position: relative;
            overflow: hidden;
            height: 150px;
            border-radius: 8px;
            background-color: white;
        }
    </style>
<body>

<?php 
echo(new TXMLBreadCrumb('menu.xml', 'WelcomeView'));

$hoje = date('Y-m-d');
$mesPassado = date('Y-m-d', strtotime($hoje. ' - 30 days'));
$apiKey = 'e16c0d9d5d3341b59ea58fc3982fd3b0';

//TODAS NOTICIAS SOBRE LICITAÇÕES NOS ULTIMOS 30 DIAS
$apiUrl = "https://newsapi.org/v2/everything?q=licitacoes&from={$mesPassado}&sortBy=publishedAt&apiKey={$apiKey}";

$curl = curl_init($apiUrl);
$headers = [
    'Content-Type: application/json',
    'User-Agent: micromoney',
];
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

try {
    $response = curl_exec($curl);
    $noticias = json_decode($response);
} catch (\Throwable $th) {
    echo 'Busca por noticias mal sucedida';
}
curl_close($curl);


$userId = TSession::getValue('userid');
TTransaction::open('licitacoes');

// Primeira consulta para obter as licitações do usuário com status
$criteriaUserLicitacoes = new TCriteria;
$criteriaUserLicitacoes->add(new TFilter('user_id', '=', $userId));

$repositoryUserLicitacoes = new TRepository('LicitacoesUser');
$userLicitacoes = $repositoryUserLicitacoes->load($criteriaUserLicitacoes);

// Montar array de licitações do usuário com status
$licitacaoInfo = array();
if ($userLicitacoes) {
    foreach ($userLicitacoes as $userLicitacao) {
        if ($userLicitacao->status >= 0){
            $licitacaoInfo[] = array(
                'id' => $userLicitacao->licitacao_id,
                'status' => $userLicitacao->status
            );
        }
    }
}
$licitacaoIds = array_column($licitacaoInfo, 'id');
TTransaction::close(); 


TTransaction::open('licitacoesdb');
$repository = new TRepository('licitacoes');
$criteria = new TCriteria;

$criteria->setProperty('order', 'abertura ASC');
$criteria->setProperty('limit', 3);
$criteria->add(new Tfilter('identificador', 'IN', $licitacaoIds));

$objects = $repository->load($criteria); 
TTransaction::close(); 

?>
<div class="container mt-4">
    <div class="row">
        <!-- Coluna de Notícias -->
        <div class="col-md-8">
            <h2 class="filter-title">Licitações, últimas Notícias</h2>
            <!-- Exemplo de notícia -->
            <div class="row row-cards">
                <?php
                if (isset($noticias->code)){
                    echo "<h4>Noticias não encontradas, codigo: $noticias->code</h4>";
                    var_dump($noticias);
                }else if($noticias->totalResults != 0){
                    
                    foreach ($noticias->articles as $article) {
                        $publishedDate = new DateTime($article->publishedAt);
                        $diaMes = $publishedDate->format('d/m');  ?>
                        <div class="col-6 col-card">
                            <div class="col-card__content">
                                <div class="card">
                                    <div class="img-container">
                                        <img class="card-img-top" src="<?php echo $article->urlToImage; ?>" alt="Card image cap">
                                    </div>                          
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $article->title ?></h5>
                                        <small><?php echo $diaMes ?></small>  
                                        <p class="card-text"><?php echo $article->description ?></p>
                                    </div>
                                    <a href="<?php echo $article->url; ?>" target="_blank" class="btn btn-secondary">Leia Mais</a>
                                </div>
                            </div>
                        </div>
                    <?php }
                }
                else{
                    echo "<h4>Novas noticias hoje: $noticias->totalResults</h4>";
                }?>

            </div>
        </div>

        <!-- Coluna de Licitações -->
        <div class="col-md-4">
            <h4 class="filter-title">Suas licitações proximas</h4>
            <!-- Exemplo de licitação -->
            
                <?php foreach($objects as $obj){ ?>
                    <div class="col-12 col-card">
                        <div class="col-card__content">
                            <div class="cardLic">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo "$obj->titulo" ?></h5>
                                    <?php $abertura = TDate::date2br($obj->abertura);?>
                                    <p class="card-text"><?php echo "Abertura em: $abertura" ?></p>
                                    <a href="<?php echo "$obj->site_original" ?>" target="_blank" class="btn btn-primary">Portal</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php }?>
        </div>
    </div>
</div>
</body>

<?php 
try {
    TTransaction::open('dbf');

} catch (\PDOException $th) {
    echo 'Erro: ' . $th->getMessage();
}


?>