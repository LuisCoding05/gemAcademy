import React, { useState, useEffect } from 'react';
import { useTheme } from '../../context/ThemeContext';
import { useNavigate } from 'react-router-dom';
import axios from '../../utils/axios';
import Icon from '../Icon';
import { motion } from 'framer-motion';

const NotificationsPage = () => {
    const { isDarkMode } = useTheme();
    const navigate = useNavigate();
    const [notifications, setNotifications] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [filter, setFilter] = useState('all');
    const [currentPage, setCurrentPage] = useState(1);
    const [pagination, setPagination] = useState({
        paginaActual: 1,
        totalPaginas: 1,
        totalNotificaciones: 0,
        porPagina: 10
    });

    useEffect(() => {
        fetchNotifications();
    }, [currentPage]);

    const fetchNotifications = async () => {
        try {
            setLoading(true);
            const response = await axios.get(`/api/notificaciones?page=${currentPage}&limit=10`);
            setNotifications(response.data.notificaciones);
            setPagination(response.data.paginacion);
        } catch (error) {
            setError('Error al cargar las notificaciones');
            console.error('Error:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleMarkAsRead = async (id) => {
        try {
            await axios.put(`/api/notificaciones/${id}/leer`);
            setNotifications(notifications.map(notif => 
                notif.id === id ? { ...notif, leida: true } : notif
            ));
        } catch (error) {
            console.error('Error al marcar como leída:', error);
        }
    };

    const handleMarkAllAsRead = async () => {
        try {
            await axios.put('/api/notificaciones/leer-todas');
            setNotifications(notifications.map(notif => ({ ...notif, leida: true })));
        } catch (error) {
            console.error('Error al marcar todas como leídas:', error);
        }
    };

    const handleNavigateToContent = (url, id) => {
        if (!url) return;
        handleMarkAsRead(id);
        navigate(url);
    };

    const getNotificationIcon = (tipo) => {
        switch (tipo) {
            case 'mensaje_curso':
                return { name: 'users', color: '#3498db' };
            case 'correccion_entrega':
                return { name: 'clipboard-edit', color: '#e74c3c' };
            case 'logro_desbloqueado':
                return { name: 'medal1', color: '#f1c40f' };
            case 'nueva_tarea':
                return { name: 'clipboard', color: '#2ecc71' };
            case 'recordatorio':
                return { name: 'stop-watch1', color: '#9b59b6' };
            case 'nuevo_nivel':
                return { name: 'transformers', color: '#e67e22' };
            default:
                return { name: 'notification', color: '#95a5a6' };
        }
    };

    const getNotificationColor = (tipo) => {
        switch (tipo) {
            case 'mensaje_curso':
                return 'border-primary';
            case 'correccion_entrega':
                return 'border-danger';
            case 'logro_desbloqueado':
                return 'border-warning';
            case 'nueva_tarea':
                return 'border-success';
            case 'recordatorio':
                return 'border-info';
            case 'nuevo_nivel':
                return 'border-warning';
            default:
                return 'border-secondary';
        }
    };

    const filteredNotifications = notifications.filter(notif => {
        if (filter === 'all') return true;
        if (filter === 'unread') return !notif.leida;
        return notif.tipo === filter;
    });

    const handlePageChange = (newPage) => {
        setCurrentPage(newPage);
        window.scrollTo(0, 0);
    };

    const renderPagination = () => {
        const pages = [];
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(pagination.totalPaginas, startPage + maxVisiblePages - 1);

        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        // Botón "Anterior"
        pages.push(
            <li key="prev" className={`page-item ${currentPage === 1 ? 'disabled' : ''}`}>
                <button
                    className="page-link"
                    onClick={() => handlePageChange(currentPage - 1)}
                    disabled={currentPage === 1}
                >
                    <Icon name="controller-fast-backward" size={16} />
                </button>
            </li>
        );

        // Primera página si no es visible
        if (startPage > 1) {
            pages.push(
                <li key={1} className="page-item">
                    <button className="page-link" onClick={() => handlePageChange(1)}>1</button>
                </li>
            );
            if (startPage > 2) {
                pages.push(<li key="ellipsis1" className="page-item disabled"><span className="page-link">...</span></li>);
            }
        }

        // Páginas numeradas
        for (let i = startPage; i <= endPage; i++) {
            pages.push(
                <li key={i} className={`page-item ${currentPage === i ? 'active' : ''}`}>
                    <button className="page-link" onClick={() => handlePageChange(i)}>{i}</button>
                </li>
            );
        }

        // Última página si no es visible
        if (endPage < pagination.totalPaginas) {
            if (endPage < pagination.totalPaginas - 1) {
                pages.push(<li key="ellipsis2" className="page-item disabled"><span className="page-link">...</span></li>);
            }
            pages.push(
                <li key={pagination.totalPaginas} className="page-item">
                    <button 
                        className="page-link" 
                        onClick={() => handlePageChange(pagination.totalPaginas)}
                    >
                        {pagination.totalPaginas}
                    </button>
                </li>
            );
        }

        // Botón "Siguiente"
        pages.push(
            <li key="next" className={`page-item ${currentPage === pagination.totalPaginas ? 'disabled' : ''}`}>
                <button
                    className="page-link"
                    onClick={() => handlePageChange(currentPage + 1)}
                    disabled={currentPage === pagination.totalPaginas}
                >
                    <Icon name="arrow-right" size={16} />
                </button>
            </li>
        );

        return (
            <nav aria-label="Paginación de notificaciones" className="mt-4">
                <ul className="pagination justify-content-center">
                    {pages}
                </ul>
                <div className="text-center mt-2">
                    <small className="text-muted">
                        Mostrando {notifications.length} de {pagination.totalNotificaciones} notificaciones
                    </small>
                </div>
            </nav>
        );
    };

    if (loading) {
        return (
            <div className="container mt-5 text-center">
                <div className="spinner-border text-primary" role="status">
                    <span className="visually-hidden">Cargando...</span>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="container mt-5">
                <div className="alert alert-danger" role="alert">
                    {error}
                </div>
            </div>
        );
    }

    return (
        <div className="container py-4">
            <div className="d-flex justify-content-between align-items-center mb-4">
                <h2 className="mb-0">
                    <Icon name="notification" size={34} className="me-2" />
                    Notificaciones
                </h2>
                <div className="d-flex gap-2">
                    <select 
                        className={`form-select ${isDarkMode ? 'bg-dark text-light' : ''}`}
                        value={filter}
                        onChange={(e) => setFilter(e.target.value)}
                    >
                        <option value="all">Todas</option>
                        <option value="unread">No leídas</option>
                        <option value="mensaje_curso">Mensajes</option>
                        <option value="correccion_entrega">Correcciones</option>
                        <option value="logro_desbloqueado">Logros</option>
                        <option value="nueva_tarea">Tareas nuevas</option>
                        <option value="recordatorio">Recordatorios</option>
                        <option value="nuevo_nivel">Niveles</option>
                    </select>
                    <button 
                        className="btn btn-primary"
                        onClick={handleMarkAllAsRead}
                        disabled={!notifications.some(n => !n.leida)}
                    >
                        <Icon name="checkmark" size={20} className="me-2" />
                        Marcar todas como leídas
                    </button>
                </div>
            </div>

            {filteredNotifications.length === 0 ? (
                <div className="alert alert-info">
                    No hay notificaciones {filter !== 'all' ? 'con el filtro seleccionado' : ''}
                </div>
            ) : (
                <>
                    <div className="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                        {filteredNotifications.map((notification) => (
                            <div className="col" key={notification.id}>
                                <motion.div 
                                    initial={{ opacity: 0, y: 20 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    className={`card h-100 ${isDarkMode ? 'bg-dark text-light' : ''} border-3 ${getNotificationColor(notification.tipo)} ${!notification.leida ? 'border-start' : ''}`}
                                >
                                    <div className="card-body">
                                        <div className="d-flex justify-content-between align-items-start mb-3">
                                            <div className="d-flex align-items-center">
                                                <div 
                                                    className="rounded-circle p-2 me-2"
                                                    style={{ 
                                                        backgroundColor: `${getNotificationIcon(notification.tipo).color}20`
                                                    }}
                                                >
                                                    <Icon 
                                                        name={getNotificationIcon(notification.tipo).name} 
                                                        size={24} 
                                                        color={getNotificationIcon(notification.tipo).color}
                                                    />
                                                </div>
                                                <h5 className="card-title mb-0">{notification.titulo}</h5>
                                            </div>
                                            {!notification.leida && (
                                                <button 
                                                    className="btn btn-outline-primary btn-sm"
                                                    onClick={() => handleMarkAsRead(notification.id)}
                                                >
                                                    <Icon name="checkmark" size={16} />
                                                </button>
                                            )}
                                        </div>
                                        <p className="card-text">{notification.contenido}</p>
                                        <div className="d-flex justify-content-between align-items-center mt-3">
                                            <small className="text-muted">
                                                {new Date(notification.fechaCreacion).toLocaleString()}
                                            </small>
                                            {notification.url && (
                                                <button
                                                    className="btn btn-link p-0"
                                                    onClick={() => handleNavigateToContent(notification.url, notification.id)}
                                                >
                                                    Ver contenido 
                                                    <Icon name="arrow-right" size={16} className="ms-1" />
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                </motion.div>
                            </div>
                        ))}
                    </div>
                    {renderPagination()}
                </>
            )}
        </div>
    );
};

export default NotificationsPage;