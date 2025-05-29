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

// Opciones de selección
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

// Componente React para el calculador nutricional
const NutritionCalculatorBlock = () => {
  // Estado para los factores nutricionales (configurable desde WordPress)
  const [nutritionFactors, setNutritionFactors] = useState(DEFAULT_NUTRITION_FACTORS);
  
  // Estado para la lista de razas
  const [dogBreeds, setDogBreeds] = useState([]);
  const [isLoadingBreeds, setIsLoadingBreeds] = useState(true);

  // Estado para manejar carga y mensajes
  const [isCalculating, setIsCalculating] = useState(false);
  const [calculationResult, setCalculationResult] = useState(null);
  const [showResult, setShowResult] = useState(false);

  // Datos del formulario
  const [formData, setFormData] = useState({
    petName: '',
    breed: '',
    ageYears: '',
    ageMonths: '',
    bodyCondition: '',
    reproductiveState: '',
    activityLevel: '',
    weight: '',
    ownerName: '',
    email: ''
  });

  // Estado para errores de validación
  const [errors, setErrors] = useState({});
  const [touched, setTouched] = useState({});

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
  const getLifeStage = (years, months) => {
    const totalMonths = (parseInt(years) || 0) * 12 + (parseInt(months) || 0);
    
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

  // Función para manejar cambios en el formulario
  const handleChange = (field, value) => {
    setFormData({ ...formData, [field]: value });
    
    // Limpiar error cuando el usuario modifica el campo
    if (errors[field]) {
      setErrors({ ...errors, [field]: '' });
    }
    
    // Limpiar resultado anterior si existe
    if (calculationResult) {
      setCalculationResult(null);
      setShowResult(false);
    }
  };

  // Función para marcar campo como tocado
  const handleBlur = (field) => {
    setTouched({ ...touched, [field]: true });
    validateField(field, formData[field]);
  };

  // Función para validar un campo específico
  const validateField = (field, value) => {
    let errorMessage = '';
    
    switch (field) {
      case 'petName':
        if (!value.trim()) {
          errorMessage = __('El nombre de la mascota es obligatorio', 'nutrition-calculator');
        } else if (value.trim().length < 2) {
          errorMessage = __('El nombre debe tener al menos 2 caracteres', 'nutrition-calculator');
        }
        break;
        
      case 'breed':
        if (!value) {
          errorMessage = __('Seleccione una raza', 'nutrition-calculator');
        }
        break;
        
      case 'ageYears':
        if (!value && !formData.ageMonths) {
          errorMessage = __('Debe especificar al menos años o meses', 'nutrition-calculator');
        } else if (value && (isNaN(value) || parseInt(value) < 0)) {
          errorMessage = __('Los años deben ser un número positivo', 'nutrition-calculator');
        } else if (value && parseInt(value) > 25) {
          errorMessage = __('La edad parece demasiado alta para un perro', 'nutrition-calculator');
        }
        break;
        
      case 'ageMonths':
        if (!formData.ageYears && !value) {
          errorMessage = __('Debe especificar al menos años o meses', 'nutrition-calculator');
        } else if (value && (isNaN(value) || parseInt(value) < 0 || parseInt(value) > 11)) {
          errorMessage = __('Los meses deben ser entre 0 y 11', 'nutrition-calculator');
        }
        break;
        
      case 'weight':
        if (!value) {
          errorMessage = __('El peso es obligatorio', 'nutrition-calculator');
        } else if (isNaN(value) || parseFloat(value) <= 0) {
          errorMessage = __('Peso inválido', 'nutrition-calculator');
        } else if (parseFloat(value) > 100) {
          errorMessage = __('El peso parece demasiado alto para un perro', 'nutrition-calculator');
        }
        break;
        
      case 'bodyCondition':
        if (!value) {
          errorMessage = __('Seleccione la condición física', 'nutrition-calculator');
        }
        break;
        
      case 'reproductiveState':
        if (!value) {
          errorMessage = __('Seleccione el estado reproductivo', 'nutrition-calculator');
        }
        break;
        
      case 'activityLevel':
        if (!value) {
          errorMessage = __('Seleccione el nivel de actividad', 'nutrition-calculator');
        }
        break;
        
      case 'ownerName':
        if (!value.trim()) {
          errorMessage = __('Su nombre es obligatorio', 'nutrition-calculator');
        }
        break;
        
      case 'email':
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!value.trim()) {
          errorMessage = __('El email es obligatorio', 'nutrition-calculator');
        } else if (!emailRegex.test(value)) {
          errorMessage = __('Ingrese un email válido', 'nutrition-calculator');
        }
        break;
        
      default:
        break;
    }
    
    setErrors(prev => ({ ...prev, [field]: errorMessage }));
    return errorMessage === '';
  };

  // Función para validar todo el formulario
  const validateForm = () => {
    const requiredFields = [
      'petName', 'breed', 'weight', 'bodyCondition', 
      'reproductiveState', 'activityLevel', 'ownerName', 'email'
    ];
    
    // Validar edad (al menos años o meses)
    if (!formData.ageYears && !formData.ageMonths) {
      setErrors(prev => ({ 
        ...prev, 
        ageYears: __('Debe especificar al menos años o meses', 'nutrition-calculator') 
      }));
      setTouched(prev => ({ ...prev, ageYears: true }));
      return false;
    }
    
    let isValid = true;
    const newTouched = { ...touched };
    
    requiredFields.forEach(field => {
      newTouched[field] = true;
      if (!validateField(field, formData[field])) {
        isValid = false;
      }
    });
    
    // Validar edad específicamente
    if (!validateField('ageYears', formData.ageYears) || 
        !validateField('ageMonths', formData.ageMonths)) {
      isValid = false;
      newTouched.ageYears = true;
      newTouched.ageMonths = true;
    }
    
    setTouched(newTouched);
    return isValid;
  };

  // Función para calcular el valor nutricional
  const handleCalculate = async (e) => {
    e.preventDefault();
    
    // Validar formulario
    if (!validateForm()) {
      return;
    }
    
    setIsCalculating(true);
    
    try {
      // Determinar etapa de vida
      const lifeStage = getLifeStage(formData.ageYears, formData.ageMonths);
      
      // Obtener factor nutricional
      const factor = getNutritionFactor(
        lifeStage, 
        formData.bodyCondition, 
        formData.reproductiveState, 
        formData.activityLevel
      );
      
      if (!factor) {
        setErrors({ 
          ...errors, 
          general: __('No se encontró un factor nutricional para esta combinación', 'nutrition-calculator') 
        });
        setIsCalculating(false);
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
      setShowResult(true);
      
      // Enviar datos a WordPress (opcional, para tracking)
      await apiFetch({
        path: '/nutrition-calculator/v1/calculate',
        method: 'POST',
        data: {
          ...formData,
          lifeStage,
          factor,
          dailyNutrition
        }
      }).catch(error => {
        console.log('Error enviando datos (opcional):', error);
      });
      
    } catch (error) {
      console.error('Error en el cálculo:', error);
      setErrors({ 
        ...errors, 
        general: __('Ocurrió un error al calcular. Por favor, intenta de nuevo.', 'nutrition-calculator') 
      });
    } finally {
      setIsCalculating(false);
    }
  };

  // Función para resetear el formulario
  const resetForm = () => {
    setFormData({
      petName: '',
      breed: '',
      ageYears: '',
      ageMonths: '',
      bodyCondition: '',
      reproductiveState: '',
      activityLevel: '',
      weight: '',
      ownerName: '',
      email: ''
    });
    setErrors({});
    setTouched({});
    setCalculationResult(null);
    setShowResult(false);
  };

  // Si se muestra el resultado
  if (showResult && calculationResult) {
    return (
      <div className="nutrition-calculator-container">
        <div className="calculation-result">
          <div className="result-header">
            <h3>{__('Resultado del Cálculo Nutricional', 'nutrition-calculator')}</h3>
            <div className="pet-info">
              <h4>{calculationResult.petName}</h4>
              <p>{__('Categoría:', 'nutrition-calculator')} <strong>{calculationResult.lifeStage}</strong></p>
            </div>
          </div>
          
          <div className="result-details">
            <div className="result-card">
              <div className="daily-nutrition">
                <span className="label">{__('Ración diaria recomendada:', 'nutrition-calculator')}</span>
                <span className="value">{calculationResult.dailyNutrition} g</span>
              </div>
              
              <div className="calculation-info">
                <h5>{__('Parámetros utilizados:', 'nutrition-calculator')}</h5>
                <ul>
                  <li>{__('Peso:', 'nutrition-calculator')} {calculationResult.weight} kg</li>
                  <li>{__('Etapa de vida:', 'nutrition-calculator')} {calculationResult.lifeStage}</li>
                  <li>{__('Condición física:', 'nutrition-calculator')} {calculationResult.bodyCondition}</li>
                  <li>{__('Estado reproductivo:', 'nutrition-calculator')} {calculationResult.reproductiveState}</li>
                  <li>{__('Actividad física:', 'nutrition-calculator')} {calculationResult.activityLevel}</li>
                  <li>{__('Factor aplicado:', 'nutrition-calculator')} {calculationResult.factor}</li>
                </ul>
              </div>
              
              <div className="formula-info">
                <h5>{__('Fórmula aplicada:', 'nutrition-calculator')}</h5>
                <p><code>(Peso^0.75 × 70 × Factor) / 1.5</code></p>
                <p><code>({calculationResult.weight}^0.75 × 70 × {calculationResult.factor}) / 1.5 = {calculationResult.dailyNutrition} g</code></p>
              </div>
            </div>
          </div>
          
          <div className="result-actions">
            <button type="button" className="btn btn-secondary" onClick={resetForm}>
              {__('Calcular para otra mascota', 'nutrition-calculator')}
            </button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="nutrition-calculator-container">
      <form onSubmit={handleCalculate}>
        <div className="form-header">
          <h3>{__('Calculadora de Valor Diario Nutricional', 'nutrition-calculator')}</h3>
          <p>{__('Calcula la ración diaria recomendada para tu mascota', 'nutrition-calculator')}</p>
        </div>

        {errors.general && (
          <div className="form-message error">
            <p>{errors.general}</p>
          </div>
        )}

        {/* Información básica de la mascota */}
        <div className="form-section">
          <h4>{__('Información de la mascota', 'nutrition-calculator')}</h4>
          
          <div className={`form-group ${touched.petName && errors.petName ? 'has-error' : ''}`}>
            <label htmlFor="petName">{__('Nombre de la mascota *', 'nutrition-calculator')}</label>
            <input
              type="text"
              id="petName"
              value={formData.petName}
              onChange={(e) => handleChange('petName', e.target.value)}
              onBlur={() => handleBlur('petName')}
              placeholder={__('Nombre de tu perro', 'nutrition-calculator')}
            />
            {touched.petName && errors.petName && <span className="error-message">{errors.petName}</span>}
          </div>

          <div className={`form-group ${touched.breed && errors.breed ? 'has-error' : ''}`}>
            <label htmlFor="breed">{__('Raza *', 'nutrition-calculator')}</label>
            <select
              id="breed"
              value={formData.breed}
              onChange={(e) => handleChange('breed', e.target.value)}
              onBlur={() => handleBlur('breed')}
              disabled={isLoadingBreeds}
            >
              <option value="">{__('Selecciona la raza...', 'nutrition-calculator')}</option>
              {dogBreeds.map(breed => (
                <option key={breed.value} value={breed.value}>
                  {breed.label}
                </option>
              ))}
            </select>
            {touched.breed && errors.breed && <span className="error-message">{errors.breed}</span>}
          </div>

          <div className="form-row">
            <div className={`form-group ${touched.ageYears && errors.ageYears ? 'has-error' : ''}`}>
              <label htmlFor="ageYears">{__('Edad - Años', 'nutrition-calculator')}</label>
              <input
                type="number"
                id="ageYears"
                min="0"
                max="25"
                value={formData.ageYears}
                onChange={(e) => handleChange('ageYears', e.target.value)}
                onBlur={() => handleBlur('ageYears')}
                placeholder="0"
              />
              {touched.ageYears && errors.ageYears && <span className="error-message">{errors.ageYears}</span>}
            </div>

            <div className={`form-group ${touched.ageMonths && errors.ageMonths ? 'has-error' : ''}`}>
              <label htmlFor="ageMonths">{__('Edad - Meses', 'nutrition-calculator')}</label>
              <input
                type="number"
                id="ageMonths"
                min="0"
                max="11"
                value={formData.ageMonths}
                onChange={(e) => handleChange('ageMonths', e.target.value)}
                onBlur={() => handleBlur('ageMonths')}
                placeholder="0"
              />
              {touched.ageMonths && errors.ageMonths && <span className="error-message">{errors.ageMonths}</span>}
            </div>
          </div>

          <div className={`form-group ${touched.weight && errors.weight ? 'has-error' : ''}`}>
            <label htmlFor="weight">{__('Peso (kg) *', 'nutrition-calculator')}</label>
            <input
              type="number"
              id="weight"
              step="0.1"
              min="0.1"
              max="100"
              value={formData.weight}
              onChange={(e) => handleChange('weight', e.target.value)}
              onBlur={() => handleBlur('weight')}
              placeholder="0.0"
            />
            {touched.weight && errors.weight && <span className="error-message">{errors.weight}</span>}
          </div>
        </div>

        {/* Condición física y estado */}
        <div className="form-section">
          <h4>{__('Condición física y estado', 'nutrition-calculator')}</h4>
          
          <div className={`form-group ${touched.bodyCondition && errors.bodyCondition ? 'has-error' : ''}`}>
            <label>{__('Condición física *', 'nutrition-calculator')}</label>
            <div className="radio-group">
              {BODY_CONDITIONS.map((condition) => (
                <label key={condition.value} className="radio-option">
                  <input
                    type="radio"
                    name="bodyCondition"
                    value={condition.value}
                    checked={formData.bodyCondition === condition.value}
                    onChange={(e) => {
                      handleChange('bodyCondition', e.target.value);
                      setTouched({...touched, bodyCondition: true});
                    }}
                  />
                  <span>{condition.label}</span>
                </label>
              ))}
            </div>
            {touched.bodyCondition && errors.bodyCondition && <span className="error-message">{errors.bodyCondition}</span>}
          </div>

          <div className={`form-group ${touched.reproductiveState && errors.reproductiveState ? 'has-error' : ''}`}>
            <label>{__('Estado reproductivo *', 'nutrition-calculator')}</label>
            <div className="radio-group">
              {REPRODUCTIVE_STATES.map((state) => (
                <label key={state.value} className="radio-option">
                  <input
                    type="radio"
                    name="reproductiveState"
                    value={state.value}
                    checked={formData.reproductiveState === state.value}
                    onChange={(e) => {
                      handleChange('reproductiveState', e.target.value);
                      setTouched({...touched, reproductiveState: true});
                    }}
                  />
                  <span>{state.label}</span>
                </label>
              ))}
            </div>
            {touched.reproductiveState && errors.reproductiveState && <span className="error-message">{errors.reproductiveState}</span>}
          </div>

          <div className={`form-group ${touched.activityLevel && errors.activityLevel ? 'has-error' : ''}`}>
            <label htmlFor="activityLevel">{__('Nivel de actividad física *', 'nutrition-calculator')}</label>
            <select
              id="activityLevel"
              value={formData.activityLevel}
              onChange={(e) => handleChange('activityLevel', e.target.value)}
              onBlur={() => handleBlur('activityLevel')}
            >
              <option value="">{__('Selecciona el nivel...', 'nutrition-calculator')}</option>
              {ACTIVITY_LEVELS.map(level => (
                <option key={level.value} value={level.value}>
                  {level.label}
                </option>
              ))}
            </select>
            {touched.activityLevel && errors.activityLevel && <span className="error-message">{errors.activityLevel}</span>}
          </div>
        </div>

        {/* Información de contacto */}
        <div className="form-section">
          <h4>{__('Información de contacto', 'nutrition-calculator')}</h4>
          
          <div className={`form-group ${touched.ownerName && errors.ownerName ? 'has-error' : ''}`}>
            <label htmlFor="ownerName">{__('Tu nombre *', 'nutrition-calculator')}</label>
            <input
              type="text"
              id="ownerName"
              value={formData.ownerName}
              onChange={(e) => handleChange('ownerName', e.target.value)}
              onBlur={() => handleBlur('ownerName')}
              placeholder={__('Tu nombre completo', 'nutrition-calculator')}
            />
            {touched.ownerName && errors.ownerName && <span className="error-message">{errors.ownerName}</span>}
          </div>

          <div className={`form-group ${touched.email && errors.email ? 'has-error' : ''}`}>
            <label htmlFor="email">{__('Email de contacto *', 'nutrition-calculator')}</label>
            <input
              type="email"
              id="email"
              value={formData.email}
              onChange={(e) => handleChange('email', e.target.value)}
              onBlur={() => handleBlur('email')}
              placeholder={__('tu.email@ejemplo.com', 'nutrition-calculator')}
            />
            {touched.email && errors.email && <span className="error-message">{errors.email}</span>}
          </div>
        </div>

        <div className="form-actions">
          <button 
            type="submit" 
            className="btn btn-primary"
            disabled={isCalculating}
          >
            {isCalculating 
              ? __('Calculando...', 'nutrition-calculator') 
              : __('Calcular valor nutricional', 'nutrition-calculator')}
          </button>
        </div>
      </form>
    </div>
  );
};

registerBlockType('nutrition-calculator/calculator-form', {
  title: __('Calculadora Nutricional', 'nutrition-calculator'),
  icon: 'chart-line',
  category: 'widgets',
  description: __('Calcula el valor diario nutricional para perros basado en edad, condición corporal, nivel de actividad y peso.', 'nutrition-calculator'),
  
  edit: NutritionCalculatorBlock,

  save: () => {
    // Renderizado dinámico del lado del servidor
    return null;
  },
}); 