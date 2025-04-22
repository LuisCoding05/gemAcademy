import React, { useState } from 'react';
import { useTheme } from '../../context/ThemeContext';
import Icon from '../Icon';

const QuestionForm = ({ questionData, onChange }) => {
    const { isDarkMode } = useTheme();
    const [newOption, setNewOption] = useState('');

    const handleChange = (e) => {
        const { name, value } = e.target;
        onChange({
            ...questionData,
            [name]: value
        });
    };

    const handleAddOption = () => {
        if (!newOption.trim()) return;

        // Validar máximo de opciones (4)
        if (questionData.opciones.length >= 4) {
            alert('Máximo 4 opciones por pregunta');
            return;
        }

        onChange({
            ...questionData,
            opciones: [
                ...questionData.opciones,
                {
                    texto: newOption,
                    esCorrecta: false,
                    retroalimentacion: ''
                }
            ]
        });
        setNewOption('');
    };

    const handleOptionChange = (index, field, value) => {
        const newOpciones = questionData.opciones.map((opcion, i) => {
            if (i === index) {
                return {
                    ...opcion,
                    [field]: value
                };
            }
            return opcion;
        });

        onChange({
            ...questionData,
            opciones: newOpciones
        });
    };

    const handleRemoveOption = (index) => {
        onChange({
            ...questionData,
            opciones: questionData.opciones.filter((_, i) => i !== index)
        });
    };

    return (
        <div className="question-form">
            <div className="mb-3">
                <label className="form-label">Texto de la pregunta</label>
                <input
                    type="text"
                    className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                    name="pregunta"
                    value={questionData.pregunta}
                    onChange={handleChange}
                    required
                />
            </div>

            <div className="mb-3">
                <label className="form-label">Puntos</label>
                <input
                    type="number"
                    className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                    name="puntos"
                    value={questionData.puntos}
                    onChange={handleChange}
                    min="0"
                    required
                />
            </div>

            <div className="mb-3">
                <label className="form-label">Opciones</label>
                <div className="opciones-list">
                    {questionData.opciones.map((opcion, index) => (
                        <div key={index} className="card mb-2">
                            <div className="card-body">
                                <div className="row align-items-center">
                                    <div className="col-md-5 mb-2 mb-md-0">
                                        <input
                                            type="text"
                                            className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                                            value={opcion.texto}
                                            onChange={(e) => handleOptionChange(index, 'texto', e.target.value)}
                                            placeholder="Texto de la opción"
                                            required
                                        />
                                    </div>
                                    <div className="col-md-4 mb-2 mb-md-0">
                                        <input
                                            type="text"
                                            className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                                            value={opcion.retroalimentacion}
                                            onChange={(e) => handleOptionChange(index, 'retroalimentacion', e.target.value)}
                                            placeholder="Retroalimentación (opcional)"
                                        />
                                    </div>
                                    <div className="col-md-2 mb-2 mb-md-0">
                                        <div className="form-check form-switch">
                                            <input
                                                type="checkbox"
                                                className="form-check-input"
                                                checked={opcion.esCorrecta}
                                                onChange={(e) => handleOptionChange(index, 'esCorrecta', e.target.checked)}
                                                role="switch"
                                                id={`opcionCorrecta${index}`}
                                            />
                                            <label className="form-check-label" htmlFor={`opcionCorrecta${index}`}>
                                                Correcta
                                            </label>
                                        </div>
                                    </div>
                                    <div className="col-md-2 text-end mt-2">
                                        <button
                                            type="button"
                                            className="btn btn-outline-danger btn-sm"
                                            onClick={() => handleRemoveOption(index)}
                                        >
                                            <Icon name="trash-can" size={16} />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>

                <div className="input-group mt-2">
                    <input
                        type="text"
                        className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                        value={newOption}
                        onChange={(e) => setNewOption(e.target.value)}
                        placeholder="Nueva opción..."
                        onKeyPress={(e) => e.key === 'Enter' && e.preventDefault()}
                    />
                    <button
                        type="button"
                        className="btn btn-outline-primary"
                        onClick={handleAddOption}
                        disabled={!newOption.trim() || questionData.opciones.length >= 4}
                    >
                        <Icon name="plus" size={20} />
                    </button>
                </div>
                {questionData.opciones.length >= 4 && (
                    <small className="text-warning">
                        Máximo 4 opciones por pregunta
                    </small>
                )}
            </div>
        </div>
    );
};

export default QuestionForm;