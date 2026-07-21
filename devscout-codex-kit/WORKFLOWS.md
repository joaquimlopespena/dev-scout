# Workflows — DevScout

## Workflow 1 — Início de tarefa

1. Ler `MASTER-PROMPT.md`.
2. Ler arquivos relacionados.
3. Inspecionar o repositório.
4. Identificar estado atual.
5. Listar dependências.
6. Propor plano curto.
7. Implementar somente após o plano.

## Workflow 2 — Nova feature

1. Confirmar requisito e critério de aceite.
2. Identificar módulo.
3. Definir autorização.
4. Definir persistência.
5. Criar migration/factory quando necessário.
6. Implementar Action/Service.
7. Implementar controller e request.
8. Implementar UI.
9. Criar testes.
10. Executar validações.
11. Atualizar documentação.
12. Resumir mudanças.

## Workflow 3 — Integração GitHub

1. Documentar dado necessário.
2. Escolher REST ou GraphQL.
3. Estimar custo de rate limit.
4. Adicionar método no client.
5. Configurar cache.
6. Normalizar resposta.
7. Persistir snapshot.
8. Tratar erros.
9. Criar fixture.
10. Testar cenários.
11. Conectar job/action.
12. Exibir status na UI.

## Workflow 4 — Alteração do score

1. Descrever motivo.
2. Definir nova versão.
3. Atualizar pesos ou fórmula.
4. Definir tratamento de ausências.
5. Atualizar evidências.
6. Criar testes.
7. Preservar scores antigos.
8. Documentar impacto.
9. Planejar recálculo.
10. Não sobrescrever histórico silenciosamente.

## Workflow 5 — Segurança e tenancy

1. Identificar recurso privado.
2. Garantir `organization_id`.
3. Garantir membership.
4. Implementar Policy.
5. Escopar query.
6. Testar usuário externo.
7. Testar papel insuficiente.
8. Testar ID manipulado.
9. Registrar auditoria.
10. Revisar logs e respostas.

## Workflow 6 — Correção de bug

1. Reproduzir.
2. Criar teste falhando.
3. Identificar causa raiz.
4. Corrigir com menor mudança possível.
5. Executar teste de regressão.
6. Executar suíte relacionada.
7. Documentar risco.

## Workflow 7 — Banco de dados

1. Modelar mudança.
2. Avaliar impacto.
3. Criar migration nova.
4. Adicionar constraints.
5. Adicionar índices.
6. Atualizar models.
7. Atualizar factories.
8. Criar testes.
9. Testar rollback quando aplicável.

## Workflow 8 — Pull request

1. Revisar diff.
2. Remover debug.
3. Executar testes.
4. Executar Pint.
5. Executar Larastan/PHPStan.
6. Executar ESLint.
7. Atualizar docs.
8. Descrever contexto.
9. Descrever testes.
10. Descrever riscos e migrações.

## Workflow 9 — Release

1. Confirmar CI verde.
2. Confirmar variáveis de ambiente.
3. Confirmar migrations.
4. Confirmar queue worker.
5. Confirmar scheduler.
6. Confirmar token GitHub.
7. Confirmar cache.
8. Fazer backup quando aplicável.
9. Fazer deploy.
10. Executar smoke test.
11. Verificar logs.
12. Registrar versão.

## Workflow 10 — Ordem inicial de implementação

### Fase 1 — Fundação

- ambiente;
- autenticação;
- organizações;
- memberships;
- papéis;
- Policies;
- testes de tenancy.

### Fase 2 — GitHub

- client;
- rate limit;
- cache;
- busca;
- persistência;
- jobs;
- testes.

### Fase 3 — Score

- dimensões;
- fórmula;
- evidências;
- versão;
- testes.

### Fase 4 — Interface

- dashboard;
- listagem;
- filtros;
- perfil;
- loading/error/empty.

### Fase 5 — Recrutamento

- favoritos;
- notas;
- tags;
- pipeline;
- comparação;
- auditoria.

### Fase 6 — Qualidade e deploy

- E2E;
- CI;
- documentação;
- segurança;
- deploy;
- smoke tests.
