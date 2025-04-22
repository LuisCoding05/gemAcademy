import React, { useState, useEffect, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTheme } from '../../context/ThemeContext';
import axios from '../../utils/axios';
import Icon from '../Icon';
import Loader from '../common/Loader';

const EntregasLista = ({ courseId, tareaId }) => {
    const { isDarkMode } = useTheme();
    const navigate = useNavigate();
    const [entregas, setEntregas] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [searchTerm, setSearchTerm] = useState('');
    const [debouncedSearchTerm, setDebouncedSearchTerm] = useState('');
    const [showOnlyRevision, setShowOnlyRevision] = useState(false);

    // Implementar debounce con useCallback
    const debounce = (func, wait) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    };

    // Crear una versión debounced del setter
    const debouncedSetSearch = useCallback(
        debounce((value) => setDebouncedSearchTerm(value), 500),
        []
    );

    // Manejar cambios en el input de búsqueda
    const handleSearchChange = (e) => {
        setSearchTerm(e.target.value);
        debouncedSetSearch(e.target.value);
    };

    const fetchEntregas = async () => {
        try {
            setLoading(true);
            setError(null);
            const params = new URLSearchParams();
            if (debouncedSearchTerm) params.append('nombre', debouncedSearchTerm);
            if (showOnlyRevision) params.append('revision', 'true');

            const response = await axios.get(
                `/api/item/${courseId}/tarea/${tareaId}/entregas?${params.toString()}`
            );
            setEntregas(response.data);
        } catch (error) {
            console.error('Error al cargar las entregas:', error);
            setError(error.response?.data?.message || 'Error al cargar las entregas');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchEntregas();
    }, [courseId, tareaId, debouncedSearchTerm, showOnlyRevision]);

    if (loading) {
        return (
            <div className="text-center p-4">
                <Loader size="medium" />
                <p>Cargando entregas...</p>
            </div>
        );
    }

    return (
        <div className="entregas-lista">
            {error && (
                <div className="alert alert-danger">{error}</div>
            )}

            <div className="mb-4">
                <div className="row g-3">
                    <div className="col-md-6">
                        <div className="input-group">
                            <span className="input-group-text">
                                <Icon name="magnifier" size={20} />
                            </span>
                            <input
                                type="text"
                                className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                                placeholder="Buscar por nombre de estudiante..."
                                value={searchTerm}
                                onChange={handleSearchChange}
                            />
                        </div>
                    </div>
                    <div className="col-md-6">
                        <div className="form-check">
                            <input
                                type="checkbox"
                                className="form-check-input"
                                id="showRevision"
                                checked={showOnlyRevision}
                                onChange={(e) => setShowOnlyRevision(e.target.checked)}
                            />
                            <label className="form-check-label" htmlFor="showRevision">
                                Mostrar solo solicitudes de revisión
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {entregas.length === 0 ? (
                <div className="alert alert-info">
                    No se encontraron entregas
                </div>
            ) : (
                <div className="table-responsive">
                    <table className={`table ${isDarkMode ? 'table-dark' : ''}`}>
                        <thead>
                            <tr>
                                <th>Estudiante</th>
                                <th>Estado</th>
                                <th>Fecha de entrega</th>
                                <th>Calificación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {entregas.map((entrega) => (
                                <tr key={entrega.id}>
                                    <td>
                                        <div className="d-flex align-items-center">
                                            <img
                                                src={entrega.estudiante.imagen || 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483544/default_bumnyb.webp'}
                                                alt={entrega.estudiante.nombre}
                                                className="rounded-circle me-2"
                                                width="32"
                                                height="32"
                                            />
                                            <div>
                                                <div>
                                                    {entrega.estudiante.nombre} {entrega.estudiante.apellido}
                                                </div>
                                                <small className="text-muted">@{entrega.estudiante.username}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span className={`badge bg-${getEstadoColor(entrega.estado)}`}>
                                            {getEstadoTexto(entrega.estado)}
                                        </span>
                                    </td>
                                    <td>
                                        {entrega.fechaEntrega ? 
                                            new Date(entrega.fechaEntrega).toLocaleString() :
                                            'No entregado'
                                        }
                                    </td>
                                    <td>
                                        {entrega.calificacion ? (
                                            <span className={entrega.calificacion >= 5 ? 'text-success' : 'text-danger'}>
                                                {entrega.calificacion}/10
                                            </span>
                                        ) : 'Sin calificar'}
                                    </td>
                                    <td>
                                        <div className="btn-group">
                                            <button
                                                className="btn btn-sm btn-primary"
                                                onClick={() => navigate(`/cursos/${courseId}/tarea/${tareaId}/entrega/${entrega.id}`)}
                                                title="Ver detalles y calificar"
                                            >
                                                <Icon name="eye" size={16} className="me-1" />
                                                Revisar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </div>
    );
};

const getEstadoColor = (estado) => {
    switch (estado) {
        case 'entregado':
            return 'primary';
        case 'calificado':
            return 'success';
        case 'revision_solicitada':
            return 'warning';
        case 'atrasado':
            return 'danger';
        case 'pendiente':
        default:
            return 'secondary';
    }
};

const getEstadoTexto = (estado) => {
    return estado.replace('_', ' ');
};

export default EntregasLista;