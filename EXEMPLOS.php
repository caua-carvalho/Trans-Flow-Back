<?php
/**
 * EXEMPLOS DE USO - TransFlow API
 * 
 * Este arquivo contém exemplos de como usar a API
 * Não execute diretamente, use como referência
 */

// ============================================
// 1. EXEMPLO: CRIAR UM CONTAINER
// ============================================

$url = 'http://localhost/Trans-Flow-Back/api/containers';
$data = [
    'codigo' => 'CONT-001',
    'status' => 'aguardando_coleta',
    'origem' => 'Porto de Santos',
    'destino' => 'São Paulo'
];

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);
echo "Criar Container: " . $result . "\n\n";


// ============================================
// 2. EXEMPLO: LISTAR TODOS OS CONTAINERS
// ============================================

$url = 'http://localhost/Trans-Flow-Back/api/containers';
$result = file_get_contents($url);
echo "Listar Containers: " . $result . "\n\n";


// ============================================
// 3. EXEMPLO: OBTER UM CONTAINER POR ID
// ============================================

$url = 'http://localhost/Trans-Flow-Back/api/containers/1';
$result = file_get_contents($url);
echo "Obter Container: " . $result . "\n\n";


// ============================================
// 4. EXEMPLO: ATUALIZAR STATUS DE CONTAINER
// ============================================

$url = 'http://localhost/Trans-Flow-Back/api/containers/1/status';
$data = [
    'status' => 'em_transito'
];

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'PATCH',
        'content' => json_encode($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);
echo "Atualizar Status: " . $result . "\n\n";


// ============================================
// 5. EXEMPLO: CRIAR UM LOTE
// ============================================

$url = 'http://localhost/Trans-Flow-Back/api/lotes';
$data = [
    'container_id' => 1,
    'codigo' => 'LOTE-001',
    'status' => 'aguardando_coleta',
    'data_envio' => '2025-11-13',
    'observacoes' => 'Lote de exemplo'
];

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);
echo "Criar Lote: " . $result . "\n\n";


// ============================================
// 6. EXEMPLO: CRIAR UM PRODUTO
// ============================================

$url = 'http://localhost/Trans-Flow-Back/api/produtos';
$data = [
    'lote_id' => 1,
    'nome' => 'Eletrônicos - Lote 1',
    'status' => 'armazenado',
    'localizacao' => 'Galpão A',
    'area' => 'A1',
    'prateleira' => 'P1'
];

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);
echo "Criar Produto: " . $result . "\n\n";


// ============================================
// 7. EXEMPLO: OBTER ESTATÍSTICAS
// ============================================

$url = 'http://localhost/Trans-Flow-Back/api/dashboard/stats';
$result = file_get_contents($url);
echo "Estatísticas: " . $result . "\n\n";


// ============================================
// 8. EXEMPLO: LISTAR HISTÓRICO COM FILTROS
// ============================================

$url = 'http://localhost/Trans-Flow-Back/api/historico?tipo=container&data_inicio=2025-11-01&data_fim=2025-11-30';
$result = file_get_contents($url);
echo "Histórico Filtrado: " . $result . "\n\n";


// ============================================
// 9. EXEMPLO: EXPORTAR HISTÓRICO EM CSV
// ============================================

$url = 'http://localhost/Trans-Flow-Back/api/historico/export?tipo=lote';
$result = file_get_contents($url);
// Salvar resultado em arquivo CSV
file_put_contents('historico.csv', $result);
echo "Histórico exportado para historico.csv\n\n";


// ============================================
// 10. EXEMPLO: DELETAR UM PRODUTO
// ============================================

$url = 'http://localhost/Trans-Flow-Back/api/produtos/1';
$options = [
    'http' => [
        'method' => 'DELETE'
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);
echo "Deletar Produto: " . $result . "\n\n";


// ============================================
// COM CURL (mais fácil)
// ============================================

/*
// Criar Container
curl -X POST http://localhost/Trans-Flow-Back/api/containers \
  -H "Content-Type: application/json" \
  -d '{
    "codigo": "CONT-002",
    "status": "aguardando_coleta",
    "origem": "Rio de Janeiro",
    "destino": "Minas Gerais"
  }'

// Listar Containers
curl http://localhost/Trans-Flow-Back/api/containers

// Obter Container
curl http://localhost/Trans-Flow-Back/api/containers/1

// Atualizar Status
curl -X PATCH http://localhost/Trans-Flow-Back/api/containers/1/status \
  -H "Content-Type: application/json" \
  -d '{"status": "em_transito"}'

// Deletar Container
curl -X DELETE http://localhost/Trans-Flow-Back/api/containers/1

// Obter Estatísticas
curl http://localhost/Trans-Flow-Back/api/dashboard/stats

// Exportar Histórico
curl http://localhost/Trans-Flow-Back/api/historico/export > historico.csv

// Registrar Evento Manual
curl -X POST http://localhost/Trans-Flow-Back/api/historico \
  -H "Content-Type: application/json" \
  -d '{
    "tipo": "container",
    "referencia_id": 1,
    "acao": "Container inspecionado",
    "usuario": "operador@sistema.com",
    "detalhes": "Inspeção completada com sucesso"
  }'
*/

?>
