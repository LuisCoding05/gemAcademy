import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useTheme } from '../../context/ThemeContext';
import axios from '../../utils/axios';
import Icon from '../Icon';
import Loader from '../common/Loader';

const EntregaDetalle = () => {
    const { courseId, tareaId, entregaId } = useParams();
    const { isDarkMode } = useTheme();
    const navigate = useNavigate();
    const [entrega, setEntrega] = useState(null);
    const [tarea, setTarea] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [submitting, setSubmitting] = useState(false);
    const [calificacion, setCalificacion] = useState('');
    const [comentarioProfesor, setComentarioProfesor] = useState('');

    useEffect(() => {
        const fetchData = async () => {
            try {
                setLoading(true);
                // Obtener detalles de la tarea
                const [tareaResponse, entregaResponse] = await Promise.all([
                    axios.get(`/api/item/${courseId}/tarea/${tareaId}`),
                    axios.get(`/api/item/${courseId}/tarea/${tareaId}/entrega/${entregaId}`)
                ]);

                setTarea(tareaResponse.data);
                setEntrega(entregaResponse.data);
                
                // Inicializar los campos de calificación si existen
                if (entregaResponse.data.calificacion) {
                    setCalificacion(entregaResponse.data.calificacion);
                }
                if (entregaResponse.data.comentarioProfesor) {
                    setComentarioProfesor(entregaResponse.data.comentarioProfesor);
                }
            } catch (error) {
                console.error('Error al cargar los detalles:', error);
                setError(error.response?.data?.message || 'Error al cargar los detalles');
            } finally {
                setLoading(false);
            }
        };

        fetchData();
    }, [courseId, tareaId, entregaId]);

    const handleCalificar = async (e) => {
        e.preventDefault();
        if (!calificacion) {
            setError('La calificación es requerida');
            return;
        }

        try {
            setSubmitting(true);
            setError(null);

            const response = await axios.post(
                `/api/item/${courseId}/tarea/${tareaId}/entrega/${entregaId}/calificar`,
                {
                    calificacion: parseFloat(calificacion),
                    comentario: comentarioProfesor
                }
            );

            setEntrega(prev => ({
                ...prev,
                ...response.data.entrega
            }));

            // Mostrar mensaje de éxito temporal
            const successMessage = document.createElement('div');
            successMessage.className = 'alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3';
            successMessage.style.zIndex = '1050';
            successMessage.textContent = 'Entrega calificada con éxito';
            document.body.appendChild(successMessage);

            setTimeout(() => {
                document.body.removeChild(successMessage);
                navigate(`/cursos/${courseId}/tarea/${tareaId}`);
            }, 2000);

        } catch (error) {
            setError(error.response?.data?.message || 'Error al calificar la entrega');
        } finally {
            setSubmitting(false);
        }
    };

    const handleDownload = async (ficheroId) => {
        try {
            const response = await axios.get(`/api/download/${ficheroId}`, {
                responseType: 'blob'
            });
            
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            // Buscar el archivo en la entrega actual
            if (entrega?.archivo?.nombreOriginal) {
                link.setAttribute('download', entrega.archivo.nombreOriginal);
            }
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
        } catch (error) {
            console.error('Error al descargar el archivo:', error);
            setError('Error al descargar el archivo');
        }
    };

    if (loading) {
        return (
            <div className="container mt-4">
                <div className="text-center">
                    <Loader size="large" />
                    <p>Cargando detalles de la entrega...</p>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="container mt-4">
                <div className="alert alert-danger">
                    {error}
                </div>
                <button 
                    className="btn btn-primary"
                    onClick={() => navigate(`/cursos/${courseId}/tarea/${tareaId}`)}
                >
                    <Icon name="controller-fast-backward" size={20} className="me-2" />
                    Volver a la tarea
                </button>
            </div>
        );
    }

    if (!entrega || !tarea) {
        return (
            <div className="container mt-4">
                <div className="alert alert-warning">
                    No se encontró la entrega solicitada
                </div>
                <button 
                    className="btn btn-primary"
                    onClick={() => navigate(`/cursos/${courseId}/tarea/${tareaId}`)}
                >
                    <Icon name="controller-fast-backward" size={20} className="me-2" />
                    Volver a la tarea
                </button>
            </div>
        );
    }

    return (
        <div className="container mt-4">
            <div className="d-flex justify-content-between align-items-center mb-4">
                <h2>Revisar entrega</h2>
                <button 
                    className="btn btn-outline-primary"
                    onClick={() => navigate(`/cursos/${courseId}/tarea/${tareaId}`)}
                >
                    <Icon name="controller-fast-backward" size={20} className="me-2" />
                    Volver a la tarea
                </button>
            </div>

            <div className="row">
                <div className="col-md-8">
                    <div className={`card mb-4 ${isDarkMode ? 'bg-dark text-light' : ''}`}>
                        <div className="card-header">
                            <h4 className="card-title mb-0">
                                <Icon name="user" size={24} className="me-2" />
                                Información del estudiante
                            </h4>
                        </div>
                        <div className="card-body">
                            <div className="d-flex align-items-center">
                                <img
                                    src={entrega.estudiante.imagen || 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483544/default_bumnyb.webp'}
                                    alt={entrega.estudiante.nombre}
                                    className="rounded-circle me-3"
                                    width="64"
                                    height="64"
                                />
                                <div>
                                    <h5 className="mb-1">{entrega.estudiante.nombre}</h5>
                                    <p className="mb-0 text-muted">@{entrega.estudiante.username}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className={`card mb-4 ${isDarkMode ? 'bg-dark text-light' : ''}`}>
                        <div className="card-header">
                            <h4 className="card-title mb-0">
                                <Icon name="folder" size={24} className="me-2" />
                                Entrega
                            </h4>
                        </div>
                        <div className="card-body">
                            <div className="mb-3">
                                <h5>Estado</h5>
                                <span className={`badge bg-${getEstadoColor(entrega.estado)}`}>
                                    {getEstadoTexto(entrega.estado)}
                                </span>
                            </div>

                            <div className="mb-3">
                                <h5>Fecha de entrega</h5>
                                <p>{entrega.fechaEntrega ? new Date(entrega.fechaEntrega).toLocaleString() : 'No entregado'}</p>
                            </div>

                            {entrega.comentarioEstudiante && (
                                <div className="mb-3">
                                    <h5>Comentario del estudiante</h5>
                                    <p>{entrega.comentarioEstudiante}</p>
                                </div>
                            )}

                            {entrega.archivo && (
                                <div className="mb-3">
                                    <h5>Archivo adjunto</h5>
                                    <button 
                                        className="btn btn-link text-decoration-none p-0"
                                        onClick={() => handleDownload(entrega.archivo.id)}
                                    >
                                        <div className="d-flex align-items-center">
                                            <Icon name="folder" size={24} className="me-2 text-primary" />
                                            <span className="text-primary text-decoration-underline">
                                                {entrega.archivo.nombreOriginal}
                                            </span>
                                        </div>
                                    </button>
                                </div>
                            )}
                            {!entrega.archivo && (
                                <div className="mb-3">
                                    <h5>Archivo adjunto</h5>
                                    <div className="alert alert-info">
                                        <Icon name="notification" size={20} className="me-2" />
                                        El estudiante no ha adjuntado ningún archivo en esta entrega
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>

                    <div className={`card ${isDarkMode ? 'bg-dark text-light' : ''}`}>
                        <div className="card-header">
                            <h4 className="card-title mb-0">
                                <Icon name="pencil" size={24} className="me-2" />
                                Calificación
                            </h4>
                        </div>
                        <div className="card-body">
                            <form onSubmit={handleCalificar}>
                                <div className="mb-3">
                                    <label htmlFor="calificacion" className="form-label">
                                        Calificación (0-10)
                                    </label>
                                    <input
                                        type="number"
                                        className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                                        id="calificacion"
                                        value={calificacion}
                                        onChange={(e) => setCalificacion(e.target.value)}
                                        min="0"
                                        max="10"
                                        step="0.01"
                                        required
                                    />
                                    <small className="text-muted">
                                        Esto otorgará {calificacion ? Math.round((parseFloat(calificacion) * tarea.puntosMaximos) / 10) : 0} de {tarea.puntosMaximos} puntos posibles
                                    </small>
                                </div>

                                <div className="mb-3">
                                    <label htmlFor="comentario" className="form-label">
                                        Comentario para el estudiante
                                    </label>
                                    <textarea
                                        className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                                        id="comentario"
                                        rows="4"
                                        value={comentarioProfesor}
                                        onChange={(e) => setComentarioProfesor(e.target.value)}
                                        placeholder="Escribe un comentario para el estudiante..."
                                    />
                                </div>

                                <button
                                    type="submit"
                                    className="btn btn-primary"
                                    disabled={submitting}
                                >
                                    {submitting ? (
                                        <>
                                            <span className="spinner-border spinner-border-sm me-2" />
                                            Guardando...
                                        </>
                                    ) : (
                                        <>
                                            <Icon name="checkmark" size={20} className="me-2" />
                                            Guardar calificación
                                        </>
                                    )}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div className="col-md-4">
                    <div className={`card position-sticky ${isDarkMode ? 'bg-dark text-light' : ''}`} style={{ top: '1rem' }}>
                        <div className="card-header">
                            <h4 className="card-title mb-0">
                                <Icon name="info" size={24} className="me-2" />
                                Información de la tarea
                            </h4>
                        </div>
                        <div className="card-body">
                            <h5>{tarea.titulo}</h5>
                            <div className="mb-3">
                                <strong>Fecha límite:</strong>
                                <p>{new Date(tarea.fechaLimite).toLocaleString()}</p>
                            </div>
                            <div className="mb-3">
                                <strong>Puntos máximos:</strong>
                                <p>{tarea.puntosMaximos}</p>
                            </div>
                            {tarea.esObligatoria && (
                                <div className="alert alert-warning">
                                    <Icon name="warning" size={20} className="me-2" />
                                    Esta tarea es obligatoria
                                </div>
                            )}
                            {entrega.fechaEntrega && new Date(entrega.fechaEntrega) > new Date(tarea.fechaLimite) && (
                                <div className="alert alert-danger">
                                    <Icon name="warning" size={20} className="me-2" />
                                    Entrega fuera de plazo
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

const getEstadoColor = (estado) => {
    switch (estado) {
        case 'entregado':
            return 'primary';
        case 'calificado':
            return 'success';
        case 'revision_solicitada':
            return 'warning';
        case 'atrasado':
            return 'danger';
        case 'pendiente':
        default:
            return 'secondary';
    }
};

const getEstadoTexto = (estado) => {
    return estado.replace('_', ' ');
};

export default EntregaDetalle;