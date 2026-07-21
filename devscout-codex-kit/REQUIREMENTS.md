# Requirements — DevScout

## Classificação

- **REQ**: obrigatório.
- **REC**: recomendação.
- **HYP**: hipótese atual.
- **PEND**: pendência de decisão.

## Requisitos funcionais

### Autenticação e equipe

- REQ-F-001: permitir login seguro.
- REQ-F-002: criar uma organização inicial.
- REQ-F-003: atribuir o primeiro usuário como owner.
- REQ-F-004: permitir que owner/admin convide membros por e-mail.
- REQ-F-005: permitir revogar convites e acessos.
- REQ-F-006: suportar papéis Owner, Admin, Evaluator e Viewer.
- REQ-F-007: registrar ações administrativas relevantes.

### Desenvolvedores

- REQ-F-010: pesquisar por username, nome, localização e linguagem.
- REQ-F-011: sincronizar perfis pela API oficial do GitHub.
- REQ-F-012: armazenar dados normalizados.
- REQ-F-013: exibir data da última sincronização.
- REQ-F-014: permitir atualização manual, sujeita a rate limit.
- REQ-F-015: marcar dados indisponíveis ou desatualizados.

### Score

- REQ-F-020: calcular score geral entre 0 e 100.
- REQ-F-021: calcular dimensões separadas.
- REQ-F-022: exibir pesos e evidências.
- REQ-F-023: versionar algoritmo.
- REQ-F-024: permitir recálculo após sincronização.
- REQ-F-025: não atribuir precisão falsa a dados ausentes.

### Busca e filtros

- REQ-F-030: filtrar por localização.
- REQ-F-031: filtrar por linguagens.
- REQ-F-032: filtrar por score mínimo e máximo.
- REQ-F-033: filtrar por seguidores.
- REQ-F-034: filtrar por estrelas.
- REQ-F-035: filtrar por repositórios públicos.
- REQ-F-036: filtrar por atividade recente.
- REQ-F-037: filtrar por status do pipeline.
- REQ-F-038: filtrar por favoritos e tags.
- REQ-F-039: ordenar por score, atividade, estrelas e nome.
- REQ-F-040: paginar resultados.

### Recrutamento

- REQ-F-050: favoritar desenvolvedor.
- REQ-F-051: adicionar notas privadas.
- REQ-F-052: adicionar tags da organização.
- REQ-F-053: alterar status do pipeline.
- REQ-F-054: registrar histórico de alterações.
- REQ-F-055: comparar até quatro candidatos.
- REC-F-056: permitir atribuir responsável interno.

## Requisitos não funcionais

### Segurança

- REQ-NF-001: usar autenticação e autorização server-side.
- REQ-NF-002: isolar dados privados por organização.
- REQ-NF-003: validar toda entrada.
- REQ-NF-004: armazenar segredos apenas em ambiente seguro.
- REQ-NF-005: aplicar rate limiting.
- REQ-NF-006: manter logs de auditoria.
- REQ-NF-007: proteger contra IDOR.
- REQ-NF-008: evitar exposição desnecessária de dados pessoais.

### Qualidade

- REQ-NF-010: testes unitários para score.
- REQ-NF-011: testes de integração para GitHub.
- REQ-NF-012: testes de autorização e tenancy.
- REQ-NF-013: testes E2E para fluxos críticos.
- REQ-NF-014: Pint sem falhas.
- REQ-NF-015: Larastan/PHPStan sem erros relevantes.
- REQ-NF-016: ESLint sem erros.
- REQ-NF-017: nenhum arquivo ou classe acima de 500 linhas.

### UX

- REQ-NF-020: interface priorizada para desktop.
- REQ-NF-021: responsividade mínima para tablet e mobile.
- REQ-NF-022: estados de loading, error e empty.
- REQ-NF-023: navegação por teclado.
- REQ-NF-024: contraste acessível.
- REQ-NF-025: feedback explícito para ações.

### Performance

- REC-NF-030: cache de respostas adequadas do GitHub.
- REC-NF-031: filas para sincronizações longas.
- REC-NF-032: índices em colunas de filtros.
- REC-NF-033: evitar N+1.
- REC-NF-034: paginação server-side.

## Hipóteses

- HYP-001: frontend será React + TypeScript + Inertia.
- HYP-002: banco será PostgreSQL.
- HYP-003: tenancy será lógica por `organization_id`.
- HYP-004: deploy inicial será em host gratuito compatível.
- HYP-005: GitHub token será de aplicação/servidor.

## Pendências

- PEND-001: confirmar provedor de e-mail.
- PEND-002: confirmar host de produção.
- PEND-003: confirmar domínio.
- PEND-004: confirmar política de retenção de logs.
- PEND-005: confirmar identidade visual final.
