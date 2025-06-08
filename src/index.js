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

// Tabla de factores nutricionales (configurable desde WordPress Admin)
const DEFAULT_NUTRITION_FACTORS = {
  'Cachorro-Muy delgado-Castrado-Sedentario': 1.9,
  'Cachorro-Muy delgado-Castrado-Activo': 2.3,
  'Cachorro-Muy delgado-Castrado-Muy activo': 2.6,
  'Cachorro-Muy delgado-Entero-Sedentario': 2.1,
  'Cachorro-Muy delgado-Entero-Activo': 2.5,
  'Cachorro-Muy delgado-Entero-Muy activo': 2.8,
  'Cachorro-Delgado-Castrado-Sedentario': 1.7,
  'Cachorro-Delgado-Castrado-Activo': 1.8,
  'Cachorro-Delgado-Castrado-Muy activo': 2.0,
  'Cachorro-Delgado-Entero-Sedentario': 1.7,
  'Cachorro-Delgado-Entero-Activo': 1.8,
  'Cachorro-Delgado-Entero-Muy activo': 2.0,
  'Cachorro-Normal-Castrado-Sedentario': 1.4,
  'Cachorro-Normal-Castrado-Activo': 1.5,
  'Cachorro-Normal-Castrado-Muy activo': 1.6,
  'Cachorro-Normal-Entero-Sedentario': 1.5,
  'Cachorro-Normal-Entero-Activo': 1.6,
  'Cachorro-Normal-Entero-Muy activo': 1.8,
  'Cachorro-Obeso-Castrado-Sedentario': 1.2,
  'Cachorro-Obeso-Castrado-Activo': 1.7,
  'Cachorro-Obeso-Castrado-Muy activo': 1.9,
  'Cachorro-Obeso-Entero-Sedentario': 1.4,
  'Cachorro-Obeso-Entero-Activo': 1.9,
  'Cachorro-Obeso-Entero-Muy activo': 2.1,
  'Adulto-Muy delgado-Castrado-Sedentario': 1.4,
  'Adulto-Muy delgado-Castrado-Activo': 1.8,
  'Adulto-Muy delgado-Castrado-Muy activo': 2.1,
  'Adulto-Muy delgado-Entero-Sedentario': 1.6,
  'Adulto-Muy delgado-Entero-Activo': 2.0,
  'Adulto-Muy delgado-Entero-Muy activo': 2.3,
  'Adulto-Delgado-Castrado-Sedentario': 1.3,
  'Adulto-Delgado-Castrado-Activo': 1.6,
  'Adulto-Delgado-Castrado-Muy activo': 1.8,
  'Adulto-Delgado-Entero-Sedentario': 1.5,
  'Adulto-Delgado-Entero-Activo': 1.8,
  'Adulto-Delgado-Entero-Muy activo': 2.0,
  'Adulto-Normal-Castrado-Sedentario': 1.4,
  'Adulto-Normal-Castrado-Activo': 1.5,
  'Adulto-Normal-Castrado-Muy activo': 1.7,
  'Adulto-Normal-Entero-Sedentario': 1.4,
  'Adulto-Normal-Entero-Activo': 1.7,
  'Adulto-Normal-Entero-Muy activo': 1.8,
  'Adulto-Obeso-Castrado-Sedentario': 1.1,
  'Adulto-Obeso-Castrado-Activo': 1.2,
  'Adulto-Obeso-Castrado-Muy activo': 1.3,
  'Adulto-Obeso-Entero-Sedentario': 1.2,
  'Adulto-Obeso-Entero-Activo': 1.3,
  'Adulto-Obeso-Entero-Muy activo': 1.4,
  'Senior-Muy delgado-Castrado-Sedentario': 1.3,
  'Senior-Muy delgado-Castrado-Activo': 1.7,
  'Senior-Muy delgado-Castrado-Muy activo': 2.0,
  'Senior-Muy delgado-Entero-Sedentario': 1.5,
  'Senior-Muy delgado-Entero-Activo': 1.9,
  'Senior-Muy delgado-Entero-Muy activo': 2.2,
  'Senior-Delgado-Castrado-Sedentario': 1.3,
  'Senior-Delgado-Castrado-Activo': 1.4,
  'Senior-Delgado-Castrado-Muy activo': 1.7,
  'Senior-Delgado-Entero-Sedentario': 1.3,
  'Senior-Delgado-Entero-Activo': 1.7,
  'Senior-Delgado-Entero-Muy activo': 1.9,
  'Senior-Normal-Castrado-Sedentario': 1.3,
  'Senior-Normal-Castrado-Activo': 1.4,
  'Senior-Normal-Castrado-Muy activo': 1.5,
  'Senior-Normal-Entero-Sedentario': 1.2,
  'Senior-Normal-Entero-Activo': 1.5,
  'Senior-Normal-Entero-Muy activo': 1.7,
  'Senior-Obeso-Castrado-Sedentario': 1.1,
  'Senior-Obeso-Castrado-Activo': 1.2,
  'Senior-Obeso-Castrado-Muy activo': 1.3,
  'Senior-Obeso-Entero-Sedentario': 1.2,
  'Senior-Obeso-Entero-Activo': 1.3,
  'Senior-Obeso-Entero-Muy activo': 1.4
};

// Opciones actualizadas para el cálculo nutricional
const BODY_CONDITIONS = [
  { value: 'Muy delgado', label: 'Muy delgado' },
  { value: 'Delgado', label: 'Delgado' },
  { value: 'Normal', label: 'Normal' },
  { value: 'Obeso', label: 'Obeso' },
];

const REPRODUCTIVE_STATES = [
  { value: 'Castrado', label: 'Castrado' },
  { value: 'Entero', label: 'Entero' },
];

const ACTIVITY_LEVELS = [
  { value: 'Sedentario', label: 'Sedentario' },
  { value: 'Activo', label: 'Activo' },
  { value: 'Muy activo', label: 'Muy activo' },
];

// Lista de alergias
const ALLERGIES = [
  { value: 'ninguna', label: 'Ninguna' },
  { value: 'pollo', label: 'Pollo' },
  { value: 'res', label: 'Res' },
  { value: 'cerdo', label: 'Cerdo' },
  { value: 'pescado', label: 'Pescado' },
  { value: 'lacteos', label: 'Lácteos' },
  { value: 'gluten', label: 'Gluten' },
  { value: 'otro', label: 'Otro' }
];

// Lista de necesidades especiales
const SPECIAL_NEEDS = [
  { value: 'ninguna', label: 'Ninguna' },
  { value: 'diabetes', label: 'Diabetes' },
  { value: 'renal', label: 'Enfermedad renal' },
  { value: 'hepatica', label: 'Enfermedad hepática' },
  { value: 'cardiaca', label: 'Enfermedad cardíaca' },
  { value: 'artritis', label: 'Artritis' },
  { value: 'sobrepeso', label: 'Sobrepeso' },
  { value: 'digestiva', label: 'Problemas digestivos' },
  { value: 'otro', label: 'Otro' }
];

// Componente React para el formulario de mascotas
const PetFormBlock = () => {
  // Estado para la navegación por pasos
  const [currentStep, setCurrentStep] = useState(1);
  const [totalSteps] = useState(6);

  // Estado para los factores nutricionales (configurable desde WordPress)
  const [nutritionFactors, setNutritionFactors] = useState(DEFAULT_NUTRITION_FACTORS);

  // Estado para la lista de razas
  const [dogBreeds, setDogBreeds] = useState([]);
  const [isLoadingBreeds, setIsLoadingBreeds] = useState(true);

  // Estado para manejar la carga y mensajes
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isSuccess, setIsSuccess] = useState(false);
  const [calculationResult, setCalculationResult] = useState(null);

  // Datos del formulario
  const [formData, setFormData] = useState({
    petName: '',
    breed: '',
    sex: '',
    age: '',
    ageType: 'años',
    naturalFood: '',
    activityLevel: '',
    reproductiveState: '',
    bodyCondition: '',
    weight: '',
    allergies: '',
    specialNeeds: '',
    ownerName: '',
    email: ''
  });

  // Estado para errores de validación
  const [errors, setErrors] = useState({});

  // Cargar datos al iniciar el componente
  useEffect(() => {
    // Cargar factores nutricionales desde WordPress (si están configurados)
    apiFetch({ path: '/nutrition-calculator/v1/factors' })
      .then(factors => {
        if (factors && Object.keys(factors).length > 0) {
          setNutritionFactors(factors);
        }
      })
      .catch(error => {
        console.log('Usando factores por defecto:', error);
      });

    // Cargar lista de razas
    if (typeof petFormSettings !== 'undefined' && petFormSettings.dogBreeds) {
      setDogBreeds(petFormSettings.dogBreeds);
      setIsLoadingBreeds(false);
    } else {
      apiFetch({ path: '/pet-form/v1/breeds' })
        .then(breeds => {
          setDogBreeds(breeds);
          setIsLoadingBreeds(false);
        })
        .catch(error => {
          console.error('Error al cargar razas:', error);
          const defaultBreeds = [
            { value: 'labrador', label: 'Labrador Retriever' },
            { value: 'golden', label: 'Golden Retriever' },
            { value: 'mixto', label: 'Raza mixta' },
            { value: 'otro', label: 'Otro' }
          ];
          setDogBreeds(defaultBreeds);
          setIsLoadingBreeds(false);
        });
    }
  }, []);

  // Función para determinar la etapa de vida basada en la edad
  const getLifeStage = (age, ageType) => {
    let totalMonths = 0;
    
    if (ageType === 'años') {
      totalMonths = parseInt(age) * 12;
    } else {
      totalMonths = parseInt(age);
    }
    
    if (totalMonths <= 12) {
      return 'Cachorro';
    } else if (totalMonths <= 96) { // 8 años
      return 'Adulto';
    } else {
      return 'Senior';
    }
  };

  // Función para obtener el factor nutricional
  const getNutritionFactor = (lifeStage, bodyCondition, reproductiveState, activityLevel) => {
    const key = `${lifeStage}-${bodyCondition}-${reproductiveState}-${activityLevel}`;
    return nutritionFactors[key] || null;
  };

  // Función para calcular la ración diaria
  const calculateDailyNutrition = (weight, factor) => {
    // Fórmula: (Peso^0.75 × 70 × Factor) / 1.5
    const result = (Math.pow(parseFloat(weight), 0.75) * 70 * factor) / 1.5;
    return Math.round(result * 100) / 100; // Redondear a 2 decimales
  };

  // Función para validar un paso específico
  const validateStep = (step) => {
    const stepErrors = {};
    
    switch (step) {
      case 1:
        if (!formData.petName.trim()) {
          stepErrors.petName = __('El nombre de la mascota es obligatorio', 'pet-form');
        }
        if (!formData.breed) {
          stepErrors.breed = __('Seleccione una raza', 'pet-form');
        }
        if (!formData.sex) {
          stepErrors.sex = __('Seleccione el sexo', 'pet-form');
        }
        if (!formData.age || parseFloat(formData.age) <= 0) {
          stepErrors.age = __('La edad es obligatoria', 'pet-form');
        }
        break;
        
      case 2:
        if (!formData.naturalFood) {
          stepErrors.naturalFood = __('Seleccione si consume alimentación natural', 'pet-form');
        }
        break;
        
      case 3:
        if (!formData.activityLevel) {
          stepErrors.activityLevel = __('Seleccione el nivel de actividad', 'pet-form');
        }
        if (!formData.reproductiveState) {
          stepErrors.reproductiveState = __('Seleccione el estado reproductivo', 'pet-form');
        }
        break;
        
      case 4:
        if (!formData.bodyCondition) {
          stepErrors.bodyCondition = __('Seleccione la condición física', 'pet-form');
        }
        if (!formData.weight || parseFloat(formData.weight) <= 0) {
          stepErrors.weight = __('Peso inválido', 'pet-form');
        }
        break;
        
      case 5:
        if (!formData.allergies) {
          stepErrors.allergies = __('Seleccione las alergias', 'pet-form');
        }
        if (!formData.specialNeeds) {
          stepErrors.specialNeeds = __('Seleccione las necesidades especiales', 'pet-form');
        }
        break;
        
      case 6:
        if (!formData.ownerName.trim()) {
          stepErrors.ownerName = __('Su nombre es obligatorio', 'pet-form');
        }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!formData.email.trim()) {
          stepErrors.email = __('El email es obligatorio', 'pet-form');
        } else if (!emailRegex.test(formData.email)) {
          stepErrors.email = __('Ingrese un email válido', 'pet-form');
        }
        break;
    }
    
    setErrors(stepErrors);
    return Object.keys(stepErrors).length === 0;
  };

  // Función para manejar cambios en el formulario
  const handleChange = (field, value) => {
    setFormData({ ...formData, [field]: value });
    
    // Limpiar error cuando el usuario modifica el campo
    if (errors[field]) {
      setErrors({ ...errors, [field]: '' });
      }
  };
    
  // Función para navegar al siguiente paso
  const nextStep = () => {
    if (validateStep(currentStep)) {
      if (currentStep < totalSteps) {
        setCurrentStep(currentStep + 1);
      } else {
        handleSubmit();
      }
    }
  };
  
  // Función para navegar al paso anterior
  const prevStep = () => {
    if (currentStep > 1) {
      setCurrentStep(currentStep - 1);
    }
  };

  // Función para manejar el envío del formulario
  const handleSubmit = async () => {
    if (!validateStep(currentStep)) {
      return;
    }
    
    setIsSubmitting(true);
    
    try {
      // Determinar etapa de vida
      const lifeStage = getLifeStage(formData.age, formData.ageType);
      
      // Obtener factor nutricional
      const factor = getNutritionFactor(
        lifeStage, 
        formData.bodyCondition, 
        formData.reproductiveState, 
        formData.activityLevel
      );
      
      if (!factor) {
        setErrors({ 
          general: __('No se encontró un factor nutricional para esta combinación', 'pet-form') 
        });
        setIsSubmitting(false);
        return;
      }
      
      // Calcular ración diaria
      const dailyNutrition = calculateDailyNutrition(formData.weight, factor);
      
      // Preparar resultado
      const result = {
        petName: formData.petName,
        lifeStage,
        factor,
        dailyNutrition,
        weight: parseFloat(formData.weight),
        bodyCondition: formData.bodyCondition,
        reproductiveState: formData.reproductiveState,
        activityLevel: formData.activityLevel
      };
      
      setCalculationResult(result);
      
      // Enviar datos a WordPress
      await apiFetch({
        path: '/pet-form/v1/submit',
        method: 'POST',
        data: {
          ...formData,
          lifeStage,
          factor,
          dailyNutrition
        }
      });
      
      setIsSuccess(true);
      
    } catch (error) {
      console.error('Error al enviar formulario:', error);
      setErrors({ 
        general: __('Ocurrió un error al enviar el formulario. Por favor, intenta de nuevo.', 'pet-form') 
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  // Función para resetear el formulario
  const resetForm = () => {
    setFormData({
      petName: '',
      breed: '',
      sex: '',
      age: '',
      ageType: 'años',
      naturalFood: '',
      activityLevel: '',
      reproductiveState: '',
      bodyCondition: '',
      weight: '',
      allergies: '',
      specialNeeds: '',
      ownerName: '',
      email: ''
    });
    setErrors({});
    setCurrentStep(1);
    setIsSuccess(false);
    setCalculationResult(null);
  };

  // Renderizar resultado exitoso
  if (isSuccess && calculationResult) {
    return (
      <div className="pet-form-container">
        <div className="form-success-message">
          <h3>{__('¡Registro exitoso!', 'pet-form')}</h3>
          <p>{__('Los datos de tu mascota han sido registrados correctamente.', 'pet-form')}</p>
          
          <div className="calculation-result">
            <h4>{__('Resultado del Cálculo Nutricional para', 'pet-form')} {calculationResult.petName}</h4>
            <div className="result-details">
              <p><strong>{__('Categoría:', 'pet-form')}</strong> {calculationResult.lifeStage}</p>
              <p><strong>{__('Ración diaria recomendada:', 'pet-form')}</strong> {calculationResult.dailyNutrition} g</p>
              <p><strong>{__('Factor aplicado:', 'pet-form')}</strong> {calculationResult.factor}</p>
              <p><strong>{__('Fórmula:', 'pet-form')}</strong> (Peso^0.75 × 70 × Factor) / 1.5</p>
            </div>
          </div>
          
          <button type="button" className="btn btn-primary" onClick={resetForm}>
            {__('Registrar otra mascota', 'pet-form')}
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="pet-form-container">
          <div className="form-header">
        <h3>{__('Registro de Mascota y Cálculo Nutricional', 'pet-form')}</h3>
        <p>{__('Complete la información de su mascota para calcular su necesidad nutricional diaria', 'pet-form')}</p>
        <span className="step-indicator">{currentStep} de {totalSteps}</span>
      </div>

      {errors.general && (
        <div className="form-message error">
          <p>{errors.general}</p>
          </div>
      )}

      {/* PASO 1: Información básica */}
      <div className={`form-step ${currentStep === 1 ? 'active' : ''}`}>
        <div className="form-group">
          <label htmlFor="petName">{__('Nombre de la mascota *', 'pet-form')}</label>
            <input
              type="text"
            id="petName"
            value={formData.petName}
            onChange={(e) => handleChange('petName', e.target.value)}
            placeholder={__('Nombre de tu perro', 'pet-form')}
            />
          {errors.petName && <span className="error-message">{errors.petName}</span>}
          </div>

        <div className="form-row">
          <div className="form-group">
            <label htmlFor="breed">{__('Raza *', 'pet-form')}</label>
            <Autocomplete
              id="breed"
              options={dogBreeds}
              getOptionLabel={(option) => option.label}
              loading={isLoadingBreeds}
              value={dogBreeds.find(breed => breed.value === formData.breed) || null}
              onChange={(event, newValue) => {
                handleChange('breed', newValue ? newValue.value : '');
              }}
              renderInput={(params) => (
                <TextField
                  {...params}
                  variant="outlined"
                  placeholder={__('Buscar raza...', 'pet-form')}
                />
              )}
            />
            {errors.breed && <span className="error-message">{errors.breed}</span>}
            </div>

            <div className="form-group">
            <FormControl variant="outlined" fullWidth>
              <InputLabel id="sex-label">{__('Sexo *', 'pet-form')}</InputLabel>
                <Select
                labelId="sex-label"
                id="sex"
                value={formData.sex}
                onChange={(e) => handleChange('sex', e.target.value)}
                label={__('Sexo *', 'pet-form')}
              >
                <MenuItem value="hembra">{__('Hembra', 'pet-form')}</MenuItem>
                <MenuItem value="macho">{__('Macho', 'pet-form')}</MenuItem>
                </Select>
              </FormControl>
            {errors.sex && <span className="error-message">{errors.sex}</span>}
            </div>
          </div>

        <div className="form-row">
          <div className="form-group">
            <label htmlFor="age">{__('Edad *', 'pet-form')}</label>
            <input
              type="number"
              id="age"
              min="0"
              value={formData.age}
              onChange={(e) => handleChange('age', e.target.value)}
              placeholder="0"
            />
            {errors.age && <span className="error-message">{errors.age}</span>}
          </div>

          <div className="form-group">
            <FormControl variant="outlined" fullWidth>
              <InputLabel id="age-type-label">{__('Tipo', 'pet-form')}</InputLabel>
              <Select
                labelId="age-type-label"
                id="ageType"
                value={formData.ageType}
                onChange={(e) => handleChange('ageType', e.target.value)}
                label={__('Tipo', 'pet-form')}
            >
                <MenuItem value="años">{__('Años', 'pet-form')}</MenuItem>
                <MenuItem value="meses">{__('Meses', 'pet-form')}</MenuItem>
              </Select>
            </FormControl>
          </div>
        </div>

        <div className="button-row">
          <button type="button" className="btn btn-primary" onClick={nextStep}>
            {__('Continuar', 'pet-form')}
          </button>
        </div>
          </div>

      {/* PASO 2: Alimentación natural */}
      <div className={`form-step ${currentStep === 2 ? 'active' : ''}`}>
        <div className="form-group">
          <label>{__('¿La alimentación actual es natural? *', 'pet-form')}</label>
          <div className="reproductive-state-selector">
                <div 
              className={`reproductive-state-option ${formData.naturalFood === 'yes' ? 'selected' : ''}`}
              onClick={() => handleChange('naturalFood', 'yes')}
                >
              <span>{__('Sí, su dieta es natural', 'pet-form')}</span>
                </div>
            <div 
              className={`reproductive-state-option ${formData.naturalFood === 'no' ? 'selected' : ''}`}
              onClick={() => handleChange('naturalFood', 'no')}
            >
              <span>{__('No, consume concentrado', 'pet-form')}</span>
            </div>
          </div>
          {errors.naturalFood && <span className="error-message">{errors.naturalFood}</span>}
        </div>

        <div className="button-row">
          <button type="button" className="btn btn-secondary" onClick={prevStep}>
            {__('Anterior', 'pet-form')}
          </button>
          <button type="button" className="btn btn-primary" onClick={nextStep}>
            {__('Continuar', 'pet-form')}
          </button>
        </div>
          </div>

      {/* PASO 3: Actividad y estado reproductivo */}
      <div className={`form-step ${currentStep === 3 ? 'active' : ''}`}>
        <div className="form-group">
          <label>{__('Nivel de actividad física *', 'pet-form')}</label>
          <div className="activity-level-selector">
            {ACTIVITY_LEVELS.map((level) => (
                <div 
                key={level.value}
                className={`activity-level-option ${formData.activityLevel === level.value ? 'selected' : ''}`}
                onClick={() => handleChange('activityLevel', level.value)}
              >
                <div className="body-image-icon"></div>
                <span>{level.label}</span>
                </div>
              ))}
            </div>
          {errors.activityLevel && <span className="error-message">{errors.activityLevel}</span>}
          </div>

        <div className="form-group">
          <label>{__('Estado reproductivo *', 'pet-form')}</label>
          <div className="reproductive-state-selector">
            {REPRODUCTIVE_STATES.map((state) => (
              <div 
                key={state.value}
                className={`reproductive-state-option ${formData.reproductiveState === state.value ? 'selected' : ''}`}
                onClick={() => handleChange('reproductiveState', state.value)}
              >
                <span>{state.label}</span>
              </div>
                ))}
          </div>
          {errors.reproductiveState && <span className="error-message">{errors.reproductiveState}</span>}
          </div>

          <div className="button-row">
            <button type="button" className="btn btn-secondary" onClick={prevStep}>
            {__('Anterior', 'pet-form')}
            </button>
            <button type="button" className="btn btn-primary" onClick={nextStep}>
            {__('Continuar', 'pet-form')}
            </button>
          </div>
        </div>

      {/* PASO 4: Condición física y peso */}
      <div className={`form-step ${currentStep === 4 ? 'active' : ''}`}>
        <div className="form-group">
          <label>{__('Condición física *', 'pet-form')}</label>
          <div className="body-image-selector">
            {BODY_CONDITIONS.map((condition) => (
              <div 
                key={condition.value}
                className={`body-image-option ${formData.bodyCondition === condition.value ? 'selected' : ''}`}
                onClick={() => handleChange('bodyCondition', condition.value)}
              >
                <div className="body-image-icon"></div>
                <span>{condition.label}</span>
              </div>
            ))}
          </div>
          {errors.bodyCondition && <span className="error-message">{errors.bodyCondition}</span>}
        </div>

          <div className="form-group">
          <label htmlFor="weight">{__('Peso (en Kilos) *', 'pet-form')}</label>
          <input
            type="number"
            id="weight"
            step="0.1"
            min="0.1"
            value={formData.weight}
            onChange={(e) => handleChange('weight', e.target.value)}
            placeholder="0.0"
          />
          {errors.weight && <span className="error-message">{errors.weight}</span>}
        </div>

        <div className="button-row">
          <button type="button" className="btn btn-secondary" onClick={prevStep}>
            {__('Anterior', 'pet-form')}
          </button>
          <button type="button" className="btn btn-primary" onClick={nextStep}>
            {__('Continuar', 'pet-form')}
          </button>
        </div>
      </div>

      {/* PASO 5: Alergias y necesidades especiales */}
      <div className={`form-step ${currentStep === 5 ? 'active' : ''}`}>
        <div className="form-group">
          <label htmlFor="allergies">{__('Alergias conocidas *', 'pet-form')}</label>
          <select
                id="allergies"
                value={formData.allergies}
                onChange={(e) => handleChange('allergies', e.target.value)}
          >
            <option value="">{__('Seleccionar...', 'pet-form')}</option>
            {ALLERGIES.map(allergy => (
              <option key={allergy.value} value={allergy.value}>
                {allergy.label}
              </option>
                ))}
          </select>
          {errors.allergies && <span className="error-message">{errors.allergies}</span>}
          </div>

          <div className="form-group">
          <label htmlFor="specialNeeds">{__('Necesidades especiales *', 'pet-form')}</label>
          <select
            id="specialNeeds"
            value={formData.specialNeeds}
            onChange={(e) => handleChange('specialNeeds', e.target.value)}
          >
            <option value="">{__('Seleccionar...', 'pet-form')}</option>
            {SPECIAL_NEEDS.map(need => (
              <option key={need.value} value={need.value}>
                {need.label}
              </option>
                ))}
          </select>
          {errors.specialNeeds && <span className="error-message">{errors.specialNeeds}</span>}
        </div>

        <div className="button-row">
          <button type="button" className="btn btn-secondary" onClick={prevStep}>
            {__('Anterior', 'pet-form')}
          </button>
          <button type="button" className="btn btn-primary" onClick={nextStep}>
            {__('Continuar', 'pet-form')}
          </button>
        </div>
          </div>

      {/* PASO 6: Información de contacto */}
      <div className={`form-step ${currentStep === 6 ? 'active' : ''}`}>
        <div className="form-group">
          <label htmlFor="ownerName">{__('Tu nombre *', 'pet-form')}</label>
            <input
              type="text"
              id="ownerName"
              value={formData.ownerName}
              onChange={(e) => handleChange('ownerName', e.target.value)}
            placeholder={__('Tu nombre completo', 'pet-form')}
            />
          {errors.ownerName && <span className="error-message">{errors.ownerName}</span>}
          </div>

        <div className="form-group">
          <label htmlFor="email">{__('Email de contacto *', 'pet-form')}</label>
            <input
              type="email"
              id="email"
              value={formData.email}
              onChange={(e) => handleChange('email', e.target.value)}
            placeholder={__('tu.email@ejemplo.com', 'pet-form')}
            />
          {errors.email && <span className="error-message">{errors.email}</span>}
          </div>

          <div className="button-row">
            <button type="button" className="btn btn-secondary" onClick={prevStep}>
            {__('Anterior', 'pet-form')}
            </button>
            <button 
            type="button" 
              className="btn btn-primary"
            onClick={nextStep}
              disabled={isSubmitting}
            >
              {isSubmitting 
              ? __('Enviando...', 'pet-form') 
              : __('Calcular y Registrar', 'pet-form')}
            </button>
          </div>
        </div>
    </div>
  );
};

registerBlockType('pet-form/form-block', {
  title: __('Formulario de Mascota', 'pet-form'),
  icon: 'pets',
  category: 'widgets',
  description: __('Formulario para registrar mascotas y calcular necesidades nutricionales.', 'pet-form'),

  edit: PetFormBlock,

  save: () => {
    // Renderizado dinámico del lado del servidor
    return null;
  },
}); 