# 🔧 Guia de Implementação - Controllers v2.0

## 📋 Controllers a Serem Criados/Atualizados

Com o novo schema, você precisará criar/atualizar os seguintes controllers:

### ✅ Manter (Com Melhorias)
- HistoricoController
- DashboardController

### 🔄 Atualizar
- ProdutoController (adicionar rastreamento por código de barras)

### ➕ Criar Novos

---

## 📚 Lista Completa de Controllers

```
1. CentrosDistribuicaoController    (Gestão de CDs)
2. LocalizacoesCDController         (Localização física no CD)
3. FornecedoresController           (Origem dos produtos)
4. ClientesController               (Clientes que fazem pedidos)
5. LotesController                  (Lotes que chegam no porto)
6. SublotesController               (Separação em sublotes)
7. ProdutosController               (Produtos individuais - ATUALIZAR)
8. EstoqueCDController              (Rastreamento no CD)
9. PedidosController                (Gestão de pedidos)
10. ItensPedidoController           (Itens do pedido)
11. EnviosController                (Gestão de envios)
12. LogisticaReversaController      (Devoluções/Defeitos)
13. MovimentacoesController         (Log de movimentações)
14. HistoricoController             (MANTER)
15. DashboardController             (ATUALIZAR)
16. RastreamentoController          (Novo - Buscar por código de barras)
```

---

## 🎯 Implementação Prioritária

### Fase 1: Estrutura Base (Essencial)
1. ✅ Criar SQL (FEITO)
2. CentrosDistribuicaoController
3. FornecedoresController
4. ClientesController
5. LotesController
6. ProdutosController

### Fase 2: CD e Armazenamento
7. LocalizacoesCDController
8. SublotesController
9. EstoqueCDController
10. MovimentacoesController

### Fase 3: Pedidos e Envios
11. PedidosController
12. ItensPedidoController
13. EnviosController

### Fase 4: Logística Reversa e Rastreamento
14. LogisticaReversaController
15. RastreamentoController

### Fase 5: Análise
16. HistoricoController (ATUALIZADO)
17. DashboardController (ATUALIZADO)

---

## 📝 Estrutura Base de um Controller

```php
<?php
class NovoController {
    private $db;
    private $table = 'tabela_nome';

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    // GET - Listar todos
    public function getAll() {
        try {
            $query = "SELECT * FROM " . $this->table . " ORDER BY data_criacao DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo Response::success($data);
        } catch (Exception $e) {
            echo Response::serverError($e->getMessage());
        }
    }

    // GET - Obter por ID
    public function getById($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$data) {
                echo Response::notFound("Registro não encontrado");
                return;
            }
            echo Response::success($data);
        } catch (Exception $e) {
            echo Response::serverError($e->getMessage());
        }
    }

    // POST - Criar novo
    public function create() {
        try {
            $input = json_decode(file_get_contents("php://input"), true);
            
            // Validações
            if (!isset($input['campo_obrigatorio'])) {
                echo Response::error("Campo obrigatório");
                return;
            }

            $query = "INSERT INTO " . $this->table . " 
                      (campo1, campo2, campo3) 
                      VALUES (:campo1, :campo2, :campo3)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':campo1', $input['campo1']);
            $stmt->bindParam(':campo2', $input['campo2'] ?? null);
            $stmt->bindParam(':campo3', $input['campo3'] ?? null);
            
            if ($stmt->execute()) {
                $id = $this->db->lastInsertId();
                $this->logHistory('tabela_nome', $id, 'Criado', null);
                echo Response::created(['id' => $id], "Criado com sucesso");
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'unique') !== false) {
                echo Response::error("Registro duplicado");
            } else {
                echo Response::serverError($e->getMessage());
            }
        }
    }

    // PUT - Atualizar
    public function update($id) {
        try {
            $input = json_decode(file_get_contents("php://input"), true);
            
            $fields = [];
            $params = [':id' => $id];
            
            if (isset($input['campo1'])) {
                $fields[] = "campo1 = :campo1";
                $params[':campo1'] = $input['campo1'];
            }
            
            if (empty($fields)) {
                echo Response::error("Nenhum campo para atualizar");
                return;
            }
            
            $fields[] = "data_atualizacao = NOW()";
            $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $this->logHistory('tabela_nome', $id, 'Atualizado', json_encode($input));
            echo Response::success(null, "Atualizado com sucesso");
        } catch (Exception $e) {
            echo Response::serverError($e->getMessage());
        }
    }

    // DELETE - Deletar
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                $this->logHistory('tabela_nome', $id, 'Deletado', null);
                echo Response::success(null, "Deletado com sucesso");
            }
        } catch (Exception $e) {
            echo Response::serverError($e->getMessage());
        }
    }

    private function logHistory($tipo, $refId, $acao, $detalhes) {
        try {
            $query = "INSERT INTO historico (tipo_entidade, id_entidade, acao, detalhes) 
                      VALUES (:tipo, :referencia_id, :acao, :detalhes)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':tipo' => $tipo,
                ':referencia_id' => $refId,
                ':acao' => $acao,
                ':detalhes' => $detalhes
            ]);
        } catch (Exception $e) {
            // Log silencioso
        }
    }
}
?>
```

---

## 🚀 Criando o Primeiro Controller - CentrosDistribuicao

```php
<?php
/**
 * Controlador de Centros de Distribuição
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/Response.php';

class CentrosDistribuicaoController {
    private $db;
    private $table = 'centros_distribuicao';

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getAll() {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                     ORDER BY data_criacao DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo Response::success($data);
        } catch (Exception $e) {
            echo Response::serverError($e->getMessage());
        }
    }

    public function getById($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$data) {
                echo Response::notFound("Centro de Distribuição não encontrado");
                return;
            }
            
            // Obter estatísticas do CD
            $query_estoque = "SELECT COUNT(*) as total_produtos, COUNT(DISTINCT localizacao_id) as localizacoes_ocupadas 
                             FROM estoque_cd 
                             WHERE cd_id = :cd_id AND status = 'armazenado'";
            $stmt_estoque = $this->db->prepare($query_estoque);
            $stmt_estoque->bindParam(':cd_id', $id, PDO::PARAM_INT);
            $stmt_estoque->execute();
            $estoque = $stmt_estoque->fetch(PDO::FETCH_ASSOC);
            
            $data['total_produtos_armazenados'] = $estoque['total_produtos'];
            $data['localizacoes_em_uso'] = $estoque['localizacoes_ocupadas'];
            
            echo Response::success($data);
        } catch (Exception $e) {
            echo Response::serverError($e->getMessage());
        }
    }

    public function create() {
        try {
            $input = json_decode(file_get_contents("php://input"), true);
            
            if (!isset($input['codigo']) || empty($input['codigo'])) {
                echo Response::error("Campo 'codigo' é obrigatório");
                return;
            }

            if (!isset($input['nome']) || empty($input['nome'])) {
                echo Response::error("Campo 'nome' é obrigatório");
                return;
            }

            $query = "INSERT INTO " . $this->table . " 
                      (codigo, nome, endereco, capacidade_maxima, status, data_atualizacao) 
                      VALUES (:codigo, :nome, :endereco, :capacidade, :status, NOW())";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':codigo', $input['codigo']);
            $stmt->bindParam(':nome', $input['nome']);
            $stmt->bindParam(':endereco', $input['endereco'] ?? null);
            $stmt->bindParam(':capacidade', $input['capacidade_maxima'] ?? null, PDO::PARAM_INT);
            $stmt->bindParam(':status', $input['status'] ?? 'ativo');
            
            if ($stmt->execute()) {
                $id = $this->db->lastInsertId();
                $this->logHistory('centros_distribuicao', $id, 'CD criado', null);
                echo Response::created(['id' => $id], "Centro de Distribuição criado com sucesso");
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'unique') !== false) {
                echo Response::error("Código de CD já existe");
            } else {
                echo Response::serverError($e->getMessage());
            }
        }
    }

    public function update($id) {
        try {
            $input = json_decode(file_get_contents("php://input"), true);
            
            $fields = [];
            $params = [':id' => $id];
            
            if (isset($input['codigo'])) {
                $fields[] = "codigo = :codigo";
                $params[':codigo'] = $input['codigo'];
            }
            if (isset($input['nome'])) {
                $fields[] = "nome = :nome";
                $params[':nome'] = $input['nome'];
            }
            if (isset($input['endereco'])) {
                $fields[] = "endereco = :endereco";
                $params[':endereco'] = $input['endereco'];
            }
            if (isset($input['capacidade_maxima'])) {
                $fields[] = "capacidade_maxima = :capacidade";
                $params[':capacidade'] = $input['capacidade_maxima'];
            }
            if (isset($input['status'])) {
                $fields[] = "status = :status";
                $params[':status'] = $input['status'];
            }
            
            if (empty($fields)) {
                echo Response::error("Nenhum campo para atualizar");
                return;
            }
            
            $fields[] = "data_atualizacao = NOW()";
            $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $this->logHistory('centros_distribuicao', $id, 'CD atualizado', json_encode($input));
            echo Response::success(null, "Centro de Distribuição atualizado com sucesso");
        } catch (Exception $e) {
            echo Response::serverError($e->getMessage());
        }
    }

    public function delete($id) {
        try {
            // Verificar se há produtos armazenados
            $check = "SELECT COUNT(*) as total FROM estoque_cd WHERE cd_id = :id AND status = 'armazenado'";
            $stmt_check = $this->db->prepare($check);
            $stmt_check->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt_check->execute();
            $result = $stmt_check->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0) {
                echo Response::error("Não é possível deletar um CD com produtos armazenados");
                return;
            }
            
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                $this->logHistory('centros_distribuicao', $id, 'CD deletado', null);
                echo Response::success(null, "Centro de Distribuição deletado com sucesso");
            }
        } catch (Exception $e) {
            echo Response::serverError($e->getMessage());
        }
    }

    private function logHistory($tipo, $refId, $acao, $detalhes) {
        try {
            $query = "INSERT INTO historico (tipo_entidade, id_entidade, codigo_entidade, acao, detalhes) 
                      SELECT :tipo, :id_entidade, codigo, :acao, :detalhes 
                      FROM centros_distribuicao WHERE id = :id_entidade";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':tipo' => $tipo,
                ':id_entidade' => $refId,
                ':acao' => $acao,
                ':detalhes' => $detalhes
            ]);
        } catch (Exception $e) {
            // Log silencioso
        }
    }
}
?>
```

---

## 📍 Rotas a Adicionar em api/index.php

```php
// ===== ROUTES: CENTROS DE DISTRIBUIÇÃO =====
$router->route('GET', '/centros-distribuicao', function() {
    $controller = new CentrosDistribuicaoController();
    $controller->getAll();
});

$router->route('GET', '/centros-distribuicao/{id}', function() {
    $controller = new CentrosDistribuicaoController();
    $controller->getById($_GET['id']);
});

$router->route('POST', '/centros-distribuicao', function() {
    $controller = new CentrosDistribuicaoController();
    $controller->create();
});

$router->route('PUT', '/centros-distribuicao/{id}', function() {
    $controller = new CentrosDistribuicaoController();
    $controller->update($_GET['id']);
});

$router->route('DELETE', '/centros-distribuicao/{id}', function() {
    $controller = new CentrosDistribuicaoController();
    $controller->delete($_GET['id']);
});
```

---

## ✨ Padrão para Todos os Outros Controllers

1. **Copiar estrutura base** do CentrosDistribuicaoController
2. **Adaptar** para a tabela específica
3. **Adicionar validações** necessárias
4. **Implementar filtros** se aplicável (ex: por CD, por status)
5. **Adicionar logHistory()** para rastreamento
6. **Adicionar rotas** em api/index.php

---

## 🎯 Próximas Ações

1. ✅ Schema SQL criado
2. ⏳ Implementar CentrosDistribuicaoController
3. ⏳ Implementar FornecedoresController
4. ⏳ Implementar ClientesController
5. ⏳ Implementar LotesController
6. ⏳ Implementar SublotesController
7. ⏳ Implementar ProdutosController (atualizar)
8. ⏳ Implementar EstoqueCDController
9. ⏳ Implementar PedidosController
10. ⏳ Implementar ItensPedidoController
11. ⏳ Implementar EnviosController
12. ⏳ Implementar LogisticaReversaController
13. ⏳ Implementar RastreamentoController
14. ⏳ Atualizar HistoricoController
15. ⏳ Atualizar DashboardController

---

**Criado em:** 13 de Novembro de 2025
**Status:** 📋 Documentação Pronta - Aguardando Implementação
