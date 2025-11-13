# Guia de Setup - TransFlow Backend

## Pré-requisitos

- PHP 7.4+
- PostgreSQL 12+
- XAMPP (ou Apache)
- mod_rewrite ativado no Apache

## Passo 1: Criar Banco de Dados

Abra um terminal PostgreSQL:

```bash
psql -U root
```

Dentro do PostgreSQL:

```sql
CREATE DATABASE transflow;
\q
```

## Passo 2: Criar as Tabelas

Na pasta `Trans-Flow-Back`, execute:

```bash
psql -U root -d transflow -f sql/create_tables.sql
```

Ou copie e execute manualmente o conteúdo de `sql/create_tables.sql` no pgAdmin.

## Passo 3: Verificar Permissões

Certifique-se de que o diretório `Trans-Flow-Back` tem permissões de leitura e escrita:

```bash
# Windows
icacls "c:\xampp\htdocs\Trans-Flow-Back" /grant:r "%USERNAME%":(OI)(CI)F

# Linux/Mac
chmod -R 755 /var/www/html/Trans-Flow-Back
```

## Passo 4: Ativar mod_rewrite

No Windows com XAMPP:
1. Edite `c:\xampp\apache\conf\httpd.conf`
2. Procure por `#LoadModule rewrite_module`
3. Remova o `#` para descomentá-lo
4. Reinicie o Apache

## Passo 5: Testar a API

Acesse em seu navegador ou via curl:

```bash
# Teste básico
curl http://localhost/Trans-Flow-Back/api/containers

# Resposta esperada
{
  "success": true,
  "data": [],
  "message": "Operação realizada com sucesso"
}
```

## Passo 6: Criar Dados de Teste (Opcional)

```bash
# Criar um container
curl -X POST http://localhost/Trans-Flow-Back/api/containers \
  -H "Content-Type: application/json" \
  -d '{
    "codigo": "CONT-001",
    "status": "aguardando_coleta",
    "origem": "Porto de Santos",
    "destino": "São Paulo"
  }'
```

## Solução de Problemas

### Erro: "CORS policy"
- Certifique-se que os headers CORS estão configurados em `api/index.php`
- Verifique se a URL de origem está correta no frontend

### Erro: "Connection refused"
- Verifique se PostgreSQL está rodando: `psql --version`
- Verifique credenciais em `config/database.php`
- Teste a conexão: `psql -U root -d transflow`

### Erro: "mod_rewrite not found"
- Ative mod_rewrite no Apache
- Verifique o arquivo `.htaccess` em `api/`

### Erro: "Permission denied"
- Verifique permissões do diretório `Trans-Flow-Back`
- Verifique permissões do diretório de logs do Apache

## Estrutura de Banco de Dados

O sistema usa 5 tabelas principais:

1. **containers** - Armazena containers em trânsito
2. **lotes** - Agrupa produtos em lotes
3. **produtos** - Itens individuais
4. **historico** - Log de todas as operações
5. **usuarios** - Usuários (opcional)

Todos os relacionamentos usam `ON DELETE CASCADE` para manter integridade referencial.

## Próximas Etapas

1. Configure o frontend para apontar para `http://localhost/Trans-Flow-Back/api/`
2. Configure variáveis de ambiente no `.env` do frontend
3. Teste os endpoints documentados em `README_ENDPOINTS.md`

## Suporte

Para dúvidas, consulte:
- `README_API.md` - Especificações gerais da API
- `README_ENDPOINTS.md` - Documentação completa dos endpoints
- `controllers/` - Implementação dos endpoints
