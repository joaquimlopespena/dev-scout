# Checklists — DevScout

## Antes de implementar

- [ ] Li o Master Prompt.
- [ ] Entendi o requisito.
- [ ] Inspecionei o código existente.
- [ ] Identifiquei módulo e dependências.
- [ ] Identifiquei regra de autorização.
- [ ] Identifiquei impacto tenant.
- [ ] Defini critérios de aceite.
- [ ] Apresentei plano curto.

## Backend

- [ ] Controller fino.
- [ ] Form Request criado ou atualizado.
- [ ] Policy aplicada.
- [ ] Action/Service com responsabilidade clara.
- [ ] Transaction usada quando necessário.
- [ ] Query sem N+1.
- [ ] Dados privados escopados por organização.
- [ ] Erros tratados.
- [ ] Testes criados.
- [ ] Arquivos abaixo de 500 linhas.

## Frontend

- [ ] Props tipadas.
- [ ] Sem `any` injustificado.
- [ ] Componente pequeno.
- [ ] Loading tratado.
- [ ] Error tratado.
- [ ] Empty tratado.
- [ ] Formulário acessível.
- [ ] Navegação por teclado.
- [ ] Feedback de sucesso/erro.
- [ ] Sem autorização apenas visual.

## GitHub API

- [ ] Chamada centralizada no client.
- [ ] Timeout definido.
- [ ] Rate limit tratado.
- [ ] Cache definido.
- [ ] Retry apenas para erro transitório.
- [ ] Token não aparece em logs.
- [ ] Payload normalizado.
- [ ] Fixture criada.
- [ ] Teste de sucesso.
- [ ] Teste de erro.
- [ ] Teste de limite.

## Score

- [ ] Fonte da métrica definida.
- [ ] Normalização definida.
- [ ] Peso definido.
- [ ] Dado ausente tratado.
- [ ] Evidências salvas.
- [ ] Algoritmo versionado.
- [ ] Score limitado a 0–100.
- [ ] Testes de borda.
- [ ] Histórico preservado.

## Segurança

- [ ] Autenticação exigida.
- [ ] Membership validada.
- [ ] Policy aplicada.
- [ ] IDOR testado.
- [ ] Isolamento entre organizações testado.
- [ ] Entrada validada.
- [ ] Rate limiting considerado.
- [ ] Logs não expõem dados sensíveis.
- [ ] Ação sensível auditada.
- [ ] Nenhum segredo no código.

## Testes

- [ ] Unitários.
- [ ] Feature/integration.
- [ ] Policies.
- [ ] Tenancy.
- [ ] Regressão.
- [ ] E2E quando fluxo crítico.
- [ ] Pint.
- [ ] Larastan/PHPStan.
- [ ] ESLint.
- [ ] CI verde.

## Antes do commit

- [ ] Sem `dd`.
- [ ] Sem `dump`.
- [ ] Sem `console.log`.
- [ ] Sem TODO oculto.
- [ ] Sem segredo.
- [ ] Diff revisado.
- [ ] Documentação atualizada.
- [ ] Commit pequeno e coeso.

## Antes do deploy

- [ ] `.env` configurado.
- [ ] `APP_KEY` configurada.
- [ ] banco configurado.
- [ ] migrations prontas.
- [ ] GitHub token configurado.
- [ ] cache configurado.
- [ ] fila configurada.
- [ ] scheduler configurado.
- [ ] build frontend gerado.
- [ ] smoke test executado.
- [ ] logs verificados.
