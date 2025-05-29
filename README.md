# Formulario React para WordPress - Calculadora de Factor Nutricional

Este es un plugin de WordPress que incluye un bloque de Gutenberg desarrollado en React para calcular factores nutricionales para perros basándose en diferentes parámetros.

## Características

- **Formulario interactivo** con campos para:
  - Raza del perro (con autocompletado)
  - Etapa de vida
  - Condición física
  - Estado reproductivo
  - Actividad física

- **Cálculo automático** del factor nutricional basado en una tabla de valores predefinida

- **Integración con WordPress** como bloque de Gutenberg

## Estructura del Proyecto

```
my-react-form-block/
├── src/
│   ├── index.js          # Componente principal React
│   └── style.css         # Estilos del formulario
├── breeds.txt            # Lista de razas de perros
├── my-react-form-block.php # Plugin principal de WordPress
├── package.json          # Dependencias del proyecto
└── README.md            # Este archivo
```

## Instalación

1. Clona este repositorio en tu directorio de plugins de WordPress:
   ```bash
   cd wp-content/plugins/
   git clone [URL_DEL_REPO]
   ```

2. Instala las dependencias:
   ```bash
   cd my-react-form-block
   npm install
   ```

3. Compila el proyecto:
   ```bash
   npm run build
   ```

4. Activa el plugin desde el panel de administración de WordPress

## Desarrollo

Para trabajar en modo desarrollo:

```bash
npm start
```

## Uso

1. En el editor de WordPress, busca el bloque "Formulario React"
2. Añádelo a tu página o entrada
3. Los usuarios podrán completar el formulario y obtener el factor nutricional calculado

## Tecnologías Utilizadas

- React
- WordPress Gutenberg API
- CSS3
- PHP (para la integración con WordPress)

## Contribuir

Las contribuciones son bienvenidas. Por favor, abre un issue primero para discutir los cambios que te gustaría hacer.

## Licencia

Este proyecto está bajo la Licencia MIT. 