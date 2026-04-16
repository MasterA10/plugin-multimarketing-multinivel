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
	 * Register system hooks for automatic logging.
	 */
	public static function register_hooks() {
		add_action( 'wp_login', array( __CLASS__, 'log_login' ), 10, 2 );
		add_action( 'wp_logout', array( __CLASS__, 'log_logout' ) );
		add_action( 'user_register', array( __CLASS__, 'log_registration' ) );
		add_action( 'profile_update', array( __CLASS__, 'log_profile_update' ), 10, 2 );
	}

	public static function log_login( $user_login, $user ) {
		self::info( 'AUTH', "Usuário autenticado no sistema", array( 'user_id' => $user->ID, 'login' => $user_login ) );
	}

	public static function log_logout() {
		$user_id = get_current_user_id();
		self::info( 'AUTH', "Usuário encerrou a sessão", array( 'user_id' => $user_id ) );
	}

	public static function log_registration( $user_id ) {
		self::info( 'AUTH', "Novo usuário registrado na plataforma", array( 'user_id' => $user_id ) );
	}

	public static function log_profile_update( $user_id, $old_user_data ) {
		self::info( 'AUTH', "Perfil de usuário atualizado", array( 'user_id' => $user_id ) );
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
