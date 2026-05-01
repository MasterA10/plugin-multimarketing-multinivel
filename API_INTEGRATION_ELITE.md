# Elite LMS: Especificação Técnica de Integração de Assinaturas (Elite Pay)

Esta documentação detalha os payloads JSON e os endpoints necessários para integrar o sistema de pagamentos externo com o Elite LMS.

## 1. Configuração de Endpoints
No painel administrativo do Elite LMS, o administrador deve configurar as URLs para cada uma das ações abaixo. Todas as requisições enviadas pelo LMS incluem um header de autorização:
`Authorization: Bearer {lms_external_api_token}`

---

## 2. Consulta de Status Individual (Single Check)
**Ação:** Verificar se um usuário específico possui assinatura ativa e qual sua data de expiração.
- **Payload Enviado pelo LMS:**
```json
{
  "action": "get_user_status",
  "email": "usuario@exemplo.com"
}
```
- **Resposta de Sucesso Esperada (HTTP 200):**
```json
{
  "success": true,
  "data": {
    "is_active": true,
    "expiry_date": "2026-12-31",
    "plan_name": "Assinatura Mensal Elite",
    "gateway_reference": "ref_12345"
  }
}
```

---

## 3. Sincronização Global (Bulk Sync)
**Ação:** Atualizar o status de toda a rede de uma só vez (recomendado via Cron).
- **Payload Enviado pelo LMS:**
```json
{
  "action": "get_active_list"
}
```
- **Resposta de Sucesso Esperada (HTTP 200):** Uma lista de objetos de usuário.
```json
{
  "success": true,
  "data": [
    { "email": "usuario1@elite.com", "is_active": true, "expiry_date": "2026-05-10" },
    { "email": "usuario2@elite.com", "is_active": true, "expiry_date": "2026-06-15" }
  ]
}
```

---

## 4. Solicitação de Cancelamento
**Ação:** Quando o aluno clica em "Confirmar Cancelamento" após preencher a pesquisa de feedback.
- **Payload Enviado pelo LMS:**
```json
{
  "action": "cancel_subscription",
  "email": "usuario@exemplo.com",
  "reason": "Preço/Financeiro: O valor está acima do meu orçamento atual."
}
```
- **Resposta de Sucesso Esperada (HTTP 200):**
```json
{
  "success": true,
  "message": "Assinatura cancelada com sucesso. Acesso garantido até 2026-04-30."
}
```

## 5. Notas Importantes (Lógica de Prazo de Graça)
O Elite LMS implementa uma lógica de **Grace Period**. 
- Se a API retornar `"is_active": false`, mas a data atual for anterior à `"expiry_date"` registrada anteriormente, o sistema **NÃO bloqueia o acesso**.
- O bloqueio total só ocorre quando `is_active` é falso **E** a data de expiração já passou.
