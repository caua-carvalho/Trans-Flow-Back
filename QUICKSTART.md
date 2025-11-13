# 🚀 QUICK START - TransFlow Backend

## ⚡ Configuração em 5 Minutos

### Passo 1: Criar Banco de Dados (1 min)
```bash
psql -U root
CREATE DATABASE transflow;
\q
```

### Passo 2: Criar Tabelas (1 min)
```bash
psql -U root -d transflow -f "c:\xampp\htdocs\Trans-Flow-Back\sql\create_tables.sql"
```

### Passo 3: Ativar mod_rewrite (2 min)
1. Abra: `c:\xampp\apache\conf\httpd.conf`
2. Procure por: `#LoadModule rewrite_module`
3. Remova o `#`
4. Salve e feche

### Passo 4: Reiniciar Apache (30 seg)
```bash
# Via XAMPP Control Panel ou terminal:
net stop Apache2.4
net start Apache2.4
```

### Passo 5: Testar (30 seg)
Abra em seu navegador:
```
http://localhost/Trans-Flow-Back/api/test.php
```

✅ Pronto! Se tudo passar, sua API está funcionando!

---

## 📡 Testar API

### Via Browser
```
http://localhost/Trans-Flow-Back/api/containers
```

### Via curl
```bash
# Listar
curl http://localhost/Trans-Flow-Back/api/containers

# Criar
curl -X POST http://localhost/Trans-Flow-Back/api/containers \
  -H "Content-Type: application/json" \
  -d '{"codigo":"CONT-001","status":"aguardando_coleta","origem":"Porto","destino":"SP"}'

# Obter
curl http://localhost/Trans-Flow-Back/api/containers/1

# Atualizar Status
curl -X PATCH http://localhost/Trans-Flow-Back/api/containers/1/status \
  -H "Content-Type: application/json" \
  -d '{"status":"em_transito"}'

# Deletar
curl -X DELETE http://localhost/Trans-Flow-Back/api/containers/1

# Estatísticas
curl http://localhost/Trans-Flow-Back/api/dashboard/stats
```

---

## 🎯 Endpoints Principais

```
GET  /api/containers              # Listar
POST /api/containers              # Criar
GET  /api/containers/1            # Obter
PATCH /api/containers/1/status    # Atualizar status

GET  /api/lotes                   # Listar
POST /api/lotes                   # Criar
GET  /api/lotes/1                 # Obter

GET  /api/produtos                # Listar
POST /api/produtos                # Criar
GET  /api/produtos/1              # Obter

GET  /api/historico               # Listar histórico
POST /api/historico               # Registrar evento
GET  /api/historico/export        # Exportar CSV

GET  /api/dashboard/stats         # Estatísticas
```

---

## 🔧 Configuração do Frontend

No arquivo `.env` do seu frontend:
```env
VITE_API_URL=http://localhost/Trans-Flow-Back/api
```

---

## ✅ Checklist de Verificação

- [ ] PostgreSQL está rodando?
  ```bash
  psql --version
  ```

- [ ] Banco `transflow` existe?
  ```bash
  psql -U root -l | grep transflow
  ```

- [ ] Tabelas foram criadas?
  ```bash
  psql -U root -d transflow -c "\dt"
  ```

- [ ] mod_rewrite está ativo?
  ```bash
  apache2ctl -M | grep rewrite
  ```

- [ ] API responde?
  ```bash
  curl http://localhost/Trans-Flow-Back/api/test.php
  ```

---

## 🆘 Problemas Comuns

### "Connection refused"
- PostgreSQL não está rodando
- Solução: `sudo systemctl start postgresql` (Linux) ou inicie o PostgreSQL

### "Database does not exist"
- Esqueceu de criar o banco
- Solução: Execute `CREATE DATABASE transflow;`

### "Endpoint not found (404)"
- mod_rewrite não está ativo
- Solução: Ative mod_rewrite conforme Passo 3

### "Permission denied"
- Permissões de arquivo incorretas
- Solução: `chmod -R 755 /var/www/html/Trans-Flow-Back`

### "CORS error"
- Frontend está em porta diferente
- Solução: Verifique CORS headers em `api/index.php`

---

## 📚 Documentação Completa

- **README.md** - Visão geral
- **SETUP.md** - Setup detalhado
- **RESUMO.md** - Resumo técnico
- **CHECKLIST.md** - Tudo implementado
- **EXEMPLOS.php** - Exemplos de código

---

## 🎓 Estrutura da Resposta

Todas as respostas seguem este formato:

```json
{
  "success": true,
  "data": [...],
  "message": "Operação realizada com sucesso"
}
```

Em caso de erro:
```json
{
  "success": false,
  "error": "Mensagem de erro"
}
```

---

## 💡 Dica Importante

Se a API não responder, execute:
```bash
# Verificar se o Apache está rodando
curl -I http://localhost/

# Testar script PHP simples
curl http://localhost/Trans-Flow-Back/api/test.php

# Ver erros do Apache
tail -f c:\xampp\apache\logs\error.log
```

---

## 🎉 Sucesso!

Sua API TransFlow está pronta para produção!

Próximo passo: Conecte seu frontend e comece a usar!

---

**Dúvidas?** Consulte os arquivos README
**Exemplos?** Veja EXEMPLOS.php
**Setup?** Veja SETUP.md

---

**Criado em:** 13 de Novembro de 2025
**Versão:** 1.0
**Status:** ✅ Pronto para Usar
