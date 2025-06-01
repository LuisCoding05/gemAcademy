import React, { useState, useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import { useTheme } from '../../context/ThemeContext';
import axios from '../../utils/axios';
import Icon from '../Icon';
import Loader from '../common/Loader';
import QuizAttempt from './QuizAttempt';
import QuizForm from './QuizForm';
import QuizResults from './QuizResults';

const QuizItem = ({ item, courseId }) => {
    const { isDarkMode } = useTheme();
    const location = useLocation();
    const [loading, setLoading] = useState(false);
    const [currentIntento, setCurrentIntento] = useState(null);
    const [error, setError] = useState(null);
    const [isEditing, setIsEditing] = useState(location.state?.isEditing || false);
    const [currentItem, setCurrentItem] = useState(item);
    const [showResults, setShowResults] = useState(null); // Estado para mostrar resultados

    const intentosCompletados = item.intentos?.filter(i => i.completado)?.length || 0;
    const puedeIntentar = item.intentosPermitidos === 0 || intentosCompletados < item.intentosPermitidos;
    const intentoEnProgreso = item.intentos?.find(i => !i.completado);
    const ultimoIntentoCompletado = item.intentos?.filter(i => i.completado)?.sort((a, b) => 
        new Date(b.fechaFin) - new Date(a.fechaFin)
    )[0];

    const handleStartQuiz = async () => {
        try {
            setLoading(true);
            const response = await axios.post(`/api/item/${courseId}/quiz/${item.id}/start`);
            setCurrentIntento(response.data);
            setError(null);
        } catch (error) {
            setError(error.response?.data?.message || 'Error al iniciar el quiz');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        // Verificar si hay intentos sin completar
        const checkAbandonedAttempts = async () => {
            const abandonedAttempt = item.intentos?.find(i => !i.completado);
            if (abandonedAttempt) {
                try {
                    const response = await axios.post(
                        `/api/item/${courseId}/quiz/${item.id}/check-abandoned/${abandonedAttempt.id}`
                    );
                    
                    if (!response.data.abandoned) {
                        // Si el intento aún es válido, permitir continuar
                        setCurrentIntento({
                            ...abandonedAttempt,
                            tiempoLimite: Math.floor(response.data.tiempoRestante / 60)
                        });
                    } else {
                        // Si el intento fue marcado como abandonado, recargar la página
                        window.location.reload();
                    }
                } catch (error) {
                    console.error('Error al verificar intento abandonado:', error);
                }
            }
        };

        checkAbandonedAttempts();
    }, [item.intentos, item.id, courseId]);

    useEffect(() => {
        if (isEditing) {
            const fetchQuizDetails = async () => {
                try {
                    setLoading(true);
                    const response = await axios.get(`/api/item/${courseId}/quiz/${item.id}/edit`);
                    setCurrentItem(response.data);
                } catch (error) {
                    setError(error.response?.data?.message || 'Error al cargar los detalles del quiz');
                } finally {
                    setLoading(false);
                }
            };
            fetchQuizDetails();
        }
    }, [isEditing, courseId, item.id]);

    const handleUpdate = async (formData) => {
        try {
            setLoading(true);
            setError(null);

            // Actualizar quiz
            const quizResponse = await axios.put(`/api/item/${courseId}/quiz/${item.id}`, {
                titulo: formData.titulo,
                descripcion: formData.descripcion,
                fechaLimite: formData.fechaLimite,
                tiempoLimite: formData.tiempoLimite,
                intentosPermitidos: formData.intentosPermitidos
            });

            // Si el quiz se actualizó correctamente, actualizar las preguntas
            for (const pregunta of formData.preguntas) {
                if (pregunta.id) {
                    // Si la pregunta existe, actualizarla
                    await axios.put(`/api/item/${courseId}/quiz/${item.id}/preguntas/${pregunta.id}`, {
                        pregunta: pregunta.pregunta,
                        puntos: parseInt(pregunta.puntos),
                        orden: pregunta.orden,
                        opciones: pregunta.opciones
                    });
                } else {
                    // Si es una pregunta nueva, crearla
                    await axios.post(`/api/item/${courseId}/quiz/${item.id}/preguntas`, {
                        pregunta: pregunta.pregunta,
                        puntos: parseInt(pregunta.puntos),
                        orden: pregunta.orden,
                        opciones: pregunta.opciones
                    });
                }
            }

            // Recargar el quiz para obtener los puntos totales actualizados
            const response = await axios.get(`/api/item/${courseId}/quiz/${item.id}`);
            setCurrentItem(response.data);
            setIsEditing(false);

        } catch (error) {
            setError(error.response?.data?.message || 'Error al actualizar el quiz');
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return <Loader />;
    }

    if (error) {
        return (
            <div className="alert alert-danger">
                {error}
            </div>
        );
    }

    if (isEditing) {
        const formattedData = {
            ...currentItem,
            preguntas: currentItem.preguntas || [],
            fechaLimite: currentItem.fechaLimite?.includes('T') ? 
                currentItem.fechaLimite : 
                currentItem.fechaLimite?.replace(' ', 'T')
        };

        return (
            <QuizForm
                initialData={formattedData}
                onSubmit={handleUpdate}
                onCancel={() => setIsEditing(false)}
            />
        );
    }    // Si se están mostrando los resultados
    if (showResults) {
        return (
            <QuizResults
                intentoId={showResults}
                quizId={item.id}
                courseId={courseId}
                onBack={() => setShowResults(null)}
            />
        );
    }

    if (currentIntento) {
        return (
            <div className="quiz-container">
                <QuizAttempt 
                    intento={currentIntento}
                    quizId={item.id}
                    courseId={courseId}
                    onComplete={() => {
                        setCurrentIntento(null);
                        window.location.reload(); // Recargar para ver el resultado
                    }}
                />
            </div>
        );
    }    return (
        <div className="quiz-details">
            {/* Descripción del quiz */}
            {item.descripcion && (
                <div className="mb-4">
                    <h5>Descripción</h5>
                    <div 
                        className={`ck-content quiz-description ${isDarkMode ? 'text-light' : ''}`}
                        dangerouslySetInnerHTML={{ __html: item.descripcion }}
                    />
                </div>
            )}

            <div className="mb-4">
                <h5>Puntos</h5>
                <p>{item.puntos} puntos (calificación sobre 10)</p>
            </div>
            
            {item.tiempoLimite && (
                <div className="mb-4">
                    <h5>Tiempo límite</h5>
                    <p>{item.tiempoLimite} minutos</p>
                </div>
            )}

            <div className="mb-4">
                <h5>Intentos permitidos</h5>
                <p>{(item.intentosPermitidos === 0 || null) ? 'Ilimitados' : `${intentosCompletados} de ${item.intentosPermitidos}`}</p>
            </div>
            
            {item.intentos && item.intentos.length > 0 && (
                <div className="mb-4">
                    <h5>Intentos realizados</h5>
                    <div className="list-group">
                        {item.intentos.map((intento, index) => (
                            <div key={index} className={`list-group-item ${isDarkMode ? 'bg-secondary text-light' : ''}`}>
                                <div className="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small className="text-muted">
                                            Inicio: {new Date(intento.fechaInicio).toLocaleString()}
                                        </small>
                                        {intento.fechaFin && (
                                            <small className="text-muted d-block">
                                                Fin: {new Date(intento.fechaFin).toLocaleString()}
                                            </small>
                                        )}
                                    </div>                                    <div>
                                        <span className={`badge ${intento.completado ? 'bg-success' : 'bg-warning'}`}>
                                            {intento.completado ? 'Completado' : 'En progreso'}
                                        </span>
                                        {intento.calificacion !== null && intento.completado && (
                                            <span className="badge bg-primary ms-2">
                                                {intento.calificacion}/10
                                            </span>
                                        )}
                                        {intento.completado && (
                                            <button
                                                className="btn btn-sm btn-outline-info ms-2"
                                                onClick={() => setShowResults(intento.id)}
                                                title="Ver resultados detallados"
                                            >
                                                <Icon name="eye" size={14} />
                                            </button>
                                        )}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}            {error && (
                <div className="alert alert-danger" role="alert">
                    {error}
                </div>
            )}            {/* Botón para ver resultados del último intento completado */}
            {ultimoIntentoCompletado && (
                <div className="mb-3">
                    <button 
                        className="btn btn-outline-info me-2"
                        onClick={() => setShowResults(ultimoIntentoCompletado.id)}
                    >
                        <Icon name="eye" className="me-2" />
                        Ver Últimos Resultados
                    </button>
                </div>
            )}

            {!intentoEnProgreso && puedeIntentar && (
                <button 
                    className="btn btn-primary mb-3"
                    onClick={handleStartQuiz}
                >
                    <Icon name="rocket" className="me-2" />
                    Comenzar Quiz
                </button>
            )}

            {!puedeIntentar && (
                <div className="alert alert-info">
                    Has alcanzado el número máximo de intentos permitidos.
                </div>
            )}

            {intentoEnProgreso && (
                <div className="alert alert-warning">
                    Tienes un intento sin completar. Debes terminarlo antes de comenzar uno nuevo.
                </div>
            )}
        </div>
    );
};

export default QuizItem;