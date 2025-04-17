import React, { useState, useEffect } from 'react';
import { useTheme } from '../../context/ThemeContext';
import axios from '../../utils/axios';
import Icon from '../Icon';
import Loader from '../common/Loader';
import QuizAttempt from './QuizAttempt';

const QuizItem = ({ item, courseId }) => {
    const { isDarkMode } = useTheme();
    const [loading, setLoading] = useState(false);
    const [currentIntento, setCurrentIntento] = useState(null);
    const [error, setError] = useState(null);

    const intentosCompletados = item.intentos?.filter(i => i.completado)?.length || 0;
    const puedeIntentar = item.intentosPermitidos === 0 || intentosCompletados < item.intentosPermitidos;
    const intentoEnProgreso = item.intentos?.find(i => !i.completado);

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

    if (loading) {
        return <Loader />;
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
    }

    return (
        <div className="quiz-details">
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
                                    </div>
                                    <div>
                                        <span className={`badge ${intento.completado ? 'bg-success' : 'bg-warning'}`}>
                                            {intento.completado ? 'Completado' : 'En progreso'}
                                        </span>
                                        {intento.puntuacionTotal !== null && (
                                            <span className="badge bg-primary ms-2">
                                                {((intento.puntuacionTotal / item.puntos) * 10).toFixed(2)}/10
                                            </span>
                                        )}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {error && (
                <div className="alert alert-danger" role="alert">
                    {error}
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