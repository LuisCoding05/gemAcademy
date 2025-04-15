import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { useTheme } from '../../context/ThemeContext';
import axios from '../../utils/axios';
import Icon from '../Icon';
import Loader from '../common/Loader';

const ItemDetails = () => {
    const { user } = useAuth();
    const { isDarkMode } = useTheme();
    const { courseId, itemType, itemId } = useParams();
    const navigate = useNavigate();
    
    const [item, setItem] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [comentario, setComentario] = useState('');
    const [editandoComentario, setEditandoComentario] = useState(false);
    const [actualizando, setActualizando] = useState(false);
    const [archivo, setArchivo] = useState(null);
    const [uploadingFile, setUploadingFile] = useState(false);

    function getPorcentaje(nota) {
        nota = parseFloat(nota)
        let porcentaje = nota*10
        porcentaje = porcentaje.toFixed(1)
        return porcentaje
    }

    useEffect(() => {
        const fetchItemDetails = async () => {
            try {
                setLoading(true);
                setError(null);
                
                let endpoint = '';
                switch (itemType) {
                    case 'material':
                        endpoint = `/api/item/${courseId}/material/${itemId}`;
                        break;
                    case 'tarea':
                        endpoint = `/api/item/${courseId}/tarea/${itemId}`;
                        break;
                    case 'quiz':
                        endpoint = `/api/item/${courseId}/quiz/${itemId}`;
                        break;
                    default:
                        setError('Tipo de elemento no válido');
                        setLoading(false);
                        return;
                }
                
                const response = await axios.get(endpoint);
                console.log(response.data);
                setItem(response.data);
            } catch (error) {
                console.error(`Error al cargar el ${itemType}:`, error);
                setError(error.response?.data?.message || `Error al cargar el ${itemType}`);
            } finally {
                setLoading(false);
            }
        };

        fetchItemDetails();
    }, [courseId, itemType, itemId]);

    const handleBack = () => {
        navigate(`/cursos/${courseId}`);
    };

    const getItemTitle = () => {
        switch (itemType) {
            case 'material':
                return 'Material';
            case 'tarea':
                return 'Tarea';
            case 'quiz':
                return 'Quiz';
            default:
                return 'Elemento';
        }
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

    const handleEntregarTarea = async () => {
        try {
            setActualizando(true);
            const response = await axios.post(`/api/item/${courseId}/tarea/${itemId}/entrega`, {
                accion: 'entregar',
                comentario: comentario || null
            });
            
            // Actualizar el estado local con la respuesta
            setItem(prev => ({
                ...prev,
                entrega: {
                    ...response.data.entrega,
                    comentarioEstudiante: response.data.entrega.comentarioEstudiante
                }
            }));
            
            setEditandoComentario(false);
        } catch (error) {
            console.error('Error al entregar la tarea:', error);
            setError(error.response?.data?.message || 'Error al entregar la tarea');
        } finally {
            setActualizando(false);
        }
    };

    const handleActualizarComentario = async () => {
        try {
            setActualizando(true);
            const response = await axios.post(`/api/item/${courseId}/tarea/${itemId}/entrega`, {
                comentario: comentario
            });
            
            // Actualizar el estado local con la respuesta
            setItem(prev => ({
                ...prev,
                entrega: {
                    ...prev.entrega,
                    comentarioEstudiante: response.data.entrega.comentarioEstudiante
                }
            }));
            
            setEditandoComentario(false);
        } catch (error) {
            console.error('Error al actualizar el comentario:', error);
            setError(error.response?.data?.message || 'Error al actualizar el comentario');
        } finally {
            setActualizando(false);
        }
    };

    const handleBorrarComentario = async () => {
        try {
            setActualizando(true);
            await axios.post(`/api/item/${courseId}/tarea/${itemId}/entrega`, {
                comentario: null
            });
            
            // Actualizar el estado local
            setItem(prev => ({
                ...prev,
                entrega: {
                    ...prev.entrega,
                    comentarioEstudiante: null
                }
            }));
            
            setComentario('');
        } catch (error) {
            console.error('Error al borrar el comentario:', error);
            setError(error.response?.data?.message || 'Error al borrar el comentario');
        } finally {
            setActualizando(false);
        }
    };

    const handleFileUpload = async (event) => {
        const file = event.target.files[0];
        if (!file) return;

        setArchivo(file);
    };

    const handleSubmitFile = async () => {
        if (!archivo) return;

        try {
            setUploadingFile(true);
            // Aquí iría la lógica para subir el archivo a tu servidor/storage
            // Por ahora simularemos una URL
            const archivoUrl = `/uploads/${archivo.name}`;
            
            const response = await axios.post(`/api/item/${courseId}/tarea/${itemId}/entrega`, {
                accion: 'entregar',
                archivoUrl: archivoUrl,
                comentario: comentario || null
            });
            
            setItem(prev => ({
                ...prev,
                entrega: response.data.entrega
            }));
            
            setArchivo(null);
            setEditandoComentario(false);
        } catch (error) {
            console.error('Error al subir el archivo:', error);
            setError(error.response?.data?.message || 'Error al subir el archivo');
        } finally {
            setUploadingFile(false);
        }
    };

    const handleMarcarEntregado = async () => {
        try {
            setActualizando(true);
            const response = await axios.post(`/api/item/${courseId}/tarea/${itemId}/entrega`, {
                accion: 'entregar',
                comentario: comentario || null
            });
            
            setItem(prev => ({
                ...prev,
                entrega: response.data.entrega
            }));
            
            setEditandoComentario(false);
        } catch (error) {
            console.error('Error al marcar como entregado:', error);
            setError(error.response?.data?.message || 'Error al marcar como entregado');
        } finally {
            setActualizando(false);
        }
    };

    const handleBorrarEntrega = async () => {
        if (!confirm('¿Estás seguro de que quieres borrar esta entrega? Esta acción no se puede deshacer.')) {
            return;
        }

        try {
            setActualizando(true);
            const response = await axios.post(`/api/item/${courseId}/tarea/${itemId}/entrega`, {
                accion: 'borrar'
            });
            
            setItem(prev => ({
                ...prev,
                entrega: response.data.entrega
            }));
        } catch (error) {
            console.error('Error al borrar la entrega:', error);
            setError(error.response?.data?.message || 'Error al borrar la entrega');
        } finally {
            setActualizando(false);
        }
    };

    const handleSolicitarRevision = async () => {
        try {
            setActualizando(true);
            const response = await axios.post(`/api/item/${courseId}/tarea/${itemId}/entrega`, {
                accion: 'solicitarRevision'
            });
            
            setItem(prev => ({
                ...prev,
                entrega: response.data.entrega
            }));
        } catch (error) {
            console.error('Error al solicitar revisión:', error);
            setError(error.response?.data?.message || 'Error al solicitar revisión');
        } finally {
            setActualizando(false);
        }
    };

    useEffect(() => {
        if (item?.entrega?.comentarioEstudiante) {
            setComentario(item.entrega.comentarioEstudiante);
        }
    }, [item?.entrega?.comentarioEstudiante]);

    if (loading) {
        return (
            <div className="container mt-5">
                <div className="text-center">
                    <Loader size="large" />
                    <p className="mt-3">Cargando detalles...</p>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="container mt-5">
                <div className="alert alert-danger" role="alert">
                    <h4 className="alert-heading">Error</h4>
                    <p>{error}</p>
                    <hr />
                    <button className="btn btn-outline-danger" onClick={handleBack}>
                        <Icon name="dglasses" size={20} className="me-2" />
                        Volver al curso
                    </button>
                </div>
            </div>
        );
    }

    if (!item) {
        return (
            <div className="container mt-5">
                <div className="alert alert-warning" role="alert">
                    <h4 className="alert-heading">No encontrado</h4>
                    <p>El elemento solicitado no existe o no tienes acceso a él.</p>
                    <hr />
                    <button className="btn btn-outline-warning" onClick={handleBack}>
                        <Icon name="dglasses" size={20} className="me-2" />
                        Volver al curso
                    </button>
                </div>
            </div>
        );
    }

    return (
        <div className="container mt-4">
            <div className="row">
                <div className="col-12">
                    <div className={`mb-4 card ${isDarkMode ? 'bg-dark text-light' : ''}`}>
                        <div className="card-header d-flex justify-content-between align-items-center">
                            <h4 className="mb-0">
                                {getItemTitle()}: {item.titulo}
                            </h4>
                            <button className="btn btn-outline-secondary" onClick={handleBack}>
                                <Icon name="dglasses" size={20} className="me-2" />
                                Volver al curso
                            </button>
                        </div>
                        <div className="card-body">
                            <div className="row">
                                <div className="col-md-8">
                                    <div className="mb-4">
                                        <h5>Descripción</h5>
                                        <p>{item.descripcion}</p>
                                    </div>

                                    {itemType === 'material' && (
                                        <div>
                                            <div className="mb-4">
                                                <h5>Contenido</h5>
                                                <p>{item.contenido}</p>
                                            </div>
                                            {item.url && (
                                                <div className="mb-4">
                                                    <h5>Recurso</h5>
                                                    <a href={item.url} target="_blank" rel="noopener noreferrer" className="btn btn-primary">
                                                        <Icon name="folder-download" size={20} className="me-2" />
                                                        Descargar recurso
                                                    </a>
                                                </div>
                                            )}
                                        </div>
                                    )}

                                    {itemType === 'tarea' && (
                                        <div className="tarea-details">
                                            <div className="mb-4">
                                                <h5>Puntos</h5>
                                                <p>{item.puntos} puntos</p>
                                            </div>

                                            {item.entrega && item.entrega.fechaEntrega !== null && item.entrega.estado !== 'pendiente' ? (
                                                <div className="mb-4">
                                                    <h5>Estado de la entrega</h5>
                                                    <div className={`card border-start border-5 border-${getEstadoColor(item.entrega.estado)} ${isDarkMode ? 'bg-dark text-light' : 'bg-light'}`}>
                                                        <div className={`card-header fw-bold text-capitalize text-${getEstadoColor(item.entrega.estado)}`}>
                                                            {item.entrega.estado.replace('_', ' ')}
                                                        </div>
                                                        <div className="card-body">
                                                            <div className="d-flex justify-content-between align-items-center mb-4">
                                                                <span>
                                                                    <Icon name="stop-watch" size={26} className="me-2" />
                                                                    Entregado el {new Date(item.entrega.fechaEntrega).toLocaleString()}
                                                                </span>
                                                                {item.entrega.calificacion && (
                                                                    <span className={'badge ' + (item.entrega.calificacion >= 5 ? 'bg-success' : 'bg-danger')}>
                                                                        {getPorcentaje(item.entrega.calificacion)}%
                                                                    </span>
                                                                )}
                                                            </div>

                                                            <div className="mb-2">
                                                                <h6><Icon name="documents" size={20}></Icon> Archivos: </h6>
                                                                {item.entrega.archivoUrl ? (
                                                                    <a href={item.entrega.archivoUrl} target="_blank" rel="noopener noreferrer" className="btn btn-sm btn-outline-primary">
                                                                        <Icon name="download" size={20} className="me-2" />
                                                                        Ver archivo entregado
                                                                    </a>
                                                                ) : (
                                                                    <strong>No se ha subido ningún fichero</strong>
                                                                )}
                                                            </div>

                                                            <div className="mb-2">
                                                                <h6><Icon name="stars" size={24} /> Puntos:</h6>
                                                                {item.entrega.puntosObtenidos != null ? (
                                                                    <div>
                                                                        <strong>Puntos obtenidos: </strong>
                                                                        {item.entrega.puntosObtenidos}/{item.puntos}
                                                                    </div>
                                                                ) : (
                                                                    <div>
                                                                        <strong>Puntos pendientes de obtención </strong>
                                                                        <Icon name="hour-glass" size={20} className="me-2" />
                                                                    </div>
                                                                )}
                                                            </div>

                                                            <div className="mb-2">
                                                                <h6><Icon name="badge" size={22}></Icon> Comentario del profesor:</h6>
                                                                <p className="mb-0">{item.entrega.comentarioProfesor ?? 'Sin comentarios'}</p>
                                                            </div>

                                                            <div className="mt-2">
                                                                <h6>Calificación:</h6>
                                                                <p className="mb-0">
                                                                    {item.entrega.calificacion ?? (
                                                                        <>
                                                                            No calificado
                                                                            <Icon name="help-circle" color="gray" size={20} className="ms-2" />
                                                                        </>
                                                                    )}

                                                                    {item.entrega.calificacion !== null && (
                                                                        <>
                                                                            {item.entrega.calificacion < 5 && (
                                                                                <Icon name="crying2" color="lightblue" size={26} className="ms-2" />
                                                                            )}
                                                                            {(item.entrega.calificacion >= 5 && item.entrega.calificacion < 6) && (
                                                                                <Icon name="confused2" color="orange" size={26} className="ms-2" />
                                                                            )}
                                                                            {(item.entrega.calificacion >= 6 && item.entrega.calificacion < 7) && (
                                                                                <Icon name="wink2" color="yellow" size={26} className="ms-2" />
                                                                            )}
                                                                            {item.entrega.calificacion >= 7 && item.entrega.calificacion <= 8 && (
                                                                                <Icon name="cool2" color="green" size={26} className="ms-2" />
                                                                            )}
                                                                            {item.entrega.calificacion >= 9 && item.entrega.calificacion <= 10 && (
                                                                                <Icon name="medal" color="gold" size={26} className="ms-2" />
                                                                            )}
                                                                        </>
                                                                    )}
                                                                </p>
                                                            </div>

                                                            <div className="mt-3">
                                                                <h6><Icon name="mail4" size={20} /> Comentario del estudiante:</h6>
                                                                {editandoComentario ? (
                                                                    <div className="mb-3">
                                                                        <textarea 
                                                                            className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                                                                            value={comentario}
                                                                            onChange={(e) => setComentario(e.target.value)}
                                                                            rows="3"
                                                                            placeholder="Escribe tu comentario aquí..."
                                                                        />
                                                                        <div className="mt-2">
                                                                            <button 
                                                                                className="btn btn-primary btn-sm me-2"
                                                                                onClick={handleActualizarComentario}
                                                                                disabled={actualizando}
                                                                            >
                                                                                {actualizando ? (
                                                                                    <>
                                                                                        <span className="spinner-border spinner-border-sm me-2" />
                                                                                        Guardando...
                                                                                    </>
                                                                                ) : (
                                                                                    <>
                                                                                        <Icon name="checkmark" size={16} className="me-2" />
                                                                                        Guardar
                                                                                    </>
                                                                                )}
                                                                            </button>
                                                                            <button 
                                                                                className="btn btn-secondary btn-sm"
                                                                                onClick={() => {
                                                                                    setEditandoComentario(false);
                                                                                    setComentario(item.entrega.comentarioEstudiante || '');
                                                                                }}
                                                                                disabled={actualizando}
                                                                            >
                                                                                <Icon name="bomb" size={16} className="me-2" />
                                                                                Cancelar
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                ) : (
                                                                    <div className="d-flex justify-content-between align-items-start">
                                                                        <p className="mb-0">
                                                                            {item.entrega.comentarioEstudiante || 'Sin comentarios'}
                                                                        </p>
                                                                        <div>
                                                                            <button 
                                                                                className="btn btn-outline-primary btn-sm me-2"
                                                                                onClick={() => setEditandoComentario(true)}
                                                                                disabled={actualizando}
                                                                            >
                                                                                <Icon name="pencil2" size={16} />
                                                                            </button>
                                                                            {item.entrega.comentarioEstudiante && (
                                                                                <button 
                                                                                    className="btn btn-outline-danger btn-sm"
                                                                                    onClick={handleBorrarComentario}
                                                                                    disabled={actualizando}
                                                                                >
                                                                                    <Icon name="trash-can" size={16} />
                                                                                </button>
                                                                            )}
                                                                        </div>
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            ) : (
                                                <div className="alert alert-warning d-flex align-items-center" role="alert">
                                                    <Icon name="note-important" size={30} className="me-3" />
                                                    <div>
                                                        <h6 className="mb-0">Tarea no entregada</h6>
                                                        <small className="text-muted">Esta tarea aún no ha sido entregada.</small>
                                                    </div>
                                                </div>
                                            )}

                                            {!item.entrega || item.entrega.estado === 'pendiente' ? (
                                                <div className="mt-4">
                                                    <h5>Entregar tarea</h5>
                                                    <div className="card">
                                                        <div className="card-body">
                                                            <div className="mb-3">
                                                                <label className="form-label">Archivo (opcional)</label>
                                                                <input 
                                                                    type="file" 
                                                                    className="form-control" 
                                                                    onChange={handleFileUpload}
                                                                    disabled={uploadingFile || actualizando}
                                                                />
                                                            </div>
                                                            <div className="mb-3">
                                                                <label className="form-label">Comentario (opcional)</label>
                                                                <textarea 
                                                                    className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                                                                    value={comentario}
                                                                    onChange={(e) => setComentario(e.target.value)}
                                                                    rows="3"
                                                                    placeholder="Escribe un comentario para el profesor..."
                                                                    disabled={uploadingFile || actualizando}
                                                                />
                                                            </div>
                                                            <div className="d-flex gap-2">
                                                                {archivo ? (
                                                                    <button 
                                                                        className="btn btn-primary"
                                                                        onClick={handleSubmitFile}
                                                                        disabled={uploadingFile || actualizando}
                                                                    >
                                                                        {uploadingFile ? (
                                                                            <>
                                                                                <span className="spinner-border spinner-border-sm me-2" />
                                                                                Subiendo archivo...
                                                                            </>
                                                                        ) : (
                                                                            <>
                                                                                <Icon name="upload" size={20} className="me-2" />
                                                                                Subir archivo y entregar
                                                                            </>
                                                                        )}
                                                                    </button>
                                                                ) : (
                                                                    <button 
                                                                        className="btn btn-primary"
                                                                        onClick={handleMarcarEntregado}
                                                                        disabled={actualizando}
                                                                    >
                                                                        {actualizando ? (
                                                                            <>
                                                                                <span className="spinner-border spinner-border-sm me-2" />
                                                                                Entregando...
                                                                            </>
                                                                        ) : (
                                                                            <>
                                                                                <Icon name="checkmark" size={20} className="me-2" />
                                                                                Marcar como entregado
                                                                            </>
                                                                        )}
                                                                    </button>
                                                                )}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            ) : (
                                                <div className="mt-4">
                                                    <div className="d-flex gap-2">
                                                        {!item.entrega.isCalificado && (
                                                            <button 
                                                                className="btn btn-danger"
                                                                onClick={handleBorrarEntrega}
                                                                disabled={actualizando}
                                                            >
                                                                {actualizando ? (
                                                                    <span className="spinner-border spinner-border-sm me-2" />
                                                                ) : (
                                                                    <Icon name="trash-can" size={20} className="me-2" />
                                                                )}
                                                                Borrar entrega
                                                            </button>
                                                        )}
                                                        
                                                        {(item.entrega.estado === 'entregado' || item.entrega.estado === 'atrasado') && (
                                                            <button 
                                                                className="btn btn-warning"
                                                                onClick={handleSolicitarRevision}
                                                                disabled={actualizando}
                                                            >
                                                                {actualizando ? (
                                                                    <span className="spinner-border spinner-border-sm me-2" />
                                                                ) : (
                                                                    <Icon name="ticket1" size={20} className="me-2" />
                                                                )}
                                                                Solicitar revisión
                                                            </button>
                                                        )}
                                                    </div>
                                                </div>
                                            )}
                                        </div>
                                    )}


                                    {itemType === 'quiz' && (
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
                                    )}
                                </div>
                                <div className="col-md-4">
                                    <div className={`card ${isDarkMode ? 'bg-secondary text-light' : 'bg-light'}`}>
                                        <div className="card-body">
                                            <h5 className="card-title">Información</h5>
                                            <ul className="list-group list-group-flush">
                                                <li className={`list-group-item ${isDarkMode ? 'bg-dark text-light' : ''}`}>
                                                    <strong>Fecha de publicación:</strong><br />
                                                    {new Date(item.fechaPublicacion).toLocaleString()}
                                                </li>
                                                {(itemType === 'tarea' || itemType === 'quiz') && (
                                                    <li className={`list-group-item ${isDarkMode ? 'bg-dark text-light' : ''}`}>
                                                        <strong>Fecha límite:</strong><br />
                                                        {new Date(item.fechaLimite).toLocaleString()}
                                                    </li>
                                                )}
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ItemDetails;