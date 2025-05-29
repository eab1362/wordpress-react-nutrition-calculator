<?php
/**
 * Plugin Name: Formulario de Mascotas BARF
 * Description: Un bloque de Gutenberg con un formulario para recomendar dietas BARF para perros.
 * Version: 1.0
 * Author: Forever Dog
 */

// Registrar custom post type para perros
function register_dog_post_type() {
    $labels = array(
        'name'               => _x('Perros', 'post type general name'),
        'singular_name'      => _x('Perro', 'post type singular name'),
        'menu_name'          => _x('Perros', 'admin menu'),
        'name_admin_bar'     => _x('Perro', 'add new on admin bar'),
        'add_new'            => _x('Añadir Nuevo', 'perro'),
        'add_new_item'       => __('Añadir Nuevo Perro'),
        'new_item'           => __('Nuevo Perro'),
        'edit_item'          => __('Editar Perro'),
        'view_item'          => __('Ver Perro'),
        'all_items'          => __('Todos los Perros'),
        'search_items'       => __('Buscar Perros'),
        'parent_item_colon'  => __('Perros Padres:'),
        'not_found'          => __('No se encontraron perros.'),
        'not_found_in_trash' => __('No se encontraron perros en la papelera.')
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_position'      => 5, // Posición en el menú (5 es justo después de Posts)
        'menu_icon'          => 'dashicons-pets',
        'query_var'          => true,
        'rewrite'            => array('slug' => 'dogs'),
        'capability_type'    => 'post',
        'capabilities'       => array(
            'edit_post'          => 'edit_post',
            'read_post'          => 'read_post',
            'delete_post'        => 'delete_post',
            'edit_posts'         => 'edit_posts',
            'edit_others_posts'  => 'edit_others_posts',
            'publish_posts'      => 'publish_posts',
            'read_private_posts' => 'read_private_posts'
        ),
        'map_meta_cap'       => true,
        'has_archive'        => true,
        'hierarchical'       => false,
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest'       => true, // Permitir Gutenberg
        'rest_base'          => 'dogs',
    );

    register_post_type('dog', $args);
}
add_action('init', 'register_dog_post_type');

// Registrar la taxonomía de razas de perros
function register_dog_breed_taxonomy() {
    $labels = array(
        'name'              => _x('Razas de Perros', 'taxonomy general name'),
        'singular_name'     => _x('Raza de Perro', 'taxonomy singular name'),
        'search_items'      => __('Buscar Razas'),
        'all_items'         => __('Todas las Razas'),
        'parent_item'       => __('Raza Padre'),
        'parent_item_colon' => __('Raza Padre:'),
        'edit_item'         => __('Editar Raza'),
        'update_item'       => __('Actualizar Raza'),
        'add_new_item'      => __('Añadir Nueva Raza'),
        'new_item_name'     => __('Nombre de Nueva Raza'),
        'menu_name'         => __('Razas de Perros'),
    );

    $args = array(
        'hierarchical'      => true, // Permite categorías padre-hijo
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'breed'),
        'show_in_rest'      => true, // Importante para el editor Gutenberg y la API REST
        'show_in_menu'      => true, // Asegura que aparezca en el menú
        'meta_box_cb'       => 'post_categories_meta_box', // Usa el mismo estilo de meta box que las categorías
    );

    // Asignar la taxonomía al post type personalizado 'dog'
    register_taxonomy('dog_breed', array('dog'), $args);

    // Crear razas predeterminadas cuando se active el plugin por primera vez
    create_default_dog_breeds();
}
add_action('init', 'register_dog_breed_taxonomy');

/**
 * Crea razas de perros por defecto 
 * Intenta usar el archivo breeds.txt primero, si no existe usa breeds.json, 
 * y si ninguno está disponible crea razas predeterminadas básicas
 */
function create_default_dog_breeds() {
    // Primero intentar cargar desde breeds.txt
    $text_file_paths = array(
        WP_CONTENT_DIR . '/plugins/tuco/breeds.txt',
        WP_PLUGIN_DIR . '/tuco/breeds.txt',
        plugin_dir_path(__FILE__) . '../tuco/breeds.txt',
        plugin_dir_path(__FILE__) . 'breeds.txt'
    );
    
    foreach ($text_file_paths as $text_file) {
        if (file_exists($text_file)) {
            $result = import_dog_breeds_from_text_file($text_file);
            if (!is_wp_error($result) && $result['imported'] > 0) {
                return; // Éxito al importar desde texto
            }
        }
    }
    
    // Si no hay archivo de texto, intentar con breeds.json
    $json_file = plugin_dir_path(__FILE__) . 'breeds.json';
    
    if (file_exists($json_file)) {
        $json_content = file_get_contents($json_file);
        $breeds = json_decode($json_content, true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($breeds)) {
            // Importar las razas desde el archivo
            import_dog_breeds_from_json($breeds);
            return;
        }
    }
    
    // Si ninguno de los archivos está disponible, crear razas básicas predeterminadas
    $default_breeds = array(
        'Labrador Retriever',
        'Golden Retriever',
        'Pastor Alemán',
        'Bulldog',
        'Chihuahua',
        'Pug',
        'Beagle',
        'Boxer',
        'Poodle',
        'Rottweiler',
        'Dachshund',
        'Husky Siberiano',
        'Pitbull',
        'Mestizo / Criollo',
        'Otro'
    );
    
    foreach ($default_breeds as $breed_name) {
        if (!term_exists($breed_name, 'dog_breed')) {
            wp_insert_term($breed_name, 'dog_breed');
        }
    }
}

// Activación del plugin
function my_react_form_block_activate() {
    // Registrar la taxonomía al activar
    register_dog_breed_taxonomy();
    
    // Crear razas predeterminadas
    create_default_dog_breeds();
    
    // Registrar el tipo de contenido personalizado "dog"
    register_dog_post_type();
    
    // Limpiar caché de reescritura
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'my_react_form_block_activate');

// Desactivación del plugin
function pet_form_block_deactivate() {
    // Forzar una actualización de las reglas de reescritura
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'pet_form_block_deactivate');

// Función para corregir problemas con las URLs personalizadas
function pet_form_block_after_switch_theme() {
    // Esta función se ejecuta cuando se cambia el tema
    // y ayuda a corregir problemas de permalinks
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'pet_form_block_after_switch_theme');

function pet_form_block_register_block() {
    // Registrar el script del editor (compilado con npm run build)
    wp_register_script(
        'pet-form-block-editor',
        plugins_url( 'build/index.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element', 'wp-editor' ), // dependencias de Gutenberg
        filemtime( plugin_dir_path( __FILE__ ) . 'build/index.js' )
    );

    // Añadir datos de la lista de razas disponibles como variable global de JavaScript
    wp_localize_script(
        'pet-form-block-editor',
        'petFormSettings',
        array(
            'dogBreeds' => get_dog_breeds_list()
        )
    );

    // Registrar el estilo para el front-end
    wp_register_style(
        'pet-form-block-style',
        plugins_url( 'build/style-index.css', __FILE__ ),
        array(),
        filemtime( plugin_dir_path( __FILE__ ) . 'build/style-index.css' )
    );

    // Registrar el bloque
    register_block_type( 'mi-block/formulario-pet', array(
        'editor_script' => 'pet-form-block-editor',  // script del editor
        'style'         => 'pet-form-block-style',   // estilo para el front-end
    ) );
}
add_action( 'init', 'pet_form_block_register_block' );

/**
 * Obtener lista de razas de perros
 */
function get_dog_breeds_list() {
    $breeds = get_terms(array(
        'taxonomy' => 'dog_breed',
        'hide_empty' => false,
    ));
    
    $formatted_breeds = [];
    
    if (!is_wp_error($breeds)) {
        foreach ($breeds as $breed) {
            $breed_data = [
                'id' => $breed->term_id,
                'value' => $breed->slug,
                'label' => $breed->name,
                'breed_id' => get_term_meta($breed->term_id, 'breed_id', true),
                'life_span' => get_term_meta($breed->term_id, 'life_span', true),
                'breed_group' => get_term_meta($breed->term_id, 'breed_group', true),
                'image_url' => get_term_meta($breed->term_id, 'image_url', true),
                'breed_size' => get_term_meta($breed->term_id, 'breed_size', true),
            ];
            
            $formatted_breeds[] = $breed_data;
        }
    }
    
    // Ordenar alfabéticamente por nombre
    usort($formatted_breeds, function($a, $b) {
        return strcasecmp($a['label'], $b['label']);
    });
    
    return $formatted_breeds;
}

// Agregar un nuevo filtro para ordenar las razas alfabéticamente
add_filter('get_terms', 'sort_dog_breeds_alphabetically', 10, 3);

/**
 * Filtro para ordenar las razas alfabéticamente, excepto "Mestizo/Criollo" y "Otro" que irán al principio y final respectivamente
 */
function sort_dog_breeds_alphabetically($terms, $taxonomies, $args) {
    // Verificar si estamos trabajando con la taxonomía dog_breed
    if (!is_array($taxonomies) || !in_array('dog_breed', $taxonomies)) {
        return $terms;
    }
    
    // Guarda los términos especiales
    $mestizo = null;
    $otro = null;
    $regular_terms = array();
    
    foreach ($terms as $term) {
        if ($term->slug === 'mestizo') {
            $mestizo = $term;
        } else if ($term->slug === 'otro') {
            $otro = $term;
        } else {
            $regular_terms[] = $term;
        }
    }
    
    // Ordenar los términos regulares alfabéticamente
    usort($regular_terms, function($a, $b) {
        return strcasecmp($a->name, $b->name);
    });
    
    // Recomponer el array con mestizo al principio y otro al final
    $sorted_terms = array();
    
    if ($mestizo) {
        $sorted_terms[] = $mestizo;
    }
    
    $sorted_terms = array_merge($sorted_terms, $regular_terms);
    
    if ($otro) {
        $sorted_terms[] = $otro;
    }
    
    return $sorted_terms;
}

// Agregar endpoint REST API para procesar formulario
function register_pet_form_endpoints() {
    register_rest_route('pet-form/v1', '/submit', array(
        'methods' => 'POST',
        'callback' => 'handle_pet_form_submission',
        'permission_callback' => function () {
            return true; // Ajustar según necesidades de seguridad
        }
    ));
    
    // Endpoint para obtener las razas de perros
    register_rest_route('pet-form/v1', '/breeds', array(
        'methods' => 'GET',
        'callback' => function() {
            return get_dog_breeds_list();
        },
        'permission_callback' => function () {
            return true;
        }
    ));
    
    // Endpoint para importar razas desde un JSON
    register_rest_route('pet-form/v1', '/import-breeds', array(
        'methods' => 'POST',
        'callback' => 'import_dog_breeds_from_json',
        'permission_callback' => function () {
            return current_user_can('manage_options'); // Solo administradores
        }
    ));
}
add_action('rest_api_init', 'register_pet_form_endpoints');

/**
 * Importa razas de perros desde un archivo JSON simplificado
 * Soporta tanto el formato antiguo (con model, pk, fields) como el nuevo formato simplificado
 * Solo importa los nombres de las razas, ignorando los metadatos adicionales
 */
function import_dog_breeds_from_json($breeds) {
    if (!is_array($breeds)) {
        return new WP_Error('invalid_json', 'El formato JSON no es válido.');
    }
    
    $imported = 0;
    $errors = 0;
    
    foreach ($breeds as $breed) {
        // Extraer solo el nombre de la raza, ignorando metadatos
        $name = '';
        
        // Determinar el formato y extraer nombre
        if (isset($breed['fields']) && isset($breed['fields']['name'])) {
            // Formato antiguo (con fields)
            $name = sanitize_text_field($breed['fields']['name']);
        } else if (isset($breed['name'])) {
            // Formato nuevo (simplificado)
            $name = sanitize_text_field($breed['name']);
        }
        
        if (empty($name)) {
            $errors++;
            continue;
        }
        
        // Verificar si la raza ya existe
        $term_exists = term_exists($name, 'dog_breed');
        
        if (!$term_exists) {
            // Crear una nueva raza (solo nombre, sin metadatos)
            $result = wp_insert_term($name, 'dog_breed');
            
            if (!is_wp_error($result)) {
                $imported++;
            } else {
                $errors++;
            }
        }
    }
    
    return array(
        'imported' => $imported,
        'errors' => $errors
    );
}

// Añadir página de administración para importar razas
function add_breed_import_admin_page() {
    add_submenu_page(
        'edit.php?post_type=dog', // Padre: menú de Perros
        'Importar Razas',         // Título de la página
        'Importar Razas',         // Título del menú
        'manage_options',         // Capacidad requerida
        'import-dog-breeds',      // Slug del menú
        'render_breed_import_page' // Función de callback
    );
}
add_action('admin_menu', 'add_breed_import_admin_page');

// Agregar un hook para incluir el archivo breeds.json en el proceso de activación
add_action('admin_init', 'check_and_create_breeds_file');

/**
 * Verificar y crear el archivo breeds.json si no existe
 */
function check_and_create_breeds_file() {
    // Verificar si existe el archivo breeds.json
    $file_path = plugin_dir_path(__FILE__) . 'breeds.json';
    
    if (!file_exists($file_path)) {
        // Contenido JSON para las razas predeterminadas
        $default_breeds_json = '[
  {
    "model": "dishes.breeds",
    "pk": "c2796beb-b7cd-4b12-924c-f15306f84b69",
    "fields": {
      "created": "2023-05-14T17:41:48.267Z",
      "name": "Afgano",
      "life_span": "10 - 13 years",
      "breed_group": "Hound",
      "image_url": "https://cdn2.thedogapi.com/images/hMyT4CDXR.jpg",
      "breed_size": null
    }
  },
  {
    "model": "dishes.breeds",
    "pk": "cf4a58eb-f836-4235-a6fe-608c4e6127c2",
    "fields": {
      "created": "2023-05-14T17:41:48.162Z",
      "name": "Afinpinscher",
      "life_span": "10 - 12 years",
      "breed_group": "Toy",
      "image_url": "https://cdn2.thedogapi.com/images/BJa4kxc4X.jpg",
      "breed_size": null
    }
  },
  {
    "model": "dishes.breeds",
    "pk": "db7fa1ff-300b-4e61-9c1d-01d9c526ff4c",
    "fields": {
      "created": "2023-05-14T17:41:58.127Z",
      "name": "Aguilucho",
      "life_span": "12 - 15 years",
      "breed_group": "Hound",
      "image_url": "https://cdn2.thedogapi.com/images/B1IcfgqE7.jpg",
      "breed_size": null
    }
  },
  {
    "model": "dishes.breeds",
    "pk": "ff50bec3-ffef-4b57-b059-613f690d60cb",
    "fields": {
      "created": "2023-05-14T17:41:48.480Z",
      "name": "Airedale Terrier",
      "life_span": "10 - 13 years",
      "breed_group": "Terrier",
      "image_url": "https://cdn2.thedogapi.com/images/1-7cgoZSh.jpg",
      "breed_size": null
    }
  },
  {
    "model": "dishes.breeds",
    "pk": "35e8f894-a3ac-4694-9412-3334b0676f3e",
    "fields": {
      "created": "2023-05-14T17:41:48.696Z",
      "name": "Akita",
      "life_span": "10 - 14 years",
      "breed_group": "Working",
      "image_url": "https://cdn2.thedogapi.com/images/BFRYBufpm.jpg",
      "breed_size": null
    }
  },
  {
    "model": "dishes.breeds",
    "pk": "8b9c674c-9c8f-4147-9ab2-2d19ba11eb3a",
    "fields": {
      "created": "2023-08-29T22:45:29.463Z",
      "name": "Criollo (raza unica)",
      "life_span": "14 - 16 years",
      "breed_group": "company",
      "image_url": null,
      "breed_size": null
    }
  },
  {
    "model": "dishes.breeds",
    "pk": "0bf93c68-0e83-42d7-8a7e-3c30534d340f",
    "fields": {
      "created": "2023-05-14T17:41:58.022Z",
      "name": "French Poodle",
      "life_span": "10-15 years",
      "breed_group": "Hound",
      "image_url": null,
      "breed_size": null
    }
  }
]';
        
        // Intentar crear el archivo
        file_put_contents($file_path, $default_breeds_json);
    }
}

// Renderizar la página de importación de razas
function render_breed_import_page() {
    ?>
    <div class="wrap">
        <h1>Importar Razas de Perros</h1>
        
        <div class="card">
            <h2>Importar desde breeds.txt</h2>
            <p>Importa las razas desde el archivo breeds.txt en la carpeta del plugin.</p>
            <button id="import-breeds-text" class="button button-primary">Importar desde breeds.txt</button>
            <div id="import-text-result" style="margin-top: 10px;"></div>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h2>Limpiar Razas</h2>
            <p>Elimina todas las razas existentes. ¡Ten cuidado, esta acción no se puede deshacer!</p>
            <button id="clear-breeds" class="button button-secondary" style="background-color: #dc3545; color: white;">Limpiar todas las razas</button>
            <div id="clear-breeds-result" style="margin-top: 10px;"></div>
        </div>

        <div class="card" style="margin-top: 20px;">
            <h2>Razas Actuales</h2>
            <div id="current-breeds"></div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Función para cargar las razas actuales
        function loadCurrentBreeds() {
            $.get('/wp-json/my-react-form-block/v1/breeds', function(response) {
                let html = '<table class="wp-list-table widefat fixed striped">';
                html += '<thead><tr><th>ID</th><th>Nombre</th><th>Slug</th></tr></thead><tbody>';
                response.forEach(function(breed) {
                    html += `<tr><td>${breed.id}</td><td>${breed.label}</td><td>${breed.value}</td></tr>`;
                });
                html += '</tbody></table>';
                $('#current-breeds').html(html);
            });
        }

        // Cargar razas al inicio
        loadCurrentBreeds();

        // Importar desde breeds.txt
        $('#import-breeds-text').click(function() {
            $(this).prop('disabled', true);
            $('#import-text-result').html('Importando...');
            
            $.post('/wp-json/my-react-form-block/v1/import-breeds-text', function(response) {
                $('#import-text-result').html(`<div class="notice notice-success"><p>${response.message}</p></div>`);
                loadCurrentBreeds();
            }).fail(function(xhr) {
                $('#import-text-result').html(`<div class="notice notice-error"><p>Error: ${xhr.responseJSON.message}</p></div>`);
            }).always(function() {
                $('#import-breeds-text').prop('disabled', false);
            });
        });

        // Limpiar razas
        $('#clear-breeds').click(function() {
            if (!confirm('¿Estás seguro de que quieres eliminar todas las razas? Esta acción no se puede deshacer.')) {
                return;
            }
            
            $(this).prop('disabled', true);
            $('#clear-breeds-result').html('Limpiando...');
            
            $.post('/wp-json/my-react-form-block/v1/clear-breeds', function(response) {
                $('#clear-breeds-result').html(`<div class="notice notice-success"><p>${response.message}</p></div>`);
                loadCurrentBreeds();
            }).fail(function(xhr) {
                $('#clear-breeds-result').html(`<div class="notice notice-error"><p>Error: ${xhr.responseJSON.message}</p></div>`);
            }).always(function() {
                $('#clear-breeds').prop('disabled', false);
            });
        });
    });
    </script>
    <?php
}

// Tabla de factores para el cálculo de la dieta
// (Basada en la tabla proporcionada por el usuario)
const DIET_FACTORS = [
    ['Cachorro', 'Muy delgado', 'Castrado', 'Sedentario', 1.9],
    ['Cachorro', 'Muy delgado', 'Castrado', 'Activo', 2.3],
    ['Cachorro', 'Muy delgado', 'Castrado', 'Muy activo', 2.6],
    ['Cachorro', 'Muy delgado', 'Entero', 'Sedentario', 2.1],
    ['Cachorro', 'Muy delgado', 'Entero', 'Activo', 2.5],
    ['Cachorro', 'Muy delgado', 'Entero', 'Muy activo', 2.8],
    ['Cachorro', 'Delgado', 'Castrado', 'Sedentario', 1.7],
    ['Cachorro', 'Delgado', 'Castrado', 'Activo', 1.8],
    ['Cachorro', 'Delgado', 'Castrado', 'Muy activo', 2],
    ['Cachorro', 'Delgado', 'Entero', 'Sedentario', 1.7],
    ['Cachorro', 'Delgado', 'Entero', 'Activo', 1.8],
    ['Cachorro', 'Delgado', 'Entero', 'Muy activo', 2],
    ['Cachorro', 'Normal', 'Castrado', 'Sedentario', 1.4],
    ['Cachorro', 'Normal', 'Castrado', 'Activo', 1.5],
    ['Cachorro', 'Normal', 'Castrado', 'Muy activo', 1.6],
    ['Cachorro', 'Normal', 'Entero', 'Sedentario', 1.5],
    ['Cachorro', 'Normal', 'Entero', 'Activo', 1.6],
    ['Cachorro', 'Normal', 'Entero', 'Muy activo', 1.8],
    ['Cachorro', 'Obeso', 'Castrado', 'Sedentario', 1.2],
    ['Cachorro', 'Obeso', 'Castrado', 'Activo', 1.7],
    ['Cachorro', 'Obeso', 'Castrado', 'Muy activo', 1.9],
    ['Cachorro', 'Obeso', 'Entero', 'Sedentario', 1.4],
    ['Cachorro', 'Obeso', 'Entero', 'Activo', 1.9],
    ['Cachorro', 'Obeso', 'Entero', 'Muy activo', 2.1],
    ['Adulto', 'Muy delgado', 'Castrado', 'Sedentario', 1.4],
    ['Adulto', 'Muy delgado', 'Castrado', 'Activo', 1.8],
    ['Adulto', 'Muy delgado', 'Castrado', 'Muy activo', 2.1],
    ['Adulto', 'Muy delgado', 'Entero', 'Sedentario', 1.6],
    ['Adulto', 'Muy delgado', 'Entero', 'Activo', 2],
    ['Adulto', 'Muy delgado', 'Entero', 'Muy activo', 2.3],
    ['Adulto', 'Delgado', 'Castrado', 'Sedentario', 1.3],
    ['Adulto', 'Delgado', 'Castrado', 'Activo', 1.6],
    ['Adulto', 'Delgado', 'Castrado', 'Muy activo', 1.8],
    ['Adulto', 'Delgado', 'Entero', 'Sedentario', 1.5],
    ['Adulto', 'Delgado', 'Entero', 'Activo', 1.8],
    ['Adulto', 'Delgado', 'Entero', 'Muy activo', 2],
    ['Adulto', 'Normal', 'Castrado', 'Sedentario', 1.4],
    ['Adulto', 'Normal', 'Castrado', 'Activo', 1.5],
    ['Adulto', 'Normal', 'Castrado', 'Muy activo', 1.7],
    ['Adulto', 'Normal', 'Entero', 'Sedentario', 1.4],
    ['Adulto', 'Normal', 'Entero', 'Activo', 1.7],
    ['Adulto', 'Normal', 'Entero', 'Muy activo', 1.8],
    ['Adulto', 'Obeso', 'Castrado', 'Sedentario', 1.1],
    ['Adulto', 'Obeso', 'Castrado', 'Activo', 1.2],
    ['Adulto', 'Obeso', 'Castrado', 'Muy activo', 1.3],
    ['Adulto', 'Obeso', 'Entero', 'Sedentario', 1.2],
    ['Adulto', 'Obeso', 'Entero', 'Activo', 1.3],
    ['Adulto', 'Obeso', 'Entero', 'Muy activo', 1.4],
    ['Senior', 'Muy delgado', 'Castrado', 'Sedentario', 1.3],
    ['Senior', 'Muy delgado', 'Castrado', 'Activo', 1.7],
    ['Senior', 'Muy delgado', 'Castrado', 'Muy activo', 2.0],
    ['Senior', 'Muy delgado', 'Entero', 'Sedentario', 1.5],
    ['Senior', 'Muy delgado', 'Entero', 'Activo', 1.9],
    ['Senior', 'Muy delgado', 'Entero', 'Muy activo', 2.2],
    ['Senior', 'Delgado', 'Castrado', 'Sedentario', 1.3],
    ['Senior', 'Delgado', 'Castrado', 'Activo', 1.4],
    ['Senior', 'Delgado', 'Castrado', 'Muy activo', 1.7],
    ['Senior', 'Delgado', 'Entero', 'Sedentario', 1.3],
    ['Senior', 'Delgado', 'Entero', 'Activo', 1.7],
    ['Senior', 'Delgado', 'Entero', 'Muy activo', 1.9],
    ['Senior', 'Normal', 'Castrado', 'Sedentario', 1.3],
    ['Senior', 'Normal', 'Castrado', 'Activo', 1.4],
    ['Senior', 'Normal', 'Castrado', 'Muy activo', 1.5],
    ['Senior', 'Normal', 'Entero', 'Sedentario', 1.2],
    ['Senior', 'Normal', 'Entero', 'Activo', 1.5],
    ['Senior', 'Normal', 'Entero', 'Muy activo', 1.7],
    ['Senior', 'Obeso', 'Castrado', 'Sedentario', 1.1],
    ['Senior', 'Obeso', 'Castrado', 'Activo', 1.2],
    ['Senior', 'Obeso', 'Castrado', 'Muy activo', 1.3],
    ['Senior', 'Obeso', 'Entero', 'Sedentario', 1.2],
    ['Senior', 'Obeso', 'Entero', 'Activo', 1.3],
    ['Senior', 'Obeso', 'Entero', 'Muy activo', 1.4]
];

/**
 * Determina la etapa de vida del perro (Cachorro, Adulto, Senior) basado en la edad.
 * NOTA: Los rangos de edad son generales. Ajustar según necesidades específicas.
 * @param int $age La edad del perro.
 * @param string $age_type El tipo de edad ('meses' o 'años').
 * @return string La etapa de vida ('Cachorro', 'Adulto', 'Senior'). Devuelve 'Desconocido' si la edad no encaja en rangos definidos.
 */
function determine_life_stage($age, $age_type) {
    $age_in_years = ($age_type === 'meses') ? $age / 12 : $age;

    if ($age_in_years < 1) {
        return 'Cachorro';
    } elseif ($age_in_years >= 1 && $age_in_years < 7) { // Rangos generales, ajustar si es necesario
        return 'Adulto';
    } elseif ($age_in_years >= 7) { // Rangos generales, ajustar si es necesario
        return 'Senior';
    }

    return 'Desconocido'; // En caso de que la edad no encaje en ningún rango
}

/**
 * Busca el factor de la tabla basado en los atributos del perro.
 * @param string $life_stage Etapa de vida ('Cachorro', 'Adulto', 'Senior').
 * @param string $body_image Condición física ('Muy delgado', 'Delgado', 'Normal', 'Obeso').
 * @param string $reproductive_state Estado reproductivo ('Castrado', 'Entero').
 * @param string $activity_level Nivel de actividad ('Sedentario', 'Activo', 'Muy activo').
 * @return float|null El factor encontrado, o null si no hay coincidencia.
 */
function find_diet_factor($life_stage, $body_image, $reproductive_state, $activity_level) {
    global $DIET_FACTORS;

    // Mapear los valores del formulario a los de la tabla si difieren
    $mapped_body_image = str_replace(['muy_delgado', 'delgado', 'peso_ideal', 'sobrepeso'], ['Muy delgado', 'Delgado', 'Normal', 'Obeso'], $body_image);
     // Nota: body_image en formulario tiene 'peso_ideal' y 'sobrepeso', en tabla 'Normal' y 'Obeso' respectivamente.
     // Asegurarse de que los valores coincidan.

    $mapped_reproductive_state = str_replace(['castrado', 'entero'], ['Castrado', 'Entero'], $reproductive_state);
    $mapped_activity_level = str_replace(['baja', 'moderada', 'alta'], ['Sedentario', 'Activo', 'Muy activo'], $activity_level);
     // Nota: activity_level en formulario tiene 'baja', 'moderada', 'alta', en tabla 'Sedentario', 'Activo', 'Muy activo'.
     // Asegurarse de que los valores coincidan.

    foreach ($DIET_FACTORS as $row) {
        if ($row[0] === $life_stage &&
            $row[1] === $mapped_body_image &&
            $row[2] === $mapped_reproductive_state &&
            $row[3] === $mapped_activity_level) {
            // Asegurarse de que el factor se lea como float (los puntos/comas)
            return floatval(str_replace(',', '.', $row[4]));
        }
    }

    return null; // No se encontró un factor coincidente
}

/**
 * Función para manejar envío del formulario
 */
function handle_pet_form_submission($request) {
    $params = $request->get_params();

    // 1. Determinar la etapa de vida
    $life_stage = determine_life_stage(intval($params['age']), $params['age_type']);

    // 2. Buscar el factor en la tabla
    $factor = find_diet_factor(
        $life_stage,
        $params['body_image'],
        $params['reproductive_state'],
        $params['activity_level']
    );

    // 3. Ahora tienes el $factor y otros datos como $params['weight']
    //    Aquí es donde integrarías la lógica de cálculo de la dieta
    //    y la comunicación con WooCommerce.

    // Por ahora, solo devolvemos el factor encontrado como ejemplo
    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Formulario recibido correctamente',
        'life_stage' => $life_stage,
        'factor' => $factor
        // Agregar más datos de la respuesta o cálculo aquí
    ));
}

// --- ELIMINAR CAMPOS PERSONALIZADOS EN FORMULARIO DE RAZA ---
// Quitar hooks/metaboxes para campos extra (ID Único, Esperanza de vida, etc.)
remove_action('dog_breed_add_form_fields', 'dog_breed_add_custom_fields', 10);
remove_action('dog_breed_edit_form_fields', 'dog_breed_edit_custom_fields', 10);
remove_action('created_dog_breed', 'save_dog_breed_custom_fields', 10, 2);
remove_action('edited_dog_breed', 'save_dog_breed_custom_fields', 10, 2);

// --- MODIFICAR COLUMNAS DE LA TABLA DE RAZAS ---
add_filter('manage_edit-dog_breed_columns', function($columns) {
    // Solo dejar nombre y slug
    return array(
        'cb' => $columns['cb'],
        'name' => __('Nombre'),
        'slug' => __('Slug'),
    );
});
add_filter('manage_dog_breed_custom_column', function($out, $column, $term_id) {
    // No mostrar nada extra
    return $out;
}, 10, 3);

// --- OPCIONAL: OCULTAR DESCRIPCIÓN ---
add_action('admin_head', function() {
    echo '<style>.term-description-wrap, .form-field.term-description-wrap, .column-description { display: none !important; }</style>';
});

/**
 * Función para guardar el archivo breeds.json en el directorio del plugin
 */
function save_breeds_json_file($json_content) {
    $file_path = plugin_dir_path(__FILE__) . 'breeds.json';
    
    // Crear el archivo breeds.json
    $result = file_put_contents($file_path, $json_content);
    
    if ($result === false) {
        return new WP_Error('file_write_error', 'No se pudo escribir en el archivo breeds.json');
    }
    
    return true;
}

/**
 * Endpoint para guardar el archivo breeds.json y a su vez importar las razas
 * @param WP_REST_Request $request La solicitud REST
 * @return WP_REST_Response Respuesta con el resultado de la operación
 */
function save_breeds_file_endpoint($request) {
    // Verificar permisos
    if (!current_user_can('manage_options')) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'No tienes permisos para realizar esta acción.'
        ), 403);
    }
    
    // Obtener el contenido JSON del cuerpo de la solicitud
    $json_content = $request->get_body();
    
    if (empty($json_content)) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'No se proporcionó contenido JSON para guardar.'
        ), 400);
    }
    
    // Decodificar para validar que es un JSON válido
    $json_data = json_decode($json_content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'El contenido proporcionado no es un JSON válido: ' . json_last_error_msg()
        ), 400);
    }
    
    // Guardar el archivo breeds.json en el directorio del plugin
    $result = save_breeds_json_file($json_content);
    
    if (is_wp_error($result)) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => $result->get_error_message()
        ), 500);
    }
    
    // Importar las razas de perros desde el JSON
    $import_result = import_dog_breeds_from_json($json_data);
    
    if (is_wp_error($import_result)) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'El archivo se guardó, pero hubo un error al importar las razas: ' . $import_result->get_error_message()
        ), 500);
    }
    
    return new WP_REST_Response(array(
        'success' => true,
        'message' => sprintf('El archivo breeds.json se guardó correctamente y se importaron %d razas con %d errores.', 
                             $import_result['imported'], $import_result['errors']),
        'imported' => $import_result['imported'],
        'errors' => $import_result['errors']
    ), 200);
}

/**
 * Endpoint para importar razas de perros desde un archivo de texto
 * Cada línea del archivo debe contener el nombre de una raza
 * @param string $file_path Ruta al archivo de texto
 * @return array Estadísticas de la importación (importadas y errores)
 */
function import_dog_breeds_from_text_file($file_path) {
    if (!file_exists($file_path)) {
        return new WP_Error('file_not_found', 'El archivo de razas no existe.');
    }
    
    // Leer el archivo de texto
    $breeds_content = file_get_contents($file_path);
    if (!$breeds_content) {
        return new WP_Error('file_read_error', 'No se pudo leer el archivo de razas.');
    }
    
    // Dividir el contenido por líneas y eliminar líneas vacías
    $breeds = array_filter(explode("\n", $breeds_content), 'trim');
    
    $imported = 0;
    $errors = 0;
    
    foreach ($breeds as $breed_name) {
        $breed_name = trim($breed_name);
        
        if (empty($breed_name)) {
            continue; // Saltar líneas vacías
        }
        
        // Comprobar si la raza ya existe
        $term_exists = term_exists($breed_name, 'dog_breed');
        
        if (!$term_exists) {
            // Insertar nueva raza
            $result = wp_insert_term($breed_name, 'dog_breed');
            
            if (!is_wp_error($result)) {
                $imported++;
            } else {
                $errors++;
            }
        }
    }
    
    return array(
        'imported' => $imported,
        'errors' => $errors,
        'total' => count($breeds)
    );
}

/**
 * Endpoint REST para importar razas desde un archivo de texto
 */
function rest_import_dog_breeds_from_text($request) {
    if (!current_user_can('manage_options')) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'No tienes permisos para realizar esta acción.'
        ), 403);
    }
    
    $file_path = $request->get_param('file_path');
    
    if (empty($file_path)) {
        // Si no se proporciona una ruta, usar el archivo breeds.txt del directorio tuco
        $file_path = WP_CONTENT_DIR . '/plugins/tuco/breeds.txt';
        
        if (!file_exists($file_path)) {
            // Buscar en el directorio raíz de plugins también
            $file_path = WP_PLUGIN_DIR . '/tuco/breeds.txt';
        }
    }
    
    if (!file_exists($file_path)) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'No se encontró el archivo de razas.'
        ), 404);
    }
    
    $result = import_dog_breeds_from_text_file($file_path);
    
    if (is_wp_error($result)) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => $result->get_error_message()
        ), 500);
    }
    
    return new WP_REST_Response(array(
        'success' => true,
        'message' => sprintf('Importación completada. Se importaron %d razas de un total de %d. Errores: %d.', 
                           $result['imported'], $result['total'], $result['errors']),
        'imported' => $result['imported'],
        'total' => $result['total'],
        'errors' => $result['errors']
    ), 200);
}

function register_rest_routes() {
    // Ruta para obtener razas de perros
    register_rest_route('pet-form/v1', '/dog-breeds', array(
        'methods' => 'GET',
        'callback' => 'get_dog_breeds',
        'permission_callback' => '__return_true'
    ));
    
    // Ruta para importar razas de perros desde JSON
    register_rest_route('pet-form/v1', '/import-breeds', array(
        'methods' => 'POST',
        'callback' => 'rest_import_dog_breeds',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        }
    ));
    
    // Nueva ruta para importar razas desde archivo de texto
    register_rest_route('pet-form/v1', '/import-breeds-text', array(
        'methods' => 'GET',
        'callback' => 'rest_import_dog_breeds_from_text',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        }
    ));
    
    // Ruta para enviar el formulario de mascotas
    register_rest_route('pet-form/v1', '/submit', array(
        'methods' => 'POST',
        'callback' => 'handle_pet_form_submission',
        'permission_callback' => '__return_true'
    ));
    
    // Ruta para guardar el archivo breeds.json
    register_rest_route('pet-form/v1', '/save-breeds-file', array(
        'methods' => 'POST',
        'callback' => 'save_breeds_file_endpoint',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        }
    ));
}
add_action('rest_api_init', 'register_rest_routes');

/**
 * Obtiene todas las razas de perros con ID y nombre
 * @return WP_REST_Response Respuesta con la lista simplificada de razas
 */
function get_dog_breeds() {
    $terms = get_terms(array(
        'taxonomy' => 'dog_breed',
        'hide_empty' => false,
    ));
    
    if (is_wp_error($terms)) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => $terms->get_error_message()
        ), 500);
    }
    
    $breeds = array();
    
    foreach ($terms as $term) {
        // Agregar la raza con estructura simplificada
        $breeds[] = array(
            'id' => $term->term_id,
            'value' => $term->slug,
            'label' => $term->name
        );
    }
    
    // Ordenar las razas por nombre
    usort($breeds, function($a, $b) {
        return strcasecmp($a['label'], $b['label']);
    });
    
    return new WP_REST_Response($breeds, 200);
}

// Agregar JS y CSS para la tabla de administración de taxonomías
function dog_breed_admin_scripts() {
    // Verificar si estamos en la página de taxonomía de razas
    $screen = get_current_screen();
    if ($screen && $screen->taxonomy === 'dog_breed') {
        // Registrar y encolar nuestro JavaScript
        wp_enqueue_script('dog-breed-admin-js', plugins_url('admin/js/dog-breed-admin.js', __FILE__), array('jquery'), '1.0', true);
        
        // Registrar y encolar nuestro CSS
        wp_enqueue_style('dog-breed-admin-css', plugins_url('admin/css/dog-breed-admin.css', __FILE__), array(), '1.0');
        
        // Pasar datos a JavaScript
        wp_localize_script('dog-breed-admin-js', 'dogBreedAdmin', array(
            'searchPlaceholder' => __('Buscar razas...', 'pet-form-block'),
        ));
    }
}
add_action('admin_enqueue_scripts', 'dog_breed_admin_scripts');

/**
 * Asegurar que existen los directorios necesarios para los archivos de admin
 */
function ensure_admin_directories() {
    // Crear directorios si no existen
    $admin_dir = plugin_dir_path(__FILE__) . 'admin';
    if (!file_exists($admin_dir)) {
        mkdir($admin_dir, 0755);
    }
    
    $js_dir = $admin_dir . '/js';
    if (!file_exists($js_dir)) {
        mkdir($js_dir, 0755);
    }
    
    $css_dir = $admin_dir . '/css';
    if (!file_exists($css_dir)) {
        mkdir($css_dir, 0755);
    }
    
    // Crear archivos JS y CSS si no existen
    $js_file = $js_dir . '/dog-breed-admin.js';
    if (!file_exists($js_file)) {
        $js_content = "jQuery(document).ready(function($) {
    // Agregar campo de búsqueda sobre la tabla de términos
    var searchBox = '<div class=\"tablenav top\"><div class=\"alignleft actions\"><input type=\"text\" id=\"breed-search\" placeholder=\"' + dogBreedAdmin.searchPlaceholder + '\" style=\"padding: 5px; width: 200px;\"></div></div>';
    $('.wp-list-table.tags').before(searchBox);
    
    // Función para filtrar la tabla
    $('#breed-search').on('keyup', function() {
        var searchText = $(this).val().toLowerCase();
        
        // Filtrar cada fila
        $('.wp-list-table.tags tbody tr').each(function() {
            var rowText = $(this).text().toLowerCase();
            if (rowText.indexOf(searchText) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});";
        file_put_contents($js_file, $js_content);
    }
    
    $css_file = $css_dir . '/dog-breed-admin.css';
    if (!file_exists($css_file)) {
        $css_content = "/* Estilos para la página de administración de razas */
#breed-search {
    margin: 8px 0;
    border-radius: 4px;
    border: 1px solid #ddd;
}
#breed-search:focus {
    border-color: #2271b1;
    box-shadow: 0 0 0 1px #2271b1;
    outline: none;
}";
        file_put_contents($css_file, $css_content);
    }
}
add_action('admin_init', 'ensure_admin_directories');

/**
 * Limpia todas las razas de perros
 */
function clear_all_dog_breeds() {
    $terms = get_terms(array(
        'taxonomy' => 'dog_breed',
        'hide_empty' => false,
    ));

    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            wp_delete_term($term->term_id, 'dog_breed');
        }
    }
}

/**
 * Endpoint REST para limpiar todas las razas
 */
function rest_clear_dog_breeds($request) {
    if (!current_user_can('manage_options')) {
        return new WP_Error('rest_forbidden', 'No tienes permisos para realizar esta acción.', array('status' => 403));
    }

    clear_all_dog_breeds();
    return rest_ensure_response(array('message' => 'Todas las razas han sido eliminadas.'));
}

// Registrar el endpoint REST
add_action('rest_api_init', function () {
    register_rest_route('my-react-form-block/v1', '/clear-breeds', array(
        'methods' => 'POST',
        'callback' => 'rest_clear_dog_breeds',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        }
    ));
}); 