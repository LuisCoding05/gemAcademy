import React from 'react';
import { useTheme } from '../../context/ThemeContext';
import Icon from '../Icon';
import axios from '../../utils/axios';

const MaterialItem = ({ item }) => {
    const { isDarkMode } = useTheme();

    const handleDownload = async (fichero) => {
        try {
            const response = await axios.get(`/api/download/${fichero.id}`, {
                responseType: 'blob'
            });
            
            // Crear URL del blob
            const url = window.URL.createObjectURL(new Blob([response.data]));
            
            // Crear elemento temporal para la descarga
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', fichero.nombreOriginal);
            document.body.appendChild(link);
            link.click();
            
            // Limpieza
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
        } catch (error) {
            console.error('Error al descargar el archivo:', error);
        }
    };

    return (
        <div className="material-details">
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
        </div>
    );
};

export default MaterialItem;