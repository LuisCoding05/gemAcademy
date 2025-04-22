import React, { useState, useEffect } from 'react';
import { useTheme } from '../../context/ThemeContext';
import { useNavigate, useLocation } from 'react-router-dom';
import Icon from '../Icon';
import axios from '../../utils/axios';
import TareaEditForm from './TareaEditForm';
import EntregasLista from './EntregasLista';
import Editor from '../common/Editor';
import Loader from '../common/Loader';

const TareaItem = ({ item, courseId, onUpdate }) => {
    const { isDarkMode } = useTheme();
    const location = useLocation();
    const navigate = useNavigate();
    const [comentario, setComentario] = useState('');
    const [editandoComentario, setEditandoComentario] = useState(false);
    const [actualizando, setActualizando] = useState(false);
    const [archivo, setArchivo] = useState(null);
    const [uploadingFile, setUploadingFile] = useState(false);
    const [editandoEntrega, setEditandoEntrega] = useState(false);
    const [isEditing, setIsEditing] = useState(false);
    const [error, setError] = useState(null);
    const [currentItem, setCurrentItem] = useState(item);

    // useEffect para manejar estados iniciales y actualizaciones
    useEffect(() => {
        if (location.state?.isEditing) {
            setIsEditing(true);
            navigate(location.pathname, { replace: true, state: {} });
        }

        setCurrentItem(item);

        if (item.entrega) {
            setComentario(item.entrega.comentarioEstudiante || '');
            setEditandoComentario(false);
            setEditandoEntrega(false);
            setArchivo(null);
        }
    }, [location.state, location.pathname, navigate, item]);

    const handleDelete = async () => {
        if (!window.confirm('¿Estás seguro de que quieres eliminar esta tarea?')) {
            return;
        }

        try {
            await axios.delete(`/api/item/${courseId}/tarea/${item.id}/delete`);
            navigate(`/cursos/${courseId}`);
        } catch (error) {
            setError(error.response?.data?.message || 'Error al eliminar la tarea');
        }
    };

    const handleUpdate = (updatedTarea) => {
        setIsEditing(false);
        setCurrentItem(updatedTarea);
        if (onUpdate) {
            onUpdate(updatedTarea);
        }
    };

    const handleDownload = async (fichero) => {
        try {
            const response = await axios.get(`/api/download/${fichero.id}`, {
                responseType: 'blob'
            });
            
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', fichero.nombreOriginal);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
        } catch (error) {
            console.error('Error al descargar el archivo:', error);
            setError('Error al descargar el archivo');
        }
    };

    const getPorcentaje = (nota) => {
        nota = parseFloat(nota);
        let porcentaje = nota * 10;
        return porcentaje.toFixed(1);
    };

    const getEstadoColor = (estado) => {
        switch (estado) {
            case 'entregado': return 'primary';
            case 'calificado': return 'success';
            case 'revision_solicitada': return 'warning';
            case 'atrasado': return 'danger';
            case 'pendiente':
            default: return 'secondary';
        }
    };

    const getEstadoTexto = (estado, atrasado = false) => {
        if (estado === 'revision_solicitada' && atrasado) {
            return 'Atrasada - Revisión solicitada';
        }
        return estado.replace('_', ' ');
    };

    const actualizarEntrega = async (accion, datos = {}) => {
        try {
            setActualizando(true);
            setError(null);
            
            const response = await axios.post(`/api/item/${courseId}/tarea/${currentItem.id}/entrega`, {
                accion,
                ...datos
            });
            
            // Actualizar el estado local y el componente padre
            const updatedItem = {
                ...currentItem,
                entrega: response.data.entrega
            };
            setCurrentItem(updatedItem);

            // Si la acción es 'borrar', resetear los estados relacionados con la entrega
            if (accion === 'borrar') {
                setArchivo(null);
                setComentario('');
                setEditandoEntrega(false);
                setEditandoComentario(false);
            }

            // Notificar al componente padre
            if (onUpdate) {
                onUpdate(updatedItem);
            }

            setActualizando(false);
            return response.data;
        } catch (error) {
            setError(error.response?.data?.message || 'Error al actualizar la entrega');
            setActualizando(false);
            throw error;
        }
    };

    const handleFileUpload = (event) => {
        const file = event.target.files[0];
        if (!file) return;
        setArchivo(file);
    };

    const uploadFile = async (file) => {
        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await axios.post('/api/upload', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            return response.data;
        } catch (error) {
            console.error('Error al subir el archivo:', error);
            throw error;
        }
    };

    const handleSubmitFile = async () => {
        try {
            setUploadingFile(true);
            let ficheroId = null;

            if (archivo) {
                const uploadResponse = await uploadFile(archivo);
                ficheroId = uploadResponse.id;
            }

            await actualizarEntrega('entregar', {
                ficheroId,
                comentario: comentario || null
            });
        } catch (error) {
            console.error('Error al entregar:', error);
        } finally {
            setUploadingFile(false);
        }
    };

    const handleUpdateFile = async () => {
        try {
            setUploadingFile(true);
            
            if (!archivo) {
                throw new Error('No se ha seleccionado ningún archivo');
            }

            const uploadResponse = await uploadFile(archivo);
            await actualizarEntrega('actualizarArchivo', { 
                ficheroId: uploadResponse.id 
            });
        } catch (error) {
            console.error('Error al actualizar el archivo:', error);
        } finally {
            setUploadingFile(false);
        }
    };

    const handleDeleteEntrega = async () => {
        if (!window.confirm('¿Estás seguro de que deseas borrar esta entrega? Esta acción no se puede deshacer.')) {
            return;
        }
        await actualizarEntrega('borrar');
    };

    const handleUpdateComment = async () => {
        await actualizarEntrega('actualizarComentario', { comentario: comentario || null });
    };

    const handleDeleteComment = async () => {
        setComentario('');
        await actualizarEntrega('actualizarComentario', { comentario: null });
    };

    const handleRequestReview = async () => {
        await actualizarEntrega('solicitarRevision');
    };

    return (
        <div className="tarea-details">
            {error && (
                <div className="alert alert-danger">{error}</div>
            )}
            
            {isEditing ? (
                <TareaEditForm 
                    item={currentItem}
                    courseId={courseId}
                    onUpdate={handleUpdate}
                    onCancel={() => setIsEditing(false)}
                />
            ) : (
                <>
                    <div className="content mb-4">
                        <div 
                            className="ck-content"
                            dangerouslySetInnerHTML={{ __html: currentItem.descripcion }} 
                        />
                    </div>

                    <div className="card mb-4">
                        <div className="card-body">
                            <h6 className="card-subtitle mb-2 text-muted">
                                <Icon name="star" size={20} className="me-2" />
                                Puntuación máxima: {currentItem.puntosMaximos} puntos
                            </h6>
                            <p className="card-text">
                                <Icon name="hour-glass" size={20} className="me-2" />
                                Fecha límite: {new Date(currentItem.fechaLimite).toLocaleString()}
                            </p>
                            {currentItem.esObligatoria && (
                                <span className="badge bg-danger">
                                    <Icon name="warning" size={16} className="me-1" />
                                    Obligatoria
                                </span>
                            )}
                        </div>
                    </div>

                    {currentItem.fichero && (
                        <div className="mb-4">
                            <button 
                                className="btn btn-primary"
                                onClick={() => handleDownload(currentItem.fichero)}
                            >
                                <Icon name="folder-download" size={20} className="me-2" />
                                Descargar {currentItem.fichero.nombreOriginal}
                            </button>
                        </div>
                    )}

                    {currentItem.userRole === 'profesor' ? (
                        <div className="mb-4">
                            <div className="mb-3">
                                <h4>Entregas de los estudiantes</h4>
                            </div>
                            <EntregasLista 
                                courseId={courseId} 
                                tareaId={currentItem.id}
                                onCalificar={(entregaActualizada) => {
                                    // Si necesitamos actualizar algo en este componente
                                    // después de calificar una entrega
                                }}
                            />
                        </div>
                    ) : (
                        currentItem.entrega && currentItem.entrega.fechaEntrega && currentItem.entrega.estado !== 'pendiente' ? (
                            <div className="mb-4">
                                <h5>Estado de la entrega</h5>
                                <div className={`card border-start border-5 border-${getEstadoColor(currentItem.entrega.estado)} ${isDarkMode ? 'bg-dark text-light' : 'bg-light'}`}>
                                    <div className={`card-header fw-bold text-capitalize text-${getEstadoColor(currentItem.entrega.estado)}`}>
                                        {getEstadoTexto(currentItem.entrega.estado, currentItem.entrega.estado === 'atrasado')}
                                    </div>
                                    <div className="card-body">
                                        <div className="d-flex justify-content-between align-items-center mb-4">
                                            <span>
                                                <Icon name="stop-watch" size={26} className="me-2" />
                                                Entregado el {new Date(currentItem.entrega.fechaEntrega).toLocaleString()}
                                            </span>
                                            {currentItem.entrega.calificacion && (
                                                <span className={'badge ' + (currentItem.entrega.calificacion >= 5 ? 'bg-success' : 'bg-danger')}>
                                                    {getPorcentaje(currentItem.entrega.calificacion)}%
                                                </span>
                                            )}
                                        </div>

                                        <div className="mb-3">
                                            <h6><Icon name="documents" size={20} /> Archivos:</h6>
                                            <div className="d-flex flex-wrap gap-2 align-items-start">
                                                {currentItem.entrega.archivo ? (
                                                    <>
                                                        <button 
                                                           onClick={async () => {
                                                               try {
                                                                   const response = await axios.get(
                                                                       `/api/download/${currentItem.entrega.archivo.id}`,
                                                                       { responseType: 'blob' }
                                                                   );
                                                                   
                                                                   const url = window.URL.createObjectURL(new Blob([response.data]));
                                                                   const link = document.createElement('a');
                                                                   link.href = url;
                                                                   link.setAttribute('download', currentItem.entrega.archivo.nombreOriginal);
                                                                   document.body.appendChild(link);
                                                                   link.click();
                                                                   document.body.removeChild(link);
                                                                   window.URL.revokeObjectURL(url);
                                                               } catch (error) {
                                                                   console.error('Error al descargar el archivo:', error);
                                                               }
                                                           }}
                                                           className="btn btn-sm btn-outline-primary d-flex align-items-center">
                                                            <Icon name="folder-download1" size={20} className="me-2" />
                                                            <span className="text-truncate" style={{maxWidth: '200px'}}>
                                                                {currentItem.entrega.archivo.nombreOriginal}
                                                            </span>
                                                        </button>
                                                        {!currentItem.entrega.isCalificado && (
                                                            <button
                                                                className="btn btn-sm btn-warning d-flex align-items-center"
                                                                onClick={() => setEditandoEntrega(true)}
                                                                disabled={actualizando}
                                                            >
                                                                <Icon name="pencil2" size={16} className="me-2" />
                                                                Editar entrega
                                                            </button>
                                                        )}
                                                    </>
                                                ) : (
                                                    <p className="mb-0"><strong>No se ha subido ningún archivo</strong></p>
                                                )}
                                            </div>

                                            {editandoEntrega && !currentItem.entrega.isCalificado && (
                                                <div className="mt-3">
                                                    <div className="alert alert-info">
                                                        <Icon name="info" size={20} className="me-2" />
                                                        Al actualizar el archivo, la fecha de entrega se actualizará automáticamente.
                                                    </div>
                                                    <input 
                                                        type="file" 
                                                        className="form-control form-control-sm mb-2" 
                                                        onChange={handleFileUpload}
                                                        disabled={uploadingFile}
                                                    />
                                                    <div className="d-flex gap-2">
                                                        {archivo && (
                                                            <button 
                                                                className="btn btn-sm btn-success"
                                                                onClick={handleUpdateFile}
                                                                disabled={uploadingFile}
                                                            >
                                                                {uploadingFile ? (
                                                                    <>
                                                                        <span className="spinner-border spinner-border-sm me-2" />
                                                                        Actualizando...
                                                                    </>
                                                                ) : (
                                                                    <>
                                                                        <Icon name="folder-upload" size={16} className="me-2" />
                                                                        Guardar cambios
                                                                    </>
                                                                )}
                                                            </button>
                                                        )}
                                                        <button 
                                                            className="btn btn-sm btn-secondary"
                                                            onClick={() => {
                                                                setEditandoEntrega(false);
                                                                setArchivo(null);
                                                            }}
                                                            disabled={uploadingFile}
                                                        >
                                                            <Icon name="circle-with-cross" size={16} className="me-2" />
                                                            Cancelar
                                                        </button>
                                                    </div>
                                                </div>
                                            )}
                                        </div>

                                        <div className="mb-2">
                                            <h6><Icon name="badge" size={22} /> Comentario del profesor:</h6>
                                            <p className="mb-0">{currentItem.entrega.comentarioProfesor ?? 'Sin comentarios'}</p>
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
                                                            onClick={handleUpdateComment}
                                                            disabled={actualizando}
                                                        >
                                                            {actualizando ? (
                                                                <span className="spinner-border spinner-border-sm me-2" />
                                                            ) : (
                                                                <Icon name="checkmark" size={16} className="me-2" />
                                                            )}
                                                            Guardar
                                                        </button>
                                                        <button 
                                                            className="btn btn-secondary btn-sm"
                                                            onClick={() => {
                                                                setEditandoComentario(false);
                                                                setComentario(currentItem.entrega.comentarioEstudiante || '');
                                                            }}
                                                            disabled={actualizando}
                                                        >
                                                            <Icon name="circle-with-cross" size={16} className="me-2" />
                                                            Cancelar
                                                        </button>
                                                    </div>
                                                </div>
                                            ) : (
                                                <div className="d-flex justify-content-between align-items-start">
                                                    <p className="mb-0">
                                                        {currentItem.entrega.comentarioEstudiante || 'Sin comentarios'}
                                                    </p>
                                                    {!currentItem.entrega.isCalificado && (
                                                        <div>
                                                            <button 
                                                                className="btn btn-outline-primary btn-sm me-2"
                                                                onClick={() => setEditandoComentario(true)}
                                                                disabled={actualizando}
                                                            >
                                                                <Icon name="pencil2" size={16} />
                                                            </button>
                                                            {currentItem.entrega.comentarioEstudiante && (
                                                                <button 
                                                                    className="btn btn-outline-danger btn-sm"
                                                                    onClick={handleDeleteComment}
                                                                    disabled={actualizando}
                                                                >
                                                                    <Icon name="trash-can" size={16} />
                                                                </button>
                                                            )}
                                                        </div>
                                                    )}
                                                </div>
                                            )}
                                        </div>

                                        {!currentItem.entrega.isCalificado && (
                                            <div className="mt-3 d-flex gap-2">
                                                <button 
                                                    className="btn btn-danger"
                                                    onClick={handleDeleteEntrega}
                                                    disabled={actualizando}
                                                >
                                                    <Icon name="trash-can" size={20} className="me-2" />
                                                    Borrar entrega
                                                </button>
                                                {(currentItem.entrega.estado === 'entregado' || currentItem.entrega.estado === 'atrasado') && (
                                                    <button 
                                                        className="btn btn-warning"
                                                        onClick={handleRequestReview}
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
                                        )}
                                    </div>
                                </div>
                            </div>
                        ) : (
                            <div className="card">
                                <div className="card-body">
                                    <h6>Entregar tarea:</h6>
                                    <div className="mb-3">
                                        <input 
                                            type="file" 
                                            className="form-control mb-2" 
                                            onChange={handleFileUpload}
                                            disabled={uploadingFile}
                                        />
                                        <div className="d-flex gap-2">
                                            <button 
                                                className="btn btn-primary"
                                                onClick={handleSubmitFile}
                                                disabled={uploadingFile}
                                            >
                                                {uploadingFile ? (
                                                    <>
                                                        <span className="spinner-border spinner-border-sm me-2" />
                                                        Entregando...
                                                    </>
                                                ) : (
                                                    <>
                                                        <Icon name="folder-upload1" size={16} className="me-2" />
                                                        {archivo ? 'Entregar con archivo' : 'Entregar sin archivo'}
                                                    </>
                                                )}
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div className="mb-3">
                                        <label className="form-label">Comentario (opcional):</label>
                                        <textarea 
                                            className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                                            value={comentario}
                                            onChange={(e) => setComentario(e.target.value)}
                                            rows="3"
                                            placeholder="Añade un comentario a tu entrega..."
                                        />
                                    </div>
                                </div>
                            </div>
                        )
                    )}

                    {currentItem.userRole === 'profesor' && (
                        <div className="mt-4 d-flex gap-2 justify-content-end">
                            <button 
                                className="btn btn-warning"
                                onClick={() => setIsEditing(true)}
                            >
                                <Icon name="pencil2" size={20} className="me-2" />
                                Editar tarea
                            </button>
                            <button 
                                className="btn btn-danger"
                                onClick={handleDelete}
                            >
                                <Icon name="trash-can" size={20} className="me-2" />
                                Eliminar tarea
                            </button>
                        </div>
                    )}
                </>
            )}
        </div>
    );
};

export default TareaItem;