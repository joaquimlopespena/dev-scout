# Guia de uso do DevScout

Este guia apresenta o fluxo principal da aplicação, desde a criação do workspace até o acompanhamento dos candidatos e da equipe.

## 1. Crie o primeiro workspace

1. Acesse [http://localhost/register](http://localhost/register).
2. Informe seu nome, o nome da organização, e-mail e senha.
3. Clique em **Criar conta**.
4. O primeiro usuário será o proprietário (`owner`) da organização e será direcionado para a visão geral.

Os dados de candidatos, pipeline, equipe e auditoria ficam vinculados à organização ativa.

## 2. Encontre candidatos no GitHub

1. No menu lateral, acesse **Desenvolvedores**.
2. Preencha um ou mais filtros disponíveis:
   - **Usuário:** parte ou totalidade do username do GitHub.
   - **Linguagem:** tecnologia principal, como `PHP`, `JavaScript` ou `Go`.
   - **Localização:** cidade, estado ou país informado no perfil.
   - **Mín. repositórios:** quantidade mínima de repositórios públicos.
   - **Mín. estrelas:** soma mínima de estrelas dos repositórios encontrados.
3. Clique em **Buscar candidatos**.
4. Use **Selecionar perfil** para adicionar um candidato ou marque várias caixas e clique em **Adicionar selecionados**.

Ao selecionar um perfil, o DevScout consulta os dados públicos do GitHub, calcula o score e adiciona o candidato à etapa `sourced` do pipeline.

## 3. Analise um perfil

1. Acesse **Pipeline** no menu lateral.
2. Clique no nome de um candidato.
3. Consulte o score geral e as seis dimensões utilizadas no cálculo.
4. Confira estrelas, seguidores, repositórios, linguagem principal e o link para o GitHub.
5. Se necessário, clique em **Favoritar** para destacar o perfil.

O score é um sinal de apoio à avaliação. As evidências públicas e o contexto da vaga também devem ser considerados na decisão.

## 4. Registre a avaliação

Na seção **Recrutamento** do perfil:

1. Escolha o status atual do candidato.
2. Informe tags separadas por vírgula, por exemplo `Laravel, Backend, Pleno`.
3. Escreva uma nota com evidências, pontos de atenção ou decisões da equipe.
4. Clique em **Salvar**.

As notas anteriores permanecem listadas no perfil. Usuários com papel `viewer` podem consultar os dados, mas não podem alterar informações de recrutamento.

## 5. Movimente o candidato no pipeline

O pipeline possui seis etapas:

| Status | Significado |
| --- | --- |
| `sourced` | Perfil identificado e adicionado ao processo |
| `screening` | Triagem inicial |
| `interview` | Em entrevistas ou avaliação técnica |
| `offer` | Proposta enviada ou em preparação |
| `hired` | Candidato contratado |
| `rejected` | Candidato encerrado no processo |

Para alterar uma etapa, arraste o cartão para outra coluna ou escolha o novo status no seletor do próprio cartão. Também é possível alterar o status pela página do perfil.

## 6. Cadastre a equipe

Apenas proprietários e administradores podem gerenciar membros:

1. Acesse **Equipe**.
2. Clique em **Cadastrar membro**.
3. Informe nome, e-mail, senha temporária e papel.
4. Compartilhe as credenciais temporárias com o novo membro por um canal seguro.
5. No primeiro login, o membro deverá substituir a senha temporária por uma senha definitiva.

Papéis disponíveis:

- `admin`: gerencia a organização, os membros e o processo seletivo.
- `evaluator`: avalia candidatos e atualiza informações de recrutamento.
- `viewer`: possui acesso somente para consulta.

## 7. Consulte a auditoria

Acesse **Auditoria** para visualizar ações administrativas, o responsável por cada evento e a data em que ocorreu. Use esse histórico para acompanhar alterações relevantes no workspace.

## 8. Encerre a sessão

Em uma tela desktop, clique em **Sair**, no final da barra lateral. O menu compacto para dispositivos móveis ainda não exibe essa ação.

## Próximos passos

- Volte para o [README principal](../README.md).
- Consulte os comandos de instalação, desenvolvimento e testes no README.
