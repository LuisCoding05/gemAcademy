import React, { useState, useEffect, useRef } from 'react';
import { useTheme } from '../../context/ThemeContext';
import axios from '../../utils/axios';
import Icon from '../Icon';
import Loader from '../common/Loader';

const QuizAttempt = ({ intento, quizId, courseId, onComplete }) => {
    const { isDarkMode } = useTheme();
    const [currentQuestion, setCurrentQuestion] = useState(0);
    const [preguntas, setPreguntas] = useState([]);
    const [respuestas, setRespuestas] = useState({});
    const [preguntasRespondidas, setPreguntasRespondidas] = useState(new Set());
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [tiempoRestante, setTiempoRestante] = useState(null);
    
    // Referencias para mantener el estado entre renders
    const timerRef = useRef(null);
    const hasSubmittedRef = useRef(false);

    useEffect(() => {
        const fetchPreguntas = async () => {
            try {
                const response = await axios.get(`/api/item/${courseId}/quiz/${quizId}/preguntas/${intento.id}`);
                setPreguntas(response.data);
                
                // Inicializar el tiempo
                const tiempoInicial = intento.tiempoLimite * 60;
                setTiempoRestante(tiempoInicial);
            } catch (error) {
                setError(error.response?.data?.message || 'Error al cargar las preguntas');
            } finally {
                setLoading(false);
            }
        };

        fetchPreguntas();

        return () => {
            if (timerRef.current) {
                clearInterval(timerRef.current);
            }
        };
    }, [courseId, quizId, intento.id, intento.tiempoLimite]);

    useEffect(() => {
        if (tiempoRestante === null) return;

        // Limpiar el timer anterior si existe
        if (timerRef.current) {
            clearInterval(timerRef.current);
        }

        timerRef.current = setInterval(() => {
            setTiempoRestante(prev => {
                if (prev <= 1) {
                    clearInterval(timerRef.current);
                    handleSubmit();
                    return 0;
                }
                return Math.floor(prev - 1);
            });
        }, 1000);

        return () => {
            if (timerRef.current) {
                clearInterval(timerRef.current);
            }
        };
    }, [tiempoRestante]);

    const handleSubmit = async () => {
        if (loading) return;
        
        try {
            setLoading(true);
            // Marcar como enviado antes de hacer la petición
            hasSubmittedRef.current = true;
            await axios.post(`/api/item/${courseId}/quiz/${quizId}/submit/${intento.id}`, {
                respuestas
            });
            onComplete();
        } catch (error) {
            hasSubmittedRef.current = false; // Restablecer si hay error
            setError(error.response?.data?.message || 'Error al enviar el quiz');
            setLoading(false);
        }
    };

    const formatTiempo = (segundos) => {
        if (segundos === null) return "--:--";
        const minutos = Math.floor(segundos / 60);
        const segs = Math.floor(segundos % 60);
        return `${minutos}:${segs.toString().padStart(2, '0')}`;
    };

    const handleOpcionSelect = (preguntaId, opcionId) => {
        setRespuestas(prev => ({
            ...prev,
            [preguntaId]: opcionId
        }));
        
        setPreguntasRespondidas(prev => {
            const newSet = new Set(prev);
            newSet.add(preguntaId);
            return newSet;
        });
    };

    // Manejar cuando el usuario intenta salir de la página
    useEffect(() => {
        const handleBeforeUnload = (e) => {
            if (!hasSubmittedRef.current) {
                const mensaje = '¿Seguro que quieres salir? Se perderá el progreso del quiz.';
                e.preventDefault();
                e.returnValue = mensaje;
                return mensaje;
            }
        };

        const handleVisibilityChange = () => {
            if (document.hidden && tiempoRestante <= 0) {
                handleSubmit();
            }
        };

        window.addEventListener('beforeunload', handleBeforeUnload);
        document.addEventListener('visibilitychange', handleVisibilityChange);

        return () => {
            window.removeEventListener('beforeunload', handleBeforeUnload);
            document.removeEventListener('visibilitychange', handleVisibilityChange);
        };
    }, [tiempoRestante]);

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

    const preguntaActual = preguntas[currentQuestion];

    return (
        <div className={`quiz-attempt ${isDarkMode ? 'text-light' : ''}`}>
            <div className="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5>Pregunta {currentQuestion + 1} de {preguntas.length}</h5>
                </div>
                <div className={`tiempo-restante ${tiempoRestante < 60 ? 'text-danger' : ''}`}>
                    <Icon name="clock" className="me-2" />
                    {formatTiempo(tiempoRestante)}
                </div>
            </div>

            <div className="pregunta-container mb-4">
                <div className="pregunta mb-3">
                    <h6>{preguntaActual?.pregunta}</h6>
                    <small className="text-muted">({preguntaActual?.puntos} puntos)</small>
                </div>

                <div className="opciones-list">
                    {preguntaActual?.opciones.map((opcion) => (
                        <div 
                            key={opcion.id}
                            className={`opcion-item mb-2 p-3 rounded ${
                                respuestas[preguntaActual.id] === opcion.id ? 
                                'bg-primary text-white' : 
                                isDarkMode ? 'bg-secondary text-light' : 'bg-light'
                            }`}
                            onClick={() => handleOpcionSelect(preguntaActual.id, opcion.id)}
                            style={{ cursor: 'pointer' }}
                        >
                            {opcion.texto}
                        </div>
                    ))}
                </div>
            </div>

            <div className="d-flex justify-content-between">
                <button
                    className="btn btn-secondary"
                    onClick={() => setCurrentQuestion(prev => Math.max(0, prev - 1))}
                    disabled={currentQuestion === 0}
                >
                    <Icon name="controller-fast-backward" className="me-2" />
                    Anterior
                </button>

                {currentQuestion < preguntas.length - 1 ? (
                    <button
                        className="btn btn-primary"
                        onClick={() => setCurrentQuestion(prev => prev + 1)}
                        disabled={!respuestas[preguntaActual?.id]}
                    >
                        Siguiente
                        <Icon name="arrow-right" className="ms-2" />
                    </button>
                ) : (
                    <button
                        className="btn btn-success"
                        onClick={handleSubmit}
                        disabled={preguntasRespondidas.size !== preguntas.length}
                    >
                        Finalizar Quiz
                        <Icon name="checkmark1" className="ms-2" />
                    </button>
                )}
            </div>

            <div className="progress mt-4">
                <div 
                    className="progress-bar" 
                    role="progressbar" 
                    style={{ 
                        width: `${(preguntasRespondidas.size / preguntas.length) * 100}%` 
                    }}
                >
                    {preguntasRespondidas.size} de {preguntas.length} respondidas
                </div>
            </div>
        </div>
    );
};

export default QuizAttempt;