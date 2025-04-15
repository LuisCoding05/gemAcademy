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
                <div className={`card border-start border-5 border-${getEstadoColor(item.entrega.estado)} ${isDarkMode ? 'bg-secondary text-light' : 'bg-light'}`}>
                    <div className={`card-header fw-bold text-capitalize text-${getEstadoColor(item.entrega.estado)}`}>
                        {item.entrega.estado.replace('_', ' ')}
                    </div>
                    <div className="card-body">
                        <div className="d-flex justify-content-between align-items-center mb-2">
                            <span>
                                <Icon name="stop-watch" size={20} className="me-2" />
                                Entregado el {new Date(item.entrega.fechaEntrega).toLocaleString()}
                            </span>
                            {item.entrega.calificacion && (
                                <span className={'badge ' + (item.entrega.calificacion >= 5 ? 'bg-success' : 'bg-danger')}>
                                    {item.entrega.calificacion * 10}%
                                </span>
                            )}
                        </div>

                        <div className="mb-2">
                            <h6>Archivos:</h6>
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
                            <h6>Puntos:</h6>
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
                            <h6>Comentario del profesor:</h6>
                            <p className="mb-0">{item.entrega.comentarioProfesor ?? 'Sin comentarios'}</p>
                        </div>

                        <div className="mt-2">
                            <h6>Calificación:</h6>
                            <p className="mb-0">
                                {item.entrega.calificacion ?? <>No calificado <Icon name="shit" color="brown" size={20} className="me-2" /></>}
                            </p>
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