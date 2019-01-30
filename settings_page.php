<?php 

	class Helper_Settings_Page{

		public $id;
		public $title;
		public $menu_title;
		public $capabilities;
		public $option_group;
		public $location = 'menu';
		public $icon = 'dashicons-forms';
		public $position = null;

		public $sections = [];	

		function init(){
			foreach($this->sections as $section_id => $section){

				add_settings_section(
					$section_id,      
					$section['title'],         
					$section['callback'],  
					$this->id               
				);

				foreach($section['fields'] as $field_id => $field){
					add_settings_field(
						$field_id,
						$field['title'],
						$field['callback'],
						$this->id,
						$section_id, 
						$field['args']
					);

					register_setting( $this->option_group, $field_id);
				}

			}
		}

		function add_settings_field($id, $title, $callback, $section = 'default', $args = []){
			$this->sections[$section]['fields'][$id] = ['title' => $title, 'callback' => $callback, 'args' => array_merge(['field_id' => $id],$args)]; 
		}

		function add_settings_section($id, $title, $callback){
			$this->sections[$id] = ['title' => $title, 'callback' => $callback];
		}

		function generate_page(){ ?>
			
			<h2><?php echo $this->title ;?></h2>
			<form method="post" action="options.php">
				<?php

					do_settings_sections($this->id);
					settings_fields($this->option_group);

					submit_button();
				
				?>
			</form>

		<?php }

		function add_options_page(){

			('add_'.($this->location).'_page')(
				$this->title,
				$this->menu_title,
				$this->capabilities,
				$this->id,
				[$this, 'generate_page'],
				$this->icon,
				$this->position
			);

		}

		function register(){
			add_action('admin_menu', [$this, 'add_options_page']);
			add_action('admin_init', [$this, 'init']);
		}

	}

?>