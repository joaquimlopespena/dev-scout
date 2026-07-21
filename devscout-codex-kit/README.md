# DevScout — Codex Development Kit

Este pacote orienta o Codex a desenvolver o **DevScout**, uma aplicação web privada para descoberta, avaliação e acompanhamento de desenvolvedores open source a partir de dados do GitHub.

## Objetivo

Fornecer contexto, regras, arquitetura, habilidades, workflows e checklists suficientes para que um agente de código implemente o projeto de forma incremental, segura e testável.

## Stack assumida

- PHP 8.3+
- Laravel 12
- React 19
- TypeScript
- Inertia.js
- PostgreSQL
- Redis quando disponível
- Laravel queues
- GitHub REST API e GraphQL API
- Pest
- Playwright
- Laravel Pint
- Larastan/PHPStan
- ESLint
- Vite
- GitHub Actions

## Modelo de tenancy

Multi-tenant lógico por `organization_id`, inicialmente com uma única organização.

Dados públicos do GitHub são compartilhados globalmente. Dados privados de recrutamento são isolados por organização.

## Ordem de leitura para o Codex

1. `MASTER-PROMPT.md`
2. `PRODUCT-BRIEF.md`
3. `REQUIREMENTS.md`
4. `ARCHITECTURE.md`
5. `RULES.md`
6. `SKILLS.md`
7. `WORKFLOWS.md`
8. `CHECKLISTS.md`

## Forma de trabalho

O agente deve trabalhar em pequenas entregas verificáveis:

1. entender a tarefa;
2. inspecionar o código existente;
3. apresentar plano curto;
4. implementar;
5. executar testes e validações;
6. documentar decisões;
7. listar arquivos alterados e riscos restantes.

## Restrições importantes

- Não armazenar tokens ou segredos no repositório.
- Não criar controllers gordos.
- Não colocar autorização apenas no frontend.
- Nenhum arquivo ou classe deve exceder 500 linhas.
- Avaliar divisão preventiva a partir de 400 linhas.
- Código em inglês.
- Documentação em português.
- Não deixar TODOs ocultos.
