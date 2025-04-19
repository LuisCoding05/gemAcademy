import React, { useState, useEffect } from 'react';
import { useTheme } from '../../context/ThemeContext';
import { useNavigate } from 'react-router-dom';
import Icon from '../Icon';
import axios from '../../utils/axios';
import Loader from '../common/Loader';

const MaterialItem = ({ item, courseId, onUpdate }) => {
    const { isDarkMode } = useTheme();
    const navigate = useNavigate();
    // Inicializar isEditing como false para permitir alternar entre vistas
    const [isEditing, setIsEditing] = useState(false);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [formData, setFormData] = useState({
        titulo: item.titulo,
        descripcion: item.descripcion
    });
    const [file, setFile] = useState(null);
    const [uploadProgress, setUploadProgress] = useState(0);

    // Actualizar el formulario cuando cambia el item
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

            // Primero subir el archivo si existe
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

            // Luego actualizar el material con la referencia al archivo
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
            navigate(`/cursos/${courseId}`);
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

    const handleFileChange = (e) => {
        if (e.target.files[0]) {
            setFile(e.target.files[0]);
        }
    };

    if (loading) {
        return <Loader size="medium" />;
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
                            className="form-control"
                            name="titulo"
                            value={formData.titulo}
                            onChange={handleChange}
                            required
                        />
                    </div>
                    <div className="mb-3">
                        <label className="form-label">Descripción</label>
                        <textarea
                            className="form-control"
                            name="descripcion"
                            value={formData.descripcion}
                            onChange={handleChange}
                            rows="4"
                            required
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
                            className="form-control"
                            onChange={handleFileChange}
                        />
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
            {item.fichero && (
                <div className="mb-4">
                    <h5>Material descargable</h5>
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
                <div className="mt-4 d-flex gap-2 mb-4">
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