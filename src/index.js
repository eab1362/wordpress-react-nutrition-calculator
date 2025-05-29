import { registerBlockType } from '@wordpress/blocks';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import Autocomplete from '@mui/material/Autocomplete';
import TextField from '@mui/material/TextField';
import FormControl from '@mui/material/FormControl';
import InputLabel from '@mui/material/InputLabel';
import Select from '@mui/material/Select';
import MenuItem from '@mui/material/MenuItem';

// Importar estilos
import './style.css';

// Opciones de selección basadas en el modelo Django
const BODY_IMAGES = [
  { value: 'muy_delgado', label: 'Muy delgado' },
  { value: 'delgado', label: 'Delgado' },
  { value: 'peso_ideal', label: 'Peso ideal' },
  { value: 'sobrepeso', label: 'Sobrepeso' },
];

const REPRODUCTIVE_STATES = [
  { value: 'castrado', label: 'Castrado' },
  { value: 'entero', label: 'Entero' },
];

const ALLERGIES_DATA = [
  { value: 'Ninguna', label: 'Ninguna' },
  { value: 'Pollo', label: 'Pollo' },
  { value: 'Res', label: 'Res' },
  { value: 'Cerdo', label: 'Cerdo' },
  { value: 'Granos', label: 'Granos' },
  { value: 'Huevo', label: 'Huevo' },
  { value: 'Otro', label: 'Otro' },
  { value: 'Pescado', label: 'Pescado' },
];

const SPECIAL_NEEDS_DATA = [
  { value: 'Ninguna', label: 'Ninguna' },
  { value: 'Digestión', label: 'Digestión' },
  { value: 'Piel', label: 'Piel' },
  { value: 'Control de peso', label: 'Control de peso' },
  { value: 'Cardiovascular', label: 'Cardiovascular' },
  { value: 'Ariticulaciones', label: 'Ariticulaciones' },
  { value: 'Urinario-renal', label: 'Urinario-renal' },
  { value: 'Aliento', label: 'Aliento' },
  { value: 'Ansiedad', label: 'Ansiedad' },
  { value: 'Hiperactividad', label: 'Hiperactividad' },
  { value: 'Cancer', label: 'Cancer' },
  { value: 'Diabetes', label: 'Diabetes' },
  { value: 'Ganancia de peso', label: 'Ganancia de peso' },
];

const AGE_TYPES = [
  { value: 'meses', label: 'Meses' },
  { value: 'años', label: 'Años' },
];

const ACTIVITY_LEVELS = [
  { value: 'baja', label: 'Baja' },
  { value: 'moderada', label: 'Moderada' },
  { value: 'alta', label: 'Alta' },
];

// Componente React para el formulario de mascotas
const PetFormBlock = () => {
  // Control del paso actual
  const [step, setStep] = useState(1);
  const totalSteps = 3;

  // Estado para la lista de razas
  const [dogBreeds, setDogBreeds] = useState([]);
  const [filteredBreeds, setFilteredBreeds] = useState([]);
  const [breedSearch, setBreedSearch] = useState('');
  const [isLoadingBreeds, setIsLoadingBreeds] = useState(true);

  // Estado para manejar carga y mensajes
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [responseMessage, setResponseMessage] = useState(null);
  const [isSuccess, setIsSuccess] = useState(false);

  // Datos del formulario
  const [formData, setFormData] = useState({
    name: '',
    age: '',
    age_type: 'años',
    breed: '',
    body_image: '',
    reproductive_state: '',
    activity_level: '',
    weight: '',
    allergies: 'Ninguna',
    special_needs: 'Ninguna',
    email: '',
    ownerName: '',
  });

  // Estado para errores de validación
  const [errors, setErrors] = useState({
    name: '',
    age: '',
    breed: '',
    body_image: '',
    reproductive_state: '',
    activity_level: '',
    weight: '',
    email: '',
    ownerName: '',
  });

  // Estado para controlar si un campo ha sido tocado
  const [touched, setTouched] = useState({
    name: false,
    age: false,
    breed: false,
    body_image: false,
    reproductive_state: false,
    activity_level: false,
    weight: false,
    email: false,
    ownerName: false,
  });

  // Cargar la lista de razas al iniciar el componente
  useEffect(() => {
    // Primero intentamos obtener las razas desde la variable global (si estamos en el editor)
    if (typeof petFormSettings !== 'undefined' && petFormSettings.dogBreeds) {
      setDogBreeds(petFormSettings.dogBreeds);
      setFilteredBreeds(petFormSettings.dogBreeds);
      setIsLoadingBreeds(false);
    } else {
      // Si no está disponible, hacer una petición a la API REST
      apiFetch({ path: '/pet-form/v1/breeds' })
        .then(breeds => {
          setDogBreeds(breeds);
          setFilteredBreeds(breeds);
          setIsLoadingBreeds(false);
        })
        .catch(error => {
          console.error('Error al cargar razas:', error);
          // En caso de error, establecer algunas razas predeterminadas
          const defaultBreeds = [
            { value: 'labrador', label: 'Labrador Retriever' },
            { value: 'golden', label: 'Golden Retriever' },
            { value: 'otro', label: 'Otro' }
          ];
          setDogBreeds(defaultBreeds);
          setFilteredBreeds(defaultBreeds);
          setIsLoadingBreeds(false);
        });
    }
  }, []);

  // Filtrar razas cuando se cambia la búsqueda
  useEffect(() => {
    if (breedSearch.trim() === '') {
      setFilteredBreeds(dogBreeds);
    } else {
      const searchTerm = breedSearch.toLowerCase();
      const filtered = dogBreeds.filter(breed => 
        breed.label.toLowerCase().includes(searchTerm)
      );
      setFilteredBreeds(filtered);
    }
  }, [breedSearch, dogBreeds]);

  // Función para manejar la búsqueda de razas
  const handleBreedSearch = (event) => {
    setBreedSearch(event.target.value);
  };

  // Función para actualizar datos
  const handleChange = (field, value) => {
    setFormData({ ...formData, [field]: value });
    
    // Validar el campo cuando cambia
    validateField(field, value);
    
    // Reset de mensajes al modificar el formulario
    if (responseMessage) {
      setResponseMessage(null);
      setIsSuccess(false);
    }
  };

  // Marcar campo como tocado cuando pierde el foco
  const handleBlur = (field) => {
    setTouched({
      ...touched,
      [field]: true
    });
    validateField(field, formData[field]);
  };

  // Función para validar un campo específico
  const validateField = (field, value) => {
    let errorMessage = '';
    
    switch (field) {
      case 'name':
        if (!value.trim()) {
          errorMessage = __('El nombre de la mascota es obligatorio', 'mi-block');
        } else if (value.trim().length < 2) {
          errorMessage = __('El nombre debe tener al menos 2 caracteres', 'mi-block');
        } else if (value.trim().length > 50) {
          errorMessage = __('El nombre no puede exceder los 50 caracteres', 'mi-block');
        }
        break;
        
      case 'breed':
        if (!value) {
          errorMessage = __('Seleccione una raza', 'mi-block');
        }
        break;
        
      case 'age':
        if (!value) {
          errorMessage = __('La edad es obligatoria', 'mi-block');
        } else if (isNaN(value) || parseInt(value) < 0) {
          errorMessage = __('La edad debe ser un número positivo', 'mi-block');
        } else if (formData.age_type === 'años' && parseInt(value) > 25) {
          errorMessage = __('La edad parece demasiado alta para un perro', 'mi-block');
        } else if (formData.age_type === 'meses' && parseInt(value) > 24) {
          errorMessage = __('Para edades mayores a 24 meses, use años', 'mi-block');
        }
        break;
        
      case 'weight':
        if (!value) {
          errorMessage = __('El peso es obligatorio', 'mi-block');
        } else if (isNaN(value) || parseFloat(value) <= 0) {
          errorMessage = __('El peso debe ser un número positivo', 'mi-block');
        } else if (parseFloat(value) > 100) {
          errorMessage = __('El peso parece demasiado alto para un perro', 'mi-block');
        }
        break;
        
      case 'body_image':
        if (!value) {
          errorMessage = __('Seleccione la condición corporal', 'mi-block');
        }
        break;
        
      case 'reproductive_state':
        if (!value) {
          errorMessage = __('Seleccione el estado reproductivo', 'mi-block');
        }
        break;
        
      case 'activity_level':
        if (!value) {
          errorMessage = __('Seleccione el nivel de actividad', 'mi-block');
        }
        break;
        
      case 'email':
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!value.trim()) {
          errorMessage = __('El email es obligatorio', 'mi-block');
        } else if (!emailRegex.test(value)) {
          errorMessage = __('Ingrese un email válido', 'mi-block');
        }
        break;
        
      case 'ownerName':
        if (!value.trim()) {
          errorMessage = __('Su nombre es obligatorio', 'mi-block');
        } else if (value.trim().length < 2) {
          errorMessage = __('El nombre debe tener al menos 2 caracteres', 'mi-block');
        }
        break;
        
      default:
        break;
    }
    
    setErrors(prev => ({
      ...prev,
      [field]: errorMessage
    }));
    
    return errorMessage === '';
  };

  // Validar todos los campos del paso actual
  const validateStep = (currentStep) => {
    let isValid = true;
    let newErrors = { ...errors };
    let newTouched = { ...touched };
    
    // Campos a validar según el paso
    const fieldsToValidate = {
      1: ['name', 'breed', 'age', 'weight'],
      2: ['body_image', 'reproductive_state', 'activity_level'],
      3: ['email', 'ownerName'],
    };
    
    // Validar cada campo del paso actual
    fieldsToValidate[currentStep].forEach(field => {
      newTouched[field] = true;
      if (!validateField(field, formData[field])) {
        isValid = false;
      }
    });
    
    setTouched(newTouched);
    return isValid;
  };

  // Navegación de pasos
  const nextStep = () => {
    // Solo avanzar si todos los campos del paso actual son válidos
    if (validateStep(step)) {
      setStep(step + 1);
    }
  };
  
  const prevStep = () => setStep(step - 1);

  // Función para enviar el formulario
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    // Validación final de todos los campos
    if (!validateStep(3)) {
      setResponseMessage(__('Por favor, corrija los errores antes de enviar.', 'mi-block'));
      return;
    }
    
    setIsSubmitting(true);
    setResponseMessage(__('Enviando información...', 'mi-block'));
    
    try {
      // Enviar datos a la API REST
      const response = await apiFetch({
        path: '/pet-form/v1/submit',
        method: 'POST',
        data: formData,
      });
      
      // Procesar respuesta exitosa
      setIsSuccess(true);
      setResponseMessage(__('¡Gracias! Hemos recibido tu información. Te enviaremos la dieta recomendada pronto.', 'mi-block'));
      
      // En entorno de desarrollo, mostrar los datos
      if (process.env.NODE_ENV === 'development') {
        console.log('Datos enviados:', formData);
        console.log('Respuesta:', response);
      }
      
      // Reset form after successful submission
      setTimeout(() => {
        setFormData({
          name: '',
          age: '',
          age_type: 'años',
          breed: '',
          body_image: '',
          reproductive_state: '',
          activity_level: '',
          weight: '',
          allergies: 'Ninguna',
          special_needs: 'Ninguna',
          email: '',
          ownerName: '',
        });
        setStep(1);
        setResponseMessage(null);
        setTouched({});
        setErrors({});
      }, 5000);
      
    } catch (error) {
      // Manejar error
      setIsSuccess(false);
      setResponseMessage(__('Ocurrió un error al enviar el formulario. Por favor, intenta de nuevo.', 'mi-block'));
      console.error('Error al enviar formulario:', error);
    } finally {
      setIsSubmitting(false);
    }
  };

  // Si se ha enviado con éxito, mostrar mensaje de agradecimiento
  if (isSuccess && responseMessage) {
    return (
      <div className="pet-form-container">
        <div className="form-success-message">
          <h3>{__('¡Formulario enviado con éxito!', 'mi-block')}</h3>
          <p>{responseMessage}</p>
        </div>
      </div>
    );
  }

  return (
    <div className="pet-form-container">
      <form onSubmit={handleSubmit}>
        {/* Paso 1: Información básica */}
        <div className={`form-step ${step === 1 ? 'active' : ''}`}>
          <div className="form-header">
            <h3>{ __('Información básica de tu mascota', 'mi-block') }</h3>
            <p>{ __('Ayúdanos a conocer a tu Forever Dog', 'mi-block') }</p>
          </div>
          <span className="step-indicator">{step} de {totalSteps}</span>

          <div className={`form-group ${touched.name && errors.name ? 'has-error' : ''}`}>
            <label htmlFor="name">{ __('Nombre de tu mascota', 'mi-block') }</label>
            <input
              type="text"
              id="name"
              value={formData.name}
              onChange={(e) => handleChange('name', e.target.value)}
              onBlur={() => handleBlur('name')}
              required
            />
            {touched.name && errors.name && <span className="error-message">{errors.name}</span>}
          </div>

          <div className={`form-group ${touched.breed && errors.breed ? 'has-error' : ''}`}>
            <label htmlFor="breed">{ __('Raza', 'mi-block') }</label>
            <Autocomplete
              id="breed"
              options={dogBreeds}
              getOptionLabel={(option) => option.label || ''}
              loading={isLoadingBreeds}
              value={dogBreeds.find(b => b.value === formData.breed) || null}
              onChange={(_, newValue) => {
                handleChange('breed', newValue ? newValue.value : '');
                setTouched({ ...touched, breed: true });
              }}
              renderInput={(params) => (
                <TextField
                  {...params}
                  label={__('Buscar y seleccionar raza...', 'mi-block')}
                  variant="outlined"
                  error={touched.breed && Boolean(errors.breed)}
                  helperText={touched.breed && errors.breed ? errors.breed : ''}
                  required
                />
              )}
              isOptionEqualToValue={(option, value) => option.value === value.value}
              noOptionsText={__('No se encontraron razas', 'mi-block')}
              disabled={isLoadingBreeds}
            />
          </div>

          <div className={`form-row`}>
            <div className={`form-group ${touched.age && errors.age ? 'has-error' : ''}`}>
              <TextField
                id="age"
                label={__('Edad', 'mi-block')}
                type="number"
                value={formData.age}
                onChange={(e) => handleChange('age', e.target.value)}
                onBlur={() => handleBlur('age')}
                required
                error={touched.age && Boolean(errors.age)}
                helperText={touched.age && errors.age ? errors.age : ''}
                variant="outlined"
                fullWidth
                InputProps={{
                  inputProps: { min: 0 },
                }}
              />
            </div>

            <div className="form-group">
              <FormControl 
                fullWidth 
                variant="outlined"
                error={touched.age_type && Boolean(errors.age_type)}
                style={{ width: '100%' }}
              >
                <InputLabel id="age_type-label">{ __('Tipo', 'mi-block') }</InputLabel>
                <Select
                  labelId="age_type-label"
                  id="age_type"
                  value={formData.age_type}
                  label={__('Tipo', 'mi-block')}
                  onChange={(e) => {
                    handleChange('age_type', e.target.value);
                    // Revalidar la edad cuando cambia el tipo
                    validateField('age', formData.age);
                  }}
                  required
                  onBlur={() => handleBlur('age_type')}
                >
                  {AGE_TYPES.map(option => (
                    <MenuItem key={option.value} value={option.value}>
                      {option.label}
                    </MenuItem>
                  ))}
                </Select>
                {touched.age_type && errors.age_type && <span className="error-message">{errors.age_type}</span>}
              </FormControl>
            </div>
          </div>

          <div className={`form-group ${touched.weight && errors.weight ? 'has-error' : ''}`}>
            <label htmlFor="weight">{ __('Peso (kg)', 'mi-block') }</label>
            <input
              type="number"
              id="weight"
              step="0.01"
              min="0"
              value={formData.weight}
              onChange={(e) => handleChange('weight', e.target.value)}
              onBlur={() => handleBlur('weight')}
              required
            />
            {touched.weight && errors.weight && <span className="error-message">{errors.weight}</span>}
          </div>

          <div className="button-row">
            <button 
              type="button" 
              className="btn btn-primary" 
              onClick={nextStep}
            >
              { __('Siguiente', 'mi-block') }
            </button>
          </div>
        </div>

        {/* Paso 2: Condición física y estado */}
        <div className={`form-step ${step === 2 ? 'active' : ''}`}>
          <div className="form-header">
            <h3>{ __('Condición física y estado', 'mi-block') }</h3>
            <p>{ __('Cuéntanos más sobre el estado de tu mascota', 'mi-block') }</p>
          </div>
          <span className="step-indicator">{step} de {totalSteps}</span>

          <div className={`form-group ${touched.body_image && errors.body_image ? 'has-error' : ''}`}>
            <label>{ __('Condición corporal', 'mi-block') }</label>
            <div className="body-image-selector">
              {BODY_IMAGES.map((option) => (
                <div 
                  key={option.value} 
                  className={`body-image-option ${formData.body_image === option.value ? 'selected' : ''}`} 
                  onClick={() => {
                    handleChange('body_image', option.value);
                    setTouched({...touched, body_image: true});
                  }}
                >
                  <div className="body-image-icon">{/* Aquí iría un icono o imagen SVG */}</div>
                  <span>{option.label}</span>
                </div>
              ))}
            </div>
            {touched.body_image && errors.body_image && <span className="error-message">{errors.body_image}</span>}
          </div>

          <div className={`form-group ${touched.reproductive_state && errors.reproductive_state ? 'has-error' : ''}`}>
            <label>{ __('Estado reproductivo', 'mi-block') }</label>
            <div className="reproductive-state-selector">
              {REPRODUCTIVE_STATES.map((option) => (
                <div 
                  key={option.value} 
                  className={`reproductive-state-option ${formData.reproductive_state === option.value ? 'selected' : ''}`}
                  onClick={() => {
                    handleChange('reproductive_state', option.value);
                    setTouched({...touched, reproductive_state: true});
                  }}
                >
                  <span>{option.label}</span>
                </div>
              ))}
            </div>
            {touched.reproductive_state && errors.reproductive_state && <span className="error-message">{errors.reproductive_state}</span>}
          </div>

          <div className={`form-group ${touched.activity_level && errors.activity_level ? 'has-error' : ''}`}>
            <FormControl 
              fullWidth 
              variant="outlined"
              error={touched.activity_level && Boolean(errors.activity_level)}
            >
              <InputLabel id="activity_level-label">{ __('Nivel de actividad', 'mi-block') }</InputLabel>
              <Select
                labelId="activity_level-label"
                id="activity_level"
                value={formData.activity_level}
                label={__('Nivel de actividad', 'mi-block')}
                onChange={(e) => handleChange('activity_level', e.target.value)}
                onBlur={() => handleBlur('activity_level')}
                required
              >
                <MenuItem value="" disabled>{ __('Selecciona...', 'mi-block') }</MenuItem>
                {ACTIVITY_LEVELS.map(option => (
                  <MenuItem key={option.value} value={option.value}>
                    {option.label}
                  </MenuItem>
                ))}
              </Select>
              {touched.activity_level && errors.activity_level && <span className="error-message">{errors.activity_level}</span>}
            </FormControl>
          </div>

          <div className="button-row">
            <button type="button" className="btn btn-secondary" onClick={prevStep}>
              { __('Anterior', 'mi-block') }
            </button>
            <button type="button" className="btn btn-primary" onClick={nextStep}>
              { __('Siguiente', 'mi-block') }
            </button>
          </div>
        </div>

        {/* Paso 3: Necesidades especiales y contacto */}
        <div className={`form-step ${step === 3 ? 'active' : ''}`}>
          <div className="form-header">
            <h3>{ __('Necesidades especiales y contacto', 'mi-block') }</h3>
            <p>{ __('Información adicional para la dieta ideal', 'mi-block') }</p>
          </div>
          <span className="step-indicator">{step} de {totalSteps}</span>

          <div className="form-group">
            <FormControl 
              fullWidth 
              variant="outlined"
              error={touched.allergies && Boolean(errors.allergies)}
            >
              <InputLabel id="allergies-label">{ __('Alergias', 'mi-block') }</InputLabel>
              <Select
                labelId="allergies-label"
                id="allergies"
                value={formData.allergies}
                label={__('Alergias', 'mi-block')}
                onChange={(e) => handleChange('allergies', e.target.value)}
                required
                onBlur={() => handleBlur('allergies')}
              >
                {ALLERGIES_DATA.map(option => (
                  <MenuItem key={option.value} value={option.value}>
                    {option.label}
                  </MenuItem>
                ))}
              </Select>
              {touched.allergies && errors.allergies && <span className="error-message">{errors.allergies}</span>}
            </FormControl>
          </div>

          <div className="form-group">
            <FormControl 
              fullWidth 
              variant="outlined"
              error={touched.special_needs && Boolean(errors.special_needs)}
            >
              <InputLabel id="special_needs-label">{ __('Necesidades especiales', 'mi-block') }</InputLabel>
              <Select
                labelId="special_needs-label"
                id="special_needs"
                value={formData.special_needs}
                label={__('Necesidades especiales', 'mi-block')}
                onChange={(e) => handleChange('special_needs', e.target.value)}
                required
                onBlur={() => handleBlur('special_needs')}
              >
                {SPECIAL_NEEDS_DATA.map(option => (
                  <MenuItem key={option.value} value={option.value}>
                    {option.label}
                  </MenuItem>
                ))}
              </Select>
              {touched.special_needs && errors.special_needs && <span className="error-message">{errors.special_needs}</span>}
            </FormControl>
          </div>

          <div className={`form-group ${touched.ownerName && errors.ownerName ? 'has-error' : ''}`}>
            <label htmlFor="ownerName">{ __('Tu nombre', 'mi-block') }</label>
            <input
              type="text"
              id="ownerName"
              value={formData.ownerName}
              onChange={(e) => handleChange('ownerName', e.target.value)}
              onBlur={() => handleBlur('ownerName')}
              required
            />
            {touched.ownerName && errors.ownerName && <span className="error-message">{errors.ownerName}</span>}
          </div>

          <div className={`form-group ${touched.email && errors.email ? 'has-error' : ''}`}>
            <label htmlFor="email">{ __('Email de contacto', 'mi-block') }</label>
            <input
              type="email"
              id="email"
              value={formData.email}
              onChange={(e) => handleChange('email', e.target.value)}
              onBlur={() => handleBlur('email')}
              required
            />
            {touched.email && errors.email && <span className="error-message">{errors.email}</span>}
          </div>

          {responseMessage && (
            <div className={`form-message ${isSuccess ? 'success' : 'error'}`}>
              <p>{responseMessage}</p>
            </div>
          )}

          <div className="button-row">
            <button type="button" className="btn btn-secondary" onClick={prevStep}>
              { __('Anterior', 'mi-block') }
            </button>
            <button 
              type="submit" 
              className="btn btn-primary"
              disabled={isSubmitting}
            >
              {isSubmitting 
                ? __('Enviando...', 'mi-block') 
                : __('Obtener dieta BARF', 'mi-block')}
            </button>
          </div>
        </div>
      </form>
    </div>
  );
};

registerBlockType('mi-block/formulario-pet', {
  title: __('Formulario de Mascota BARF', 'mi-block'),
  icon: 'pets',
  category: 'widgets',

  edit: PetFormBlock,

  save: () => {
    // Renderizado dinámico del lado del servidor, no necesitamos guardar HTML estático
    return null;
  },
}); 