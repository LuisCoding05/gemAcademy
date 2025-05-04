import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useTheme } from '../../context/ThemeContext';
import axios from '../../utils/axios';
import Icon from '../Icon';

const NotificationIndicator = () => {
    const { isDarkMode } = useTheme();
    const [unreadCount, setUnreadCount] = useState(0);

    useEffect(() => {
        fetchUnreadCount();
        const interval = setInterval(fetchUnreadCount, 30000); // Actualizar cada 30 segundos
        return () => clearInterval(interval);
    }, []);

    const fetchUnreadCount = async () => {
        try {
            const response = await axios.get('/api/notificaciones');
            setUnreadCount(response.data.noLeidas);
        } catch (error) {
            console.error('Error al obtener notificaciones:', error);
        }
    };

    return (
        <Link 
            to="/notificaciones" 
            className="nav-link position-relative"
            title="Ver notificaciones"
        >
            <Icon 
                name="envelope" 
                size={24} 
                color={'white'} 
            />
            {unreadCount > 0 && (
                <span className="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {unreadCount > 99 ? '99+' : unreadCount}
                    <span className="visually-hidden">notificaciones no leídas</span>
                </span>
            )}
        </Link>
    );
};

export default NotificationIndicator;