# Registro de Tarefas e Definições de CPT

Este documento detalha todas as implementações realizadas no plugin `multinivel_marketing` conforme o histórico de alterações, incluindo a definição técnica dos Custom Post Types (CPTs).

## 1. Definição dos Custom Post Types (CPTs)

Para o pleno funcionamento do sistema, os seguintes CPTs devem ser registrados no WordPress:

### **Elite Landing Pages (`elite_lp`)**
- **Propósito**: Páginas de venda de alta conversão (Singleton).
- **Slug Base**: `/elite/`
- **Configurações**:
    - `supports`: `['title', 'editor', 'thumbnail', 'revisions']`
    - `has_archive`: `false`
    - `publicly_queryable`: `true`
    - **Páginas Fixas (Slugs)**: `mestre`, `baile-de-gala`, `ccp-academy`.

### **Lessons (`lms_lesson`)**
- **Propósito**: Conteúdo das aulas dos cursos.
- **Hierarquia**: Associado a cursos (LMS).
- **Ações**: Redirecionamento de botões de desbloqueio para a LP da CCP Academy.

### **Lives (`lms_live`)**
- **Propósito**: Mentorias e transmissões ao vivo.
- **Ações**: Redirecionamento de botões de acesso na sala de espera para a LP da CCP Academy.

---

## 2. Checklist Detalhado das Tarefas Realizadas

### Gestão de Landing Pages (Singleton System)
- `[x]` **Implementação de Singleton**: Travamento do sistema para permitir apenas os 3 modelos core (Elite Gran Master, Baile de Gala, CCP Academy).
- `[x]` **Bloqueio de UI**: Remoção dos botões "Nova Página" e "Lixeira" no painel administrativo para evitar deleções acidentais.
- `[x]` **Provisionamento Automático**: Lógica para criar as páginas automaticamente caso não existam.
- `[x]` **Sincronização de Botões**: Botões de Hero em todas as LPs agora são dinâmicos e editáveis via painel (`get_elite_button`).

### Melhorias Visual e UX (Landing Pages)
- `[x]` **CCP Academy - Preços**: Correção do layout de `/mês` para evitar compressão visual.
- `[x]` **Baile de Gala - Embaixadoras**: Adição de 4 campos de imagem (Juliana, Cátia, Cley, Paty) para tornar as fotos editáveis via painel.
- `[x]` **Baile de Gala - Estilo**: Implementação de efeito hover (brilho dourado) e máscaras circulares para as fotos das embaixadoras.
- `[x]` **Hooks Globais**: Inclusão de `wp_head()` e `wp_footer()` em templates que usavam HTML puro, permitindo scripts de terceiros e plugins.

### Fluxo de Vendas e Redirecionamentos
- `[x]` **Centralização de Vendas**: Todo tráfego de "Adquirir Acesso" redirecionado para `/elite/ccp-academy/`.
- `[x]` **Redirecionamento 301**: Implementada regra no core para capturar acessos em `/adquirir-acesso/` e enviar para a nova LP premium.
- `[x]` **Integração no Player**: Botões de "Desbloquear Jornada" e "Liberar Downloads" dentro das aulas atualizados para o novo fluxo.
- `[x]` **Badge flutuante**: Atualizado o texto e o link do indicador flutuante de visitante.

### Área de Membros e Privacidade
- `[x]` **Privacidade do Usuário**: Remoção do link "Editar Perfil" no dashboard para manter o aluno dentro da experiência customizada.
- `[x]` **Página de Equipe Standalone**: Transformação da página de equipe em independente (sem carregar cabeçalho/rodapé do tema original).

### Sistema de Certificação de Elite
- `[x]` **Modal de Confirmação**: Implementado modal global para que o aluno confirme o nome impresso antes de gerar qualquer certificado.
- `[x]` **Lógica Auto-Fit**: Script para redimensionar automaticamente a fonte do nome conforme o comprimento, evitando cortes.
- `[x]` **Layout de Luxo**: Expansão do container de nome para 1020px e aumento da fonte base para 5.5rem.
- `[x]` **Limpeza de Design**: Remoção definitiva das seções de assinatura no rodapé dos certificados.

### Divulgação e Afiliados (Referral)
- `[x]` **Botão de Indicação Flutuante**: Novo botão exclusivo para Educadoras e Administradores.
- `[x]` **Cópia Inteligente**: Botão copia a URL da página atual já com o parâmetro `?ref=usuario` anexado.
- `[x]` **Compatibilidade Universal**: Botão reconstruído em Vanilla CSS para funcionar em áreas sem Tailwind (Dashboard/Admin).

### Diagnóstico e Estabilidade
- `[x]` **Logger Avançado**: Implementação de captura de Erros Fatais, falhas de E-mail (`wp_mail_failed`), trocas de temas e atualizações de plugins no `elite-debug.log`.
