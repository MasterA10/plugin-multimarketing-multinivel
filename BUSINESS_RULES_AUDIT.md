# BUSINESS_RULES_AUDIT.md

Este documento audita as regras de negócio do plugin contra a implementação atual, identificando riscos arquiteturais.

## Auditoria de Regras de Negócio

| Regra de Negócio | Status | Risco | Observações |
| :--- | :--- | :--- | :--- |
| **Gestão Centralizada de Conteúdo** | ⚠️ ATENÇÃO | Alto | A duplicidade de CPTs (`mlm_` vs `lms_`) viola a regra de fonte única de verdade para cursos/aulas. |
| **Integridade de Indicação (Referral)** | ✅ OK | Baixo | Lógica protegida por checagens de banco de dados, apesar da redundância de hooks. |
| **Hierarquia de Acesso (RBAC)** | ✅ OK | Baixo | Aplicação consistente de metadados (`_lms_visibility_role`). |
| **Singularidade de Landing Pages** | ✅ OK | Nenhum | Pattern Singleton aplicado via `ensure_singleton_landing_pages`. |
| **Sincronização de Status (ASAAS)** | ⚠️ ATENÇÃO | Médio | Depende da estabilidade da API e do cron; conflito de roles pode bloquear o acesso indevidamente. |

## Riscos Identificados

### 1. Fragmentação de Conteúdo (CPTs Duplicados)
*   **O que está acontecendo:** Existem dois sistemas de registro de CPTs (`mlm_` no base e `lms_` no core).
*   **Violação:** O sistema não possui uma "governança centralizada". Conteúdos criados via menu `mlm_` não aparecem na interface "Elite" do `expressive-core`.
*   **Impacto:** Usuários administradores podem criar cursos na área errada, perdendo a visibilidade no frontend.

### 2. Conflito de Governança de Roles (Race Condition)
*   **O que está acontecendo:** O módulo base tenta aplicar roles pós-checkout, enquanto o core intercepta para aprovação.
*   **Violação:** A regra de negócio de "Aprovação Elite" é vulnerável a sobrescrita imediata pelo sistema base.
*   **Impacto:** Risco de "Pagamento Aprovado, Acesso Negado" por causa de timing de execução.

## Recomendações
1.  **Depreciação Programada:** Tratar o `mlm_` como legado. Impedir a criação de novos conteúdos via interface `mlm_`.
2.  **Locking de Role:** Centralizar a atribuição de roles exclusivamente no `Expressive_Engine` após o checkout, ignorando a lógica do módulo base.
3.  **Auditoria de Logs:** Implementar logs específicos para falhas de atribuição de role, facilitando o suporte ao cliente final.
