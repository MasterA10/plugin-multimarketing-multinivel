<?php

/**
 * Expressive Logger — Sistema de Log Centralizado para o Plugin Elite LMS.
 *
 * Grava logs em arquivo dedicado com rotação automática.
 * Formato: [TIMESTAMP] [LEVEL] [CATEGORY] Message | {context_json}
 *
 * @package Expressive_Core
 */
class Expressive_Logger {

	/** @var string Caminho absoluto para o arquivo de log */
	private static $log_file = '';

	/** @var int Tamanho máximo do arquivo antes de rotação (5 MB) */
	private static $max_size = 5242880;

	/** @var bool Se o logger já foi inicializado */
	private static $initialized = false;

	/** @var string Impressão digital do último log para evitar duplicidade */
	private static $last_log_fingerprint = '';

	/**
	 * Inicializa o logger criando o diretório e arquivo se necessário.
	 */
	public static function init() {
		if ( self::$initialized ) return;

		$log_dir = EXPRESSIVE_CORE_PATH . 'logs';

		// Criar diretório se não existir
		if ( ! file_exists( $log_dir ) ) {
			wp_mkdir_p( $log_dir );
		}

		// Proteger diretório com .htaccess
		$htaccess = $log_dir . '/.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			file_put_contents( $htaccess, "Order deny,allow\nDeny from all\n" );
		}

		// Index.php de proteção
		$index = $log_dir . '/index.php';
		if ( ! file_exists( $index ) ) {
			file_put_contents( $index, '<?php // Silence is golden.' );
		}

		self::$log_file = $log_dir . '/elite-debug.log';
		self::$initialized = true;
	}

	/**
	 * Grava uma entrada de log.
	 *
	 * @param string $level    Nível: INFO, WARNING, ERROR, DEBUG
	 * @param string $category Categoria: ACCESS, ENGINE, REFERRAL, API, GAMIFY, AUTH, CERT, CORE, DISCOUNT
	 * @param string $message  Mensagem descritiva
	 * @param array  $context  Dados adicionais (serão serializados como JSON)
	 */
	public static function log( $level, $category, $message, $context = array() ) {
		if ( ! self::$initialized ) {
			self::init();
		}

		// ─── Deduplicação em nível de requisição ───
		$fingerprint = md5( $level . $category . $message . wp_json_encode( $context ) );
		if ( $fingerprint === self::$last_log_fingerprint ) {
			return; // Pula se for identico ao anterior
		}
		self::$last_log_fingerprint = $fingerprint;
		// ───────────────────────────────────────────

		// Rotação automática
		if ( file_exists( self::$log_file ) && filesize( self::$log_file ) > self::$max_size ) {
			$old_file = self::$log_file . '.old';
			if ( file_exists( $old_file ) ) {
				@unlink( $old_file );
			}
			@rename( self::$log_file, $old_file );
		}

		$timestamp = current_time( 'Y-m-d H:i:s' );
		$level     = strtoupper( $level );
		$category  = strtoupper( $category );

		$entry = sprintf( '[%s] [%s] [%s] %s', $timestamp, $level, $category, $message );

		if ( ! empty( $context ) ) {
			$entry .= ' | ' . wp_json_encode( $context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		}

		$entry .= PHP_EOL;

		@file_put_contents( self::$log_file, $entry, FILE_APPEND | LOCK_EX );
	}

	// ─── Atalhos por nível ───────────────────────────────────────────

	public static function info( $category, $message, $context = array() ) {
		self::log( 'INFO', $category, $message, $context );
	}

	public static function warning( $category, $message, $context = array() ) {
		self::log( 'WARNING', $category, $message, $context );
	}

	public static function error( $category, $message, $context = array() ) {
		self::log( 'ERROR', $category, $message, $context );
	}

	public static function debug( $category, $message, $context = array() ) {
		self::log( 'DEBUG', $category, $message, $context );
	}

	// ─── Leitura e Manutenção ────────────────────────────────────────

	/**
	 * Retorna as últimas N linhas do log.
	 *
	 * @param int $lines Número de linhas para retornar (padrão: 500)
	 * @return string
	 */
	public static function get_log_contents( $lines = 500 ) {
		if ( ! self::$initialized ) self::init();
		if ( ! file_exists( self::$log_file ) ) return '';

		$file = new SplFileObject( self::$log_file, 'r' );
		$file->seek( PHP_INT_MAX );
		$total_lines = $file->key();

		$start = max( 0, $total_lines - $lines );
		$output = '';

		$file->seek( $start );
		while ( ! $file->eof() ) {
			$output .= $file->current();
			$file->next();
		}

		return $output;
	}

	/**
	 * Retorna o tamanho do arquivo de log em bytes.
	 *
	 * @return int
	 */
	public static function get_log_size() {
		if ( ! self::$initialized ) self::init();
		if ( ! file_exists( self::$log_file ) ) return 0;
		return filesize( self::$log_file );
	}

	/**
	 * Limpa o arquivo de log.
	 */
	public static function clear_log() {
		/* Desativado por solicitação: Logs não podem ser limpos manualmente */
		/*
		if ( ! self::$initialized ) self::init();
		if ( file_exists( self::$log_file ) ) {
			file_put_contents( self::$log_file, '' );
		}
		self::info( 'CORE', 'Log limpo manualmente por admin', array( 'user_id' => get_current_user_id() ) );
		*/
	}

	/**
	 * Retorna o caminho do arquivo de log.
	 *
	 * @return string
	 */
	public static function get_log_path() {
		if ( ! self::$initialized ) self::init();
		return self::$log_file;
	}
}
