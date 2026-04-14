import { serve } from "https://deno.land/std@0.168.0/http/server.ts"

const corsHeaders = {
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Headers': 'authorization, x-client-info, apikey, content-type',
};

// Dados mockados conforme solicitação do usuário
const mockUsers = [
    { email: "akkkks@gmail.com", is_active: true, name: "akkkks" },
    { email: "jose@gmail.com", is_active: true, name: "jose" },
    { email: "maria1@gmail.com", is_active: true, name: "maria1" },
    { email: "nasale@gmail.com", is_active: false, name: "nasale" },
    { email: "coco@gmail.com", is_active: false, name: "coco" },
    { email: "mariajoao@coco.com", is_active: false, name: "mariajoao" },
    { email: "mastera11cr11@gmail.com", is_active: true, name: "mastera11cr11" },
    { email: "eaglelink764@gmail.com", is_active: true, name: "eaglelink764" },
    { email: "nasalexalves@gmail.com", is_active: true, name: "nasalexalves" },
    { email: "educator_final@example.com", is_active: false, name: "educator_final" },
    { email: "educadora_test10@example.com", is_active: false, name: "educadora_test10" },
    { email: "test_jetski_final306@example.com", is_active: false, name: "test_jetski_final306" },
    { email: "ccpacademybeauty@gmail.com", is_active: true, name: "Catia Araujo" },
    { email: "contato@designerale.com", is_active: true, name: "admin" }
];

serve(async (req) => {
    if (req.method === 'OPTIONS') {
        return new Response('ok', { headers: corsHeaders });
    }

    try {
        const { action, email } = await req.json();
        const now = new Date();
        const futureDate = "2029-12-31";
        const pastDate = "2024-01-01";

        // 1. AÇÃO: Listar todos os usuários (Mock Sync - Apenas Ativos)
        if (action === 'get_active_list') {
            console.log("Mock: Executando get_active_list (apenas ativos)...");
            
            // Retornamos apenas os usuários ativos conforme solicitado
            const mappedData = mockUsers
                .filter(u => u.is_active)
                .map(u => ({
                    email: u.email,
                    is_active: u.is_active,
                    expiry_date: futureDate,
                    plan_name: "Elite VIP (Mock)",
                    gateway_reference: `MOCK_${u.name.toUpperCase()}`
                }));

            return new Response(
                JSON.stringify({
                    success: true,
                    message: "Dados MOCK recuperados com sucesso para teste de conexão",
                    data: mappedData,
                    last_sync: now.toISOString()
                }),
                { headers: { ...corsHeaders, 'Content-Type': 'application/json' } }
            );
        }

        // 2. AÇÃO: Status Individual (Mock)
        if (action === 'get_user_status' && email) {
            console.log(`Mock: Buscando status para ${email}...`);
            const user = mockUsers.find(u => u.email === email);

            if (!user) {
                return new Response(
                    JSON.stringify({
                        error: "Usuário não encontrado no mock de teste."
                    }),
                    { status: 404, headers: corsHeaders }
                );
            }

            return new Response(
                JSON.stringify({
                    success: true,
                    message: "Status recuperado via MOCK",
                    data: {
                        email: user.email,
                        is_active: user.is_active,
                        expiry_date: user.is_active ? futureDate : pastDate,
                        plan_name: user.is_active ? "Elite VIP (Mock)" : "N/A",
                        gateway_reference: `MOCK_${user.name.toUpperCase()}`
                    },
                    last_sync: now.toISOString()
                }),
                { headers: { ...corsHeaders, 'Content-Type': 'application/json' } }
            );
        }

        return new Response(JSON.stringify({ error: "Ação inválida no MOCK" }), { status: 400 });

    } catch (error) {
        return new Response(JSON.stringify({ error: error.message }), {
            headers: { ...corsHeaders, 'Content-Type': 'application/json' },
            status: 500,
        });
    }
})
