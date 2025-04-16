import React, { useState, useEffect } from 'react';
import { useTheme } from '../../context/ThemeContext';
import Icon from '../Icon';
import axios, { API_BASE_URL } from '../../utils/axios';

const TareaItem = ({ item, courseId, onUpdate }) => {
    const { isDarkMode } = useTheme();
    const [comentario, setComentario] = useState(item.entrega?.comentarioEstudiante || '');
    const [editandoComentario, setEditandoComentario] = useState(false);
    const [actualizando, setActualizando] = useState(false);
    const [archivo, setArchivo] = useState(null);
    const [uploadingFile, setUploadingFile] = useState(false);
    const [editandoEntrega, setEditandoEntrega] = useState(false);

    // Efecto para sincronizar los estados locales cuando el item cambia
    useEffect(() => {
        if (item.entrega) {
            setComentario(item.entrega.comentarioEstudiante || '');
            setEditandoComentario(false);
            setEditandoEntrega(false);
            setArchivo(null);
        }
    }, [item, item.entrega]);

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
            const response = await axios.post(`/api/item/${courseId}/tarea/${item.id}/entrega`, {
                accion,
                ...datos
            });
            
            if (response.data.entrega) {
                // Actualizar con los datos completos de la entrega
                const entregaActualizada = {
                    ...response.data.entrega,
                    archivo: response.data.entrega.archivo ? {
                        id: response.data.entrega.archivo.id,
                        url: response.data.entrega.archivo.url,
                        nombreOriginal: response.data.entrega.archivo.nombre // Asegurarse de usar el nombre correcto
                    } : null
                };
                onUpdate(entregaActualizada);
            }
            
            // Resetear estados después de la actualización
            setArchivo(null);
            setEditandoComentario(false);
            setEditandoEntrega(false);
        } catch (error) {
            console.error(`Error al ${accion}:`, error);
        } finally {
            setActualizando(false);
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
            <div className="mb-4">
                <h5>Puntos</h5>
                <p>{item.puntos} puntos</p>
            </div>

            {item.entrega && item.entrega.fechaEntrega && item.entrega.estado !== 'pendiente' ? (
                <div className="mb-4">
                    <h5>Estado de la entrega</h5>
                    <div className={`card border-start border-5 border-${getEstadoColor(item.entrega.estado)} ${isDarkMode ? 'bg-dark text-light' : 'bg-light'}`}>
                        <div className={`card-header fw-bold text-capitalize text-${getEstadoColor(item.entrega.estado)}`}>
                            {getEstadoTexto(item.entrega.estado, item.entrega.estado === 'atrasado')}
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

                            <div className="mb-3">
                                <h6><Icon name="documents" size={20} /> Archivos:</h6>
                                <div className="d-flex flex-wrap gap-2 align-items-start">
                                    {item.entrega.archivo ? (
                                        <>
                                            <a href={`${API_BASE_URL}${item.entrega.archivo.url}`} 
                                               target="_blank" 
                                               rel="noopener noreferrer" 
                                               className="btn btn-sm btn-outline-primary d-flex align-items-center">
                                                <Icon name="folder-download1" size={20} className="me-2" />
                                                <span className="text-truncate" style={{maxWidth: '200px'}}>
                                                    {item.entrega.archivo.nombreOriginal}
                                                </span>
                                            </a>
                                            {!item.entrega.isCalificado && (
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

                                {editandoEntrega && !item.entrega.isCalificado && (
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
                                                <Icon name="cross" size={16} className="me-2" />
                                                Cancelar
                                            </button>
                                        </div>
                                    </div>
                                )}
                            </div>

                            <div className="mb-2">
                                <h6><Icon name="badge" size={22} /> Comentario del profesor:</h6>
                                <p className="mb-0">{item.entrega.comentarioProfesor ?? 'Sin comentarios'}</p>
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
                                                    setComentario(item.entrega.comentarioEstudiante || '');
                                                }}
                                                disabled={actualizando}
                                            >
                                                <Icon name="cross" size={16} className="me-2" />
                                                Cancelar
                                            </button>
                                        </div>
                                    </div>
                                ) : (
                                    <div className="d-flex justify-content-between align-items-start">
                                        <p className="mb-0">
                                            {item.entrega.comentarioEstudiante || 'Sin comentarios'}
                                        </p>
                                        {!item.entrega.isCalificado && (
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

                            {!item.entrega.isCalificado && (
                                <div className="mt-3 d-flex gap-2">
                                    <button 
                                        className="btn btn-danger"
                                        onClick={handleDeleteEntrega}
                                        disabled={actualizando}
                                    >
                                        <Icon name="trash-can" size={20} className="me-2" />
                                        Borrar entrega
                                    </button>
                                    {(item.entrega.estado === 'entregado' || item.entrega.estado === 'atrasado') && (
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
                                            <Icon name="upload" size={16} className="me-2" />
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
            )}
        </div>
    );
};

export default TareaItem;