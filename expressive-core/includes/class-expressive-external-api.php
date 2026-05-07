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

        $users_data = array();
        $raw_data = null;
        if ( isset( $data['data'] ) && is_array( $data['data'] ) ) {
            $raw_data = $data['data'];
        } elseif ( is_array( $data ) && ! empty( $data ) && isset( $data[0] ) ) {
            $raw_data = $data;
        }

        if ( is_array( $raw_data ) ) {
            foreach ( $raw_data as $item ) {
                if ( is_array( $item ) && isset( $item['email'] ) ) {
                    $email = strtolower( trim( $item['email'] ) );
                    if ( ! isset( $item['status'] ) ) {
                        $item['status'] = ( isset( $item['is_active'] ) && ! $item['is_active'] ) ? 'inactive' : 'active';
                    }
                    $users_data[$email] = $item;
                } elseif ( is_string( $item ) ) {
                    $email = strtolower( trim( $item ) );
                    $users_data[$email] = array(
                        'email'     => $email,
                        'is_active' => true,
                        'status'    => 'active',
                    );
                }
            }

            $users = get_users();
            foreach ( $users as $user ) {
                $email = strtolower( trim( $user->user_email ) );
                $u_data = isset( $users_data[$email] ) ? $users_data[$email] : array(
                    'email'     => $email,
                    'is_active' => false,
                    'status'    => 'inactive',
                );

                $u_data['email'] = $email;
                self::update_local_access( $email, $u_data );
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

            // Sync result to local access table, preserving the paid-through date if the API omits it.
            $api_data = isset( $data['data'] ) && is_array( $data['data'] ) ? $data['data'] : array();
            if ( empty( $api_data['status'] ) ) {
                $api_data['status'] = 'cancelled';
            }
            if ( empty( $api_data['access_expires_at'] ) && empty( $api_data['expiry_date'] ) ) {
                $existing_expiry = get_user_meta( $user_id, '_lms_elite_api_expiry', true );
                if ( $existing_expiry ) {
                    $api_data['access_expires_at'] = $existing_expiry;
                }
            }
            $api_data['cancel_reason'] = $reason;
            self::update_local_access( $user->user_email, $api_data );

            self::record_access_event( $user->user_email, 'cancel_subscription', 'active', $api_data['status'] ?? 'cancelled', $reason, wp_json_encode($payload), $body );

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

        $status = $data['status'] ?? ( ( isset( $data['is_active'] ) && $data['is_active'] ) ? 'active' : 'inactive' );
        $status = sanitize_key( strtolower( $status ) );
        $existing_value = function( $field, $default = null ) use ( $existing ) {
            return ( $existing && isset( $existing->$field ) && $existing->$field !== '' ) ? $existing->$field : $default;
        };
        $access_expires_at = $data['access_expires_at'] ?? $data['expiry_date'] ?? $existing_value( 'access_expires_at' );
        $grace_ends_at = $data['grace_ends_at'] ?? $existing_value( 'grace_ends_at' );

        if ( $status === 'grace_period' && empty( $grace_ends_at ) && ! empty( $access_expires_at ) ) {
            $grace_days = max( 0, intval( get_option( 'lms_grace_period_days', 7 ) ) );
            $base_time = strtotime( $access_expires_at );
            if ( $base_time ) {
                $grace_ends_at = date( 'Y-m-d H:i:s', $base_time + ( $grace_days * DAY_IN_SECONDS ) );
            }
        }
        if ( $status === 'active' ) {
            $grace_ends_at = null;
        }

        $fields = array(
            'user_id'               => $user_id,
            'email'                 => $email,
            'asaas_customer_id'     => $data['customer_id'] ?? $data['gateway_customer_id'] ?? $existing_value( 'asaas_customer_id' ),
            'asaas_subscription_id' => $data['subscription_id'] ?? $data['gateway_reference'] ?? $existing_value( 'asaas_subscription_id' ),
            'status'                => $status,
            'plan_name'             => $data['plan_name'] ?? $existing_value( 'plan_name' ),
            'gateway_status'        => $data['gateway_status'] ?? $existing_value( 'gateway_status', ( $status === 'active' ) ? 'ACTIVE' : 'INACTIVE' ),
            'access_expires_at'     => $access_expires_at,
            'grace_ends_at'         => $grace_ends_at,
            'cancel_requested_at'   => in_array( $status, array( 'grace_period', 'cancelled', 'canceled' ), true ) ? $existing_value( 'cancel_requested_at', current_time('mysql') ) : null,
            'cancel_reason'         => $data['cancel_reason'] ?? $existing_value( 'cancel_reason' ),
            'last_sync_at'          => current_time('mysql'),
            'raw_response'          => wp_json_encode($data)
        );

        if ( $existing ) {
            $wpdb->update( $table, $fields, array( 'email' => $email ) );
        } else {
            $fields['created_at'] = current_time('mysql');
            $wpdb->insert( $table, $fields );
        }

        // Sync to legacy user meta for compatibility
        if ( $user_id ) {
            update_user_meta( $user_id, '_lms_elite_api_status', $status );
            update_user_meta( $user_id, '_lms_elite_api_last_check', time() );
            if ( !empty($fields['access_expires_at']) ) {
                update_user_meta( $user_id, '_lms_elite_api_expiry', $fields['access_expires_at'] );
            }
            if ( ! empty( $fields['plan_name'] ) ) {
                update_user_meta( $user_id, '_lms_elite_api_plan', $fields['plan_name'] );
            }
            if ( ! empty( $fields['asaas_subscription_id'] ) ) {
                update_user_meta( $user_id, '_lms_elite_api_gateway_ref', $fields['asaas_subscription_id'] );
            }

            $manual = get_user_meta( $user_id, '_lms_elite_manual_status', true ) ?: 'none';
            if ( $manual === 'none' && class_exists( 'Expressive_Access' ) ) {
                $snapshot = Expressive_Access::get_user_access_snapshot( $user_id );
                update_user_meta( $user_id, '_lms_subscription_status', ! empty( $snapshot['has_access'] ) ? 'active' : 'suspended' );
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
