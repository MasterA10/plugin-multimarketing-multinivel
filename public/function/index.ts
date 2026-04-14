import { serve } from "https://deno.land/std@0.168.0/http/server.ts"

const ASAAS_API_KEY = Deno.env.get('ASAAS_API_KEY') || '';
const ASAAS_URL = Deno.env.get('ASAAS_URL') || 'https://api.asaas.com/v3';

const corsHeaders = {
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Headers': 'authorization, x-client-info, apikey, content-type',
};

// Função auxiliar para buscar todos os registros de um endpoint Asaas com paginação
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
    // Lida com requisições OPTIONS (CORS)
    if (req.method === 'OPTIONS') {
        return new Response('ok', { headers: corsHeaders });
    }

    try {
        const { action, email } = await req.json();

        // 1. AÇÃO: Listar todos os ativos com detalhes completos (Sync Bulk)
        if (action === 'get_active_list') {
            console.log("Iniciando get_active_list com paginação...");
            
            // Buscamos em paralelo assinaturas e clientes para o "join"
            const [subscriptions, customers] = await Promise.all([
                fetchAsaasAll('subscriptions', 'status=ACTIVE'),
                fetchAsaasAll('customers')
            ]);

            // Criamos um mapa de ID do Cliente -> Email para busca rápida O(1)
            const customerMap = new Map();
            customers.forEach((c: any) => {
                customerMap.set(c.id, c.email);
            });

            // Mapeamos os dados conforme o api_sync_sample.json
            const mappedData = subscriptions.map((sub: any) => {
                const customerEmail = customerMap.get(sub.customer) || 'N/A';
                return {
                    email: customerEmail,
                    is_active: sub.status === 'ACTIVE',
                    expiry_date: sub.nextDueDate,
                    plan_name: sub.description || 'Plano Elite',
                    gateway_reference: sub.id
                };
            });

            console.log(`Sincronização concluída: ${mappedData.length} assinaturas ativas encontradas.`);

            return new Response(
                JSON.stringify({
                    success: true,
                    message: `Lista detalhada recuperada: ${mappedData.length} usuários ativos`,
                    data: mappedData,
                    last_sync: new Date().toISOString()
                }),
                { headers: { ...corsHeaders, 'Content-Type': 'application/json' } }
            );
        }

        // 2. AÇÃO: Status Individual
        if (action === 'get_user_status' && email) {
            // Primeiro buscamos o cliente pelo email
            const customerRes = await fetch(`${ASAAS_URL}/customers?email=${email}`, {
                headers: { 'access_token': ASAAS_API_KEY }
            });
            
            if (!customerRes.ok) throw new Error("Erro ao buscar cliente no Asaas.");
            
            const customerData = await customerRes.json();

            if (customerData.data.length === 0) {
                throw new Error("Usuário não encontrado no gateway.");
            }

            const customerId = customerData.data[0].id;

            // Buscamos a assinatura desse cliente
            const subRes = await fetch(`${ASAAS_URL}/subscriptions?customer=${customerId}`, {
                headers: { 'access_token': ASAAS_API_KEY }
            });
            
            if (!subRes.ok) throw new Error("Erro ao buscar assinatura no Asaas.");
            
            const subData = await subRes.json();
            const activeSub = subData.data[0]; // Pegamos a assinatura mais recente

            return new Response(
                JSON.stringify({
                    success: true,
                    message: "Status recuperado com sucesso",
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
        console.error("Erro na execução:", error.message);
        return new Response(JSON.stringify({ error: error.message }), {
            headers: { ...corsHeaders, 'Content-Type': 'application/json' },
            status: 500,
        });
    }
})