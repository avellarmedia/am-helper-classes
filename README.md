# Documentação

## Settings_page

```PHP
	Public String $id; //Define id da página
	Public String $title; //Define título da página
	Public String $menu_title; //Título no menu
	Public String $capabilities; //Nível de acesso necessário
	Public String $option_group; //Nome do grupo de opções
	Public String $location; //Localização Exs.: menu lateral, submenu em opções, etc
	Public String $field_prefix; // Prefixo a ser adicionado nos campos
```


### Function add_settings_field(String $id, String $title , Callable $callback [, String $section [, Array $args ]])

Adiciona campo a uma seção na página de configuração definida

###	Function add_settings_section(String $id, String $title , Callable $callback)

Adiciona uma seção na página de configuração definida