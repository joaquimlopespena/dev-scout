# Arquitetura do DevScout

Este documento descreve como o DevScout está organizado, como uma requisição percorre a aplicação e qual é a responsabilidade de cada classe e método relevante do projeto.

> Estado documentado: arquitetura presente no código atual. Componentes marcados como **preparados para uso futuro** existem no projeto, mas ainda não participam das rotas principais.

## 1. Visão geral

O DevScout é uma aplicação web monolítica dividida em backend Laravel e frontend React, conectados pelo Inertia.js.

```text
Navegador
   │
   │ visita ou formulário Inertia
   ▼
Rotas Laravel
   ▼
Middlewares ── autenticação ── senha definitiva ── organização ativa
   ▼
Form Request ── autorização + validação
   ▼
Controller
   ▼
Service ── regras de negócio e transações
   ├── Repository ── consultas e persistência Eloquent
   ├── GitHubClient ── API externa + cache
   └── Models ── entidades e relacionamentos
   ▼
Resposta Inertia
   ▼
Página React
```

As decisões arquiteturais centrais são:

- **Controllers finos:** recebem a requisição, delegam regras e retornam uma resposta.
- **Form Requests:** concentram autorização e validação de entrada.
- **Services:** implementam casos de uso e transações de negócio.
- **Repositories:** abstraem persistência onde há um contrato explícito.
- **TenantContext:** disponibiliza a organização ativa durante uma requisição.
- **Models Eloquent:** representam dados, casts e relacionamentos.
- **Inertia:** entrega props do Laravel diretamente a páginas React, sem uma API REST separada.
- **Auditoria:** registra alterações administrativas e de recrutamento.
- **Soft delete:** preserva historicamente as entidades de domínio removidas.

## 2. Fluxo de uma requisição

### 2.1 Inicialização

`bootstrap/app.php` cria a aplicação, carrega `routes/web.php`, registra a rota de saúde `/up`, adiciona o middleware do Inertia e cria os aliases `password.changed` e `tenant`.

`bootstrap/providers.php` registra `AppServiceProvider`, que configura dependências e limites de requisição.

### 2.2 Grupos de rotas

`routes/web.php` divide o tráfego em três grupos:

1. **Público/guest:** login e cadastro.
2. **Autenticado:** definição da senha definitiva no primeiro acesso.
3. **Workspace:** exige autenticação, senha definitiva e organização ativa; contém dashboard, desenvolvedores, pipeline, equipe, auditoria e logout.

As rotas de login usam o limitador `login`. Descoberta e sincronização com o GitHub usam `github-sync`.

### 2.3 Resolução do tenant

`ResolveActiveOrganization` lê `active_organization_id` da sessão e procura uma associação ativa do usuário. Quando não existe uma organização selecionada, usa a primeira associação ativa. A associação é salva em `TenantContext`, o id é mantido na sessão e também enviado ao `Context` do Laravel para logs e tarefas do ciclo da requisição.

### 2.4 Resposta Inertia

O controller chama `Inertia::render('Nome/Pagina', $props)`. `resources/js/app.tsx` encontra o componente correspondente em `resources/js/pages`, monta o React e entrega as props. `HandleInertiaRequests` compartilha em todas as páginas o usuário autenticado e a mensagem flash de sucesso.

## 3. Fluxos principais

### 3.1 Cadastro

```text
POST /register
→ RegisterRequest
→ RegisteredUserController::store()
→ RegistrationService::register()
→ cria User + Organization + Membership owner + AuditLog
→ autentica o usuário
→ /dashboard
```

Todo o cadastro inicial ocorre em uma transação. Se qualquer etapa falhar, nenhuma entidade parcial permanece no banco.

### 3.2 Primeiro acesso de um membro

```text
Login com senha temporária
→ must_change_password = true
→ /first-access/password
→ UpdateFirstPasswordRequest
→ FirstPasswordService::update()
→ troca a senha + libera o usuário + registra auditoria
→ /dashboard
```

`EnsurePasswordWasChanged` impede o acesso ao restante do workspace enquanto a troca não for concluída.

### 3.3 Descoberta e sincronização de desenvolvedor

```text
GET /developers/discover
→ DeveloperDiscoveryRequest
→ DiscoverDevelopersService::search()
→ GitHubClient::searchUsers()
→ GitHubClient::repositories() para calcular estrelas
→ lista candidatos e informa quais já estão no pipeline

POST /developers/sync
→ SyncDeveloperService::sync()
→ busca perfil e repositórios
→ GitHubProfileNormalizer::normalize()
→ DeveloperRepository::upsertFromGitHub()
→ DeveloperScoringService::calculate()
→ RecruitmentService::update(status=sourced)
→ /pipeline
```

### 3.4 Avaliação e pipeline

`RecruitmentService::update()` centraliza favoritos, notas, etapa do pipeline e tags. Todas as alterações são limitadas à organização ativa, executadas na mesma transação e registradas em `audit_logs`.

### 3.5 Gestão da equipe

Owners e admins podem criar, editar e remover membros. A criação define uma senha temporária e exige troca no primeiro acesso. Um owner não pode ser editado ou removido; um usuário também não pode remover o próprio acesso. Usuários sem outras associações são removidos por soft delete.

## 4. Multi-tenancy e segurança

O projeto usa multi-tenancy por coluna: tabelas privadas possuem `organization_id`. Não há banco ou schema separado por organização.

As proteções trabalham em conjunto:

- `ResolveActiveOrganization` valida se a organização pertence ao usuário.
- `TenantContext` fornece somente o tenant resolvido.
- Policies definem permissões por papel.
- Form Requests autorizam antes de validar e executar o controller.
- Services filtram favoritos, notas, tags, pipeline, equipe e auditoria por organização.
- `throttle:login` reduz tentativas de autenticação.
- `throttle:github-sync` limita consumo da API externa.
- Senhas usam o cast `hashed` do model `User`.
- Sessões são regeneradas após login e troca de senha; o logout invalida a sessão e renova o token CSRF.

Papéis:

| Papel | Organização/equipe | Avaliação | Consulta |
| --- | --- | --- | --- |
| `owner` | Gerencia | Altera | Visualiza |
| `admin` | Gerencia | Altera | Visualiza |
| `evaluator` | Não gerencia | Altera | Visualiza |
| `viewer` | Não gerencia | Não altera | Visualiza |

Essa tabela representa a regra de domínio pretendida. Na implementação atual, `DeveloperPolicy::updateRecruitment()` procura um papel de edição em qualquer associação ativa do usuário, sem limitar a consulta à organização carregada no `TenantContext`. O `RecruitmentService` grava corretamente no tenant atual, mas a autorização deve ser vinculada ao mesmo tenant para impedir que um usuário admin em uma organização edite como viewer em outra.

## 5. Catálogo do backend

### 5.1 Enums

#### `MembershipRole`

Representa `owner`, `admin`, `evaluator` e `viewer`.

- `canManageOrganization()`: retorna `true` apenas para owner e admin.

#### `PipelineStatus`

Enumera as seis etapas válidas: `sourced`, `screening`, `interview`, `offer`, `hired` e `rejected`. Não possui métodos; funciona como vocabulário do domínio.

### 5.2 Controllers

#### `Controller`

Classe-base abstrata dos controllers. Atualmente não adiciona comportamento ao framework.

#### `AuthenticatedSessionController`

- `create()`: renderiza a página `Login`.
- `store(LoginRequest)`: tenta autenticar, regenera a sessão e encaminha usuários com senha temporária para o primeiro acesso; os demais seguem para o destino pretendido ou dashboard.
- `destroy(Request)`: encerra a autenticação, invalida a sessão, renova o token CSRF e volta ao login.

#### `RegisteredUserController`

- `create()`: renderiza a página `Register`.
- `store(RegisterRequest, RegistrationService)`: cria usuário e workspace por meio do service, autentica e redireciona ao dashboard.

#### `FirstPasswordController`

- `edit()`: mostra a troca de senha somente quando `must_change_password` está ativo; caso contrário, retorna ao dashboard.
- `update(UpdateFirstPasswordRequest, FirstPasswordService)`: encerra outras sessões, troca a senha, regenera a sessão atual e redireciona ao dashboard.

#### `DashboardController`

- `__invoke(TenantContext)`: autoriza a leitura da organização ativa e entrega ao React dados básicos da organização e o papel atual.

#### `DeveloperController`

- `index()`: autoriza a listagem e renderiza a tela inicial de descoberta sem resultados.
- `show(Request, Developer, TenantContext)`: carrega perfil, score mais recente e dados privados de recrutamento filtrados pelo tenant.
- `compare(Request)`: recebe ids separados por vírgula, limita a quatro perfis e renderiza a comparação com os scores mais recentes.

#### `DeveloperDiscoveryController`

- `__invoke(DeveloperDiscoveryRequest, DiscoverDevelopersService)`: valida filtros, executa a busca externa e reapresenta `Developers/Index` com resultados e filtros.

#### `DeveloperSyncController`

- `__invoke(Request, SyncDeveloperService, RecruitmentService)`: valida um login ou uma lista, sincroniza cada perfil, adiciona cada candidato ao estágio `sourced` e redireciona ao pipeline.

#### `RecruitmentController`

- `index(TenantContext)`: lista entradas do pipeline da organização, incluindo desenvolvedor e score.
- `update(StoreRecruitmentRequest, Developer, RecruitmentService)`: aplica alterações validadas ao recrutamento e retorna com flash de sucesso.

#### `TeamController`

- `index(TenantContext)`: lista membros da organização, calcula permissões de edição/remoção e informa se o usuário pode gerenciar.
- `store(StoreMemberRequest, MembershipService)`: cadastra um membro com senha temporária.
- `update(UpdateMembershipRequest, int, MembershipService)`: altera usuário, papel e opcionalmente redefine a senha temporária.
- `destroy(DestroyMembershipRequest, int, MembershipService)`: remove a associação selecionada.

#### `AuditLogController`

- `__invoke(TenantContext)`: exige permissão administrativa e retorna os 30 eventos mais recentes, paginados e acompanhados do ator.

### 5.3 Middlewares

#### `EnsurePasswordWasChanged`

- `handle(Request, Closure)`: redireciona usuários com senha temporária para a definição de senha e libera os demais.

#### `ResolveActiveOrganization`

- `__construct(MembershipRepository, TenantContext)`: recebe o repositório de associações e o contexto scoped.
- `handle(Request, Closure)`: resolve uma associação ativa, inicializa o tenant, atualiza sessão/contexto e continua a requisição.

#### `HandleInertiaRequests`

- `$rootView`: define `resources/views/app.blade.php` como documento HTML inicial.
- `version(Request)`: delega ao Inertia o versionamento de assets.
- `share(Request)`: compartilha usuário autenticado e flash de sucesso com todas as páginas.

### 5.4 Form Requests

Todos os métodos `authorize()` decidem se a ação pode prosseguir. Todos os métodos `rules()` definem e normalizam os dados aceitos.

| Classe | `authorize()` | `rules()` |
| --- | --- | --- |
| `LoginRequest` | Público | E-mail, senha e remember opcional |
| `RegisterRequest` | Público | Nome, organização, e-mail único, senha confirmada |
| `UpdateFirstPasswordRequest` | Exige senha temporária pendente | Senha atual válida e nova senha confirmada |
| `DeveloperDiscoveryRequest` | Exige `viewAny` de Developer | Username, linguagem, localização, mínimos, ordenação e página |
| `DeveloperSearchRequest` | Exige `viewAny` | Filtros da listagem local; **preparado para uso futuro** |
| `StoreRecruitmentRequest` | Exige `updateRecruitment` | Favorito, nota, status e até dez tags |
| `StoreMemberRequest` | Exige gestão da organização | Dados do novo usuário, senha temporária e papel não-owner |
| `UpdateMembershipRequest` | Exige gestão da organização | Localiza associação no tenant e valida dados/unique do usuário |
| `DestroyMembershipRequest` | Exige gestão da organização | Não recebe corpo; autoriza a remoção |
| `StoreInvitationRequest` | Exige gestão da organização | E-mail e papel não-owner; **preparado para uso futuro** |

### 5.5 Integração com GitHub

#### `GitHubClient`

- `profile(string)`: busca `/users/{login}`, guarda por 15 minutos, converte 404 em resposta da aplicação e lança outros erros HTTP.
- `repositories(string)`: busca até 100 repositórios ordenados por atualização e guarda por 15 minutos.
- `searchUsers(array)`: transforma filtros em qualificadores da busca do GitHub, pagina 12 resultados e guarda cada busca por 5 minutos.
- `request()`: método privado que cria o cliente HTTP com base URL, headers, user-agent, timeouts, retry progressivo e token opcional de `config('services.github.token')`.

#### `GitHubProfileNormalizer`

- `normalize(profile, repositories)`: converte o formato da API para o model `Developer`; soma estrelas, conta linguagens, determina linguagem principal e atividade mais recente e preenche metadados de sincronização.

### 5.6 Services

#### `RegistrationService`

- `__construct(OrganizationRepository, MembershipRepository)`: recebe contratos de persistência.
- `register(array)`: em transação, cria usuário, organização com slug único, associação owner e evento `organization.created`.

#### `FirstPasswordService`

- `update(User, string)`: em transação, grava a nova senha, desativa a exigência de troca e registra `user.first_password_changed` na primeira organização ativa.

#### `DiscoverDevelopersService`

- `__construct(GitHubClient, TenantContext)`: recebe integração externa e organização atual.
- `search(array)`: pesquisa usuários, consulta repositórios para calcular estrelas, aplica mínimo de estrelas localmente, marca candidatos já selecionados e monta paginação.

#### `SyncDeveloperService`

- `__construct(...)`: recebe cliente GitHub, normalizador, repositório e calculadora de score.
- `sync(string)`: busca dados externos, normaliza, faz upsert, cria um novo snapshot de score e devolve o desenvolvedor atualizado.

#### `DeveloperScoringService`

- `VERSION`: versão persistida junto ao score para rastreabilidade do algoritmo.
- `$weights`: pesos das seis dimensões.
- `calculate(Developer)`: calcula dimensões disponíveis, redistribui implicitamente o peso quando uma dimensão não possui dado, salva `ScoreSnapshot` e devolve o snapshot.
- `cap(int, int)`: converte uma métrica bruta para escala 0–100 com teto.
- `evidence(string, Developer)`: registra os valores públicos usados em cada dimensão.

#### `RecruitmentService`

- `__construct(TenantContext)`: recebe o tenant da requisição.
- `update(Developer, User, array)`: em uma transação, alterna favorito, cria nota, cria/atualiza pipeline, sincroniza tags da organização e registra `developer.recruitment_updated`.

#### `MembershipService`

- `__construct(TenantContext)`: recebe o tenant atual.
- `create(User, array)`: cria ou restaura usuário e associação, atribui senha temporária, exige troca e audita `membership.created`.
- `update(int, User, array)`: atualiza nome, e-mail, papel e, se informada, senha temporária; audita `membership.updated`.
- `remove(int, User)`: impede autorremoção, audita, aplica soft delete na associação e remove também o usuário quando não restam associações.
- `findEditable(int)`: localiza a associação dentro do tenant e impede edição do owner.
- `audit(Membership, User, string, array)`: método auxiliar que padroniza a criação do `AuditLog`.

#### `InvitationService`

Serviço **preparado para uso futuro**; ainda não possui controller/rota.

- `__construct(TenantContext)`: recebe a organização ativa.
- `invite(User, array)`: cria ou renova convite de sete dias, guarda apenas o hash do token e audita `invitation.created`.

### 5.7 Repositories

#### `OrganizationRepository` / `EloquentOrganizationRepository`

- `create(array)`: contrato e implementação Eloquent para criar uma organização.

#### `MembershipRepository` / `EloquentMembershipRepository`

- `create(array)`: cria uma associação usuário-organização.
- `activeForUser(User, ?int)`: encontra a associação ativa do usuário, opcionalmente limitada ao id salvo na sessão, e carrega a organização.

#### `DeveloperRepository` / `EloquentDeveloperRepository`

- `paginate(array, int)`: lista desenvolvedores locais com score, busca textual, filtros de localização/linguagem/favorito/pipeline/score e ordenação pelo score atual. Está implementado, mas a tela atual de descoberta não o chama.
- `upsertFromGitHub(array)`: cria ou atualiza o desenvolvedor usando `github_id` como identidade externa.

`AppServiceProvider::register()` liga cada interface à implementação Eloquent.

### 5.8 Models

Todos os models de domínio usam `SoftDeletes`. `#[Fillable]` define os campos aceitos por mass assignment.

#### `User`

Usuário autenticável. Oculta senha/token, aplica hash automático, converte flags/datas e possui muitas associações.

- `memberships()`: `hasMany` para organizações das quais o usuário participa.
- `casts()`: email verificado como data, troca obrigatória como boolean e senha como hashed.

#### `Organization`

Workspace/tenant identificado por nome e slug.

- `memberships()`: associações de usuários da organização.

#### `Membership`

Vínculo entre usuário e organização, com papel e possível revogação.

- `organization()`: organização associada.
- `user()`: usuário associado.
- `casts()`: converte papel para `MembershipRole` e revogação para data.

#### `Developer`

Perfil público sincronizado globalmente do GitHub.

- `scores()`: histórico completo de snapshots.
- `latestScore()`: snapshot mais recente por `calculated_at`.
- `casts()`: linguagens como array e datas do GitHub/sincronização como datetime.

#### `ScoreSnapshot`

Registro imutável do resultado de uma versão do algoritmo.

- `developer()`: perfil avaliado.
- `casts()`: total float, dimensões array e cálculo datetime.

#### `PipelineEntry`

Posição de um desenvolvedor no pipeline de uma organização.

- `developer()`: perfil relacionado.

#### `AuditLog`

Evento administrativo ou de recrutamento com ator, alvo polimórfico e metadata.

- `actor()`: usuário responsável; usa `actor_id`.
- `organization()`: tenant do evento.
- `casts()`: metadata como array.

#### `Invitation`

Convite de acesso com papel, hash, validade e estados de aceite/revogação.

- `organization()`: organização que emitiu o convite.
- `casts()`: papel para enum e datas para datetime.

#### Models sem métodos próprios

- `Favorite`: marca um desenvolvedor como favorito por organização.
- `Note`: nota privada de recrutamento com autor.
- `Tag`: marcador exclusivo dentro de uma organização.

Esses models dependem das operações explícitas do `RecruitmentService`; seus relacionamentos ainda não foram expostos como métodos Eloquent.

### 5.9 Policies

#### `OrganizationPolicy`

- `view(User, Organization)`: permite quando existe associação ativa com aquela organização.
- `update(User, Organization)`: permite quando a associação ativa possui papel owner ou admin.

#### `DeveloperPolicy`

- `viewAny(User)`: exige ao menos uma associação ativa.
- `view(User, Developer)`: reutiliza `viewAny`; perfis públicos são globais.
- `updateRecruitment(User, Developer)`: permite quando o usuário possui alguma associação ativa como owner, admin ou evaluator. Atualmente a consulta não é limitada ao tenant da requisição; essa diferença está registrada como ponto de atenção de segurança.

### 5.10 Provider e contexto

#### `AppServiceProvider`

- `register()`: registra bindings dos repositories e define `TenantContext` como scoped, isolado por requisição.
- `boot()`: configura 10 operações GitHub/minuto por usuário e 5 tentativas de login/minuto por e-mail + IP.

#### `TenantContext`

- `set(Membership)`: inicializa o contexto com uma associação já validada.
- `organization()`: retorna a organização ativa ou responde 403 se não inicializada.
- `membership()`: retorna a associação ativa ou responde 403.

## 6. Persistência e banco de dados

### 6.1 Tabelas de infraestrutura

- `users`, `password_reset_tokens`, `sessions`: autenticação e sessão.
- `cache`, `cache_locks`: cache de aplicação e locks.
- `jobs`, `job_batches`, `failed_jobs`: infraestrutura de filas.

### 6.2 Tabelas de domínio

| Tabela | Responsabilidade |
| --- | --- |
| `organizations` | Workspaces/tenants |
| `memberships` | Usuários, organizações e papéis |
| `developers` | Dados públicos consolidados do GitHub |
| `score_snapshots` | Histórico versionado de scores |
| `pipeline_entries` | Etapa do candidato por organização |
| `favorites` | Favoritos por organização |
| `notes` | Notas privadas por organização |
| `tags` | Vocabulário de tags do tenant |
| `developer_tag` | Relação muitos-para-muitos entre perfis e tags |
| `audit_logs` | Histórico de ações |
| `invitations` | Convites preparados para fluxo futuro |

As migrations anônimas possuem dois métodos padrão:

- `up()`: cria ou altera a estrutura ao executar uma migração.
- `down()`: desfaz a alteração em um rollback.

`add_soft_deletes_to_domain_tables` percorre todas as tabelas de domínio para adicionar `deleted_at`; o `down()` percorre a ordem inversa para removê-lo.

## 7. Frontend Inertia + React

### 7.1 Entrada e layout

#### `resources/js/app.tsx`

- `import.meta.glob`: carrega antecipadamente todas as páginas TSX.
- `resolve(name)`: converte o nome enviado pelo Laravel no componente React e lança erro quando ele não existe.
- `setup({ el, App, props })`: monta a aplicação Inertia com React 19.

`resources/js/app.js` é apenas um arquivo placeholder e não é usado pelo Vite atual.

#### `AppLayout`

Layout autenticado com marca, navegação, usuário e conteúdo.

- `usePage()`: lê URL e props compartilhadas.
- `useEffect()`: exibe flash de sucesso via Toastify.
- Mapeamento de `links`: cria navegação Inertia e marca a rota ativa.
- Botão **Sair**: envia POST para `/logout` com `router.post()`.

#### `BrandLogo`

- Recebe `compact` para escolher ícone + nome ou logo horizontal.
- Recebe `href` para envolver a marca em `Link`; sem href, renderiza apenas a imagem.

### 7.2 Páginas públicas

- `Login`: usa `<Form>` para autenticar e exibe erros/processamento.
- `Register`: envia criação de usuário e organização.
- `FirstPassword`: envia senha atual, nova senha e confirmação; também oferece logout.

### 7.3 Páginas autenticadas

- `Dashboard`: apresenta organização, papel e atalhos para busca.
- `Developers/Index`: mantém seleção local de logins, envia filtros de descoberta, adiciona um ou vários candidatos e pagina resultados.
- `Developers/Show`: exibe perfil/score, mantém formulário local com `useForm()` e usa `save()` para normalizar tags e enviar PATCH; favorito usa PATCH separado.
- `Developers/Compare`: exibe até quatro perfis ou um estado vazio quando há menos de dois.
- `Recruitment/Pipeline`: agrupa entradas por status; drag-and-drop e `<select>` enviam PATCH ao recrutamento.
- `Team/Index`: controla qual modal está aberto. `openCreateModal()`, `openEditModal()`, `closeModal()` e `closeDeleteModal()` administram o estado local.
- `Audit/Index`: lista eventos e formata datas no locale `pt-BR`.

### 7.4 Componentes de equipe

#### `MemberModal`

- Inicializa `useForm()` para cadastro ou edição.
- `useEffect()`: fecha com Escape, bloqueia scroll do body e limpa listeners ao desmontar.
- `submit(event)`: previne submit nativo e escolhe POST para criação ou PATCH para edição.

#### `DeleteMemberModal`

- `useEffect()`: aplica o mesmo ciclo de Escape/bloqueio de scroll.
- `remove()`: envia DELETE para a associação e fecha ao concluir com sucesso.

## 8. Factories e seeder

Factories criam dados previsíveis para testes:

- `UserFactory::definition()`: usuário verificado com senha compartilhada em cache; `unverified()` remove verificação.
- `OrganizationFactory::definition()`: empresa fake com slug único.
- `MembershipFactory::definition()`: associação viewer; `owner()` altera o papel e `revoked()` define revogação.
- `DeveloperFactory::definition()`: perfil GitHub fictício com métricas.
- `AuditLogFactory::definition()`: evento ligado a organização e ator.
- `FavoriteFactory`, `InvitationFactory`, `NoteFactory`, `PipelineEntryFactory`, `ScoreSnapshotFactory` e `TagFactory`: existem, mas seus `definition()` ainda retornam atributos vazios e dependem de dados informados pelo teste.

`DatabaseSeeder::run()` cria `Test User` com `test@example.com`. Ele não cria uma organização ou membership, portanto esse usuário isolado não acessa o workspace sem dados adicionais.

## 9. Testes

- `AuthenticationTest`: cadastro owner, login/logout e credenciais inválidas.
- `OrganizationTenancyTest`: resolução correta do tenant, bloqueio de tenant estrangeiro e associação revogada.
- `DeveloperWorkflowTest`: sincronização fake do GitHub, score, isolamento de recrutamento, permissão viewer e descoberta.
- `TeamManagementTest`: criação/edição/remoção de membros, senha temporária, primeiro acesso, autorização e soft deletes.
- `ExampleTest`: redirecionamento inicial e presença dos assets da marca.
- `Unit/ExampleTest`: teste placeholder sem regra de domínio.
- `tests/Pest.php`: configura o ambiente global do Pest e atualização do banco entre testes.
- `tests/TestCase.php`: classe-base que inicializa a aplicação Laravel nos testes.

Chamadas externas são substituídas por `Http::fake()` e `Http::preventStrayRequests()`, evitando acesso real ao GitHub durante os testes.

## 10. Configuração e infraestrutura

- `.env`: ambiente, banco, sessão, cache, fila e `GITHUB_TOKEN`.
- `config/services.php`: expõe o token do GitHub para a aplicação.
- `compose.yaml`: executa PHP 8.5/Laravel Sail e PostgreSQL 18.
- `vite.config.ts`: build do React, TypeScript, CSS e integração Laravel/Vite.
- `resources/views/app.blade.php`: HTML raiz, favicon, Vite e diretivas Inertia.
- `resources/css/app.css`: Tailwind CSS 4 e estilos específicos da interface.
- `public/brand`: logo e ícone do DevScout.

## 11. Pontos preparados para evolução

O código já contém bases que ainda não estão completamente conectadas ao produto atual:

- `InvitationService`, `Invitation` e `StoreInvitationRequest`: convites existem no domínio, mas não há rota de aceite/envio.
- `DeveloperRepository::paginate()` e `DeveloperSearchRequest`: listagem local filtrada pronta, enquanto a tela atual prioriza descoberta externa.
- `PipelineStatus`: enum existe, mas algumas validações e telas ainda repetem strings de status.
- `DeveloperPolicy::updateRecruitment()`: deve conferir o papel da associação pertencente ao tenant atual, e não qualquer associação ativa do usuário.
- Factories vazias: seis factories precisam de estados padrão para serem reutilizadas sem atributos manuais.
- `resources/js/app.js`: placeholder legado, pois a entrada real é `app.tsx`.

## 12. Onde alterar cada comportamento

| Necessidade | Arquivo/camada principal |
| --- | --- |
| Alterar filtros do GitHub | `DeveloperDiscoveryRequest`, `GitHubClient`, `DiscoverDevelopersService` |
| Alterar dados sincronizados | `GitHubProfileNormalizer`, migration/model `Developer` |
| Alterar fórmula do score | `DeveloperScoringService` e testes de workflow |
| Adicionar estágio do pipeline | `PipelineStatus`, requests, service e páginas React |
| Alterar permissões | Policies e Form Requests |
| Alterar isolamento por organização | Middleware, `TenantContext` e queries dos services |
| Alterar cadastro/login | Controllers Auth, Requests Auth e services Identity |
| Alterar gestão de equipe | `MembershipService`, Requests, `TeamController` e componentes Team |
| Alterar dados globais do frontend | `HandleInertiaRequests` |
| Alterar navegação/layout | `AppLayout.tsx` e `app.css` |

## Documentos relacionados

- [README principal](../README.md)
- [Guia de uso](guia-de-uso.md)
