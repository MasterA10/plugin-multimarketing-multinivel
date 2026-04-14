<?php

class Expressive_External_API {

    public static $last_error = '';

    /**
     * Get the subscription status from the external API (POST + JSON).
     */
    public static function check_user_status( $user_id ) {
        self::$last_error = '';
        $api_url   = get_option( 'lms_external_api_url' );
        $api_token = get_option( 'lms_external_api_token' );

        if ( ! $api_url ) {
            self::$last_error = 'URL da API não configurada.';
            return null;
        }

        $user = get_userdata( $user_id );
        if ( ! $user ) return null;

        $payload = array(
            'action' => 'get_user_status',
            'email'  => $user->user_email
        );

        $response = wp_remote_post( $api_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ),
            'body'    => wp_json_encode( $payload ),
            'timeout' => 15
        ) );

        if ( is_wp_error( $response ) ) {
            self::$last_error = "Erro de rede: " . $response->get_error_message();
            Expressive_Logger::error( 'API', self::$last_error, array( 'user_id' => $user_id ) );
            return null;
        }

        $body = wp_remote_retrieve_body( $response );
        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( $body, true );

        // Store log for the dashboard
        update_option( 'lms_api_last_log', array(
            'timestamp' => current_time( 'mysql' ),
            'endpoint'  => $api_url,
            'code'      => $code,
            'response'  => $body
        ) );

        if ( $code !== 200 ) {
            self::$last_error = "Erro HTTP $code: " . ( $data['message'] ?? $data['error'] ?? 'Resposta inesperada' );
            Expressive_Logger::error( 'API', self::$last_error, array( 'body' => $body ) );
            return null;
        }

        if ( isset( $data['data']['is_active'] ) ) {
            $status = (bool) $data['data']['is_active'] ? 'active' : 'inactive';
            $expiry = isset( $data['data']['expiry_date'] ) ? sanitize_text_field( $data['data']['expiry_date'] ) : '';

            update_user_meta( $user_id, '_lms_elite_api_status', $status );
            update_user_meta( $user_id, '_lms_elite_api_last_check', time() );
            if ( $expiry ) update_user_meta( $user_id, '_lms_elite_api_expiry', $expiry );

            return $status;
        }

        self::$last_error = 'Estrutura JSON não reconhecida.';
        return null;
    }

    /**
     * Fetch all active subscriptions from the API (bulk POST + JSON).
     */
    public static function sync_all_users_status() {
        self::$last_error = '';
        $api_url   = get_option( 'lms_external_api_url' );
        $api_token = get_option( 'lms_external_api_token' );

        if ( ! $api_url ) {
            self::$last_error = 'URL da API ausente.';
            return false;
        }

        $payload = array( 'action' => 'get_active_list' );

        $response = wp_remote_post( $api_url, array(
            'headers' => array( 
                'Authorization' => 'Bearer ' . $api_token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ),
            'body'    => wp_json_encode( $payload ),
            'timeout' => 45
        ) );

        if ( is_wp_error( $response ) ) {
            self::$last_error = "Erro de Conexão: " . $response->get_error_message();
            Expressive_Logger::error( 'API', self::$last_error );
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( $body, true );

        // Store log for the dashboard
        update_option( 'lms_api_last_log', array(
            'timestamp' => current_time( 'mysql' ),
            'endpoint'  => $api_url,
            'code'      => $code,
            'response'  => $body
        ) );

        if ( $code !== 200 ) {
            self::$last_error = "HTTP $code: " . ( $data['message'] ?? $data['error'] ?? 'Falha no servidor de sincronização' );
            Expressive_Logger::error( 'API', "Falha na sincronização", array( 'code' => $code, 'body' => $body ) );
            return false;
        }

        $active_users_data = array();
        $raw_data = null;
        if ( isset( $data['data'] ) && is_array( $data['data'] ) ) {
            $raw_data = $data['data'];
        } elseif ( is_array( $data ) && ! empty( $data ) && isset( $data[0] ) ) {
            $raw_data = $data;
        }

        if ( is_array( $raw_data ) ) {
            foreach ( $raw_data as $item ) {
                if ( is_array( $item ) && isset( $item['email'] ) ) {
                    if ( !isset( $item['is_active'] ) || $item['is_active'] ) {
                        $email = strtolower( trim( $item['email'] ) );
                        $active_users_data[$email] = $item;
                    }
                } elseif ( is_string( $item ) ) {
                    $email = strtolower( trim( $item ) );
                    $active_users_data[$email] = array();
                }
            }

            $users = get_users();
            foreach ( $users as $user ) {
                $email = strtolower( trim( $user->user_email ) );
                $is_active = isset( $active_users_data[$email] );
                
                update_user_meta( $user->ID, '_lms_elite_api_status', $is_active ? 'active' : 'inactive' );
                update_user_meta( $user->ID, '_lms_elite_api_last_check', time() );
                
                if ( $is_active ) {
                    $u_data = $active_users_data[$email];
                    if ( !empty( $u_data['expiry_date'] ) ) {
                        update_user_meta( $user->ID, '_lms_elite_api_expiry', sanitize_text_field( $u_data['expiry_date'] ) );
                    }
                    if ( !empty( $u_data['plan_name'] ) ) {
                        update_user_meta( $user->ID, '_lms_elite_api_plan', sanitize_text_field( $u_data['plan_name'] ) );
                    }
                    if ( !empty( $u_data['gateway_reference'] ) ) {
                        update_user_meta( $user->ID, '_lms_elite_api_gateway_ref', sanitize_text_field( $u_data['gateway_reference'] ) );
                    }
                    
                    // Se a API confirma que é ativo, remove bloqueio automático (mas preserva bloqueio manual do admin)
                    $manual = get_user_meta( $user->ID, '_lms_elite_manual_status', true );
                    if ( $manual === 'blocked' ) {
                        update_user_meta( $user->ID, '_lms_elite_manual_status', 'none' );
                        update_user_meta( $user->ID, '_lms_subscription_status', 'active' );
                    }
                }
            }
            return true;
        }

        self::$last_error = 'O servidor retornou um JSON válido, mas a lista de membros não foi encontrada na estrutura.';
        Expressive_Logger::error( 'API', self::$last_error, array( 'received_json' => $body ) );
        return false;
    }
}
