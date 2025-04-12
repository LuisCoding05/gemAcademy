import { useEffect, useState } from 'react';
import { useTheme } from '../../context/ThemeContext';
import { useAuth } from '../../context/AuthContext';
import axios from '../../utils/axios';
import Loader from '../common/Loader';

export const Logs = () => {
    const { isDarkMode } = useTheme();
    const { token } = useAuth();
    const [logs, setLogs] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [isSearching, setIsSearching] = useState(false);
    const [error, setError] = useState(null);
    const [pagination, setPagination] = useState({
        total: 0,
        page: 1,
        limit: 10,
        pages: 0
    });
    const [filters, setFilters] = useState({
        username: '',
        email: '',
        startDate: '',
        endDate: ''
    });
    
    // Función para manejar cambios en los filtros
    const handleFilterChange = (e) => {
        const { name, value } = e.target;
        setFilters(prev => ({ ...prev, [name]: value }));
    };
    
    // Función para manejar el envío del formulario de búsqueda
    const handleSearch = (e) => {
        e.preventDefault();
        setPagination(prev => ({ ...prev, page: 1 }));
        fetchData();
    };
    
    // Función para cambiar de página
    const handlePageChange = (newPage) => {
        setPagination(prev => ({ ...prev, page: newPage }));
    };
    
    // Función para cambiar el límite de registros por página
    const handleLimitChange = (e) => {
        const newLimit = parseInt(e.target.value);
        setPagination(prev => ({ ...prev, limit: newLimit, page: 1 }));
    };
    
    async function fetchData() {
        try {
            setIsLoading(true);
            setIsSearching(true);
            
            // Construir URL con parámetros
            const params = new URLSearchParams({
                page: pagination.page,
                limit: pagination.limit,
                ...(filters.username && { username: filters.username }),
                ...(filters.email && { email: filters.email }),
                ...(filters.startDate && { startDate: filters.startDate }),
                ...(filters.endDate && { endDate: filters.endDate })
            });
            
            const response = await axios.get(`/api/logs?${params.toString()}`);
            
            if (response.status === 401) {
                throw new Error('No autorizado: Token inválido o expirado');
            }
            if (response.status === 403) {
                throw new Error('Acceso denegado: No tienes permisos de administrador');
            }
            
            setLogs(response.data.logs);
            setPagination(response.data.pagination);
            setError(null);
        } catch (error) {
            setError(error.message || 'Error al cargar los logs');
        } finally {
            setIsLoading(false);
            setIsSearching(false);
        }
    }
    
    useEffect(() => {
        if (token) {
            fetchData();
        } else {
            setError('No hay token de autenticación');
            setIsLoading(false);
        }
    }, [token, pagination.page, pagination.limit]);
    
    // Renderizar paginación
    const renderPagination = () => {
        const { page, pages } = pagination;
        const items = [];
        
        // Botón anterior
        items.push(
            <li key="prev" className={`page-item ${page === 1 ? 'disabled' : ''}`}>
                <button 
                    className="page-link" 
                    onClick={() => handlePageChange(page - 1)}
                    disabled={page === 1}
                >
                    &laquo;
                </button>
            </li>
        );
        
        // Páginas
        for (let i = 1; i <= pages; i++) {
            // Mostrar solo 5 páginas alrededor de la actual
            if (
                i === 1 || 
                i === pages || 
                (i >= page - 2 && i <= page + 2)
            ) {
                items.push(
                    <li key={i} className={`page-item ${i === page ? 'active' : ''}`}>
                        <button 
                            className="page-link" 
                            onClick={() => handlePageChange(i)}
                        >
                            {i}
                        </button>
                    </li>
                );
            } else if (
                (i === page - 3 && page > 4) || 
                (i === page + 3 && page < pages - 3)
            ) {
                // Agregar elipsis
                items.push(
                    <li key={`ellipsis-${i}`} className="page-item disabled">
                        <span className="page-link">...</span>
                    </li>
                );
            }
        }
        
        // Botón siguiente
        items.push(
            <li key="next" className={`page-item ${page === pages ? 'disabled' : ''}`}>
                <button 
                    className="page-link" 
                    onClick={() => handlePageChange(page + 1)}
                    disabled={page === pages}
                >
                    &raquo;
                </button>
            </li>
        );
        
        return (
            <nav aria-label="Navegación de páginas">
                <ul className="pagination justify-content-center">
                    {items}
                </ul>
            </nav>
        );
    };
    
    if (isLoading && logs.length === 0) {
        return (
            <div className="container mt-5">
                <Loader size="large" />
            </div>
        );
    }
    
    if (error && logs.length === 0) {
        return <div className="alert alert-danger mt-5">Error: {error}</div>;
    }
    
    return (
        <div className={`${isDarkMode ? 'bg-dark text-light' : 'bg-light'} p-4 rounded shadow-sm`}>
            <h2 className="mb-4 fw-bold">Registro de Actividades</h2>
            
            {/* Formulario de filtros */}
            <form onSubmit={handleSearch} className="row mb-4">
                <div className="col-md-3 mb-2">
                    <label className="form-label fw-semibold">Nombre de usuario</label>
                    <input 
                        type="text" 
                        className="form-control" 
                        name="username" 
                        value={filters.username} 
                        onChange={handleFilterChange}
                        placeholder="Filtrar por usuario"
                    />
                </div>
                <div className="col-md-3 mb-2">
                    <label className="form-label fw-semibold">Email</label>
                    <input 
                        type="text" 
                        className="form-control" 
                        name="email" 
                        value={filters.email} 
                        onChange={handleFilterChange}
                        placeholder="Filtrar por email"
                    />
                </div>
                <div className="col-md-2 mb-2">
                    <label className="form-label fw-semibold">Fecha inicio</label>
                    <input 
                        type="date" 
                        className="form-control" 
                        name="startDate" 
                        value={filters.startDate} 
                        onChange={handleFilterChange}
                    />
                </div>
                <div className="col-md-2 mb-2">
                    <label className="form-label fw-semibold">Fecha fin</label>
                    <input 
                        type="date" 
                        className="form-control" 
                        name="endDate" 
                        value={filters.endDate} 
                        onChange={handleFilterChange}
                    />
                </div>
                <div className="col-md-2 mb-2 d-flex align-items-end">
                    <button 
                        type="submit" 
                        className="btn btn-primary w-100"
                        disabled={isSearching}
                    >
                        {isSearching ? (
                            <>
                                <Loader size="small" className="me-1" />
                                Buscando...
                            </>
                        ) : (
                            'Buscar'
                        )}
                    </button>
                </div>
            </form>
            
            {/* Tabla de logs */}
            {logs.length > 0 ? (
                <>
                    <div className="table-responsive">
                        <table className={`table ${isDarkMode ? 'table-dark' : 'table-striped'} table-hover`}>
                            <thead>
                                <tr>
                                    <th className="fw-bold">Fecha</th>
                                    <th className="fw-bold">Usuario</th>
                                    <th className="fw-bold">Nombre</th>
                                    <th className="fw-bold">Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                {logs.map((log) => (
                                    <tr key={log.id}>
                                        <td>{log.fecha}</td>
                                        <td>{log.usuario.username}</td>
                                        <td>{log.usuario.nombre}</td>
                                        <td>{log.usuario.email}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    
                    {/* Paginación y límite */}
                    <div className="row mt-3">
                        <div className="col-md-6">
                            <div className="d-flex align-items-center">
                                <label className="me-2 fw-semibold">Mostrar:</label>
                                <select 
                                    className="form-select w-auto" 
                                    value={pagination.limit} 
                                    onChange={handleLimitChange}
                                >
                                    <option value={5}>5</option>
                                    <option value={10}>10</option>
                                    <option value={20}>20</option>
                                    <option value={50}>50</option>
                                </select>
                                <span className="ms-2">de {pagination.total} registros</span>
                            </div>
                        </div>
                        <div className="col-md-6">
                            {renderPagination()}
                        </div>
                    </div>
                </>
            ) : (
                <div className="alert alert-info">
                    No hay registros disponibles con los filtros seleccionados.
                </div>
            )}
        </div>
    );
};