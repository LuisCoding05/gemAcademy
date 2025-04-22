import React, { useState } from 'react';
import { useTheme } from '../../context/ThemeContext';
import Editor from '../common/Editor';
import Icon from '../Icon';
import QuestionForm from './QuestionForm';

const QuizForm = ({ onSubmit, onCancel, initialData = {} }) => {
    const { isDarkMode } = useTheme();
    const [formData, setFormData] = useState({
        titulo: initialData?.titulo || '',
        descripcion: initialData?.descripcion || '',
        fechaLimite: initialData?.fechaLimite?.includes('T') ? 
            initialData.fechaLimite : 
            initialData?.fechaLimite?.replace(' ', 'T') || '',
        tiempoLimite: initialData?.tiempoLimite || 30,
        intentosPermitidos: initialData?.intentosPermitidos || 1,
        preguntas: initialData?.preguntas || [],
        id: initialData?.id || null
    });
    const [previewMode, setPreviewMode] = useState(false);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
    };

    const handleDescriptionChange = (content) => {
        setFormData(prev => ({
            ...prev,
            descripcion: content
        }));
    };

    const handleAddQuestion = () => {
        setFormData(prev => ({
            ...prev,
            preguntas: [
                ...prev.preguntas,
                {
                    pregunta: '',
                    puntos: 10,
                    orden: prev.preguntas.length + 1,
                    opciones: []
                }
            ]
        }));
    };

    const handleQuestionChange = (index, questionData) => {
        setFormData(prev => ({
            ...prev,
            preguntas: prev.preguntas.map((p, i) => 
                i === index ? questionData : p
            )
        }));
    };

    const handleRemoveQuestion = (index) => {
        setFormData(prev => ({
            ...prev,
            preguntas: prev.preguntas.filter((_, i) => i !== index)
        }));
    };

    const handleMoveQuestion = (index, direction) => {
        const newPreguntas = [...formData.preguntas];
        const newIndex = index + direction;

        if (newIndex >= 0 && newIndex < newPreguntas.length) {
            // Intercambiar preguntas
            [newPreguntas[index], newPreguntas[newIndex]] = [newPreguntas[newIndex], newPreguntas[index]];
            // Actualizar orden
            newPreguntas.forEach((pregunta, i) => {
                pregunta.orden = i + 1;
            });

            setFormData(prev => ({
                ...prev,
                preguntas: newPreguntas
            }));
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        // Al enviar el formulario, no enviamos los puntos totales ya que se calcularán en el backend
        const formDataToSend = {
            ...formData,
            preguntas: formData.preguntas.map(pregunta => ({
                ...pregunta,
                puntos: parseInt(pregunta.puntos) // Asegurar que los puntos sean números
            }))
        };
        delete formDataToSend.puntosTotales; // Eliminar puntos totales del envío
        onSubmit(formDataToSend);
    };

    return (
        <div className="quiz-form">
            <div className="d-flex justify-content-between align-items-center mb-3">
                <h4>{initialData.id ? 'Editar Quiz' : 'Nuevo Quiz'}</h4>
                <div>
                    <button
                        type="button"
                        className={`btn ${previewMode ? 'btn-primary' : 'btn-outline-primary'} me-2`}
                        onClick={() => setPreviewMode(!previewMode)}
                    >
                        <Icon name="eye" size={20} className="me-2" />
                        {previewMode ? 'Editar' : 'Vista previa'}
                    </button>
                    <button
                        type="button"
                        className="btn btn-outline-secondary"
                        onClick={onCancel}
                    >
                        <Icon name="cross" size={20} className="me-2" />
                        Cancelar
                    </button>
                </div>
            </div>

            {previewMode ? (
                <div className="preview-mode">
                    <h2>{formData.titulo}</h2>
                    {formData.descripcion && (
                        <div 
                            className="ck-content mb-4"
                            dangerouslySetInnerHTML={{ __html: formData.descripcion }}
                        />
                    )}
                    <div className="card mb-4">
                        <div className="card-body">
                            <h5>Detalles del Quiz</h5>
                            <ul className="list-unstyled">
                                <li><strong>Tiempo límite:</strong> {formData.tiempoLimite} minutos</li>
                                <li><strong>Intentos permitidos:</strong> {formData.intentosPermitidos === 0 ? 'Ilimitados' : formData.intentosPermitidos}</li>
                                <li><strong>Fecha límite:</strong> {new Date(formData.fechaLimite).toLocaleString()}</li>
                                <li><strong>Puntos totales:</strong> {formData.preguntas.reduce((sum, p) => sum + (p.puntos || 0), 0)}</li>
                            </ul>
                        </div>
                    </div>
                    <div className="preguntas-preview">
                        {formData.preguntas.map((pregunta, index) => (
                            <div key={index} className="card mb-3">
                                <div className="card-header">
                                    <h5 className="mb-0">Pregunta {index + 1}</h5>
                                    <small className="text-muted">({pregunta.puntos} puntos)</small>
                                </div>
                                <div className="card-body">
                                    <p>{pregunta.pregunta}</p>
                                    <div className="opciones-list">
                                        {pregunta.opciones.map((opcion, optIndex) => (
                                            <div 
                                                key={optIndex}
                                                className={`option-item p-2 rounded mb-2 ${
                                                    opcion.esCorrecta ? 'bg-success text-white' : isDarkMode ? 'bg-secondary' : 'bg-light'
                                                }`}
                                            >
                                                {opcion.texto}
                                                {opcion.retroalimentacion && (
                                                    <small className="d-block text-muted mt-1">
                                                        Retroalimentación: {opcion.retroalimentacion}
                                                    </small>
                                                )}
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            ) : (
                <form onSubmit={handleSubmit}>
                    <div className="mb-3">
                        <label className="form-label">Título</label>
                        <input
                            type="text"
                            className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                            name="titulo"
                            value={formData.titulo}
                            onChange={handleChange}
                            required
                        />
                    </div>

                    <div className="mb-3">
                        <label className="form-label">Descripción</label>
                        <Editor
                            data={formData.descripcion}
                            onChange={handleDescriptionChange}
                            placeholder="Escribe la descripción del quiz aquí..."
                        />
                    </div>

                    <div className="row mb-3">
                        <div className="col-md-6">
                            <label className="form-label">Fecha límite de entrega</label>
                            <input
                                type="datetime-local"
                                className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                                name="fechaLimite"
                                value={formData.fechaLimite}
                                onChange={handleChange}
                                required
                            />
                        </div>
                        <div className="col-md-3">
                            <label className="form-label">Tiempo límite (minutos)</label>
                            <input
                                type="number"
                                className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                                name="tiempoLimite"
                                value={formData.tiempoLimite}
                                onChange={handleChange}
                                min="1"
                                required
                            />
                        </div>
                        <div className="col-md-3">
                            <label className="form-label">Intentos permitidos</label>
                            <input
                                type="number"
                                className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                                name="intentosPermitidos"
                                value={formData.intentosPermitidos}
                                onChange={handleChange}
                                min="0"
                                title="0 para intentos ilimitados"
                                required
                            />
                            <small className="text-muted">0 para ilimitados</small>
                        </div>
                    </div>

                    <div className="mb-3">
                        <div className="d-flex justify-content-between align-items-center mb-2">
                            <h5>Preguntas</h5>
                            <button
                                type="button"
                                className="btn btn-success"
                                onClick={handleAddQuestion}
                            >
                                <Icon name="plus" size={20} className="me-2" />
                                Añadir pregunta
                            </button>
                        </div>

                        <div className="preguntas-container">
                            {formData.preguntas.map((pregunta, index) => (
                                <div key={index} className="card mb-3">
                                    <div className="card-header d-flex justify-content-between align-items-center">
                                        <h6 className="mb-0">Pregunta {index + 1}</h6>
                                        <div>
                                            <button
                                                type="button"
                                                className="btn btn-outline-secondary btn-sm me-2"
                                                onClick={() => handleMoveQuestion(index, -1)}
                                                disabled={index === 0}
                                            >
                                                <Icon name="forward" size={16} />
                                            </button>
                                            <button
                                                type="button"
                                                className="btn btn-outline-secondary btn-sm me-2"
                                                onClick={() => handleMoveQuestion(index, 1)}
                                                disabled={index === formData.preguntas.length - 1}
                                            >
                                                <Icon name="controller-fast-backward" size={16} />
                                            </button>
                                            <button
                                                type="button"
                                                className="btn btn-outline-danger btn-sm"
                                                onClick={() => handleRemoveQuestion(index)}
                                            >
                                                <Icon name="trash-can" size={16} />
                                            </button>
                                        </div>
                                    </div>
                                    <div className="card-body">
                                        <QuestionForm
                                            questionData={pregunta}
                                            onChange={(data) => handleQuestionChange(index, data)}
                                        />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="d-flex justify-content-end mb-3 gap-2">
                        <button
                            type="button"
                            className="btn btn-secondary"
                            onClick={onCancel}
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            className="btn btn-primary"
                            disabled={formData.preguntas.length === 0}
                        >
                            {initialData.id ? 'Guardar cambios' : 'Crear Quiz'}
                        </button>
                    </div>
                </form>
            )}
        </div>
    );
};

export default QuizForm;