# Especificação de Regras de Negócio — Sistema Eventos Control

Este documento consolida as regras de negócio implícitas e explícitas identificadas durante o brainstorming sobre o **Sistema Eventos Control**. O objetivo é prover uma especificação conceitual pura e estruturada, livre de ruídos de conversação, para servir como base documental única nas futuras etapas de refatoração do sistema legado.

---

## 1. Resumo Executivo das Regras

O **Sistema Eventos Control** gerencia o ciclo de vida financeiro e operacional de apresentações artísticas (gigs) nacionais e internacionais, cobrindo desde a confecção dos contratos até a baixa financeira e conciliação bancária final. 

O fluxo principal do sistema opera de forma sequencial e interdependente:
1. **Contratos:** O fluxo se inicia com a criação do contrato, no qual são definidos os dados básicos do contratante e do evento.
2. **Contas a Receber (Provisionamento):** O setor de contas a receber detalha as parcelas e os destinos financeiros (Panorama, Coral ou Artista). O contas a pagar permanece bloqueado para o respectivo contrato até que o contas a receber valide e conclua o provisionamento.
3. **Contas a Pagar (Execução de Despesas):** Com o provisionamento validado e liberado pelo contas a receber, o financeiro lança os repasses (artistas, custos operacionais e logística).
4. **Conciliação Bancária:** O extrato bancário importado é confrontado com os lançamentos provisionados de entrada (receber) e saída (pagar), permitindo baixas unitárias ou agrupadas, além de ajustes rápidos de divergências de valores.
5. **Conclusão:** O ciclo de vida do contrato é encerrado quando todas as obrigações a receber foram liquidadas e todos os repasses/despesas a pagar foram devidamente executados, sem pendências contábeis ou de movimentação interna.

---

## 2. Lista Detalhada de Regras de Negócio por Categoria

### Categoria A: Gestão de Contratos
* **CR-01: Fluxo Inicial de Confecção:** O processo se inicia com a redação e inserção dos dados básicos de um novo contrato, baseando-se inicialmente nos dados fornecidos pela planilha de contratos (ex: planilha da Rosângela).
* **CR-02: Pré-cadastro de Contratante:** Ao criar ou editar um contrato, o sistema deve permitir o pré-cadastro ou a seleção de um contratante já existente na base de dados unificada.
* **CR-03: Filtros de Estado do Contrato:** A tela de controle de contratos deve possuir filtros avançados para pesquisar por:
  * **Status do Contrato** (ex: Ativo, Cancelado, Concluído).
  * **Status de Assinatura** (ex: Em Confecção, Enviado para Assinatura, Assinado).
* **CR-04: Ciclo de Vida (Lifecycle) do Contrato:** O contrato possui estados lógicos controlados:
  * **Draft/Confeccionando:** Edição inicial.
  * **Provisionando:** Aguardando definição das parcelas a receber.
  * **Em Execução (Liberado para Pagar/Receber):** Ativado após conciliação das parcelas.
  * **Concluído:** Status final quando todas as parcelas foram recebidas e todas as contas a pagar vinculadas foram pagas.
* **CR-05: Restrição de Perfil de Acesso (Contratos):** Usuários com o perfil restrito de "Contrato" podem confeccionar e visualizar o andamento contratual básico, mas não devem ter visibilidade sobre informações de contas a pagar/receber ou dados administrativos/recursos humanos.

### Categoria B: Contas a Receber
* **AR-01: Dependência de Dados:** O Contas a Receber é alimentado pelas especificações detalhadas do contrato (baseadas na planilha de contas a receber - ex: planilha da Cecília), incluindo:
  * Contratante (e respectivo CNPJ).
  * Nome e ano do evento.
  * Cronograma de parcelas e valores extras.
* **AR-02: Validação de Lançamentos Completos:** O sistema impede o avanço do contrato para a fase de contas a pagar enquanto houver "Lançamentos Incompletos".
  * *Regra de Validação:* A soma das parcelas contratuais provisionadas deve ser exatamente igual ao valor total estipulado no contrato.
* **AR-03: Desconsideração de Extras na Validação Contratual:** Lançamentos do tipo "Extra Contratual" (ex: reembolso de alimentação ou logística extra) não entram na validação do valor total do contrato (não abatem nem somam ao valor base do contrato para fins de liberação do Contas a Pagar).
* **AR-04: Direcionamento de Recebíveis (Roteamento Financeiro):** Cada parcela de recebimento deve ter um destino de conta contábil/bancária explicitamente definido no momento do provisionamento. Os destinos possíveis são:
  * **Panorama** (Conta da Agência Panorama).
  * **Coral** (Conta da Agência Coral).
  * **Artista** (Recebimento direto pelo artista, caracterizando movimentação interna).
* **AR-05: Divisão de Parcelas (Split de Lançamentos):** Caso uma parcela contratual (ex: R$ 2.000,00) seja paga com destinos distintos (ex: R$ 1.000,00 para a agência e R$ 1.000,00 diretamente ao artista), o sistema deve permitir o desmembramento (split) da parcela na interface, gerando lançamentos específicos com seus respectivos destinos.
* **AR-06: Notificação de Provisionamento Concluído:** Assim que o valor provisionado a receber atingir a equivalência com o valor total do contrato, o contas a receber conclui a etapa e o financeiro é notificado de que o Contas a Pagar para aquela gig está liberado para lançamentos.
* **AR-07: Controle de Inadimplência e Prazos:** A interface do contas a receber deve apresentar de forma clara:
  * Dias de atraso (ex: "Atrasada há X dias" em vermelho).
  * Dias restantes para o vencimento (ex: "Vence em Y dias").
  * Badges visuais coloridas para indicar o status da parcela (ex: Verde para Pago, Vermelho para Atrasado, Amarelo para Provisionado/Pendente).
* **AR-08: Roteamento de Comissão via Movimentação Interna:** Se o contratante pagar 100% do valor do contrato diretamente ao artista (incluindo a parte que seria da agência), o valor da comissão da agência deve ser registrado como saldo pendente em "Movimentação Interna" para ser ajustado/compensado no acerto de contas de outro evento futuro com o mesmo artista.

### Categoria C: Contas a Pagar
* **AP-01: Bloqueio Prévio:** O lançamento de contas a pagar referentes a um contrato (gig) está bloqueado até que o contas a receber faça o encaminhamento/liberação do provisionamento.
* **AP-02: Separação de Lançamentos (Gigs vs. Administrativo/Operacional):** 
  * **Lançamentos Contratuais (Gigs):** Devem obrigatoriamente referenciar um contrato, data do evento e artista.
  * **Lançamentos Operacionais/Administrativos (ex: Folha de pagamento, Aluguel, RH):** Não referenciam eventos ou contratos. Os campos de vínculo de evento devem ser ocultados ou definidos como opcionais.
* **AP-03: Rateio de Cachê (Múltiplas Contrapartes):** Para artistas que dividem o cachê com terceiros ou equipe (ex: rateio em 3 pessoas), o sistema deve permitir a criação de múltiplos lançamentos de contas a pagar para a mesma gig, dividindo os valores devidos para contrapartes distintas, sem que isso invalide o vínculo com o contrato original.
* **AP-04: Estrutura de Informações do Lançamento:** Um lançamento de Contas a Pagar deve conter as seguintes colunas/campos obrigatórios:
  * Conta bancária de saída.
  * Data devida (vencimento) — *Nota: Lançamentos de cachê de gigs nacionais geralmente são provisionados para a quarta-feira subsequente à data do evento.*
  * Valor devido.
  * Contraparte (Favorecido vinculado a um CNPJ/CPF).
  * Tipo de documento fiscal (Nota Fiscal, Contrato de Câmbio, Recibo ou Vazio se estiver aguardando recebimento do prestador).
  * Número do documento fiscal.
  * Descrição (ex: cachê, alimentação, logística).
  * Data do evento (se aplicável).
  * Contrato de referência (se aplicável).
  * Conta contábil/Centro de Custo (Cash Flow: Ex: Artista Nacional, Custo de Gig).
  * Caixa competência.
  * Meio de pagamento.
  * Detalhes/Informações do favorecido (dados bancários).
* **AP-05: Fluxo de Status de Pagamento (Validação Gerencial):**
  1. **Pendente:** Lançado no sistema.
  2. **Processando:** Lançamento verificado e enviado para pagamento.
  3. **Pago:** Pagamento executado e confirmado.
* **AP-06: Restrição de Acesso Administrativo:** Lançamentos de contas a pagar de cunho administrativo, recursos humanos ou particulares da agência são confidenciais e devem ser visíveis apenas para o perfil de Administrador Financeiro, ocultando-se dos operadores gerais de contas a pagar/receber.

### Categoria D: Conciliação Bancária
* **CB-01: Central de Conciliação:** Tela unificada onde o operador pode visualizar o extrato bancário importado ao lado da lista de lançamentos pendentes de contas a receber e contas a pagar.
* **CB-02: Mecanismo de Busca Dinâmica:** O sistema deve permitir buscar lançamentos pendentes por termos genéricos contidos nas observações ou campos chave (ex: nome do evento, nome do artista, nome do contratante).
* **CB-03: Sugestão de Confrontação (Auto-match):** Ao selecionar uma linha do extrato bancário, o sistema deve sugerir lançamentos com base em proximidade de valor, data e descrição do evento.
* **CB-04: Conciliação Multilateral (Múltiplos Lançamentos para uma Transação):** Se uma transação do extrato (ex: recebimento de R$ 10.000,00) corresponder a vários lançamentos provisionados (ex: 4 parcelas de R$ 2.500,00 ou um misto de parcela e alimentação), o operador deve poder selecionar múltiplos títulos até que a soma destes bata exatamente com o valor do extrato.
  * *Validação:* O sistema impede a conclusão da conciliação se a soma dos títulos selecionados divergir do valor da transação bancária.
* **CB-05: Ajuste de Divergência de Valores (Ajuste Rápido):** Caso o valor recebido seja maior ou menor que o provisionado (ex: devido a taxas extras ou alimentação inclusa não provisionada), a interface da central de conciliação deve permitir o ajuste rápido do valor provisionado para igualar ao valor real do extrato antes de conciliar.
* **CB-06: Duplicidade de Importação:** O sistema não deve permitir a importação de transações de extrato já importadas anteriormente para o mesmo banco/período.
* **CB-07: Contas de Tesouraria:** O sistema gerencia saldos específicos por conta de movimentação ativa:
  * Contas Bancárias Reais (ex: Bradesco, Itaú).
  * Conta de Movimentação Interna (registros sem trânsito bancário real).
* **CB-08: Exibição de Saldos Conciliados vs. Não Conciliados:** A tela de conciliação deve expor claramente o saldo total em conta, segregando valores "Não Conciliados", "Não Identificados" ou "Não Localizados".

### Categoria E: Fluxo Internacional (Spec 0010)
* **INT-01: Modalidades de Operação:**
  * **Importação:** Contratação de artista gringo para tocar no Brasil.
  * **Exportação:** Envio de artista nacional para tocar no exterior.
* **INT-02: Controle Multimoedas:** Cada linha/lançamento internacional deve permitir o controle de valores em três moedas distintas em colunas separadas:
  * **BRL (Real)**
  * **USD (Dólar)**
  * **EUR (Euro)**
  * *Regra de Conversão:* A presença de valores em moeda estrangeira sem equivalente em BRL indica que o lançamento está aguardando o fechamento do câmbio ou a conversão oficial.
* **INT-03: Tratamento de Valores Negativos:** Valores negativos nas colunas de moeda representam débitos contábeis (valores devidos) e devem ser mantidos como valores absolutos negativos nas operações de cálculo financeiro internacional.
* **INT-04: Remessas Diretas (Movimentação Interna):** Quando o contratante envia o pagamento em moeda estrangeira diretamente ao artista gringo no exterior (sem passar pela conta bancária da agência no Brasil), a transação não deve sofrer conciliação bancária tradicional. Ela deve ser registrada sob a conta de **Movimentação Interna** vinculada ao artista do contrato para fins de registro contábil e para evitar bitributação desnecessária sobre a agência nacional.
* **INT-05: Alçada de Aprovação de Câmbio:** Operadores do setor Internacional podem realizar os lançamentos de câmbio e provisões, mas a conversão oficial para BRL e a consequente baixa no contas a receber dependem da validação e execução direta do Administrador Financeiro.

### Categoria F: Cadastro Unificado de Pessoas (Entidades)
* **ENT-01: Registro Único de Pessoas:** Unificação das bases de clientes, fornecedores e colaboradores em uma única entidade/tabela. A busca deve ser indexada por Nome, CPF ou CNPJ.
* **ENT-02: Categorização por Tags/Etiquetas:** A distinção de papéis de uma entidade na plataforma deve ser feita por meio de tags dinâmicas atribuíveis:
  * `Artista` (com associação a dados específicos de agenciamento).
  * `Colaborador` (vinculado a folhas de pagamento e despesas administrativas).
  * `Geral` (fornecedores de insumos, contratantes e clientes gerais).

---

## 3. Notas Técnicas para Refatoração Futura

Com base nas discussões do brainstorming, as seguintes diretrizes arquiteturais e técnicas são recomendadas para a fase de desenvolvimento do novo sistema:

### 3.1. Arquitetura e Modelagem de Banco de Dados
* **Implementação de Padrão State Machine (Máquina de Estados):** Utilizar uma máquina de estados robusta para gerenciar os status de Contratos (`Draft` -> `Provisioning` -> `Executing` -> `Concluded`) e de Contas a Pagar/Receber (`Pending` -> `Processing` -> `Paid` / `Reconciled`). Isso impedirá transições inválidas (ex: pagar uma conta de contrato não liberado).
* **Estrutura de Roteamento Financeiro (Splits):** A tabela de parcelas (`installments`) não deve ter um único campo de destino. Deve existir uma relação de 1-para-N entre parcelas e transações de destino (`installment_destinations`), suportando o rateio (splits) de uma mesma parcela para destinos diferentes (ex: parte agência, parte artista).
* **Tabela Única de Pessoas (Polimorfismo de Entidades):** Criar uma tabela `entities` (com campos comuns como nome, CPF/CNPJ, dados de contato e dados bancários) e uma tabela associativa `entity_tags` para categorização (`artist`, `collaborator`, `general`). Isso evita redundância de cadastro quando um mesmo artista atua como fornecedor de outro serviço ou colaborador interno.
* **Modelo Multimoedas Adaptativo:** Suportar tabelas financeiras com campos para moeda base do lançamento, taxa de câmbio aplicada e valor convertido, permitindo auditoria clara do histórico de fechamento de câmbio de cada gig internacional.

### 3.2. Segurança e Controle de Acesso (RBAC)
* **Granularidade de Permissões (Roles):**
  * `role_contract`: Permite apenas a criação e edição básica de contratos. Bloqueado para ver campos financeiros reais de contas a pagar/receber.
  * `role_receivable`: Acesso à parametrização de parcelas, valores extras e baixas de recebíveis.
  * `role_payable`: Acesso à inserção e alteração de despesas operacionais vinculadas aos eventos.
  * `role_international`: Prerrogativas específicas para lidar com lançamentos multimoedas e remessas diretas.
  * `role_admin_finance`: Acesso total, incluindo despesas administrativas (folhas de pagamento, despesas operacionais da empresa) e aprovação final de baixas e câmbio.
* **Segregação de Dados Confidenciais:** Aplicar filtros de escopo nas consultas SQL/ORM para que usuários sem a permissão `role_admin_finance` nunca recebam payloads contendo dados de despesas de recursos humanos ou administrativas da agência.

### 3.3. Requisitos de Interface e Experiência do Usuário (UI/UX)
* **Formulários Compactos por Etapas (Steps/Wizards):** Inspirar-se no modelo compacto do Omie para evitar rolagens infinitas na criação de lançamentos de contas a pagar. Organizar em passos bem definidos:
  * **Passo 1:** Informações Gerais (Valor, Vencimento, Descrição, Centro de Custo).
  * **Passo 2:** Dados Bancários / Destinatário.
  * **Passo 3:** Periodicidade (se recorrente, como folha de pagamento).
* **Painel Dinâmico de Vencimentos:** Uso de cores semânticas e contadores de dias para status críticos:
  * Atrasados: vermelho vibrante com contador de dias decorridos.
  * A vencer: amarelo ou azul com contador de dias restantes.
  * Liquidado/Conciliado: verde com marcação de data de liquidação.
* **Central de Conciliação Interativa (Split-Screen / Drag-and-Drop):** Desenvolver uma interface onde de um lado estão as transações do extrato importado e do outro as provisões. O usuário deve ser capaz de selecionar múltiplos itens e ver a soma atualizada em tempo real antes de clicar no botão "Conciliar". Incluir um campo rápido de edição de valor direto na linha de provisão para facilitar o ajuste fino de divergências.
* **Campos Condicionais Dinâmicos:** No formulário de contas a pagar, ao alternar entre "Lançamento Contratual (Gig)" e "Lançamento Operacional/Administrativo", a interface deve ocultar ou mostrar dinamicamente os campos de Evento e Contrato de Referência, evitando poluição visual.
