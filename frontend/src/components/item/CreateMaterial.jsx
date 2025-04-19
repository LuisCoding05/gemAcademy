import React, { useState } from 'react';
import { useTheme } from '../../context/ThemeContext';
import Icon from '../Icon';
import axios from '../../utils/axios';
import Loader from '../common/Loader';

const CreateMaterial = ({ courseId, onCreated, onCancel }) => {
    const { isDarkMode } = useTheme();
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [formData, setFormData] = useState({
        titulo: '',
        descripcion: ''
    });
    const [file, setFile] = useState(null);
    const [uploadProgress, setUploadProgress] = useState(0);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError(null);
        setUploadProgress(0);

        try {
            let ficheroId = null;

            // Primero subir el archivo si existe
            if (file) {
                const formData = new FormData();
                formData.append('file', file);

                const uploadResponse = await axios.post('/api/upload', formData, {
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

            // Luego crear el material con la referencia al archivo
            const createData = new FormData();
            createData.append('data', JSON.stringify({
                ...formData,
                ficheroId
            }));

            const response = await axios.post(
                `/api/item/${courseId}/material/create`,
                createData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }
            );

            if (onCreated) {
                onCreated(response.data.material);
            }
        } catch (error) {
            console.error('Error al crear el material:', error);
            setError(error.response?.data?.message || 'Error al crear el material');
        } finally {
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
        const selectedFile = e.target.files[0];
        if (selectedFile) {
            // Validar el tamaño del archivo (20MB máximo)
            if (selectedFile.size > 20 * 1024 * 1024) {
                setError('El archivo no puede superar los 20MB');
                e.target.value = '';
                return;
            }
            
            // Validar el tipo de archivo
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

    if (loading) {
        return (
            <div className="text-center p-4">
                <Loader size="large" />
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
        <div className={`card ${isDarkMode ? 'bg-dark text-light' : ''}`}>
            <div className="card-header">
                <h5 className="mb-0">Crear nuevo material</h5>
            </div>
            <div className="card-body">
                {error && (
                    <div className="alert alert-danger">{error}</div>
                )}
                <form onSubmit={handleSubmit}>
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
                        <textarea
                            className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                            name="descripcion"
                            value={formData.descripcion}
                            onChange={handleChange}
                            rows="4"
                            required
                        />
                    </div>
                    <div className="mb-3">
                        <label className="form-label">Archivo (opcional)</label>
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
                    <div className="d-flex gap-2">
                        <button type="submit" className="btn btn-primary" disabled={loading}>
                            <Icon name="plus" size={20} className="me-2" />
                            Crear material
                        </button>
                        <button 
                            type="button" 
                            className="btn btn-secondary"
                            onClick={onCancel}
                        >
                            <Icon name="circle-with-cross" size={20} className="me-2" />
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default CreateMaterial;