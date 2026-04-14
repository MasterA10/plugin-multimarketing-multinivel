<?php

class Expressive_External_API {

    /**
     * Get the subscription status from the external API.
     */
    public static function check_user_status( $user_id ) {
        $api_url   = get_option( 'lms_external_api_url' );
        $api_token = get_option( 'lms_external_api_token' );

        if ( ! $api_url ) return null;

        $user = get_userdata( $user_id );
        if ( ! $user ) return null;

        $endpoint = add_query_arg( array(
            'action' => 'get_user_status',
            'email'  => $user->user_email
        ), $api_url );

        Expressive_Logger::info( 'API', "Requisição individual: check_user_status", array( 'user_id' => $user_id, 'email' => $user->user_email, 'endpoint' => $endpoint ) );

        $response = wp_remote_get( $endpoint, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_token,
                'Accept'        => 'application/json',
            ),
            'timeout' => 15
        ) );

        if ( is_wp_error( $response ) ) {
            Expressive_Logger::error( 'API', "Erro de conexão: " . $response->get_error_message(), array( 'user_id' => $user_id, 'endpoint' => $endpoint ) );
            return null;
        }

        $body = wp_remote_retrieve_body( $response );
        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( $body, true );

        Expressive_Logger::info( 'API', "Resposta recebida", array( 'user_id' => $user_id, 'http_code' => $code, 'body_length' => strlen( $body ) ) );

        // Store log for the dashboard
        update_option( 'lms_api_last_log', array(
            'timestamp' => current_time( 'mysql' ),
            'endpoint'  => $endpoint,
            'code'      => $code,
            'response'  => $body
        ) );

        if ( isset( $data['data']['is_active'] ) ) {
            $status = (bool) $data['data']['is_active'] ? 'active' : 'inactive';
            $expiry = isset( $data['data']['expiry_date'] ) ? sanitize_text_field( $data['data']['expiry_date'] ) : '';

            update_user_meta( $user_id, '_lms_elite_api_status', $status );
            update_user_meta( $user_id, '_lms_elite_api_last_check', time() );
            if ( $expiry ) update_user_meta( $user_id, '_lms_elite_api_expiry', $expiry );

            Expressive_Logger::info( 'API', "Status atualizado via API", array( 'user_id' => $user_id, 'status' => $status, 'expiry' => $expiry ) );
            return $status;
        }

        Expressive_Logger::warning( 'API', "Resposta sem campo 'is_active' — retornando null", array( 'user_id' => $user_id, 'response_body' => substr( $body, 0, 500 ) ) );
        return null;
    }

    /**
     * Fetch all active subscriptions from the API (bulk) and sync local DB.
     */
    public static function sync_all_users_status() {
        $api_url   = get_option( 'lms_external_api_url' );
        $api_token = get_option( 'lms_external_api_token' );

        if ( ! $api_url ) {
            Expressive_Logger::warning( 'API', "Sincronização abortada: API URL não configurada" );
            return false;
        }

        $endpoint = add_query_arg( array( 'action' => 'get_active_list' ), $api_url );

        Expressive_Logger::info( 'API', "Sincronização em massa iniciada", array( 'endpoint' => $endpoint ) );

        $response = wp_remote_get( $endpoint, array(
            'headers' => array( 'Authorization' => 'Bearer ' . $api_token ),
            'timeout' => 30
        ) );

        if ( is_wp_error( $response ) ) {
            Expressive_Logger::error( 'API', "Erro na sincronização em massa: " . $response->get_error_message() );
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( $body, true );

        // Store log for the dashboard
        update_option( 'lms_api_last_log', array(
            'timestamp' => current_time( 'mysql' ),
            'endpoint'  => $endpoint,
            'code'      => $code,
            'response'  => $body
        ) );

        if ( isset( $data['data'] ) && is_array( $data['data'] ) ) {
            $active_emails = $data['data'];

            $users = get_users();
            $active_count = 0;
            $inactive_count = 0;

            foreach ( $users as $user ) {
                $is_active = in_array( $user->user_email, $active_emails );
                update_user_meta( $user->ID, '_lms_elite_api_status', $is_active ? 'active' : 'inactive' );
                update_user_meta( $user->ID, '_lms_elite_api_last_check', time() );
                $is_active ? $active_count++ : $inactive_count++;
            }

            Expressive_Logger::info( 'API', "Sincronização em massa concluída", array(
                'total_users'    => count( $users ),
                'active_count'   => $active_count,
                'inactive_count' => $inactive_count,
                'api_list_size'  => count( $active_emails )
            ) );
            return true;
        }

        Expressive_Logger::error( 'API', "Sincronização falhou: Resposta inválida", array( 'http_code' => $code, 'body_preview' => substr( $body, 0, 500 ) ) );
        return false;
    }
}
