<?php 

    trait HelperWidgetFunctions{
        
        private $registered_styles = [];
        private $registered_scripts = [];
        
        private function register_widget($name, $widget_options = []){

            if(!empty($this->registered_styles)) $widget_options['styles'] = $this->registered_styles;
            if(!empty($this->registered_scripts)) $widget_options['scripts'] = $this->registered_scripts;

            // $id = $this->helper_widgetId;

            parent::__construct(
                'f', // Base ID
                _x($name, 'widget Name' ), // Name
                $widget_options
            );

        }

        public function helper_setId($id){
            $base_id = 'f';

            if($this->id_base == $base_id) $this->id_base = $id;
            if($this->option_name ==  'widget_'.$base_id) $this->option_name = $id;
            if($this->control_options['id_base'] ==  $base_id) $this->control_options['id_base'] = $id;
            if($this->widget_options['classname'] == 'widget_'.$base_id)$this->widget_options['classname'] = $id;

        }

        public function helper_getId(){
            return $this->id_base;
        }

        public function load_styles($id){
            
            if(is_active_widget( false, false, $id, true )){
                
                foreach($this->registered_styles as $key => $style){
                    wp_enqueue_style($key, $style['src'], $style['deps'], $style['ver'], $style['media']);
                }

            }

        }

        public function load_scripts($id){
            if(is_active_widget( false, false, $id, true )){
                foreach($this->registered_scripts as $key => $script){
                    
                    if($script['localize']){

                        wp_register_script($key, $script['src'], $script['deps'], $script['ver'], $script['in_footer']);
                        // wp_localize_script($key, 'wpReactPosts', array( 'root' => esc_url_raw( rest_url() ), 'nonce' => wp_create_nonce( 'wp_rest' ) ) );
                        wp_enqueue_script($key);

                        continue;
                    }

                    wp_enqueue_script($key, $script['src'], $script['deps'], $script['ver'], $script['in_footer']);
                }
            } 
        }

        private function register_style( $handle, $src, $deps = [], $ver = false, $media = 'all' ){
            
            // wp_register_style($handle, $src, $deps, $ver, $media);
            
            $this->registered_styles[$handle] = (object) [
                'src' => $src,
                'deps' => $deps,
                'ver' => $ver,
                'media' => $media,
                'enqueue' => false
            ];

        }

        private function register_script( $handle, $src, $deps = [], $ver = false, $in_footer = true ){

            $this->registered_scripts[$handle] = (object) [
                'src' => $src,
                'deps' => $deps,
                'ver' => $ver,
                'in_footer' => $in_footer,
                'enqueue' => false
            ];

        }

        private function enqueue_style( $handle, $src = '', $deps = [], $ver = false, $media = 'all' ){
            
            // wp_enqueue_style($handle, $src, $deps, $ver, $media);
            
            if(empty($src)){
                if(!empty($this->registered_styles[$handle])) $this->registered_styles[$handle]['enqueue'] = true;
            }else{
                $this->registered_styles[$handle] = (object) [
                    'src' => $src,
                    'deps' => $deps,
                    'ver' => $ver,
                    'media' => $media,
                    'enqueue' => true
                ];
            }
                
        }

        private function enqueue_script( $handle, $src = '', $deps = [], $ver = false, $in_footer = true ){
            
            if(empty($src)){
                if(!empty($this->registered_scripts[$handle])) $this->registered_scripts[$handle]['enqueue'] = true;
            }else{
                $this->registered_scripts[$handle] = (object) [
                    'src' => $src,
                    'deps' => $deps,
                    'ver' => $ver,
                    'in_footer' => $in_footer,
                    'enqueue' => true
                ];
            }
        }

        private function helper_getOptions(){
            return $this->helper_options;
        }

        private function helper_createProperty($obj, $name, $value){
            $obj->{$name} = $value;
        }

    }

    class HelperWidget{
        public static function createWidget($id, $widget){

            add_action( 'wp_head', [$widget, 'load_styles', $id]);
            add_action( 'wp_enqueue_scripts', [$widget, 'load_scripts', $id]);

            $widget->helper_setId($id);            
            add_action('widgets_init', function() use ($widget) {register_widget($widget);});
        }
    }

?>