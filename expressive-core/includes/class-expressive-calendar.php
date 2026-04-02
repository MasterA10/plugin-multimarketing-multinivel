<?php

class Expressive_Calendar {

	public function register_calendar_shortcode() {
		add_shortcode( 'lms_calendar', array( $this, 'render_calendar' ) );
		add_shortcode( 'lms_detailed_calendar', array( $this, 'render_detailed_calendar' ) );
		
		// AJAX for calendar navigation
		add_action( 'wp_ajax_lms_get_calendar_month', array( $this, 'ajax_get_calendar_month' ) );
		add_action( 'wp_ajax_nopriv_lms_get_calendar_month', array( $this, 'ajax_get_calendar_month' ) );
	}

	/**
	 * Original simple list view
	 */
	public function render_calendar() {
		$args = array(
			'post_type'      => 'lms_live',
			'posts_per_page' => 10,
			'meta_key'       => '_lms_live_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		);

		$query = new WP_Query( $args );

		$output = '<div class="expressive-calendar-wrapper">';
		$output .= '<h2 class="calendar-title" style="color: #D4AF37; font-family: \'Playfair Display\', serif; italic; margin-bottom: 20px;">Próximas Transmissões ao Vivo</h2>';

		if ( $query->have_posts() ) {
			$output .= '<div class="live-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">';
			while ( $query->have_posts() ) {
				$query->the_post();
				$live_date = get_post_meta( get_the_ID(), '_lms_live_date', true );
				$live_time = get_post_meta( get_the_ID(), '_lms_live_time', true );
				$professor = get_post_meta( get_the_ID(), '_lms_professor_name', true );

				$output .= '<div class="live-card" style="background: #111; border: 1px solid rgba(212, 175, 55, 0.2); border-radius: 15px; padding: 25px; transition: transform 0.3s; cursor: pointer;" onmouseover="this.style.transform=\'translateY(-5px)\'" onmouseout="this.style.transform=\'translateY(0)\'">';
				$output .= '  <div class="live-date" style="color: #D4AF37; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px;">' . esc_html( date('d M Y', strtotime($live_date)) ) . ' ' . esc_html($live_time) . '</div>';
				$output .= '  <h3 style="margin: 0; color: #fff; font-family: \'Playfair Display\', serif; font-size: 1.25rem;">' . get_the_title() . '</h3>';
				if ( $professor ) {
					$output .= '  <div class="professor-tag" style="background: rgba(212, 175, 55, 0.1); display: inline-block; padding: 4px 12px; border-radius: 6px; border: 1px solid rgba(212, 175, 55, 0.2); margin-top: 15px;">';
					$output .= '    <span style="color: #999; font-size: 0.7rem; text-transform: uppercase; font-weight: 700; letter-spacing: 1px; margin-right: 5px;">Prof:</span>';
					$output .= '    <span style="color: #D4AF37; font-weight: 800; font-size: 0.8rem;">' . esc_html( $professor ) . '</span>';
					$output .= '  </div>';
				}
				$output .= '  <a href="' . get_permalink() . '" class="btn-gold" style="display: block; text-align: center; margin-top: 20px; text-decoration: none; padding: 10px; border-radius: 8px; background: linear-gradient(45deg, #D4AF37, #F2D480); color: #000; font-weight: bold; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Acessar Sala</a>';
				$output .= '</div>';
			}
			$output .= '</div>';
		} else {
			$output .= '<p style="color: #666;">Nenhuma live agendada para os próximos dias.</p>';
		}
		$output .= '</div>';

		wp_reset_postdata();

		return $output;
	}

	/**
	 * New Detailed Monthly Calendar Widget
	 */
	public function render_detailed_calendar( $atts ) {
		$atts = shortcode_atts( array(
			'month' => date('m'),
			'year'  => date('Y'),
		), $atts );

		ob_start();
		$this->get_calendar_html( $atts['month'], $atts['year'] );
		return ob_get_clean();
	}

	/**
	 * AJAX Handler for Month Navigation
	 */
	public function ajax_get_calendar_month() {
		$month = isset( $_POST['month'] ) ? intval( $_POST['month'] ) : date('m');
		$year  = isset( $_POST['year'] ) ? intval( $_POST['year'] ) : date('Y');

		$this->get_calendar_html( $month, $year );
		wp_die();
	}

	/**
	 * Helper: Generate Calendar HTML
	 */
	private function get_calendar_html( $month, $year ) {
		$first_day_of_month = mktime(0, 0, 0, $month, 1, $year);
		$number_of_days = date('t', $first_day_of_month);
		$date_info = getdate($first_day_of_month);
		$day_of_week = $date_info['wday']; // 0 (Sun) to 6 (Sat)

		$prev_month = $month - 1;
		$prev_year = $year;
		if ($prev_month == 0) { $prev_month = 12; $prev_year--; }

		$next_month = $month + 1;
		$next_year = $year;
		if ($next_month == 13) { $next_month = 1; $next_year++; }

		$month_names = array(
			1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
			5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
			9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
		);

		// Fetch Events (Lessons & Lives)
		$events = $this->get_monthly_events( $month, $year );

		?>
		<div class="elite-calendar-detailed bg-[#0a0a0a] border border-white/5 rounded-3xl p-6 md:p-10 shadow-2xl" id="lms-detailed-calendar-container">
			<div class="calendar-header flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
				<div class="calendar-title-wrap">
					<h2 class="text-2xl md:text-3xl font-serif italic text-gold-500 mb-1"><?php echo $month_names[$month] . ' ' . $year; ?></h2>
					<p class="text-[9px] uppercase tracking-[0.3em] text-zinc-500 font-bold">Cronograma Estratégico de Elite</p>
				</div>
				<div class="calendar-nav flex gap-2">
					<button onclick="changeLmsMonth(<?php echo $prev_month; ?>, <?php echo $prev_year; ?>)" class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-zinc-400 hover:text-gold-500 transition-all">
						<span class="dashicons dashicons-arrow-left-alt2"></span>
					</button>
					<button onclick="changeLmsMonth(<?php echo date('m'); ?>, <?php echo date('Y'); ?>)" class="px-4 h-10 rounded-xl bg-white/5 border border-white/10 text-[10px] font-bold uppercase tracking-widest text-zinc-400 hover:text-white transition-all">Hoje</button>
					<button onclick="changeLmsMonth(<?php echo $next_month; ?>, <?php echo $next_year; ?>)" class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-zinc-400 hover:text-gold-500 transition-all">
						<span class="dashicons dashicons-arrow-right-alt2"></span>
					</button>
				</div>
			</div>

			<div class="calendar-grid grid grid-cols-7 gap-px bg-white/5 border border-white/5 rounded-2xl overflow-hidden">
				<?php 
				$days = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb');
				foreach($days as $day): ?>
					<div class="bg-zinc-900/50 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-zinc-500 border-b border-white/5"><?php echo $day; ?></div>
				<?php endforeach; ?>

				<?php 
				// Blank spaces for previous month
				for($i = 0; $i < $day_of_week; $i++): ?>
					<div class="bg-black/20 min-h-[100px] md:min-h-[140px]"></div>
				<?php endfor; ?>

				<?php 
				// Days of current month
				for($day = 1; $day <= $number_of_days; $day++): 
					$current_date = sprintf('%04d-%02d-%02d', $year, $month, $day);
					$day_events = isset($events[$current_date]) ? $events[$current_date] : array();
					$is_today = ($current_date === date('Y-m-d'));
				?>
					<div class="bg-zinc-900/40 min-h-[100px] md:min-h-[140px] p-2 md:p-4 border-r border-b border-white/5 group relative hover:bg-zinc-800/60 transition-all <?php echo $is_today ? 'ring-1 ring-gold-500/30' : ''; ?>">
						<span class="text-xs font-bold <?php echo $is_today ? 'text-gold-500' : 'text-zinc-600'; ?> group-hover:text-white transition-colors"><?php echo $day; ?></span>
						
						<div class="mt-2 space-y-2 overflow-y-auto max-h-[80px] md:max-h-[110px] custom-scrollbar">
							<?php foreach($day_events as $event): ?>
								<a href="<?php echo get_permalink($event['id']); ?>" class="block p-2 rounded-lg text-[9px] leading-tight border transition-all hover:scale-[1.02] shadow-sm <?php echo $event['type'] === 'lms_live' ? 'bg-gold-500 text-black border-gold-600' : 'bg-white/5 text-zinc-300 border-white/10 hover:border-gold-500/50'; ?>">
									<div class="flex items-center gap-1 mb-1">
										<span class="w-1.5 h-1.5 rounded-full <?php echo $event['type'] === 'lms_live' ? 'bg-black animate-pulse' : 'bg-gold-500'; ?>"></span>
										<span class="uppercase font-bold tracking-widest opacity-70"><?php echo $event['time'] ?: ($event['type'] === 'lms_live' ? 'Mentoria' : 'Aula'); ?></span>
									</div>
									<div class="line-clamp-2 font-serif italic text-[10px]"><?php echo $event['title']; ?></div>
									<?php if($event['professor']): ?>
										<div class="mt-1 opacity-70 italic truncate">Prof: <?php echo $event['professor']; ?></div>
									<?php endif; ?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endfor; ?>
				
				<?php 
				// Blank spaces for next month
				$total_cells = $day_of_week + $number_of_days;
				$remaining_cells = (7 - ($total_cells % 7)) % 7;
				for($i = 0; $i < $remaining_cells; $i++): ?>
					<div class="bg-black/20 min-h-[100px] md:min-h-[140px]"></div>
				<?php endfor; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Fetch events for a specific month
	 */
	private function get_monthly_events( $month, $year ) {
		$events = array();
		$start_date = sprintf('%04d-%02d-01', $year, $month);
		$end_date = date('Y-m-t', strtotime($start_date));

		// Search for Lives
		$lives = get_posts(array(
			'post_type' => 'lms_live',
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => '_lms_live_date',
					'value' => array($start_date, $end_date),
					'compare' => 'BETWEEN',
					'type' => 'DATE'
				)
			)
		));

		foreach($lives as $live) {
			$date = get_post_meta($live->ID, '_lms_live_date', true);
			$time = get_post_meta($live->ID, '_lms_live_time', true);
			$prof = get_post_meta($live->ID, '_lms_professor_name', true);
			$events[$date][] = array(
				'id' => $live->ID,
				'title' => $live->post_title,
				'type' => 'lms_live',
				'time' => $time,
				'professor' => $prof
			);
		}

		// Search for Lessons (Aulas)
		$lessons = get_posts(array(
			'post_type' => 'lms_lesson',
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => '_lms_lesson_date',
					'value' => array($start_date, $end_date),
					'compare' => 'BETWEEN',
					'type' => 'DATE'
				)
			)
		));

		foreach($lessons as $lesson) {
			$date = get_post_meta($lesson->ID, '_lms_lesson_date', true);
			$prof = get_post_meta($lesson->ID, '_lms_professor_name', true);
			$events[$date][] = array(
				'id' => $lesson->ID,
				'title' => $lesson->post_title,
				'type' => 'lms_lesson',
				'time' => '',
				'professor' => $prof
			);
		}

		return $events;
	}

}
