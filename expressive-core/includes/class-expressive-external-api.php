<?php

class Expressive_External_API {

    public static $last_error = '';

    /**
     * Get the subscription status from the external API (POST + JSON).
     */
    public static function check_user_status( $user_id ) {
        self::$last_error = '';
        $api_url   = get_option( 'lms_external_api_url_status' ) ?: get_option( 'lms_external_api_url' );
        $api_token = get_option( 'lms_external_api_token' );

        if ( ! $api_url ) {
            self::$last_error = 'URL da API de Status não configurada.';
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
            self::update_local_access( $user->user_email, $data['data'] );
            return $data['data']['is_active'] ? 'active' : 'inactive';
        }

        self::$last_error = 'Estrutura JSON não reconhecida.';
        return null;
    }

    /**
     * Fetch all active subscriptions from the API (bulk POST + JSON).
     */
    public static function sync_all_users_status() {
        self::$last_error = '';
        $api_url   = get_option( 'lms_external_api_url_sync' ) ?: get_option( 'lms_external_api_url' ); // Fallback
        $api_token = get_option( 'lms_external_api_token' );

        if ( ! $api_url ) {
            self::$last_error = 'URL da API de Sincronização ausente.';
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
                    if ( $manual !== 'blocked' ) {
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

    /**
     * Request a subscription cancellation via External API.
     */
    public static function cancel_subscription( $user_id, $reason = '' ) {
        self::$last_error = '';
        $api_url   = get_option( 'lms_external_api_url_cancel' ) ?: get_option( 'lms_external_api_url' );
        $api_token = get_option( 'lms_external_api_token' );

        if ( ! $api_url ) {
            self::$last_error = 'URL da API de Cancelamento não configurada.';
            return false;
        }

        $user = get_userdata( $user_id );
        if ( ! $user ) return false;

        $payload = array(
            'action' => 'cancel_subscription',
            'email'  => $user->user_email,
            'reason' => $reason
        );

        Expressive_Logger::info( 'API', "Iniciando solicitação de cancelamento", array( 'user_id' => $user_id, 'email' => $user->user_email, 'reason' => $reason ) );

        $response = wp_remote_post( $api_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ),
            'body'    => wp_json_encode( $payload ),
            'timeout' => 20
        ) );

        if ( is_wp_error( $response ) ) {
            self::$last_error = "Erro de rede no cancelamento: " . $response->get_error_message();
            Expressive_Logger::error( 'API', self::$last_error, array( 'user_id' => $user_id ) );
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( $body, true );

        if ( $code !== 200 ) {
            self::$last_error = $data['message'] ?? $data['error'] ?? "Erro HTTP $code no cancelamento.";
            Expressive_Logger::error( 'API', "Falha no cancelamento da assinatura", array( 'user_id' => $user_id, 'body' => $body ) );
            return false;
        }

        if ( isset( $data['success'] ) && $data['success'] ) {
            Expressive_Logger::info( 'API', "Assinatura cancelada com sucesso via API", array( 'user_id' => $user_id ) );
            
            // Sync result to local access table
            $api_data = $data['data'] ?? array();
            $api_data['cancel_reason'] = $reason;
            self::update_local_access( $user->user_email, $api_data );

            self::record_access_event( $user->user_email, 'cancel_subscription', 'active', $api_data['status'] ?? 'inactive', $reason, wp_json_encode($payload), $body );

            return true;
        }

        self::$last_error = $data['message'] ?? 'A API não confirmou o sucesso do cancelamento.';
        return false;
    }

    /**
     * Update the local access table with data from the gateway.
     */
    public static function update_local_access( $email, $data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'elite_subscription_access';
        $email = strtolower( trim( $email ) );

        $user = get_user_by( 'email', $email );
        $user_id = $user ? $user->ID : null;

        $existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE email = %s", $email ) );

        $status = $data['status'] ?? ( (isset($data['is_active']) && $data['is_active']) ? 'active' : 'inactive' );
        
        $fields = array(
            'user_id'               => $user_id,
            'email'                 => $email,
            'asaas_customer_id'     => $data['customer_id'] ?? $data['gateway_customer_id'] ?? null,
            'asaas_subscription_id' => $data['subscription_id'] ?? $data['gateway_reference'] ?? null,
            'status'                => $status,
            'plan_name'             => $data['plan_name'] ?? null,
            'gateway_status'        => $data['gateway_status'] ?? ( ($status === 'active') ? 'ACTIVE' : 'INACTIVE' ),
            'access_expires_at'     => $data['access_expires_at'] ?? $data['expiry_date'] ?? null,
            'grace_ends_at'         => $data['grace_ends_at'] ?? null,
            'cancel_requested_at'   => ($status === 'grace_period' || $status === 'cancelled') ? current_time('mysql') : null,
            'cancel_reason'         => $data['cancel_reason'] ?? null,
            'last_sync_at'          => current_time('mysql'),
            'raw_response'          => wp_json_encode($data)
        );

        // Map legacy/simple API fields if missing
        if ( empty($fields['access_expires_at']) && !empty($data['expiry_date']) ) {
            $fields['access_expires_at'] = $data['expiry_date'];
        }

        if ( $existing ) {
            $wpdb->update( $table, $fields, array( 'email' => $email ) );
        } else {
            $fields['created_at'] = current_time('mysql');
            $wpdb->insert( $table, $fields );
        }

        // Sync to legacy user meta for compatibility
        if ( $user_id ) {
            update_user_meta( $user_id, '_lms_elite_api_status', ($status === 'active' || $status === 'grace_period') ? 'active' : 'inactive' );
            update_user_meta( $user_id, '_lms_elite_api_last_check', time() );
            if ( !empty($fields['access_expires_at']) ) {
                update_user_meta( $user_id, '_lms_elite_api_expiry', $fields['access_expires_at'] );
            }
        }
    }

    /**
     * Record an audit event.
     */
    public static function record_access_event( $email, $action, $before, $after, $reason, $request, $response ) {
        global $wpdb;
        $table = $wpdb->prefix . 'elite_subscription_events';
        
        $wpdb->insert( $table, array(
            'email'            => $email,
            'action'           => $action,
            'status_before'    => $before,
            'status_after'     => $after,
            'reason'           => $reason,
            'request_payload'  => $request,
            'response_payload' => $response,
            'created_at'       => current_time('mysql')
        ) );
    }
}
