<?php
/**
 * Plugin Name: Calculadora Nutricional para Perros
 * Description: Un bloque de Gutenberg que calcula el valor diario nutricional para perros basado en edad, condición corporal, nivel de actividad y peso.
 * Version: 2.0
 * Author: Forever Dog
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('NUTRITION_CALCULATOR_VERSION', '2.0');
define('NUTRITION_CALCULATOR_PATH', plugin_dir_path(__FILE__));
define('NUTRITION_CALCULATOR_URL', plugin_dir_url(__FILE__));

/**
 * Activación del plugin
 */
function nutrition_calculator_activate() {
    // Crear tabla de factores nutricionales
    create_nutrition_factors_table();
    
    // Insertar factores por defecto
    insert_default_nutrition_factors();
    
    // Registrar el tipo de contenido personalizado
    register_dog_post_type();
    
    // Limpiar caché de reescritura
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'nutrition_calculator_activate');

/**
 * Desactivación del plugin
 */
function nutrition_calculator_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'nutrition_calculator_deactivate');

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
 * Registrar custom post type para perros (mantenido para compatibilidad)
 */
function register_dog_post_type() {
    $labels = array(
        'name'               => _x('Cálculos Nutricionales', 'post type general name'),
        'singular_name'      => _x('Cálculo Nutricional', 'post type singular name'),
        'menu_name'          => _x('Cálculos', 'admin menu'),
        'name_admin_bar'     => _x('Cálculo', 'add new on admin bar'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => 'nutrition-calculator',
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'supports'           => array('title'),
        'show_in_rest'       => true,
    );

    register_post_type('nutrition_calc', $args);
}

/**
 * Registrar la taxonomía de razas de perros
 */
function register_dog_breed_taxonomy() {
    $labels = array(
        'name'              => _x('Razas de Perros', 'taxonomy general name'),
        'singular_name'     => _x('Raza de Perro', 'taxonomy singular name'),
        'menu_name'         => __('Razas de Perros'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'breed'),
        'show_in_rest'      => true,
        'show_in_menu'      => 'nutrition-calculator',
    );

    register_taxonomy('dog_breed', array('nutrition_calc'), $args);
    create_default_dog_breeds();
}
add_action('init', 'register_dog_breed_taxonomy');

/**
 * Crear razas de perros por defecto
 */
function create_default_dog_breeds() {
    // Verificar si ya existen términos
    $existing_terms = get_terms(array(
        'taxonomy' => 'dog_breed',
        'hide_empty' => false,
        'number' => 1
    ));
    
    if (!empty($existing_terms)) {
        return; // Ya existen razas
    }
    
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

/**
 * Registrar el bloque de Gutenberg
 */
function nutrition_calculator_register_block() {
    // Registrar el script del editor
    wp_register_script(
        'nutrition-calculator-editor',
        plugins_url('build/index.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor'),
        filemtime(plugin_dir_path(__FILE__) . 'build/index.js')
    );

    // Registrar los estilos
    wp_register_style(
        'nutrition-calculator-style',
        plugins_url('src/style.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'src/style.css')
    );

    // Agregar datos de razas como variable global
    wp_localize_script(
        'nutrition-calculator-editor',
        'petFormSettings',
        array(
            'dogBreeds' => get_dog_breeds_list(),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pet_form_nonce')
        )
    );

    // Registrar el bloque
    register_block_type('nutrition-calculator/calculator-form', array(
        'editor_script' => 'nutrition-calculator-editor',
        'style' => 'nutrition-calculator-style',
        'render_callback' => 'nutrition_calculator_render_block'
    ));
}
add_action('init', 'nutrition_calculator_register_block');

/**
 * Función de renderizado del bloque
 */
function nutrition_calculator_render_block($attributes) {
    wp_enqueue_script('nutrition-calculator-editor');
    wp_enqueue_style('nutrition-calculator-style');
    
    return '<div id="nutrition-calculator-block"></div>';
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
 * Agregar página de administración
 */
function nutrition_calculator_admin_menu() {
    add_menu_page(
        'Calculadora Nutricional',
        'Calculadora Nutricional',
        'manage_options',
        'nutrition-calculator',
        'nutrition_calculator_admin_page',
        'dashicons-chart-line',
        25
    );
    
    add_submenu_page(
        'nutrition-calculator',
        'Factores Nutricionales',
        'Factores Nutricionales',
        'manage_options',
        'nutrition-factors',
        'nutrition_factors_admin_page'
    );
    
    add_submenu_page(
        'nutrition-calculator',
        'Configuración',
        'Configuración',
        'manage_options',
        'nutrition-settings',
        'nutrition_settings_admin_page'
    );
}
add_action('admin_menu', 'nutrition_calculator_admin_menu');

/**
 * Página principal de administración
 */
function nutrition_calculator_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'nutrition_factors';
    $total_factors = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    
    ?>
    <div class="wrap">
        <h1>Calculadora Nutricional</h1>
        
        <div class="nutrition-admin-dashboard">
            <div class="dashboard-stats">
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
                <a href="<?php echo admin_url('admin.php?page=nutrition-factors'); ?>" class="button button-primary">
                    Gestionar Factores Nutricionales
                </a>
                <a href="<?php echo admin_url('edit-tags.php?taxonomy=dog_breed&post_type=nutrition_calc'); ?>" class="button">
                    Gestionar Razas
                </a>
            </div>
        </div>
        
        <style>
        .nutrition-admin-dashboard { margin-top: 20px; }
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
function nutrition_settings_admin_page() {
    ?>
    <div class="wrap">
        <h1>Configuración de la Calculadora</h1>
        
        <div class="nutrition-settings">
            <h2>Información del Plugin</h2>
            <p><strong>Versión:</strong> <?php echo NUTRITION_CALCULATOR_VERSION; ?></p>
            <p><strong>Shortcode:</strong> [nutrition-calculator]</p>
            
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
            <p>Para usar la calculadora nutricional:</p>
            <ol>
                <li>Ve al editor de WordPress (Gutenberg)</li>
                <li>Busca el bloque "Calculadora Nutricional"</li>
                <li>Añádelo a tu página o entrada</li>
                <li>Guarda y publica</li>
            </ol>
        </div>
    </div>
    <?php
}

/**
 * Registrar endpoints de la API REST
 */
function nutrition_calculator_register_rest_routes() {
    // Endpoint para obtener factores nutricionales
    register_rest_route('nutrition-calculator/v1', '/factors', array(
        'methods' => 'GET',
        'callback' => 'get_nutrition_factors_rest',
        'permission_callback' => '__return_true'
    ));
    
    // Endpoint para guardar cálculo
    register_rest_route('nutrition-calculator/v1', '/calculate', array(
        'methods' => 'POST',
        'callback' => 'save_nutrition_calculation_rest',
        'permission_callback' => '__return_true'
    ));
    
    // Endpoint para obtener razas
    register_rest_route('pet-form/v1', '/breeds', array(
        'methods' => 'GET',
        'callback' => 'get_dog_breeds_rest',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'nutrition_calculator_register_rest_routes');

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
 * Guardar cálculo nutricional via REST
 */
function save_nutrition_calculation_rest($request) {
    $data = $request->get_json_params();
    
    // Aquí puedes guardar el cálculo en la base de datos si lo deseas
    // Por ahora solo retornamos success
    
    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Cálculo guardado correctamente'
    ));
}

/**
 * Obtener razas de perros via REST
 */
function get_dog_breeds_rest($request) {
    return rest_ensure_response(get_dog_breeds_list());
}

/**
 * Agregar shortcode para la calculadora
 */
function nutrition_calculator_shortcode($atts) {
    wp_enqueue_script('nutrition-calculator-editor');
    wp_enqueue_style('nutrition-calculator-style');
    
    return '<div id="nutrition-calculator-block"></div>';
}
add_shortcode('nutrition-calculator', 'nutrition_calculator_shortcode');
?> 