import React from 'react';
import { useTheme } from '../../context/ThemeContext';
import Icon from '../Icon';

const QuizItem = ({ item }) => {
    const { isDarkMode } = useTheme();

    return (
        <div className="quiz-details">
            <div className="mb-4">
                <h5>Puntos</h5>
                <p>{item.puntos} puntos</p>
            </div>
            
            {item.tiempoLimite && (
                <div className="mb-4">
                    <h5>Tiempo límite</h5>
                    <p>{item.tiempoLimite} minutos</p>
                </div>
            )}
            
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
                                        {intento.puntuacionTotal && (
                                            <span className="badge bg-primary ms-2">
                                                {intento.puntuacionTotal} puntos
                                            </span>
                                        )}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
};

export default QuizItem;