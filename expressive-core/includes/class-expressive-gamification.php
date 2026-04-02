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
		
		// 1. Process Rule of 10 (Bonus)
		if ( $referral_count > 0 && $referral_count % 10 === 0 ) {
			$this->trigger_financial_bonus( $educator_id, $referral_count );
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
				'amount'  => 100.00, // Default bonus for every 10 sales
				'source'  => "Bônus Regra dos 10 ($count indicações)",
			),
			array( '%d', '%f', '%s' )
		);

		// Optionally send notification or log activity
	}

	private function update_educator_rank( $educator_id, $count ) {
		// Level 1: Bronze (0-9)
		// Level 2: Prata (10-19)
		// Level 3: Ouro (20-29)
		// Level 4: Rubi (30-39)
		// Level 5: Diamante (40+)
		
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
			
			// Action for level up
			do_action( 'lms_educator_leveled_up', $educator_id, $level, $ranks[$level] );
		}
	}

}
