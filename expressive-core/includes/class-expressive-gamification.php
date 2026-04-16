<?php

class Expressive_Gamification {

	public function register_hooks() {
		add_action( 'lms_new_referral_registered', array( $this, 'evaluate_gamification_progress' ), 10, 2 );
	}

	/**
	 * Evaluate if the educator should level up or receive a bonus.
	 */
	public function evaluate_gamification_progress( $educator_id, $authority_id ) {
		$referral_count = $this->get_educator_referral_count( $educator_id );
		
		Expressive_Logger::info( 'GAMIFY', "Avaliando progresso de gamificação", array( 'educator_id' => $educator_id, 'authority_id' => $authority_id, 'referral_count' => $referral_count ) );

		// 1. Process Rule of 10 (Bonus)
		if ( $referral_count > 0 && $referral_count % 10 === 0 ) {
			$this->trigger_financial_bonus( $educator_id, $referral_count );
		} else {
			Expressive_Logger::debug( 'GAMIFY', "Progressão verificada: Bônus financeiro não aplicável para esta contagem", array( 'educator_id' => $educator_id, 'count' => $referral_count ) );
		}

		// 2. Process Ranking (Level Up)
		$this->update_educator_rank( $educator_id, $referral_count );
	}

	public function get_educator_referral_count( $educator_id ) {
		global $wpdb;
		$table_referrals = $wpdb->prefix . 'lms_referrals';
		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM $table_referrals WHERE educator_id = %d",
			$educator_id
		) );
	}

	private function trigger_financial_bonus( $educator_id, $count ) {
		global $wpdb;
		$table_bonus = $wpdb->prefix . 'lms_bonus_log';

		$wpdb->insert(
			$table_bonus,
			array(
				'user_id' => $educator_id,
				'amount'  => 100.00,
				'source'  => "Bônus Regra dos 10 ($count indicações)",
			),
			array( '%d', '%f', '%s' )
		);

		Expressive_Logger::info( 'GAMIFY', "Bônus financeiro gerado (Regra dos 10)", array( 'educator_id' => $educator_id, 'count' => $count, 'bonus' => 100.00 ) );
	}

	private function update_educator_rank( $educator_id, $count ) {
		$level = floor( $count / 10 ) + 1;
		if ( $level > 5 ) {
			$level = 5;
		}

		$current_level = (int) get_user_meta( $educator_id, '_lms_rank_level', true );
		if ( ! $current_level ) {
			$current_level = 1;
		}

		if ( $level > $current_level ) {
			update_user_meta( $educator_id, '_lms_rank_level', $level );
			
			$ranks = array(
				1 => 'Bronze',
				2 => 'Prata',
				3 => 'Ouro',
				4 => 'Rubi',
				5 => 'Diamante'
			);
			
			update_user_meta( $educator_id, '_lms_rank_name', $ranks[$level] );

			Expressive_Logger::info( 'GAMIFY', "LEVEL UP! Educador promovido", array( 'educator_id' => $educator_id, 'old_level' => $current_level, 'new_level' => $level, 'rank' => $ranks[$level] ) );
			
			do_action( 'lms_educator_leveled_up', $educator_id, $level, $ranks[$level] );
		}
	}

}
