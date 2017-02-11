<?php
/*
Plugin Name: CodewarsWP
Description: Plugin to get codewars profile
*/
/* Start Adding Functions Below this Line */



class cw_widget extends WP_Widget {


	function __construct() {
		parent::__construct(
			'cw_widget', 
			__('CodewarsWP', 'cw_widget_domain'), 
			array( 'description' => __( 'Simple widget to show you your CodeWars Profile', 'cw_widget_domain' ), ) 
		);
		wp_register_style( 'namespace',  plugins_url('css/style.css',__FILE__ ) );
	}
	
	// Get user profile from Codewars REST API
	function codewars_get_profile($user) {

		$response = wp_remote_get( 'https://www.codewars.com/api/v1/users/'.$user );
		if( is_array($response) ) {
			$body = $response['body']; 
		}
	
		return json_decode($body, true);

	}

	// Display the profile in front
	function codewars_display_profile($user_data) {
		
		$value = $user_data['ranks']['overall'];
		?>
		<div class = "cw-profile">
			<div class = "cw-basic-userdata">
				<h2 class = "cw-header"><a href="https://www.codewars.com/users/<?php echo $user_data['username'];?>" target="_blank"><?php echo $user_data['username'];?></a></h2>
				<p>
					<span class = "cw-small-header"><img src = "https://www.codewars.com/users/<?php echo $user_data['username'];?>/badges/micro" title = "Profile badge small" alt = "small profile badge"> <br>
					<span class = "cw-small-header"><?php echo __( 'Klan', 'cw_widget_domain' );?></span>: <?php echo $user_data['clan'];?> <br>
					<span class = "cw-small-header"><?php echo __( 'Pozycja w rankingu', 'cw_widget_domain' );?></span>: <?php echo $user_data['leaderboardPosition'];?>
				</p>
			</div>
			<div class = "cw-rank">
				<h3 class = "cw-header"><?php echo __( 'Rangi', 'cw_widget_domain' );?>:</h3>

			<?php foreach($user_data['ranks']['languages'] as $key => $value) { ?>
				<span class = "cw-language"><?php echo $key?></span> - <span style = "color: <?php echo $value['color'];?>;"><?php echo $value['name'];?> (<?php echo $value['score'];?> pts)</span><br>
			<?php } ?>
			</div>

			<div class = "cw-challenges">
				<h3 class = "cw-header"><?php echo __( 'Wyzwania', 'cw_widget_domain' );?>:</h3>
				<span class = "cw-small-header"><?php echo __( 'Stworzone', 'cw_widget_domain' );?></span>: <?php echo $user_data['codeChallenges']['totalAuthored'];?><br>
				<span class = "cw-small-header"><?php echo __( 'Ukończone', 'cw_widget_domain' );?></span>: <?php echo $user_data['codeChallenges']['totalCompleted'];?><br>
			</div>
		</div>
		<?php
	}

	// Display the widget in front
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		$user = $instance['user'];

		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];

		if ( ! empty( $user ) ) {
			$user_data = $this->codewars_get_profile($user);
			if( !empty( $user_data['reason']))
			{
				echo 'Error: '.$user_data['reason'];
				return;

			}
			wp_enqueue_style('namespace');
			$this->codewars_display_profile($user_data);
		}
		echo $args['after_widget'];
	}
		
	// Backend form to set title and user
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'cw_widget_domain' );
		}

		if ( isset( $instance[ 'user' ] ) ) {
			$user = $instance[ 'user' ];
		}
		else {
			$user = __( '', 'cw_widget_domain' );
		}

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'user' ); ?>"><?php _e( 'User:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'user' ); ?>" name="<?php echo $this->get_field_name( 'user' ); ?>" type="text" value="<?php echo esc_attr( $user ); ?>" />
		</p>
		<?php 
	}
	
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['user'] = ( ! empty( $new_instance['user'] ) ) ? strip_tags( $new_instance['user'] ) : '';
		return $instance;
	}
} 

function cw_load_widget() {
		register_widget( 'cw_widget' );
}
add_action( 'widgets_init', 'cw_load_widget' );

/* Stop Adding Functions Below this Line */
?>
