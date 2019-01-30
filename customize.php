<?php

	abstract class Helper_Customize{

		public $customize;
		
		public $panels = [];
		public $sections = [];
		public $controls = [];
		public $settings = [];

		public $styles = ['controls' => [], 'preview' => [] ];
		public $scripts = ['controls' => [], 'preview' => [] ];

		final function __construct(){
			add_action('customize_register', [$this, 'register']);
			add_action('customize_preview_init', [$this, 'load_preview_scripts']);
			add_action('customize_controls_enqueue_scripts', [$this, 'load_control_scripts']);
			add_action('customize_save_after', [$this, 'initialize_mod_defaults']);
		}

		abstract function customization_page();

		public function add_panel($id, $args = []){
			$this->panels = is_string($id)? 
				array_merge(['id' => $id], $args):
				array_merge($this->panels, $id); 
		}

		public function add_section($id, $args = []){
			$this->sections = is_string($id)? 
				array_merge(['id' => $id], $args):
				array_merge($this->sections, $id); 
		}

		public function add_setting($id, $args = []){
			$this->settings = is_string($id)? 
				array_merge(['id' => $id], $args):
				array_merge($this->settings, $id);
		}

		public function add_control($id, $args = []){

			$controls = ($arr = is_array($id))? $id: array_merge(['id' => $id], $args);
			
			if($arr){
				foreach($controls as $key => $control){
	
					if(!empty($control['settings']) && is_array($control['settings'][0])){
						
						if(!isset($control['settings'][0]['id'])):
							$control['settings'][0]['id'] = $control['id'];
						endif;

						$this->add_setting($control['settings']);
						
						$settings = $control['settings'];
						$controls[$key]['settings'] = [];
						
						foreach($settings as $setting){
							$controls[$key]['settings'][] = $setting['id'];
						}
						
					}

				}
			}
			
			$this->controls = array_merge($this->controls, $controls);
		}

		public function register($customize){
			
			$this->customize = $customize;
			$this->customization_page();

			foreach($this->panels as $panel){

				$id = $panel['id'];
				$title = $panel['title'];
				$description = $panel['title'];

				unset($panel['id'], $panel['title']);
				$this->customize->add_panel($id, array_merge([
					'title' => _x($title, 'Customize panel title'),
					'description' => _x($description, 'Customize panel Description')
				], $panel));

			}

			foreach($this->sections as $sec){
				// print_r($sec);
				$id = $sec['id'];
				$title = $sec['title'];

				unset($sec['id'], $sec['title']);
				$this->customize->add_section($id, array_merge(['title' => _x($title, 'Customize section title')], $sec));

			}

			foreach($this->settings as $setting){

				$id = $setting['id'];

				unset($setting['id']);
				$this->customize->add_setting($id, $setting);
			}

			foreach($this->controls as $control){

				$id = $control['id'];
				$type = $control['type'];
				$label = $control['label'] ?? $control['title'];
				$className = 'WP_Customize_'.ucfirst($control['type']).'_Control';
				$choices = $control['choices'] ?? null;

				unset($control['label'], $control['title'], $control['id']);
				
				switch($control['type']){
					case 'color_input':
					case 'media':
					case 'upload':
						$this->customize->add_control(new $className($this->customize, $id, array_merge([
							'label' => $label
						], $control)));
						break;
					case 'select':
						$this->customize->add_control($id, array_merge([
							'label' => _x($label, 'Customize control label'), 
							'type' => $type,
							'choices' => $choices
						], $control));
					default:
						$this->customize->add_control($id, array_merge(['label' => _x($label, 'Customize control label'), 'type' => $type], $control));
				}
				// $this->customize->add_control($id, array_merge(['label' => _x($label, 'Customize control label')], $control));


			}

		}


		private function enqueue_style( $handle, $src = '', $deps = [], $ver = false, $media = 'all', $in_footer = 'controls' ){
			
			if(in_array($in_footer, ['controls', 'preview'])){

				$this->styles[$in_footer][$handle] = (object) [
					'src' => $src,
					'deps' => $deps,
					'ver' => $ver,
					'enqueue' => true
				];

			}      

		}

		private function enqueue_script( $handle, $src = '', $deps = [], $ver = false, $in_footer = 'controls' ){
			
			if(in_array($in_footer, ['controls', 'preview'])){

				$this->scripts[$in_footer][$handle] = (object) [
					'src' => $src,
					'deps' => $deps,
					'ver' => $ver,
					'enqueue' => true
				];

			}            
		}
		
		public function load_preview_scripts($id){

			foreach($this->styles['preview'] as $key => $style){

				wp_enqueue_style($key, $style['src'], $style['deps'], $style['ver']);

			}


			foreach($this->scripts['preview'] as $key => $script){

				wp_enqueue_script($key, $script['src'], $script['deps'], $script['ver'], true);

			}

		}
		
		public function load_control_scripts($id){

			wp_enqueue_style('wp-color-picker'); 
			wp_enqueue_script('wp-color-picker');

			foreach($this->styles['controls'] as $key => $style){

				wp_enqueue_style($key, $style['src'], $style['deps'], $style['ver']);

			}

			foreach($this->scripts['controls'] as $key => $script){

				wp_enqueue_script($key, $script['src'], $script['deps'], $script['ver'], true);

			}

        }

		public function initialize_mod_defaults() {
			$mods = get_theme_mods();
			$setting_set = [];

			foreach ($this->settings as $setting) {
				
				
				if(count($xp = explode('[', $setting['id'])) > 1 ){
					$setting_set[$xp[0]][str_replace(']','',$xp[1])] = $setting['default'];
					continue;
				}
				
				if(isset($mods[$setting['id']])) continue;

				set_theme_mod($setting['id'], $setting['default']);
			
			}

			foreach($setting_set as $id => $set){

				if(isset($mods[$id])) continue;

				set_theme_mod($id, $set);

			}

		}


	}

?>