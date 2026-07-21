# Architecture — DevScout

## Estilo

Monólito modular em Laravel com frontend React via Inertia.

A escolha reduz complexidade operacional e mantém fronteiras claras.

## Módulos

### Identity

- autenticação;
- usuários;
- sessão;
- recuperação de senha.

### Organizations

- organizações;
- memberships;
- papéis;
- convites;
- tenant context.

### Developers

- perfil global;
- linguagens;
- repositórios;
- métricas brutas;
- snapshots.

### GitHubIntegration

- client HTTP;
- REST;
- GraphQL;
- rate limit;
- cache;
- retries;
- normalização;
- sincronização.

### Scoring

- algoritmo;
- dimensões;
- pesos;
- evidências;
- versões;
- snapshots de score.

### Recruitment

- favoritos;
- notas;
- tags;
- pipeline;
- avaliações manuais;
- comparação.

### Audit

- eventos;
- ator;
- organização;
- recurso;
- payload seguro;
- timestamp.

## Camadas

### HTTP

- Controllers
- Form Requests
- API Resources
- Middleware

### Application

- Actions
- Use Cases
- DTOs
- Jobs

### Domain

- regras de score;
- políticas de pipeline;
- invariantes de membership;
- value objects quando úteis.

### Infrastructure

- Eloquent;
- GitHub client;
- cache;
- filas;
- mail;
- logging.

## Estrutura sugerida

```text
app/
  Actions/
  Domain/
    Developers/
    Organizations/
    Recruitment/
    Scoring/
  Http/
    Controllers/
    Middleware/
    Requests/
    Resources/
  Integrations/
    GitHub/
  Jobs/
  Models/
  Policies/
  Providers/
  Support/
```

## Entidades principais

### Globais

- users
- developers
- developer_profiles
- repositories
- developer_languages
- github_metric_snapshots
- score_snapshots

### Organizacionais

- organizations
- organization_user
- invitations
- favorites
- notes
- tags
- developer_tag
- pipeline_entries
- evaluations
- audit_logs

## Regras de dados

- usar IDs internos;
- manter `github_id` e `login` como identificadores externos;
- criar índices em filtros;
- usar foreign keys;
- preferir soft delete apenas quando houver requisito;
- armazenar timestamps UTC;
- evitar JSON para dados relacionais;
- JSON pode ser usado para evidências imutáveis e snapshots.

## Fluxo de sincronização

1. usuário solicita busca ou atualização;
2. aplicação valida rate limit;
3. action consulta cache;
4. se necessário, job consulta GitHub;
5. payload é normalizado;
6. dados globais são persistidos;
7. métricas são recalculadas;
8. score versionado é gravado;
9. UI recebe status;
10. evento de auditoria é registrado.

## Isolamento tenant

Toda entidade privada contém `organization_id`.

A organização ativa deve ser resolvida por middleware ou contexto explícito.

Defesas:

- global scopes com cautela;
- Policies;
- route model binding restrito;
- queries explícitas por organização;
- testes negativos de acesso cruzado.

Não confiar apenas em global scope. Policies continuam obrigatórias.

## API GitHub

- timeout explícito;
- retries somente para erros transitórios;
- backoff;
- cache;
- registro de rate limit;
- tratamento de 401, 403, 404, 422 e 5xx;
- circuit breaker simples quando necessário;
- fixtures para testes;
- nunca chamar API real em testes comuns.

## Observabilidade

- logs estruturados;
- correlation/request ID;
- eventos de sincronização;
- erros da API sem vazar token;
- auditoria de mudanças sensíveis.
