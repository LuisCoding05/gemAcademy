import React, { useState } from 'react';
import { useTheme } from '../../context/ThemeContext';
import Icon from '../Icon';
import Editor from '../common/Editor';
import axios from '../../utils/axios';
import Loader from '../common/Loader';

const TareaEditForm = ({ item, courseId, onUpdate, onCancel }) => {
    const { isDarkMode } = useTheme();
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [uploadProgress, setUploadProgress] = useState(0);
    const [formData, setFormData] = useState({
        titulo: item?.titulo || '',
        descripcion: item?.descripcion || '',
        fechaLimite: item?.fechaLimite ? new Date(item.fechaLimite).toISOString().slice(0, 16) : '',
        puntosMaximos: item?.puntosMaximos || 100,
        esObligatoria: item?.esObligatoria !== false
    });
    const [file, setFile] = useState(null);

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value
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
                setError('El archivo excede el límite de 20MB');
                e.target.value = '';
                return;
            }
            
            const allowedTypes = [
                'application/pdf', 
                'image/jpeg', 
                'image/png', 
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ];
            
            if (!allowedTypes.includes(selectedFile.type)) {
                setError('Tipo de archivo no permitido. Solo se permiten PDF, imágenes y documentos Word.');
                e.target.value = '';
                return;
            }

            setFile(selectedFile);
            setError(null);
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

    const handleSubmit = async (e) => {
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
                `/api/item/${courseId}/tarea/${item.id}/edit`,
                updateData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }
            );

            if (onUpdate) {
                onUpdate(response.data.tarea);
            }
        } catch (error) {
            setError(error.response?.data?.message || 'Error al guardar la tarea');
        } finally {
            setLoading(false);
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

    return (
        <form onSubmit={handleSubmit}>
            {error && (
                <div className="alert alert-danger">{error}</div>
            )}

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
                    placeholder="Escribe las instrucciones de la tarea aquí..."
                />
            </div>

            <div className="mb-3">
                <label className="form-label">Fecha límite de entrega</label>
                <input
                    type="datetime-local"
                    className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                    name="fechaLimite"
                    value={formData.fechaLimite}
                    onChange={handleChange}
                    required
                />
            </div>

            <div className="mb-3">
                <label className="form-label">Puntos máximos</label>
                <input
                    type="number"
                    className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                    name="puntosMaximos"
                    value={formData.puntosMaximos}
                    onChange={handleChange}
                    min="0"
                    max="100"
                    required
                />
            </div>

            <div className="mb-3">
                <div className="form-check">
                    <input
                        type="checkbox"
                        className="form-check-input"
                        name="esObligatoria"
                        checked={formData.esObligatoria}
                        onChange={handleChange}
                        id="esObligatoria"
                    />
                    <label className="form-check-label" htmlFor="esObligatoria">
                        Es obligatoria
                    </label>
                </div>
            </div>

            <div className="mb-3">
                <label className="form-label">
                    {item?.fichero ? 'Archivo actual' : 'Archivo adjunto (opcional)'}
                </label>
                {item?.fichero && (
                    <div className="mb-2 d-flex gap-2 align-items-center">
                        <Icon name="folder" size={20} className="me-2" />
                        <span>{item.fichero.nombreOriginal}</span>
                        <button 
                            type="button"
                            className="btn btn-sm btn-primary"
                            onClick={() => handleDownload(item.fichero)}
                        >
                            <Icon name="folder-download" size={16} className="me-2" />
                            Descargar
                        </button>
                    </div>
                )}
                <input
                    type="file"
                    className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                    onChange={handleFileChange}
                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                />
                <small className="text-muted d-block mt-1">
                    Formatos permitidos: PDF, Word, JPG, PNG. Tamaño máximo: 20MB
                </small>
            </div>

            {uploadProgress > 0 && (
                <div className="mb-3">
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

            <div className="d-flex gap-2">
                <button 
                    type="submit" 
                    className="btn btn-primary"
                    disabled={loading}
                >
                    {loading ? (
                        <>
                            <span className="spinner-border spinner-border-sm me-2" />
                            Guardando...
                        </>
                    ) : (
                        <>
                            <Icon name="checkmark" size={20} className="me-2" />
                            {item ? 'Guardar cambios' : 'Crear tarea'}
                        </>
                    )}
                </button>
                <button
                    type="button"
                    className="btn btn-secondary"
                    onClick={onCancel}
                    disabled={loading}
                >
                    <Icon name="circle-with-cross" size={20} className="me-2" />
                    Cancelar
                </button>
            </div>
        </form>
    );
};

export default TareaEditForm;