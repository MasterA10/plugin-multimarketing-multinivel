# Elite LMS: Documentação de Sincronização de Acessos via API

Esta documentação descreve o funcionamento técnico da integração entre o Elite LMS e APIs externas para o controle de permissões e vigência de assinaturas.

## 1. Arquitetura de Autenticação
O sistema utiliza o método de **Bearer Token** para todas as requisições.
- **Header**: `Authorization: Bearer {lms_external_api_token}`
- **Accept**: `application/json`

## 2. Endpoints Suportados

### A. Sincronização Global (Bulk Sync)
Utilizado para atualizar o status de todos os usuários cadastrados de uma só vez.
- **Endpoint**: `{lms_external_api_url}?action=get_active_list`
- **Método**: `GET`
- **Resposta Esperada**: Um objeto JSON contendo a chave `data` com uma lista de e-mails ativos.
```json
{
    "success": true,
    "data": [
        "usuario1@exemplo.com.br",
        "usuario2@exemplo.com.br",
        "lider@elite.com"
    ]
}
```

### B. Verificação Individual (Single Check)
Utilizado para validar o status e a data de expiração de um usuário específico.
- **Endpoint**: `{lms_external_api_url}?action=get_user_status&email={user_email}`
- **Método**: `GET`
- **Resposta Esperada**: Um objeto contendo o booleano `is_active` e, opcionalmente, a data `expiry_date`.
```json
{
    "success": true,
    "data": {
        "is_active": true,
        "expiry_date": "2026-12-31"
    }
}
```

## 3. Hierarquia de Controle de Acesso
O Elite LMS processa o acesso seguindo esta ordem de precedência (definida em `Expressive_Access`):

1. **Controle Manual (Hard Override)**:
    - `blocked`: Acesso negado independentemente da API.
    - `unblocked`: Acesso liberado independentemente da API.
2. **Controle Automático (API Mode)**:
    - Se o controle manual estiver em `none`, o sistema consulta o metadado `_lms_elite_api_status` (atualizado via API).

## 4. Mapeamento de Dados (WordPress User Meta)
| Campo JSON | Metadado WordPress | Descrição |
| :--- | :--- | :--- |
| `is_active` | `_lms_elite_api_status` | Armazena 'active' ou 'inactive' |
| `expiry_date` | `_lms_elite_api_expiry` | Data da próxima renovação/expiração |
| - | `_lms_elite_api_last_check` | Timestamp do último contato com a API |

## 5. Implementação Técnica
As classes responsáveis por este fluxo são:
- `Expressive_External_API`: Gerencia as requisições `wp_remote_get`.
- `Expressive_Access`: Centraliza a lógica de decisão final de acesso.
- `Expressive_Engine`: Disponibiliza os hooks AJAX para os botões do painel.

---
*Documentação gerada automaticamente para suporte à infraestrutura Elite LMS.*
