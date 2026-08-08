<?php
// ==========================================
// 1. CONFIGURAÇÃO DE CONSUMO DA API
// ==========================================

// OPÇÃO A: Consumindo de uma API real
$api_url = "http://api:8000/api/produtos";
$json_data = @file_get_contents($api_url);

// OPÇÃO B: Executando o script Python diretamente (descomente as 2 linhas abaixo se não tiver uma API rodando)
// $comando = "python3 consumir_dados.py"; // ou apenas 'python' dependendo do seu SO
// $json_data = shell_exec($comando);

// ==========================================
// 2. TRATAMENTO DOS DADOS
// ==========================================
$produtos = json_decode($json_data, true);
$erro_api = false;

if ($produtos === null || isset($produtos['erro_conexao']) || isset($produtos['erro_consulta'])) {
    $erro_api = true;
    $produtos = []; // Array vazio para não quebrar o HTML
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Produtos | Loja Genérica</title>
    
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- CSS Personalizado -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 1px;
        }
        .card-produto {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 12px;
            overflow: hidden;
        }
        .card-produto:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .preco-destaque {
            font-size: 1.5rem;
            font-weight: bold;
            color: #198754;
        }
        .badge-categoria {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 0.85rem;
        }
        .estoque-baixo {
            color: #dc3545;
            font-weight: bold;
        }
        .header-bg {
            background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
        }
    </style>
</head>
<body>

    <!-- Navegação -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fa-solid fa-store me-2"></i>TechStore</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="#">Início</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Produtos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Contato</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Cabeçalho (Hero) -->
    <div class="header-bg text-center shadow-sm">
        <div class="container">
            <h1 class="display-5 fw-bold">Nossos Produtos</h1>
            <p class="lead">Confira as novidades integradas diretamente do nosso banco de dados.</p>
        </div>
    </div>

    <!-- Conteúdo Principal -->
    <div class="container mb-5">
        
        <?php if ($erro_api): ?>
            <!-- Alerta de Erro -->
            <div class="alert alert-danger shadow-sm" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                <strong>Ops!</strong> Não foi possível carregar os dados da API. Verifique se o servidor Python/Banco de Dados está rodando.
            </div>
        <?php else: ?>
            
            <!-- Grid de Produtos -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                
                <?php foreach ($produtos as $produto): ?>
                    <div class="col">
                        <div class="card card-produto h-100 shadow-sm position-relative">
                            
                            <!-- Badge de Categoria -->
                            <span class="badge bg-primary badge-categoria shadow-sm">
                                <?= htmlspecialchars($produto['categoria']) ?>
                            </span>

                            <div class="card-body mt-3">
                                <h5 class="card-title text-dark fw-bold mb-3">
                                    <i class="fa-solid fa-box-open text-secondary me-2"></i>
                                    <?= htmlspecialchars($produto['produto']) ?>
                                </h5>
                                
                                <p class="card-text preco-destaque mb-2">
                                    R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
                                </p>
                                
                                <?php if ($produto['estoque'] > 15): ?>
                                    <p class="card-text text-muted mb-0">
                                        <i class="fa-solid fa-check-circle text-success me-1"></i> 
                                        Estoque: <?= $produto['estoque'] ?> unid.
                                    </p>
                                <?php else: ?>
                                    <p class="card-text estoque-baixo mb-0">
                                        <i class="fa-solid fa-fire me-1"></i> 
                                        Restam apenas <?= $produto['estoque'] ?>!
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-footer bg-white border-0 pb-3 pt-0">
                                <button class="btn btn-outline-primary w-100 fw-bold">
                                    <i class="fa-solid fa-cart-shopping me-2"></i>Comprar
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div> <!-- Fim Grid -->
        <?php endif; ?>

    </div>

    <!-- Rodapé -->
    <footer class="bg-dark text-light text-center py-4 mt-auto">
        <div class="container">
            <p class="mb-0">&copy; <?= date('Y') ?> TechStore. Consumindo dados via API.</p>
        </div>
    </footer>

    <!-- Bootstrap JS (opcional, para menus responsivos) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.css"></script>
</body>
</html>
