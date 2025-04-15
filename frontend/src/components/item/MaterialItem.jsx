import React from 'react';
import { useTheme } from '../../context/ThemeContext';
import Icon from '../Icon';

const MaterialItem = ({ item }) => {
    const { isDarkMode } = useTheme();

    return (
        <div className="material-details">
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
            {item.completado && (
                <div className="alert alert-success">
                    <Icon name="checkmark" size={20} className="me-2" />
                    Material completado
                </div>
            )}
        </div>
    );
};

export default MaterialItem;