<?php
    //Classe para Extensão da API Rest

    abstract class EndpointObject{ //Define objeto a ser passado na criação do endpoint
    
        public $route;
        public $schema;
        public $methods;
        public $permission;

        abstract function resolver($args);

    }
    

    class Theme_Endpoint extends WP_REST_Controller{

        private static $count = 0;
        private static $endpoints = [];

        private $id;
        private $resolver;
        
        public function __construct(EndpointObject $obj){ //Constructor
            
            $this->id = self::$count; //Seta o id do endpoint
            $this->resolver = [$obj, 'resolver']; //Seta o resolver do endpoint

            self::set_endpoint(
				$obj->root,
                $obj->route, 
                [$this, 'resolve'], 
                $obj->permission ?? null,
                [$this, 'get_permission'], 
                $obj->methods ?? 'GET', 
                $obj->schema ?? null
            );

            self::$count++;

        }
    
        public function resolve($request){ //Função passada ao endpoint para executar o resolver setado
            
            if ( ! $request instanceof WP_REST_Request ) throw new InvalidArgumentException( __METHOD__ . ' expects an instance of WP_REST_Request' );
           
            $func = $this->resolver;

            return new WP_REST_Response($func($request), 200 );
        }

        public function register(){ //Registra endpoint atual
            $endpoint = self::$endpoints[$this->id];

            add_action('rest_api_init', function() use ($endpoint){
                self::register_endpoints([$endpoint]);
            });
        }

        public function get_permission($request){ //Verifica se o usuário possui permissão necessária, caso contrário returna um erro
    
            if (empty(self::$endpoints[$this->id]['permission']) || 
                current_user_can(self::$endpoints[$this->id]['permission'])){
                    return true;
            } 
            return new WP_Error( 'rest_forbidden', esc_html__( 'You cannot view the post resource.' ), ['status' => $this->authorization_status_code()] );

        }

        public static function set_endpoint($root, $route, $resolver, $permission = null, $permission_callback = null, $methods = 'GET', $schema = null){ //Seta novo endpoint
            
            self::$endpoints[] = [
				'root' => $root,
                'route' => $route, 
                'resolver' => $resolver, 
                'permission' => $permission,
                'permission_callback' => $permission_callback,
                'methods' => $methods,
                'schema' => $schema
			];  

        }

        public function authorization_status_code() {
 
            $status = 401;
     
            if ( is_user_logged_in() ) {
                $status = 403;
            }
     
            return $status;
        }

        public static function register_endpoints($endpoints = null){ //Registar endpoints passados para função ou previamente setados    
            $endpoints = $endpoints ?: self::$endpoints;

            foreach($endpoints as $e){

                $args = [ //Define argumentos básicos
                    'methods' => $e['methods'] ?: 'GET',
                    'callback' => $e['resolver'],
                    'permission_callback' => $e['permission_callback']
				];

                if(!empty($e['schema'])){//Adiciona schema aos argumentos caso registrado
                    $args = [$args, 'schema' => $e['schema']];
                }

                register_rest_route($e['root'], $e['route'],  $args); //Registar endpoint
            }
        }
    }

?>