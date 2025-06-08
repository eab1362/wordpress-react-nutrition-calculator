<?php
/**
 * Plugin Name: Formulario de Mascota y Calculadora Nutricional
 * Description: Un bloque de Gutenberg para registrar mascotas con formulario multi-paso y calcular valor diario nutricional para perros.
 * Version: 2.0
 * Author: Forever Dog
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('PET_FORM_VERSION', '2.0');
define('PET_FORM_PATH', plugin_dir_path(__FILE__));
define('PET_FORM_URL', plugin_dir_url(__FILE__));

/**
 * Activación del plugin
 */
function pet_form_activate() {
    // Crear tabla de factores nutricionales
    create_nutrition_factors_table();
    
    // Insertar factores por defecto
    insert_default_nutrition_factors();
    
    // Crear tabla de mascotas
    create_pets_table();
    
    // Registrar el tipo de contenido personalizado
    register_pet_post_type();
    
    // Limpiar caché de reescritura
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'pet_form_activate');

/**
 * Desactivación del plugin
 */
function pet_form_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'pet_form_deactivate');

/**
 * Crear tabla para factores nutricionales
 */
function create_nutrition_factors_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'nutrition_factors';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        life_stage varchar(20) NOT NULL,
        body_condition varchar(20) NOT NULL,
        reproductive_state varchar(20) NOT NULL,
        activity_level varchar(20) NOT NULL,
        factor decimal(3,2) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY factor_combination (life_stage, body_condition, reproductive_state, activity_level)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Crear tabla para mascotas registradas
 */
function create_pets_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'pets';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        pet_name varchar(100) NOT NULL,
        breed varchar(100) NOT NULL,
        sex varchar(10) NOT NULL,
        age int(3) NOT NULL,
        age_type varchar(10) NOT NULL,
        natural_food varchar(10) NOT NULL,
        activity_level varchar(20) NOT NULL,
        reproductive_state varchar(20) NOT NULL,
        body_condition varchar(20) NOT NULL,
        weight decimal(5,2) NOT NULL,
        allergies text,
        special_needs text,
        owner_name varchar(100) NOT NULL,
        email varchar(100) NOT NULL,
        life_stage varchar(20),
        nutrition_factor decimal(3,2),
        daily_nutrition decimal(6,2),
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Insertar factores nutricionales por defecto
 */
function insert_default_nutrition_factors() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'nutrition_factors';
    
    // Verificar si ya existen datos
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    if ($count > 0) {
        return; // Ya hay datos, no insertar duplicados
    }
    
    $default_factors = array(
        // Cachorro
        array('Cachorro', 'Muy delgado', 'Castrado', 'Sedentario', 1.9),
        array('Cachorro', 'Muy delgado', 'Castrado', 'Activo', 2.3),
        array('Cachorro', 'Muy delgado', 'Castrado', 'Muy activo', 2.6),
        array('Cachorro', 'Muy delgado', 'Entero', 'Sedentario', 2.1),
        array('Cachorro', 'Muy delgado', 'Entero', 'Activo', 2.5),
        array('Cachorro', 'Muy delgado', 'Entero', 'Muy activo', 2.8),
        array('Cachorro', 'Delgado', 'Castrado', 'Sedentario', 1.7),
        array('Cachorro', 'Delgado', 'Castrado', 'Activo', 1.8),
        array('Cachorro', 'Delgado', 'Castrado', 'Muy activo', 2.0),
        array('Cachorro', 'Delgado', 'Entero', 'Sedentario', 1.7),
        array('Cachorro', 'Delgado', 'Entero', 'Activo', 1.8),
        array('Cachorro', 'Delgado', 'Entero', 'Muy activo', 2.0),
        array('Cachorro', 'Normal', 'Castrado', 'Sedentario', 1.4),
        array('Cachorro', 'Normal', 'Castrado', 'Activo', 1.5),
        array('Cachorro', 'Normal', 'Castrado', 'Muy activo', 1.6),
        array('Cachorro', 'Normal', 'Entero', 'Sedentario', 1.5),
        array('Cachorro', 'Normal', 'Entero', 'Activo', 1.6),
        array('Cachorro', 'Normal', 'Entero', 'Muy activo', 1.8),
        array('Cachorro', 'Obeso', 'Castrado', 'Sedentario', 1.2),
        array('Cachorro', 'Obeso', 'Castrado', 'Activo', 1.7),
        array('Cachorro', 'Obeso', 'Castrado', 'Muy activo', 1.9),
        array('Cachorro', 'Obeso', 'Entero', 'Sedentario', 1.4),
        array('Cachorro', 'Obeso', 'Entero', 'Activo', 1.9),
        array('Cachorro', 'Obeso', 'Entero', 'Muy activo', 2.1),
        
        // Adulto
        array('Adulto', 'Muy delgado', 'Castrado', 'Sedentario', 1.4),
        array('Adulto', 'Muy delgado', 'Castrado', 'Activo', 1.8),
        array('Adulto', 'Muy delgado', 'Castrado', 'Muy activo', 2.1),
        array('Adulto', 'Muy delgado', 'Entero', 'Sedentario', 1.6),
        array('Adulto', 'Muy delgado', 'Entero', 'Activo', 2.0),
        array('Adulto', 'Muy delgado', 'Entero', 'Muy activo', 2.3),
        array('Adulto', 'Delgado', 'Castrado', 'Sedentario', 1.3),
        array('Adulto', 'Delgado', 'Castrado', 'Activo', 1.6),
        array('Adulto', 'Delgado', 'Castrado', 'Muy activo', 1.8),
        array('Adulto', 'Delgado', 'Entero', 'Sedentario', 1.5),
        array('Adulto', 'Delgado', 'Entero', 'Activo', 1.8),
        array('Adulto', 'Delgado', 'Entero', 'Muy activo', 2.0),
        array('Adulto', 'Normal', 'Castrado', 'Sedentario', 1.4),
        array('Adulto', 'Normal', 'Castrado', 'Activo', 1.5),
        array('Adulto', 'Normal', 'Castrado', 'Muy activo', 1.7),
        array('Adulto', 'Normal', 'Entero', 'Sedentario', 1.4),
        array('Adulto', 'Normal', 'Entero', 'Activo', 1.7),
        array('Adulto', 'Normal', 'Entero', 'Muy activo', 1.8),
        array('Adulto', 'Obeso', 'Castrado', 'Sedentario', 1.1),
        array('Adulto', 'Obeso', 'Castrado', 'Activo', 1.2),
        array('Adulto', 'Obeso', 'Castrado', 'Muy activo', 1.3),
        array('Adulto', 'Obeso', 'Entero', 'Sedentario', 1.2),
        array('Adulto', 'Obeso', 'Entero', 'Activo', 1.3),
        array('Adulto', 'Obeso', 'Entero', 'Muy activo', 1.4),
        
        // Senior
        array('Senior', 'Muy delgado', 'Castrado', 'Sedentario', 1.3),
        array('Senior', 'Muy delgado', 'Castrado', 'Activo', 1.7),
        array('Senior', 'Muy delgado', 'Castrado', 'Muy activo', 2.0),
        array('Senior', 'Muy delgado', 'Entero', 'Sedentario', 1.5),
        array('Senior', 'Muy delgado', 'Entero', 'Activo', 1.9),
        array('Senior', 'Muy delgado', 'Entero', 'Muy activo', 2.2),
        array('Senior', 'Delgado', 'Castrado', 'Sedentario', 1.3),
        array('Senior', 'Delgado', 'Castrado', 'Activo', 1.4),
        array('Senior', 'Delgado', 'Castrado', 'Muy activo', 1.7),
        array('Senior', 'Delgado', 'Entero', 'Sedentario', 1.3),
        array('Senior', 'Delgado', 'Entero', 'Activo', 1.7),
        array('Senior', 'Delgado', 'Entero', 'Muy activo', 1.9),
        array('Senior', 'Normal', 'Castrado', 'Sedentario', 1.3),
        array('Senior', 'Normal', 'Castrado', 'Activo', 1.4),
        array('Senior', 'Normal', 'Castrado', 'Muy activo', 1.5),
        array('Senior', 'Normal', 'Entero', 'Sedentario', 1.2),
        array('Senior', 'Normal', 'Entero', 'Activo', 1.5),
        array('Senior', 'Normal', 'Entero', 'Muy activo', 1.7),
        array('Senior', 'Obeso', 'Castrado', 'Sedentario', 1.1),
        array('Senior', 'Obeso', 'Castrado', 'Activo', 1.2),
        array('Senior', 'Obeso', 'Castrado', 'Muy activo', 1.3),
        array('Senior', 'Obeso', 'Entero', 'Sedentario', 1.2),
        array('Senior', 'Obeso', 'Entero', 'Activo', 1.3),
        array('Senior', 'Obeso', 'Entero', 'Muy activo', 1.4),
    );
    
    foreach ($default_factors as $factor) {
        $wpdb->insert(
            $table_name,
            array(
                'life_stage' => $factor[0],
                'body_condition' => $factor[1],
                'reproductive_state' => $factor[2],
                'activity_level' => $factor[3],
                'factor' => $factor[4]
            ),
            array('%s', '%s', '%s', '%s', '%f')
        );
    }
}

/**
 * Registrar custom post type para mascotas (para compatibilidad)
 */
function register_pet_post_type() {
    $labels = array(
        'name'               => _x('Mascotas', 'post type general name'),
        'singular_name'      => _x('Mascota', 'post type singular name'),
        'menu_name'          => _x('Mascotas', 'admin menu'),
        'name_admin_bar'     => _x('Mascota', 'add new on admin bar'),
        'add_new'            => _x('Añadir nueva', 'Mascota'),
        'add_new_item'       => __('Añadir nueva mascota'),
        'new_item'           => __('Nueva mascota'),
        'edit_item'          => __('Editar mascota'),
        'view_item'          => __('Ver mascota'),
        'all_items'          => __('Todas las mascotas'),
        'search_items'       => __('Buscar mascotas'),
        'not_found'          => __('No se encontraron mascotas.'),
        'not_found_in_trash' => __('No se encontraron mascotas en la papelera.')
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => 'pet-form',
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'supports'           => array('title'),
        'show_in_rest'       => true,
    );

    register_post_type('pet', $args);
}

/**
 * Registrar la taxonomía de razas de perros
 */
function register_dog_breed_taxonomy() {
    $labels = array(
        'name'              => _x('Razas de Perros', 'taxonomy general name'),
        'singular_name'     => _x('Raza de Perro', 'taxonomy singular name'),
        'menu_name'         => __('Razas de Perros'),
        'add_new_item'      => __('Agregar Nueva Raza'),
        'edit_item'         => __('Editar Raza'),
        'update_item'       => __('Actualizar Raza'),
        'new_item_name'     => __('Nombre de Nueva Raza'),
        'not_found'         => __('No se encontraron razas')
    );

    $args = array(
        'hierarchical'      => false,  // Quita la categoría superior
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'breed'),
        'show_in_rest'      => true,
        'show_in_menu'      => 'pet-form',
        'meta_box_cb'       => false,  // Quita metaboxes adicionales
        'show_tagcloud'     => false   // Quita campos adicionales
    );

    register_taxonomy('dog_breed', array('pet'), $args);
  //  create_default_dog_breeds();
}
add_action('init', 'register_dog_breed_taxonomy');

/**
 * Crear razas de perros por defecto
 */
//function create_default_dog_breeds() {
    // No crear razas por defecto
    // Los administradores pueden usar la página "Gestión de Razas" 
    // para agregar las razas que necesiten, ya sea individualmente 
    // o importando desde archivos .txt
    return;
//}

/**
 * Registrar el bloque de Gutenberg
 */
function pet_form_register_block() {
    // Registrar el script del editor
    wp_register_script(
        'pet-form-editor',
        plugins_url('build/index.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor'),
        filemtime(plugin_dir_path(__FILE__) . 'build/index.js')
    );

    // Registrar los estilos
    wp_register_style(
        'pet-form-style',
        plugins_url('src/style.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'src/style.css')
    );

    // Agregar datos de razas como variable global
    wp_localize_script(
        'pet-form-editor',
        'petFormSettings',
        array(
            'dogBreeds' => get_dog_breeds_list(),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pet_form_nonce')
        )
    );

    // Registrar el bloque
    register_block_type('pet-form/form-block', array(
        'editor_script' => 'pet-form-editor',
        'style' => 'pet-form-style',
        'render_callback' => 'pet_form_render_block'
    ));
}
add_action('init', 'pet_form_register_block');

/**
 * Función de renderizado del bloque
 */
function pet_form_render_block($attributes) {
    wp_enqueue_script('pet-form-editor');
    wp_enqueue_style('pet-form-style');
    
    return '<div id="pet-form-block"></div>';
}

/**
 * Obtener lista de razas de perros
 */
function get_dog_breeds_list() {
    $terms = get_terms(array(
        'taxonomy' => 'dog_breed',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC'
    ));
    
    $breeds = array();
    foreach ($terms as $term) {
        $breeds[] = array(
            'value' => $term->slug,
            'label' => $term->name
        );
    }
    
    return $breeds;
}

/**
 * Agregar página de administración principal
 */
function pet_form_admin_menu() {
    add_menu_page(
        'Formulario de Mascotas',
        'Mascotas',
        'manage_options',
        'pet-form',
        'pet_form_admin_page',
        'dashicons-pets',
        25
    );
    
    add_submenu_page(
        'pet-form',
        'Lista de Mascotas',
        'Lista de Mascotas',
        'manage_options',
        'pet-form-list',
        'pet_form_list_page'
    );
    
    add_submenu_page(
        'pet-form',
        'Agregar Mascota',
        'Agregar Mascota',
        'manage_options',
        'pet-form-add',
        'pet_form_add_page'
    );
    
    add_submenu_page(
        'pet-form',
        'Factores Nutricionales',
        'Factores Nutricionales',
        'manage_options',
        'nutrition-factors',
        'nutrition_factors_admin_page'
    );
    
    add_submenu_page(
        'pet-form',
        'Gestión de Razas',
        'Gestión de Razas',
        'manage_options',
        'pet-form-breeds',
        'pet_form_breeds_management_page'
    );
    
    add_submenu_page(
        'pet-form',
        'Configuración',
        'Configuración',
        'manage_options',
        'pet-form-settings',
        'pet_form_settings_page'
    );
}
add_action('admin_menu', 'pet_form_admin_menu');

/**
 * Página principal de administración
 */
function pet_form_admin_page() {
    global $wpdb;
    $pets_table = $wpdb->prefix . 'pets';
    $factors_table = $wpdb->prefix . 'nutrition_factors';
    
    $total_pets = $wpdb->get_var("SELECT COUNT(*) FROM $pets_table");
    $total_factors = $wpdb->get_var("SELECT COUNT(*) FROM $factors_table");
    
    ?>
    <div class="wrap">
        <h1>Panel de Control - Mascotas</h1>
        
        <div class="pet-form-dashboard">
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h3>Mascotas Registradas</h3>
                    <div class="stat-number"><?php echo $total_pets; ?></div>
        </div>

                <div class="stat-card">
                    <h3>Factores Configurados</h3>
                    <div class="stat-number"><?php echo $total_factors; ?></div>
        </div>

                <div class="stat-card">
                    <h3>Razas Disponibles</h3>
                    <div class="stat-number"><?php echo wp_count_terms('dog_breed'); ?></div>
        </div>
    </div>

            <div class="quick-actions">
                <h2>Acciones Rápidas</h2>
                <a href="<?php echo admin_url('admin.php?page=pet-form-add'); ?>" class="button button-primary">
                    Agregar Nueva Mascota
                </a>
                <a href="<?php echo admin_url('admin.php?page=pet-form-list'); ?>" class="button">
                    Ver Mascotas Registradas
                </a>
                <a href="<?php echo admin_url('admin.php?page=nutrition-factors'); ?>" class="button">
                    Gestionar Factores Nutricionales
                </a>
                <a href="<?php echo admin_url('edit-tags.php?taxonomy=dog_breed&post_type=pet'); ?>" class="button">
                    Gestionar Razas
                </a>
            </div>
        </div>
        
        <style>
        .pet-form-dashboard { margin-top: 20px; }
        .dashboard-stats { display: flex; gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1; }
        .stat-card h3 { margin: 0 0 10px 0; color: #666; }
        .stat-number { font-size: 3em; font-weight: bold; color: #0073aa; }
        .quick-actions h2 { margin-bottom: 15px; }
        .quick-actions .button { margin-right: 10px; }
        </style>
    </div>
    <?php
}

/**
 * Página de lista de mascotas
 */
function pet_form_list_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pets';
    
    // Manejar eliminación
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['pet_id'])) {
        $pet_id = intval($_GET['pet_id']);
        if (check_admin_referer('delete_pet_' . $pet_id)) {
            $wpdb->delete($table_name, array('id' => $pet_id), array('%d'));
            echo '<div class="notice notice-success"><p>Mascota eliminada correctamente.</p></div>';
        }
    }
    
    // Obtener mascotas con paginación
    $per_page = 20;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $per_page;
    
    $total_pets = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $pets = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
        $per_page,
        $offset
    ));
    
    $total_pages = ceil($total_pets / $per_page);
    
    ?>
    <div class="wrap">
        <h1>Lista de Mascotas Registradas</h1>
        
        <?php if (empty($pets)): ?>
            <p>No hay mascotas registradas aún.</p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Raza</th>
                        <th>Sexo</th>
                        <th>Edad</th>
                        <th>Peso</th>
                        <th>Ración Diaria</th>
                        <th>Propietario</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pets as $pet): ?>
                    <tr>
                        <td><strong><?php echo esc_html($pet->pet_name); ?></strong></td>
                        <td><?php echo esc_html($pet->breed); ?></td>
                        <td><?php echo esc_html($pet->sex); ?></td>
                        <td><?php echo esc_html($pet->age . ' ' . $pet->age_type); ?></td>
                        <td><?php echo esc_html($pet->weight); ?> kg</td>
                        <td><?php echo esc_html($pet->daily_nutrition); ?> g</td>
                        <td>
                            <?php echo esc_html($pet->owner_name); ?><br>
                            <small><?php echo esc_html($pet->email); ?></small>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($pet->created_at)); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=pet-form-add&edit=' . $pet->id); ?>" 
                               class="button button-small">
                                Editar
                            </a>
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pet-form-list&action=delete&pet_id=' . $pet->id), 'delete_pet_' . $pet->id); ?>" 
                               onclick="return confirm('¿Está seguro de eliminar esta mascota?')" 
                               class="button button-small" style="margin-left: 5px;">
                                Eliminar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if ($total_pages > 1): ?>
                <div class="tablenav bottom">
                    <div class="tablenav-pages">
                        <?php
                        echo paginate_links(array(
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                            'total' => $total_pages,
                            'current' => $current_page
                        ));
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Página para agregar/editar mascotas
 */
function pet_form_add_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pets';
    
    $edit_mode = false;
    $pet_data = null;
    
    // Verificar si estamos editando
    if (isset($_GET['edit']) && !empty($_GET['edit'])) {
        $pet_id = intval($_GET['edit']);
        $pet_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $pet_id));
        if ($pet_data) {
            $edit_mode = true;
        }
    }
    
    // Procesar envío del formulario
    if (isset($_POST['submit_pet']) && check_admin_referer('pet_form_admin_nonce')) {
        $pet_name = sanitize_text_field($_POST['pet_name']);
        $breed = sanitize_text_field($_POST['breed']);
        $sex = sanitize_text_field($_POST['sex']);
        $age = intval($_POST['age']);
        $age_type = sanitize_text_field($_POST['age_type']);
        $natural_food = sanitize_text_field($_POST['natural_food']);
        $activity_level = sanitize_text_field($_POST['activity_level']);
        $reproductive_state = sanitize_text_field($_POST['reproductive_state']);
        $body_condition = sanitize_text_field($_POST['body_condition']);
        $weight = floatval($_POST['weight']);
        $allergies = sanitize_text_field($_POST['allergies']);
        $special_needs = sanitize_text_field($_POST['special_needs']);
        $owner_name = sanitize_text_field($_POST['owner_name']);
        $email = sanitize_email($_POST['email']);
        
        // Calcular campos derivados
        $life_stage = get_life_stage($age, $age_type);
        $factor = get_nutrition_factor_value($life_stage, $body_condition, $reproductive_state, $activity_level);
        $daily_nutrition = ($factor) ? calculate_daily_nutrition($weight, $factor) : 0;
        
        $data = array(
            'pet_name' => $pet_name,
            'breed' => $breed,
            'sex' => $sex,
            'age' => $age,
            'age_type' => $age_type,
            'natural_food' => $natural_food,
            'activity_level' => $activity_level,
            'reproductive_state' => $reproductive_state,
            'body_condition' => $body_condition,
            'weight' => $weight,
            'allergies' => $allergies,
            'special_needs' => $special_needs,
            'owner_name' => $owner_name,
            'email' => $email,
            'life_stage' => $life_stage,
            'nutrition_factor' => $factor,
            'daily_nutrition' => $daily_nutrition
        );
        
        if ($edit_mode && $pet_data) {
            // Actualizar
            $result = $wpdb->update(
                $table_name,
                $data,
                array('id' => $pet_data->id),
                array('%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%s', '%f', '%f'),
                array('%d')
            );
            if ($result !== false) {
                echo '<div class="notice notice-success"><p>Mascota actualizada correctamente.</p></div>';
                $pet_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $pet_data->id));
            } else {
                echo '<div class="notice notice-error"><p>Error al actualizar la mascota.</p></div>';
            }
        } else {
            // Insertar nueva
            $result = $wpdb->insert(
                $table_name,
                $data,
                array('%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%s', '%f', '%f')
            );
            if ($result !== false) {
                echo '<div class="notice notice-success"><p>Mascota registrada correctamente.</p></div>';
                wp_redirect(admin_url('admin.php?page=pet-form-list'));
                exit;
            } else {
                echo '<div class="notice notice-error"><p>Error al registrar la mascota.</p></div>';
            }
        }
    }
    
    // Obtener razas
    $breeds = get_dog_breeds_list();
    
    ?>
    <div class="wrap">
        <h1><?php echo $edit_mode ? 'Editar Mascota' : 'Agregar Nueva Mascota'; ?></h1>
        
        <form method="post" action="">
            <?php wp_nonce_field('pet_form_admin_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="pet_name">Nombre de la Mascota *</label></th>
                    <td><input type="text" id="pet_name" name="pet_name" value="<?php echo $pet_data ? esc_attr($pet_data->pet_name) : ''; ?>" required class="regular-text"></td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="breed">Raza *</label></th>
                    <td>
                        <select id="breed" name="breed" required>
                            <option value="">Seleccionar raza...</option>
                            <?php foreach ($breeds as $breed): ?>
                                <option value="<?php echo esc_attr($breed['value']); ?>" <?php echo ($pet_data && $pet_data->breed === $breed['value']) ? 'selected' : ''; ?>>
                                    <?php echo esc_html($breed['label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="sex">Sexo *</label></th>
                    <td>
                        <select id="sex" name="sex" required>
                            <option value="">Seleccionar...</option>
                            <option value="hembra" <?php echo ($pet_data && $pet_data->sex === 'hembra') ? 'selected' : ''; ?>>Hembra</option>
                            <option value="macho" <?php echo ($pet_data && $pet_data->sex === 'macho') ? 'selected' : ''; ?>>Macho</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="age">Edad *</label></th>
                    <td>
                        <input type="number" id="age" name="age" value="<?php echo $pet_data ? esc_attr($pet_data->age) : ''; ?>" required min="0" style="width: 80px;">
                        <select id="age_type" name="age_type" style="width: 100px; margin-left: 10px;">
                            <option value="años" <?php echo ($pet_data && $pet_data->age_type === 'años') ? 'selected' : ''; ?>>Años</option>
                            <option value="meses" <?php echo ($pet_data && $pet_data->age_type === 'meses') ? 'selected' : ''; ?>>Meses</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="natural_food">¿Alimentación natural? *</label></th>
                    <td>
                        <select id="natural_food" name="natural_food" required>
                            <option value="">Seleccionar...</option>
                            <option value="yes" <?php echo ($pet_data && $pet_data->natural_food === 'yes') ? 'selected' : ''; ?>>Sí</option>
                            <option value="no" <?php echo ($pet_data && $pet_data->natural_food === 'no') ? 'selected' : ''; ?>>No</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="activity_level">Nivel de Actividad *</label></th>
                    <td>
                        <select id="activity_level" name="activity_level" required>
                            <option value="">Seleccionar...</option>
                            <option value="Sedentario" <?php echo ($pet_data && $pet_data->activity_level === 'Sedentario') ? 'selected' : ''; ?>>Sedentario</option>
                            <option value="Activo" <?php echo ($pet_data && $pet_data->activity_level === 'Activo') ? 'selected' : ''; ?>>Activo</option>
                            <option value="Muy activo" <?php echo ($pet_data && $pet_data->activity_level === 'Muy activo') ? 'selected' : ''; ?>>Muy activo</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="reproductive_state">Estado Reproductivo *</label></th>
                    <td>
                        <select id="reproductive_state" name="reproductive_state" required>
                            <option value="">Seleccionar...</option>
                            <option value="Castrado" <?php echo ($pet_data && $pet_data->reproductive_state === 'Castrado') ? 'selected' : ''; ?>>Castrado</option>
                            <option value="Entero" <?php echo ($pet_data && $pet_data->reproductive_state === 'Entero') ? 'selected' : ''; ?>>Entero</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="body_condition">Condición Física *</label></th>
                    <td>
                        <select id="body_condition" name="body_condition" required>
                            <option value="">Seleccionar...</option>
                            <option value="Muy delgado" <?php echo ($pet_data && $pet_data->body_condition === 'Muy delgado') ? 'selected' : ''; ?>>Muy delgado</option>
                            <option value="Delgado" <?php echo ($pet_data && $pet_data->body_condition === 'Delgado') ? 'selected' : ''; ?>>Delgado</option>
                            <option value="Normal" <?php echo ($pet_data && $pet_data->body_condition === 'Normal') ? 'selected' : ''; ?>>Normal</option>
                            <option value="Obeso" <?php echo ($pet_data && $pet_data->body_condition === 'Obeso') ? 'selected' : ''; ?>>Obeso</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="weight">Peso (kg) *</label></th>
                    <td><input type="number" id="weight" name="weight" value="<?php echo $pet_data ? esc_attr($pet_data->weight) : ''; ?>" required step="0.1" min="0.1" class="regular-text"></td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="allergies">Alergias *</label></th>
                    <td>
                        <select id="allergies" name="allergies" required>
                            <option value="">Seleccionar...</option>
                            <option value="ninguna" <?php echo ($pet_data && $pet_data->allergies === 'ninguna') ? 'selected' : ''; ?>>Ninguna</option>
                            <option value="pollo" <?php echo ($pet_data && $pet_data->allergies === 'pollo') ? 'selected' : ''; ?>>Pollo</option>
                            <option value="res" <?php echo ($pet_data && $pet_data->allergies === 'res') ? 'selected' : ''; ?>>Res</option>
                            <option value="cerdo" <?php echo ($pet_data && $pet_data->allergies === 'cerdo') ? 'selected' : ''; ?>>Cerdo</option>
                            <option value="pescado" <?php echo ($pet_data && $pet_data->allergies === 'pescado') ? 'selected' : ''; ?>>Pescado</option>
                            <option value="lacteos" <?php echo ($pet_data && $pet_data->allergies === 'lacteos') ? 'selected' : ''; ?>>Lácteos</option>
                            <option value="gluten" <?php echo ($pet_data && $pet_data->allergies === 'gluten') ? 'selected' : ''; ?>>Gluten</option>
                            <option value="otro" <?php echo ($pet_data && $pet_data->allergies === 'otro') ? 'selected' : ''; ?>>Otro</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="special_needs">Necesidades Especiales *</label></th>
                    <td>
                        <select id="special_needs" name="special_needs" required>
                            <option value="">Seleccionar...</option>
                            <option value="ninguna" <?php echo ($pet_data && $pet_data->special_needs === 'ninguna') ? 'selected' : ''; ?>>Ninguna</option>
                            <option value="diabetes" <?php echo ($pet_data && $pet_data->special_needs === 'diabetes') ? 'selected' : ''; ?>>Diabetes</option>
                            <option value="renal" <?php echo ($pet_data && $pet_data->special_needs === 'renal') ? 'selected' : ''; ?>>Enfermedad renal</option>
                            <option value="hepatica" <?php echo ($pet_data && $pet_data->special_needs === 'hepatica') ? 'selected' : ''; ?>>Enfermedad hepática</option>
                            <option value="cardiaca" <?php echo ($pet_data && $pet_data->special_needs === 'cardiaca') ? 'selected' : ''; ?>>Enfermedad cardíaca</option>
                            <option value="artritis" <?php echo ($pet_data && $pet_data->special_needs === 'artritis') ? 'selected' : ''; ?>>Artritis</option>
                            <option value="sobrepeso" <?php echo ($pet_data && $pet_data->special_needs === 'sobrepeso') ? 'selected' : ''; ?>>Sobrepeso</option>
                            <option value="digestiva" <?php echo ($pet_data && $pet_data->special_needs === 'digestiva') ? 'selected' : ''; ?>>Problemas digestivos</option>
                            <option value="otro" <?php echo ($pet_data && $pet_data->special_needs === 'otro') ? 'selected' : ''; ?>>Otro</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="owner_name">Nombre del Propietario *</label></th>
                    <td><input type="text" id="owner_name" name="owner_name" value="<?php echo $pet_data ? esc_attr($pet_data->owner_name) : ''; ?>" required class="regular-text"></td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="email">Email *</label></th>
                    <td><input type="email" id="email" name="email" value="<?php echo $pet_data ? esc_attr($pet_data->email) : ''; ?>" required class="regular-text"></td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="submit_pet" class="button-primary" value="<?php echo $edit_mode ? 'Actualizar Mascota' : 'Registrar Mascota'; ?>">
                <a href="<?php echo admin_url('admin.php?page=pet-form-list'); ?>" class="button">Cancelar</a>
            </p>
        </form>
    </div>
    <?php
}

/**
 * Funciones auxiliares para cálculos
 */
function get_life_stage($age, $age_type) {
    $total_months = ($age_type === 'años') ? ($age * 12) : $age;
    
    if ($total_months <= 12) {
        return 'Cachorro';
    } elseif ($total_months <= 96) {
        return 'Adulto';
    } else {
        return 'Senior';
    }
}

function get_nutrition_factor_value($life_stage, $body_condition, $reproductive_state, $activity_level) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'nutrition_factors';
    
    $factor = $wpdb->get_var($wpdb->prepare(
        "SELECT factor FROM $table_name WHERE life_stage = %s AND body_condition = %s AND reproductive_state = %s AND activity_level = %s",
        $life_stage, $body_condition, $reproductive_state, $activity_level
    ));
    
    return $factor ? floatval($factor) : null;
}

function calculate_daily_nutrition($weight, $factor) {
    return round((pow($weight, 0.75) * 70 * $factor) / 1.5, 2);
}

/**
 * Página de administración de factores nutricionales
 */
function nutrition_factors_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'nutrition_factors';
    
    // Manejar actualización de factores
    if (isset($_POST['update_factors']) && check_admin_referer('update_nutrition_factors')) {
        $factors = $_POST['factors'];
        
        foreach ($factors as $id => $factor_data) {
            $wpdb->update(
                $table_name,
                array('factor' => floatval($factor_data['factor'])),
                array('id' => intval($id)),
                array('%f'),
                array('%d')
            );
        }
        
        echo '<div class="notice notice-success"><p>Factores actualizados correctamente.</p></div>';
    }
    
    // Obtener todos los factores
    $factors = $wpdb->get_results("SELECT * FROM $table_name ORDER BY life_stage, body_condition, reproductive_state, activity_level");
    
    ?>
    <div class="wrap">
        <h1>Factores Nutricionales</h1>
        <p>Configura los factores nutricionales que se utilizan en el cálculo de la ración diaria.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('update_nutrition_factors'); ?>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Etapa de Vida</th>
                        <th>Condición Física</th>
                        <th>Estado Reproductivo</th>
                        <th>Actividad Física</th>
                        <th>Factor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($factors as $factor): ?>
                    <tr>
                        <td><?php echo esc_html($factor->life_stage); ?></td>
                        <td><?php echo esc_html($factor->body_condition); ?></td>
                        <td><?php echo esc_html($factor->reproductive_state); ?></td>
                        <td><?php echo esc_html($factor->activity_level); ?></td>
                        <td>
                            <input type="number" 
                                   name="factors[<?php echo $factor->id; ?>][factor]" 
                                   value="<?php echo esc_attr($factor->factor); ?>" 
                                   step="0.01" 
                                   min="0.1" 
                                   max="5.0" 
                                   style="width: 80px;">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <p class="submit">
                <input type="submit" name="update_factors" class="button-primary" value="Actualizar Factores">
            </p>
        </form>
        
        <div class="nutrition-formula-info">
            <h2>Fórmula de Cálculo</h2>
            <p><strong>Ración diaria (gramos) = (Peso^0.75 × 70 × Factor) / 1.5</strong></p>
            <p>Donde el Factor se determina según la combinación de etapa de vida, condición física, estado reproductivo y nivel de actividad.</p>
        </div>
        
        <style>
        .nutrition-formula-info {
            background: #f9f9f9;
            padding: 20px;
            border-left: 4px solid #0073aa;
            margin-top: 30px;
        }
        .nutrition-formula-info h2 {
            margin-top: 0;
            color: #0073aa;
        }
        </style>
    </div>
    <?php
}

/**
 * Página de configuración
 */
function pet_form_settings_page() {
    ?>
    <div class="wrap">
        <h1>Configuración del Formulario de Mascotas</h1>
        
        <div class="pet-form-settings">
            <h2>Información del Plugin</h2>
            <p><strong>Versión:</strong> <?php echo PET_FORM_VERSION; ?></p>
            <p><strong>Shortcode:</strong> [pet-form]</p>
            
            <h2>Configuración de Edad</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Rangos de Edad</th>
                    <td>
                        <p><strong>Cachorro:</strong> 0-12 meses</p>
                        <p><strong>Adulto:</strong> 1-8 años</p>
                        <p><strong>Senior:</strong> 8+ años</p>
                    </td>
                </tr>
            </table>
            
            <h2>Uso del Bloque</h2>
            <p>Para usar el formulario de mascotas:</p>
            <ol>
                <li>Ve al editor de WordPress (Gutenberg)</li>
                <li>Busca el bloque "Formulario de Mascota"</li>
                <li>Añádelo a tu página o entrada</li>
                <li>Guarda y publica</li>
            </ol>
            
            <h2>Funcionalidades</h2>
            <ul>
                <li>✅ Formulario multi-paso para registro de mascotas</li>
                <li>✅ Cálculo automático de necesidades nutricionales</li>
                <li>✅ Gestión de razas de perros</li>
                <li>✅ Factores nutricionales configurables</li>
                <li>✅ Lista y gestión de mascotas registradas</li>
                <li>✅ Validación completa de formularios</li>
            </ul>
        </div>
    </div>
    <?php
}

/**
 * Página de gestión de razas
 */
function pet_form_breeds_management_page() {
    // Manejar descarga de razas
    if (isset($_GET['download_breeds'])) {
        $breeds = get_terms(array(
            'taxonomy' => 'dog_breed',
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ));
        
        $content = '';
        foreach ($breeds as $breed) {
            $content .= $breed->name . "\n";
        }
        
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="razas-perros-' . date('Y-m-d') . '.txt"');
        header('Content-Length: ' . strlen($content));
        echo $content;
        exit;
    }
    
    // Procesar acciones
    $message = '';
    $error = '';
    
    // Importar desde archivo TXT
    if (isset($_POST['import_breeds']) && check_admin_referer('breed_management_nonce')) {
        if (isset($_FILES['breeds_file']) && $_FILES['breeds_file']['error'] === UPLOAD_ERR_OK) {
            $file_content = file_get_contents($_FILES['breeds_file']['tmp_name']);
            if ($file_content !== false) {
                $breeds = array_filter(array_map('trim', explode("\n", $file_content)));
                $imported = 0;
                $skipped = 0;
                
                foreach ($breeds as $breed_name) {
                    if (!empty($breed_name) && !term_exists($breed_name, 'dog_breed')) {
                        $result = wp_insert_term($breed_name, 'dog_breed');
                        if (!is_wp_error($result)) {
                            $imported++;
                        }
                    } else {
                        $skipped++;
                    }
                }
                
                $message = "Importación completada: $imported razas agregadas, $skipped omitidas (ya existían o estaban vacías).";
            } else {
                $error = 'Error al leer el archivo.';
            }
        } else {
            $error = 'Error al subir el archivo.';
        }
    }
    
    // Agregar raza individual
    if (isset($_POST['add_single_breed']) && check_admin_referer('breed_management_nonce')) {
        $breed_name = trim(sanitize_text_field($_POST['breed_name']));
        if (!empty($breed_name)) {
            if (!term_exists($breed_name, 'dog_breed')) {
                $result = wp_insert_term($breed_name, 'dog_breed');
                if (!is_wp_error($result)) {
                    $message = "Raza '$breed_name' agregada correctamente.";
                } else {
                    $error = 'Error al agregar la raza: ' . $result->get_error_message();
                }
            } else {
                $error = "La raza '$breed_name' ya existe.";
            }
        } else {
            $error = 'El nombre de la raza no puede estar vacío.';
        }
    }
    
    // Eliminar todas las razas
    if (isset($_POST['delete_all_breeds']) && check_admin_referer('breed_management_nonce')) {
        $terms = get_terms(array('taxonomy' => 'dog_breed', 'hide_empty' => false));
        $deleted = 0;
        foreach ($terms as $term) {
            if (wp_delete_term($term->term_id, 'dog_breed')) {
                $deleted++;
            }
        }
        $message = "$deleted razas eliminadas.";
    }
    
    // Obtener razas actuales
    $current_breeds = get_terms(array(
        'taxonomy' => 'dog_breed',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC'
    ));
    
    ?>
    <div class="wrap">
        <h1>Gestión de Razas de Perros</h1>
        
        <?php if ($message): ?>
            <div class="notice notice-success"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="notice notice-error"><p><?php echo esc_html($error); ?></p></div>
        <?php endif; ?>
        
        <div class="breed-management-container">
            <!-- Importar desde archivo TXT -->
            <div class="breed-section">
                <h2>Importar Razas desde Archivo TXT</h2>
                <form method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field('breed_management_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Archivo TXT</th>
                            <td>
                                <input type="file" name="breeds_file" accept=".txt" required>
                                <p class="description">
                                    Selecciona un archivo .txt con una raza por línea.<br>
                                    <strong>Formato:</strong> Cada línea debe contener el nombre de una raza.
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="import_breeds" class="button-primary" value="Importar Razas">
                    </p>
                </form>
            </div>
            
            <!-- Agregar raza individual -->
            <div class="breed-section">
                <h2>Agregar Raza Individual</h2>
                <form method="post">
                    <?php wp_nonce_field('breed_management_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Nombre de la Raza</th>
                            <td>
                                <input type="text" name="breed_name" class="regular-text" placeholder="Ej: Pastor Alemán" required>
                                <p class="description">Ingresa el nombre completo de la raza.</p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="add_single_breed" class="button-primary" value="Agregar Raza">
                    </p>
                </form>
            </div>
            
            <!-- Acciones adicionales -->
            <div class="breed-section">
                <h2>Acciones Adicionales</h2>
                
                <p>
                    <a href="<?php echo admin_url('edit-tags.php?taxonomy=dog_breed&post_type=pet'); ?>" class="button">
                        Gestionar Razas (WordPress Nativo)
                    </a>
                </p>
                
                <p>
                    <a href="<?php echo admin_url('admin.php?page=pet-form-breeds&download_breeds=1'); ?>" class="button">
                        Descargar Razas Actuales (.txt)
                    </a>
                </p>
                
                <form method="post" style="display: inline;">
                    <?php wp_nonce_field('breed_management_nonce'); ?>
                    <input type="submit" name="delete_all_breeds" class="button button-secondary" 
                           value="Eliminar Todas las Razas" 
                           onclick="return confirm('¿Estás seguro de eliminar TODAS las razas? Esta acción no se puede deshacer.')">
                </form>
            </div>
            
            <!-- Lista de razas actuales -->
            <div class="breed-section">
                <h2>Razas Actuales (<?php echo count($current_breeds); ?>)</h2>
                
                <?php if (!empty($current_breeds)): ?>
                    <div class="breeds-grid">
                        <?php foreach ($current_breeds as $breed): ?>
                            <div class="breed-item">
                                <span class="breed-name"><?php echo esc_html($breed->name); ?></span>
                                <span class="breed-count">(<?php echo $breed->count; ?>)</span>
                                <a href="<?php echo admin_url('edit-tags.php?action=edit&taxonomy=dog_breed&tag_ID=' . $breed->term_id . '&post_type=pet'); ?>" 
                                   class="breed-edit">Editar</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No hay razas registradas. Usa las opciones superiores para agregar razas.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <style>
        .breed-management-container {
            max-width: 1200px;
        }
        
        .breed-section {
            background: #fff;
            border: 1px solid #ccd0d4;
            margin-bottom: 20px;
            padding: 20px;
        }
        
        .breed-section h2 {
            margin-top: 0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        
        .breeds-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 10px;
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 15px;
        }
        
        .breed-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        
        .breed-name {
            font-weight: 500;
            flex-grow: 1;
        }
        
        .breed-count {
            color: #666;
            font-size: 0.9em;
            margin-right: 10px;
        }
        
        .breed-edit {
            color: #0073aa;
            text-decoration: none;
            font-size: 0.9em;
        }
        
        .breed-edit:hover {
            text-decoration: underline;
        }
        </style>
    </div>
    <?php
}

/**
 * Registrar endpoints de la API REST
 */
function pet_form_register_rest_routes() {
    // Endpoint para obtener factores nutricionales
    register_rest_route('nutrition-calculator/v1', '/factors', array(
        'methods' => 'GET',
        'callback' => 'get_nutrition_factors_rest',
        'permission_callback' => '__return_true'
    ));
    
    // Endpoint para enviar formulario
    register_rest_route('pet-form/v1', '/submit', array(
        'methods' => 'POST',
        'callback' => 'submit_pet_form_rest',
        'permission_callback' => '__return_true'
    ));
    
    // Endpoint para obtener razas
    register_rest_route('pet-form/v1', '/breeds', array(
        'methods' => 'GET',
        'callback' => 'get_dog_breeds_rest',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'pet_form_register_rest_routes');

/**
 * Obtener factores nutricionales via REST
 */
function get_nutrition_factors_rest($request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'nutrition_factors';
    
    $factors = $wpdb->get_results("SELECT * FROM $table_name", OBJECT);
    
    $formatted_factors = array();
    foreach ($factors as $factor) {
        $key = $factor->life_stage . '-' . $factor->body_condition . '-' . $factor->reproductive_state . '-' . $factor->activity_level;
        $formatted_factors[$key] = floatval($factor->factor);
    }
    
    return rest_ensure_response($formatted_factors);
}

/**
 * Procesar envío del formulario via REST
 */
function submit_pet_form_rest($request) {
    global $wpdb;
    
    $data = $request->get_json_params();
    
    // Validar datos requeridos
    $required_fields = ['petName', 'breed', 'sex', 'age', 'ageType', 'naturalFood', 'activityLevel', 'reproductiveState', 'bodyCondition', 'weight', 'allergies', 'specialNeeds', 'ownerName', 'email'];
    
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            return new WP_Error('missing_field', 'Campo requerido faltante: ' . $field, array('status' => 400));
        }
    }
    
    // Insertar en la base de datos
    $table_name = $wpdb->prefix . 'pets';
    
    $result = $wpdb->insert(
        $table_name,
        array(
            'pet_name' => sanitize_text_field($data['petName']),
            'breed' => sanitize_text_field($data['breed']),
            'sex' => sanitize_text_field($data['sex']),
            'age' => intval($data['age']),
            'age_type' => sanitize_text_field($data['ageType']),
            'natural_food' => sanitize_text_field($data['naturalFood']),
            'activity_level' => sanitize_text_field($data['activityLevel']),
            'reproductive_state' => sanitize_text_field($data['reproductiveState']),
            'body_condition' => sanitize_text_field($data['bodyCondition']),
            'weight' => floatval($data['weight']),
            'allergies' => sanitize_text_field($data['allergies']),
            'special_needs' => sanitize_text_field($data['specialNeeds']),
            'owner_name' => sanitize_text_field($data['ownerName']),
            'email' => sanitize_email($data['email']),
            'life_stage' => sanitize_text_field($data['lifeStage']),
            'nutrition_factor' => floatval($data['factor']),
            'daily_nutrition' => floatval($data['dailyNutrition'])
        ),
        array(
            '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%f', 
            '%s', '%s', '%s', '%s', '%s', '%f', '%f'
        )
    );
    
    if ($result === false) {
        return new WP_Error('db_error', 'Error al guardar en la base de datos', array('status' => 500));
    }
    
    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Mascota registrada correctamente',
        'pet_id' => $wpdb->insert_id
    ));
}

/**
 * Obtener razas de perros via REST
 */
function get_dog_breeds_rest($request) {
    return rest_ensure_response(get_dog_breeds_list());
}

/**
 * Agregar shortcode para el formulario
 */
function pet_form_shortcode($atts) {
    wp_enqueue_script('pet-form-editor');
    wp_enqueue_style('pet-form-style');
    
    return '<div id="pet-form-block"></div>';
}
add_shortcode('pet-form', 'pet_form_shortcode');