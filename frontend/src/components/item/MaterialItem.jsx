import React, { useState, useEffect } from 'react';
import { useTheme } from '../../context/ThemeContext';
import Icon from '../Icon';
import axios from '../../utils/axios';
import Loader from '../common/Loader';
import Editor from '../common/Editor';

const MaterialItem = ({ item, courseId, onUpdate }) => {
    const { isDarkMode } = useTheme();
    const [isEditing, setIsEditing] = useState(false);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [formData, setFormData] = useState({
        titulo: item.titulo,
        descripcion: item.descripcion
    });
    const [file, setFile] = useState(null);
    const [uploadProgress, setUploadProgress] = useState(0);

    useEffect(() => {
        setFormData({
            titulo: item.titulo,
            descripcion: item.descripcion
        });
    }, [item]);

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

            // Marcar como completado si es necesario
            if (!item.completado) {
                await axios.post(`/api/item/${courseId}/material/${item.id}/complete`);
                if (onUpdate) {
                    onUpdate({ ...item, completado: true });
                }
            }
        } catch (error) {
            console.error('Error al descargar el archivo:', error);
            setError('Error al descargar el archivo');
        }
    };

    const handleEdit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError(null);
        setUploadProgress(0);

        try {
            let ficheroId = null;

            if (file) {
                const uploadFormData = new FormData();
                uploadFormData.append('file', file);

                const uploadResponse = await axios.post('/api/upload', uploadFormData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    },
                    onUploadProgress: (progressEvent) => {
                        const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                        setUploadProgress(percentCompleted);
                    }
                });

                ficheroId = uploadResponse.data.id;
            }

            const updateData = new FormData();
            updateData.append('data', JSON.stringify({
                ...formData,
                ficheroId
            }));

            const response = await axios.post(
                `/api/item/${courseId}/material/${item.id}/edit`,
                updateData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }
            );

            if (onUpdate) {
                onUpdate(response.data.material);
            }
            setIsEditing(false);
        } catch (error) {
            setError(error.response?.data?.message || 'Error al editar el material');
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async () => {
        if (!window.confirm('¿Estás seguro de que quieres eliminar este material?')) {
            return;
        }

        setLoading(true);
        setError(null);

        try {
            await axios.delete(`/api/item/${courseId}/material/${item.id}/delete`);
            if (onUpdate) {
                onUpdate(null);
            }
        } catch (error) {
            setError(error.response?.data?.message || 'Error al eliminar el material');
            setLoading(false);
        }
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
    };

    const handleDescriptionChange = (content) => {
        setFormData(prev => ({
            ...prev,
            descripcion: content
        }));
    };

    const handleFileChange = (e) => {
        const selectedFile = e.target.files[0];
        if (selectedFile) {
            if (selectedFile.size > 20 * 1024 * 1024) {
                setError('El archivo no puede superar los 20MB');
                e.target.value = '';
                return;
            }
            
            const allowedTypes = [
                'application/pdf', 
                'image/jpeg', 
                'image/png', 
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'video/mp4',
                'video/webm',
                'audio/mpeg',
                'audio/wav'
            ];
            
            if (!allowedTypes.includes(selectedFile.type)) {
                setError('Tipo de archivo no permitido. Solo se permiten PDF, imágenes, documentos Word, videos (MP4, WebM) y audio (MP3, WAV).');
                e.target.value = '';
                return;
            }

            setFile(selectedFile);
            setError(null);
        }
    };

    if (loading) {
        return (
            <div className="text-center">
                <Loader size="medium" />
                {uploadProgress > 0 && (
                    <div className="mt-3">
                        <div className="progress">
                            <div 
                                className="progress-bar" 
                                role="progressbar" 
                                style={{ width: `${uploadProgress}%` }} 
                                aria-valuenow={uploadProgress} 
                                aria-valuemin="0" 
                                aria-valuemax="100"
                            >
                                {uploadProgress}%
                            </div>
                        </div>
                    </div>
                )}
            </div>
        );
    }

    if (isEditing) {
        return (
            <div className="material-edit">
                {error && (
                    <div className="alert alert-danger">{error}</div>
                )}
                <form onSubmit={handleEdit}>
                    <div className="mb-3">
                        <label className="form-label">Título</label>
                        <input
                            type="text"
                            className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                            name="titulo"
                            value={formData.titulo}
                            onChange={handleChange}
                            required
                        />
                    </div>
                    <div className="mb-3">
                        <label className="form-label">Descripción</label>
                        <Editor
                            data={formData.descripcion}
                            onChange={handleDescriptionChange}
                            placeholder="Escribe la descripción del material aquí..."
                        />
                    </div>
                    <div className="mb-3">
                        <label className="form-label">Archivo actual</label>
                        {item.fichero ? (
                            <div>
                                <Icon name="folder" size={20} className="me-2" />
                                {item.fichero.nombreOriginal}
                            </div>
                        ) : (
                            <p className="text-muted">No hay archivo adjunto</p>
                        )}
                    </div>
                    <div className="mb-3">
                        <label className="form-label">Nuevo archivo (opcional)</label>
                        <input
                            type="file"
                            className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                            onChange={handleFileChange}
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.mp4,.webm,.mp3,.wav"
                        />
                        <small className="text-muted d-block mt-1">
                            Formatos permitidos: PDF, Word, JPG, PNG, MP4, WebM, MP3, WAV. Tamaño máximo: 20MB
                        </small>
                    </div>
                    <div className="d-flex gap-2">
                        <button type="submit" className="btn btn-primary" disabled={loading}>
                            <Icon name="share-alternitive" size={20} className="me-2" />
                            Guardar cambios
                        </button>
                        <button 
                            type="button" 
                            className="btn btn-secondary"
                            onClick={() => setIsEditing(false)}
                        >
                            <Icon name="circle-with-cross" size={20} className="me-2" />
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        );
    }

    return (
        <div className="material-details">
            {error && (
                <div className="alert alert-danger">{error}</div>
            )}
            <div className="content mb-4">
                <div 
                    className="ck-content"
                    dangerouslySetInnerHTML={{ __html: item.descripcion }} 
                />
            </div>
            {item.fichero && (
                <div className="mb-4">
                    <button 
                        className="btn btn-primary"
                        onClick={() => handleDownload(item.fichero)}
                    >
                        <Icon name="folder-download" size={20} className="me-2" />
                        Descargar {item.fichero.nombreOriginal}
                    </button>
                </div>
            )}
            {item.userRole === 'profesor' && (
                <div className="mt-4 d-flex gap-2">
                    <button 
                        className="btn btn-warning"
                        onClick={() => setIsEditing(true)}
                    >
                        <Icon name="pencil2" size={20} className="me-2" />
                        Editar material
                    </button>
                    <button 
                        className="btn btn-danger"
                        onClick={handleDelete}
                    >
                        <Icon name="trash-can" size={20} className="me-2" />
                        Eliminar material
                    </button>
                </div>
            )}
        </div>
    );
};

export default MaterialItem;