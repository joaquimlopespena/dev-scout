# Skills — DevScout

Este documento define as competências que o agente deve aplicar.

## Skill: Laravel Feature Development

### Quando usar

Ao implementar endpoints, páginas Inertia, casos de uso, persistência ou regras de aplicação.

### Procedimento

1. localizar módulo afetado;
2. revisar rotas e Policies;
3. criar ou atualizar migration;
4. criar Form Request;
5. criar Action/Service;
6. manter controller fino;
7. criar Resource ou props tipadas;
8. criar testes;
9. executar Pint e testes.

### Saída esperada

- código coeso;
- validação;
- autorização;
- testes;
- sem acoplamento desnecessário.

## Skill: Multi-Tenant Isolation

### Quando usar

Em qualquer dado privado da organização.

### Procedimento

1. identificar `organization_id`;
2. validar membership ativa;
3. aplicar query escopada;
4. aplicar Policy;
5. restringir route binding;
6. criar teste de acesso cruzado;
7. registrar auditoria se sensível.

### Critério crítico

Um usuário de outra organização não pode ler, alterar ou inferir a existência do recurso.

## Skill: GitHub API Integration

### Quando usar

Ao buscar perfis, repositórios, linguagens, contribuições ou métricas.

### Procedimento

1. definir dado necessário;
2. verificar se REST ou GraphQL é mais adequado;
3. implementar no client central;
4. definir timeout;
5. tratar rate limit;
6. adicionar cache;
7. normalizar payload;
8. persistir timestamp de sincronização;
9. criar fixture;
10. testar sucesso, erro e limite.

### Restrições

- sem chamadas diretas no controller;
- sem token em logs;
- sem API real nos testes comuns.

## Skill: Scoring Engine

### Quando usar

Ao implementar ou alterar métricas.

### Procedimento

1. definir dimensão;
2. definir fonte;
3. definir normalização;
4. definir peso;
5. tratar dado ausente;
6. produzir evidências;
7. versionar algoritmo;
8. criar testes de borda;
9. validar score entre 0 e 100.

### Regras

- score deve ser explicável;
- não usar métricas não verificáveis;
- não privilegiar apenas popularidade;
- evitar precisão falsa;
- preservar histórico de versões.

## Skill: React/Inertia UI

### Quando usar

Ao construir dashboards, listagens, filtros, formulários e detalhes.

### Procedimento

1. definir props TypeScript;
2. criar componentes pequenos;
3. tratar loading;
4. tratar error;
5. tratar empty;
6. garantir teclado e labels;
7. adicionar testes quando relevante;
8. evitar estado duplicado.

## Skill: Secure Authorization

### Quando usar

Em toda ação autenticada.

### Procedimento

1. identificar ator;
2. identificar organização;
3. identificar recurso;
4. identificar permissão;
5. aplicar Policy;
6. validar no backend;
7. testar allowed e denied;
8. auditar mudanças sensíveis.

## Skill: Database Design

### Quando usar

Ao criar ou alterar persistência.

### Procedimento

1. modelar entidades e relacionamentos;
2. definir constraints;
3. definir índices;
4. verificar cardinalidade;
5. evitar JSON indevido;
6. criar migration reversível;
7. criar factories;
8. criar testes.

## Skill: Automated Testing

### Quando usar

Sempre que houver mudança de comportamento.

### Pirâmide

- unitários para score e regras;
- integração para banco e GitHub client;
- feature tests para HTTP e Policies;
- E2E para fluxos críticos.

### Fluxos críticos

- login;
- convite;
- busca;
- sincronização;
- favoritos;
- notas;
- pipeline;
- isolamento tenant.

## Skill: Code Review

### Checklist

- regra correta;
- autorização correta;
- queries escopadas;
- testes adequados;
- sem N+1;
- sem segredo;
- sem debug;
- arquivos abaixo do limite;
- nomes claros;
- documentação atualizada.

## Skill: Documentation

### Quando usar

Ao alterar instalação, arquitetura, variáveis, comandos ou comportamento relevante.

### Regras

- português claro;
- comandos copiáveis;
- sem credenciais reais;
- decisões e limitações explícitas;
- atualizar README quando necessário.
