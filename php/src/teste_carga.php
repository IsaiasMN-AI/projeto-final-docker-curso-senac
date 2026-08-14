<?php
$resultado = null;
$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Pega os dados do formulário
    $payload = json_encode([
        "url" => $_POST['url'],
        "total" => (int)$_POST['total'],
        "concorrencia" => (int)$_POST['concorrencia']
    ]);

    // 2. Faz a chamada interna para o container do Python
    // Lembre-se: 'api' deve ser o nome do serviço no seu docker-compose.yml
    $ch = curl_init('http://api:8000/api/teste-carga');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload)
    ]);

    // 3. Captura a resposta
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $erro = 'Erro na comunicação com a API (Container Python): ' . curl_error($ch);
    } else {
        $resultado = json_decode($response, true);
    }
    curl_close($ch);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Teste de Carga</title>
    <style>
        :root {
            --bg-color: #f4f7f6;
            --card-bg: #ffffff;
            --primary: #4361ee;
            --success: #2ecc71;
            --error: #e74c3c;
            --text-main: #2b2d42;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0; padding: 40px 20px;
            display: flex; justify-content: center;
        }
        .container {
            width: 100%; max-width: 800px;
        }
        
        /* Estilo do Botão de Voltar */
        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--primary);
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            padding: 10px 15px;
            border: 2px solid var(--primary);
            border-radius: 6px;
            transition: 0.3s;
        }
        .btn-back:hover {
            background-color: var(--primary);
            color: white;
        }

        .card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        h2 { margin-top: 0; border-bottom: 2px solid var(--bg-color); padding-bottom: 10px;}
        
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #555; }
        input { 
            width: 100%; padding: 12px; border: 1px solid #ddd; 
            border-radius: 6px; box-sizing: border-box; font-size: 15px;
        }
        input:focus { outline: none; border-color: var(--primary); }
        
        button {
            width: 100%; background: var(--primary); color: white;
            border: none; padding: 15px; border-radius: 6px;
            font-size: 16px; font-weight: bold; cursor: pointer;
            transition: 0.3s;
        }
        button:hover { background: #3a53cc; }

        /* Estilos dos Resultados */
        .metrics-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;
        }
        .metric-box {
            background: var(--bg-color); padding: 15px; border-radius: 8px; text-align: center;
        }
        .metric-box span { display: block; font-size: 24px; font-weight: bold; color: var(--primary); }
        
        .status-list { list-style: none; padding: 0; }
        .status-item { 
            padding: 12px 15px; margin-bottom: 10px; border-radius: 6px; 
            display: flex; justify-content: space-between; font-weight: bold;
        }
        .status-200 { background: #e8f8f5; color: var(--success); border-left: 5px solid var(--success); }
        .status-error { background: #fdedec; color: var(--error); border-left: 5px solid var(--error); }
        .alert-error { background: #fdedec; color: var(--error); padding: 15px; border-radius: 6px; }
    </style>
</head>
<body>

<div class="container">
    
    <!-- Botão de Voltar adicionado aqui -->
    <a href="index.php" class="btn-back">← Voltar para a Loja</a>

    <div class="card">
        <h2>Teste de Carga</h2>
        <form method="POST">
            <div class="form-group">
                <label>URL Alvo:</label>
                <!-- Ajuste o value para a rota web real que o Nginx está expondo -->
                <input type="url" name="url" value="http://api:8000/api/produtos" required>
            </div>
            <div class="metrics-grid">
                <div class="form-group">
                    <label>Requisições Totais:</label>
                    <input type="number" name="total" value="500" min="1" required>
                </div>
                <div class="form-group">
                    <label>Concorrência Simultânea:</label>
                    <input type="number" name="concorrencia" value="20" min="1" max="100" required>
                </div>
            </div>
            <button type="submit">Iniciar Teste de Carga</button>
        </form>
    </div>

    <?php if ($erro): ?>
        <div class="alert-error"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <?php if ($resultado): ?>
    <div class="card">
        <h2>Relatório de Desempenho</h2>
        <div class="metrics-grid">
            <div class="metric-box">
                Tempo de Execução
                <span><?= htmlspecialchars($resultado['tempo_total_segundos']) ?>s</span>
            </div>
            <div class="metric-box">
                Total Disparado
                <span><?= htmlspecialchars($resultado['total_disparado']) ?> reqs</span>
            </div>
        </div>

        <h3>Códigos HTTP Retornados:</h3>
        <ul class="status-list">
            <?php foreach ($resultado['status_codes'] as $codigo => $quantidade): ?>
                <?php $classe = ($codigo == 200) ? 'status-200' : 'status-error'; ?>
                <li class="status-item <?= $classe ?>">
                    <span>HTTP <?= htmlspecialchars($codigo) ?></span>
                    <span><?= htmlspecialchars($quantidade) ?> requisições</span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>

</body>
</html>