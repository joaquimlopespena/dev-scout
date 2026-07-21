# Rules — DevScout

## Regras gerais

1. Código sempre em inglês.
2. Documentação e mensagens funcionais podem ser em português.
3. Não alterar escopo sem registrar a decisão.
4. Não fazer refatorações amplas junto com features pequenas.
5. Não esconder falhas de teste.
6. Não adicionar dependências sem justificar.
7. Não criar abstrações para um único uso sem benefício claro.

## Laravel

1. Controllers devem ser finos.
2. Validação em Form Requests.
3. Autorização em Policies ou Gates.
4. Casos de uso em Actions ou Services.
5. Operações lentas em Jobs.
6. Respostas estruturadas com Resources quando aplicável.
7. Usar transactions em operações multi-etapas.
8. Evitar N+1.
9. Usar eager loading explicitamente.
10. Migrations devem ser reversíveis quando possível.
11. Não editar migrations já usadas; criar novas.
12. Models não devem concentrar toda a regra de negócio.

## React

1. TypeScript obrigatório.
2. Não usar `any` sem justificativa.
3. Componentes pequenos e focados.
4. Separar UI, estado e acesso a dados quando necessário.
5. Toda tela deve tratar loading, error e empty.
6. Formulários devem ter feedback de validação.
7. Garantir navegação por teclado.
8. Não duplicar estado do servidor sem necessidade.
9. Não colocar regra de autorização apenas no cliente.
10. Evitar componentes acima de 300 linhas.

## Segurança

1. Segredos somente em `.env`.
2. Nunca registrar tokens.
3. Validar toda entrada externa.
4. Tratar payloads GitHub como não confiáveis.
5. Escopar dados privados por `organization_id`.
6. Policies obrigatórias.
7. Aplicar rate limiting em login, convites e sincronização.
8. Prevenir IDOR.
9. Sanitizar conteúdo exibido.
10. Registrar eventos administrativos.
11. Não armazenar dados pessoais sem necessidade.
12. Não expor e-mail privado.

## Testes

1. Toda regra de score deve ter teste unitário.
2. Toda Policy deve ter casos permitidos e negados.
3. Toda query tenant-aware deve ter teste de isolamento.
4. GitHub client deve usar mocks/fixtures.
5. Fluxos críticos devem ter E2E.
6. Bugs corrigidos devem ganhar teste de regressão.
7. Não reduzir cobertura apenas para passar CI.

## Tamanho e organização

1. Nenhum arquivo ou classe acima de 500 linhas.
2. Avaliar divisão aos 400.
3. Métodos devem ser curtos.
4. Uma classe deve ter responsabilidade clara.
5. Nomes devem explicar intenção.
6. Evitar comentários que apenas repetem o código.
7. Documentar decisões, não obviedades.

## Git

1. Commits pequenos e coesos.
2. Mensagens no padrão Conventional Commits.
3. Não commitar `.env`.
4. Não commitar artefatos temporários.
5. Não commitar debug.
6. Não fazer force push em branch compartilhada.
7. Pull requests devem descrever contexto, testes e riscos.

## Definição de pronto

Uma tarefa só termina quando:

- código implementado;
- testes relevantes passando;
- lint passando;
- análise estática passando;
- autorização verificada;
- estados de UI considerados;
- documentação atualizada;
- sem debug ou segredos;
- mudanças resumidas.
