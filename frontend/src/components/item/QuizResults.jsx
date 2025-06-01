import React, { useState, useEffect } from 'react';
import { useTheme } from '../../context/ThemeContext';
import axios from '../../utils/axios';
import Icon from '../Icon';
import Loader from '../common/Loader';

const QuizResults = ({ intentoId, quizId, courseId, onBack }) => {
    const { isDarkMode } = useTheme();
    const [resultados, setResultados] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchResultados = async () => {
            try {
                setLoading(true);
                const response = await axios.get(`/api/item/${courseId}/quiz/${quizId}/results/${intentoId}`);
                setResultados(response.data);
            } catch (error) {
                setError(error.response?.data?.message || 'Error al cargar los resultados');
            } finally {
                setLoading(false);
            }
        };

        fetchResultados();
    }, [courseId, quizId, intentoId]);

    if (loading) {
        return (
            <div className="text-center py-5">
                <Loader />
                <p className="mt-3">Cargando resultados...</p>
            </div>
        );
    }

    if (error) {
        return (
            <div className="alert alert-danger">
                <Icon name="warning" size={20} className="me-2" />
                {error}
            </div>
        );
    }

    if (!resultados) {
        return (
            <div className="alert alert-warning">
                <Icon name="info" size={20} className="me-2" />
                No se encontraron resultados
            </div>
        );
    }

    const { intento, quiz, estadisticas, resultados: preguntasResultados } = resultados;

    return (
        <div className={`quiz-results ${isDarkMode ? 'text-light' : ''}`}>
            {/* Header con información general */}
            <div className="d-flex justify-content-between align-items-center mb-4">
                <h4 className="mb-0">
                    <Icon name="gamepad" size={24} className="me-2" />
                    Resultados: {quiz.titulo}
                </h4>
                <button
                    className="btn btn-outline-secondary"
                    onClick={onBack}
                >
                    <Icon name="controller-fast-backward" size={20} className="me-2" />
                    Volver
                </button>
            </div>

            {/* Resumen de calificación */}
            <div className="card mb-4">
                <div className="card-body">
                    <div className="row">
                        <div className="col-md-3 text-center">
                            <h2 className={`mb-1 ${
                                parseFloat(intento.calificacion) >= 7 ? 'text-success' : 
                                parseFloat(intento.calificacion) >= 5 ? 'text-warning' : 'text-danger'
                            }`}>
                                {intento.calificacion}/10
                            </h2>
                            <small className="text-muted">Calificación Final</small>
                        </div>
                        <div className="col-md-3 text-center">
                            <h4 className="text-success mb-1">{estadisticas.preguntasCorrectas}</h4>
                            <small className="text-muted">Correctas</small>
                        </div>
                        <div className="col-md-3 text-center">
                            <h4 className="text-danger mb-1">{estadisticas.preguntasIncorrectas}</h4>
                            <small className="text-muted">Incorrectas</small>
                        </div>
                        <div className="col-md-3 text-center">
                            <h4 className="text-info mb-1">{estadisticas.porcentajeAcierto}%</h4>
                            <small className="text-muted">Porcentaje de Acierto</small>
                        </div>
                    </div>
                    
                    <div className="mt-3">
                        <div className="progress">
                            <div 
                                className={`progress-bar ${
                                    estadisticas.porcentajeAcierto >= 70 ? 'bg-success' : 
                                    estadisticas.porcentajeAcierto >= 50 ? 'bg-warning' : 'bg-danger'
                                }`}
                                style={{ width: `${estadisticas.porcentajeAcierto}%` }}
                            >
                                {estadisticas.porcentajeAcierto}%
                            </div>
                        </div>
                    </div>

                    <div className="row mt-3 text-center">
                        <div className="col-md-6">
                            <small className="text-muted">
                                <Icon name="calendar" size={16} className="me-1" />
                                Realizado: {new Date(intento.fechaFin).toLocaleString()}
                            </small>
                        </div>
                        <div className="col-md-6">
                            <small className="text-muted">
                                <Icon name="star" size={16} className="me-1" />
                                Puntos: {intento.puntuacionTotal}/{quiz.puntosTotales}
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {/* Resultados por pregunta */}
            <div className="preguntas-resultados">
                <h5 className="mb-3">
                    <Icon name="list" size={20} className="me-2" />
                    Revisión Detallada
                </h5>

                {preguntasResultados.map((resultado, index) => (
                    <div key={resultado.pregunta.id} className="card mb-4">
                        <div className="card-header d-flex justify-content-between align-items-center">
                            <h6 className="mb-0">
                                Pregunta {index + 1}
                                <span className={`badge ms-2 ${
                                    resultado.esCorrecta ? 'bg-success' : 'bg-danger'
                                }`}>
                                    {resultado.esCorrecta ? 'Correcta' : 'Incorrecta'}
                                </span>
                            </h6>
                            <small className="text-muted">
                                {resultado.puntosObtenidos}/{resultado.pregunta.puntos} puntos
                            </small>
                        </div>
                        
                        <div className="card-body">
                            <p className="fw-bold">{resultado.pregunta.texto}</p>
                            
                            <div className="opciones-resultados">
                                {resultado.opciones.map((opcion) => {
                                    let className = 'option-result p-3 mb-2 rounded border';
                                    let icon = null;
                                    
                                    // Determinar el estilo según el estado de la opción
                                    if (opcion.esCorrecta) {
                                        className += ' border-success bg-success bg-opacity-10';
                                        icon = <Icon name="checkmark1" size={16} className="text-success me-2" />;
                                    } else if (resultado.respuestaUsuario && resultado.respuestaUsuario.id === opcion.id) {
                                        className += ' border-danger bg-danger bg-opacity-10';
                                        icon = <Icon name="cross" size={16} className="text-danger me-2" />;
                                    } else {
                                        className += isDarkMode ? ' border-secondary bg-secondary bg-opacity-10' : ' border-light bg-light';
                                    }

                                    return (
                                        <div key={opcion.id} className={className}>
                                            <div className="d-flex align-items-start">
                                                {icon}
                                                <div className="flex-grow-1">
                                                    <div className="fw-semibold">{opcion.texto}</div>
                                                    
                                                    {/* Mostrar etiquetas */}
                                                    <div className="mt-2">
                                                        {opcion.esCorrecta && (
                                                            <span className="badge bg-success me-2">
                                                                <Icon name="checkmark1" size={12} className="me-1" />
                                                                Respuesta Correcta
                                                            </span>
                                                        )}
                                                        {resultado.respuestaUsuario && resultado.respuestaUsuario.id === opcion.id && (
                                                            <span className="badge bg-info">
                                                                <Icon name="user" size={12} className="me-1" />
                                                                Tu Respuesta
                                                            </span>
                                                        )}
                                                    </div>
                                                    
                                                    {/* Mostrar retroalimentación si existe */}
                                                    {opcion.retroalimentacion && (
                                                        <div className="mt-2 p-2 rounded bg-info bg-opacity-10 border border-info border-opacity-25">
                                                            <small className="text-info">
                                                                <Icon name="info" size={14} className="me-1" />
                                                                <strong>Retroalimentación:</strong> {opcion.retroalimentacion}
                                                            </small>
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>

                            {/* Mostrar retroalimentación específica de la respuesta seleccionada */}
                            {resultado.retroalimentacion && (
                                <div className="mt-3 p-3 rounded bg-primary bg-opacity-10 border border-primary border-opacity-25">
                                    <h6 className="text-primary mb-2">
                                        <Icon name="message" size={16} className="me-2" />
                                        Retroalimentación para tu respuesta:
                                    </h6>
                                    <p className="mb-0">{resultado.retroalimentacion}</p>
                                </div>
                            )}
                        </div>
                    </div>
                ))}
            </div>

            {/* Botón para volver */}
            <div className="text-center mt-4">
                <button
                    className="btn btn-primary"
                    onClick={onBack}
                >
                    <Icon name="controller-fast-backward" size={20} className="me-2" />
                    Volver al Quiz
                </button>
            </div>
        </div>
    );
};

export default QuizResults;
