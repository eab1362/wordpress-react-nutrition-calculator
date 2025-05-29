# Calculadora Nutricional para Perros - WordPress Plugin

Este es un plugin de WordPress que implementa el **Caso de Uso CU-01: Calcular valor diario nutricional** mediante un bloque de Gutenberg desarrollado en React para calcular factores nutricionales para perros basándose en edad, condición corporal, nivel de actividad y peso.

## 🐕 Características Principales

### Formulario Interactivo
- **Información de la mascota**: Nombre, raza, edad (años/meses), peso
- **Condición física**: Muy delgado, Delgado, Normal, Obeso
- **Estado reproductivo**: Castrado, Entero
- **Nivel de actividad**: Sedentario, Activo, Muy activo
- **Información de contacto**: Nombre del propietario, email

### Cálculo Automático
- **Determinación automática de etapa de vida**:
  - Cachorro: 0-12 meses
  - Adulto: 1-8 años  
  - Senior: 8+ años
- **Fórmula aplicada**: `(Peso^0.75 × 70 × Factor) / 1.5`
- **Resultado en gramos** de ración diaria recomendada

### Panel de Administración
- **Gestión de factores nutricionales** configurable desde WordPress Admin
- **Dashboard con estadísticas** del sistema
- **Gestión de razas de perros** con interfaz administrativa
- **Configuración completa** del plugin

## 📊 Tabla de Factores Nutricionales

El plugin incluye una tabla completa de 72 factores nutricionales que cubren todas las combinaciones de:

| Etapa de Vida | Condición Física | Estado Reproductivo | Actividad Física | Factor |
|---------------|------------------|--------------------|-----------------| -------|
| Cachorro      | Muy delgado      | Castrado           | Sedentario      | 1.9    |
| Cachorro      | Muy delgado      | Castrado           | Activo          | 2.3    |
| Cachorro      | Muy delgado      | Castrado           | Muy activo      | 2.6    |
| ...           | ...              | ...                | ...             | ...    |

*Nota: La tabla completa incluye todas las combinaciones para las 3 etapas de vida (Cachorro, Adulto, Senior)*

## 🏗️ Estructura del Proyecto

```
my-react-form-block/
├── src/
│   ├── index.js          # Componente React principal con lógica de cálculo
│   └── style.css         # Estilos modernos y responsive
├── breeds.txt            # Lista de razas de perros
├── my-react-form-block.php # Plugin principal con panel de administración
├── package.json          # Dependencias del proyecto
└── README.md            # Este archivo
```

## ⚙️ Instalación

1. **Clona este repositorio** en tu directorio de plugins de WordPress:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/eab1362/wordpress-react-nutrition-calculator.git
   ```

2. **Instala las dependencias**:
   ```bash
   cd wordpress-react-nutrition-calculator
   npm install
   ```

3. **Compila el proyecto**:
   ```bash
   npm run build
   ```

4. **Activa el plugin** desde el panel de administración de WordPress

## 🎯 Uso del Plugin

### Como Bloque de Gutenberg
1. En el editor de WordPress, busca el bloque **"Calculadora Nutricional"**
2. Añádelo a tu página o entrada
3. Los usuarios podrán completar el formulario y obtener el cálculo nutricional

### Como Shortcode
Usa el shortcode `[nutrition-calculator]` en cualquier página o widget.

### Panel de Administración
Accede a **"Calculadora Nutricional"** en el menú de WordPress para:
- **Configurar factores nutricionales** específicos
- **Gestionar razas de perros** disponibles
- **Ver estadísticas** del sistema

## 🔧 Validaciones Implementadas

### Flujos Alternativos del CU-01

- **FA-1: Edad fuera de rango**: Validación de rangos de edad apropiados
- **FA-2: Peso no válido**: Validación de peso mayor a 0 kg
- **FA-3: Campos obligatorios**: Validación de todos los campos requeridos
- **Desactivación automática** del botón de cálculo hasta completar datos válidos

## 🌐 API REST Endpoints

El plugin expone los siguientes endpoints:

- `GET /wp-json/nutrition-calculator/v1/factors` - Obtener factores nutricionales
- `POST /wp-json/nutrition-calculator/v1/calculate` - Guardar cálculo realizado
- `GET /wp-json/pet-form/v1/breeds` - Obtener lista de razas

## 🎨 Características de UI/UX

- **Diseño moderno y responsive** compatible con móviles
- **Formulario en secciones** para mejor organización
- **Validación en tiempo real** con mensajes de error claros
- **Resultado destacado** con información detallada del cálculo
- **Animaciones suaves** para mejor experiencia de usuario

## 🛠️ Desarrollo

Para trabajar en modo desarrollo:

```bash
npm start
```

### Estructura de Archivos de Desarrollo
- `src/index.js` - Componente React principal
- `src/style.css` - Estilos CSS personalizados
- `my-react-form-block.php` - Lógica del plugin WordPress

## 📱 Responsive Design

El formulario está optimizado para:
- **Desktop**: Layout de 2 columnas para campos relacionados
- **Tablet**: Adaptación automática del diseño
- **Mobile**: Layout vertical con botones de ancho completo

## 🔐 Seguridad

- **Validación server-side** de todos los datos
- **Sanitización** de inputs del usuario
- **Nonces de WordPress** para peticiones AJAX
- **Permisos de administrador** para configuración de factores

## 🚀 Versiones

### v2.0 - Calculadora Nutricional
- ✅ Implementación completa del Caso de Uso CU-01
- ✅ Panel de administración para factores nutricionales
- ✅ Fórmula de cálculo: (Peso^0.75 × 70 × Factor) / 1.5
- ✅ Validaciones completas según especificaciones
- ✅ Interface moderna y responsive

### v1.0 - Formulario Básico
- Formulario inicial para mascotas BARF

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Abre un **issue** para discutir cambios importantes
2. Realiza un **fork** del repositorio  
3. Crea una **rama** para tu feature
4. Envía un **pull request** con descripción detallada

## 📄 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo LICENSE para detalles.

## 🐾 Desarrollado por Forever Dog

*Calculando la nutrición perfecta para cada etapa de vida de tu mascota.* 