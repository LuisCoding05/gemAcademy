import React, { useState, useEffect } from 'react';
import { useTheme } from '../../context/ThemeContext';
import { useAuth } from '../../context/AuthContext';
import axios from '../../utils/axios';
import Loader from '../common/Loader';
import ConfirmModal from './ConfirmModal';

const UserManagement = () => {
    const { isDarkMode } = useTheme();
    const { user: currentUser } = useAuth();
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [searchTerm, setSearchTerm] = useState('');
    const [modalConfig, setModalConfig] = useState({
        show: false,
        title: '',
        message: '',
        onConfirm: () => {},
        onCancel: () => setModalConfig(prev => ({ ...prev, show: false }))
    });

    const fetchUsers = async (page = 1, search = '') => {
        try {
            setLoading(true);
            const response = await axios.get('/api/home/users', {
                params: {
                    page,
                    search,
                    limit: 10
                }
            });
            setUsers(response.data.usuarios);
            setTotalPages(response.data.totalPaginas);
        } catch (err) {
            setError(err.response?.data?.message || 'Error al cargar los usuarios');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchUsers(currentPage, searchTerm);
    }, [currentPage, searchTerm]);

    const handleSearch = (e) => {
        e.preventDefault();
        setCurrentPage(1);
        fetchUsers(1, searchTerm);
    };

    const handlePageChange = (newPage) => {
        setCurrentPage(newPage);
    };    const handleBanUser = (user) => {
        // Evitar que el admin se banee a sí mismo
        if (user.id === currentUser.id) {
            setError('No puedes banearte a ti mismo');
            return;
        }

        // Verificar si el usuario actual es super admin
        const isCurrentUserSuperAdmin = currentUser.roles.includes('ROLE_SUPER_ADMIN');

        // Evitar que se banee a un super administrador si no eres super admin
        if (user.roles.includes('ROLE_SUPER_ADMIN') && !isCurrentUserSuperAdmin) {
            setError('No puedes banear a un Super Administrador');
            return;
        }

        setModalConfig({
            show: true,
            title: user.banned ? 'Desbanear Usuario' : 'Banear Usuario',
            message: user.banned 
                ? `¿Estás seguro de que deseas desbanear a ${user.username}?`
                : `¿Estás seguro de que deseas banear a ${user.username}?`,
            onConfirm: async () => {
                try {
                    await axios.put(`/api/home/users/${user.id}/update`, {
                        banned: !user.banned
                    });
                    await fetchUsers(currentPage, searchTerm);
                    setModalConfig(prev => ({ ...prev, show: false }));
                } catch (err) {
                    setError(err.response?.data?.message || 'Error al actualizar el usuario');
                }
            },
            onCancel: () => setModalConfig(prev => ({ ...prev, show: false }))
        });
    };    const handleToggleAdmin = (user) => {
        // Evitar que el admin se quite sus propios permisos
        if (user.id === currentUser.id) {
            setError('No puedes modificar tus propios permisos de administrador');
            return;
        }

        // Verificar si el usuario actual es super admin
        const isCurrentUserSuperAdmin = currentUser.roles.includes('ROLE_SUPER_ADMIN');

        // Evitar que se modifiquen los permisos de un super administrador si no eres super admin
        if (user.roles.includes('ROLE_SUPER_ADMIN') && !isCurrentUserSuperAdmin) {
            setError('No puedes modificar los permisos de un Super Administrador');
            return;
        }

        const isAdmin = user.roles.includes('ROLE_ADMIN');
        setModalConfig({
            show: true,
            title: isAdmin ? 'Quitar Rol de Administrador' : 'Hacer Administrador',
            message: isAdmin
                ? `¿Estás seguro de que deseas quitar el rol de administrador a ${user.username}?`
                : `¿Estás seguro de que deseas hacer administrador a ${user.username}?`,
            onConfirm: async () => {                try {
                    let newRoles;
                    
                    // Preservar ROLE_SUPER_ADMIN si ya lo tiene
                    if (user.roles.includes('ROLE_SUPER_ADMIN')) {
                        newRoles = isAdmin 
                            ? ['ROLE_USER', 'ROLE_SUPER_ADMIN']
                            : ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'];
                    } else {
                        newRoles = isAdmin 
                            ? ['ROLE_USER']
                            : ['ROLE_USER', 'ROLE_ADMIN'];
                    }
                    
                    await axios.put(`/api/home/users/${user.id}/update`, {
                        roles: newRoles
                    });
                    await fetchUsers(currentPage, searchTerm);
                    setModalConfig(prev => ({ ...prev, show: false }));
                } catch (err) {
                    setError(err.response?.data?.message || 'Error al actualizar el usuario');
                }
            },
            onCancel: () => setModalConfig(prev => ({ ...prev, show: false }))
        });
    };

    return (
        <div className="container py-4">
            <div className={`card ${isDarkMode ? 'bg-dark text-light' : ''}`}>
                <div className="card-body">
                    <h2 className="card-title mb-4">Gestión de Usuarios</h2>
                    
                    {error && (
                        <div className="alert alert-danger alert-dismissible fade show">
                            {error}
                            <button 
                                type="button" 
                                className="btn-close" 
                                onClick={() => setError(null)}
                                aria-label="Cerrar"
                            ></button>
                        </div>
                    )}

                    <form onSubmit={handleSearch} className="mb-4">
                        <div className="input-group">
                            <input
                                type="text"
                                className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                                placeholder="Buscar por nombre de usuario o email..."
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                            />
                        </div>
                    </form>

                    {loading ? (
                        <div className="text-center py-4">
                            <Loader />
                        </div>
                    ) : (
                        <>
                            <div className="table-responsive">
                                <table className={`table ${isDarkMode ? 'table-dark' : 'table-striped'}`}>
                                    <thead>
                                        <tr>                                            <th>ID</th>
                                            <th>Usuario</th>
                                            <th>Email</th>
                                            <th>Nombre</th>
                                            <th>Fecha de registro</th>
                                            <th>Última conexión</th>
                                            <th>Estado</th>
                                            <th>Rol</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {users.map(user => (
                                            <tr key={user.id} className={user.id === currentUser.id ? 'table-primary' : ''}>
                                                <td>{user.id}</td>
                                                <td>{user.username}</td>
                                                <td>{user.email}</td>
                                                <td>{user.nombre} {user.apellido}</td>
                                                <td>{new Date(user.fechaRegistro).toLocaleDateString()}</td>
                                                <td>{user.ultimaConexion ? new Date(user.ultimaConexion).toLocaleDateString() : 'N/A'}</td>                                                <td>
                                                    <span className={`badge ${user.banned ? 'bg-danger' : 'bg-success'}`}>
                                                        {user.banned ? 'Baneado' : 'Activo'}
                                                    </span>
                                                </td>
                                                <td>
                                                    {user.roles.includes('ROLE_SUPER_ADMIN') && (
                                                        <span className="badge bg-info">Super Admin</span>
                                                    )}
                                                    {user.roles.includes('ROLE_ADMIN') && !user.roles.includes('ROLE_SUPER_ADMIN') && (
                                                        <span className="badge bg-primary">Admin</span>
                                                    )}
                                                    {!user.roles.includes('ROLE_ADMIN') && !user.roles.includes('ROLE_SUPER_ADMIN') && (
                                                        <span className="badge bg-secondary">Usuario</span>
                                                    )}
                                                </td><td>
                                                    <div className="btn-group">
                                                        <button
                                                            className={`btn btn-sm ${user.banned ? 'btn-success' : 'btn-danger'}`}
                                                            onClick={() => handleBanUser(user)}                                                            disabled={
                                                                user.id === currentUser.id || 
                                                                (user.roles.includes('ROLE_SUPER_ADMIN') && !currentUser.roles.includes('ROLE_SUPER_ADMIN'))
                                                            }
                                                            title={
                                                                user.id === currentUser.id 
                                                                ? 'No puedes banearte a ti mismo' 
                                                                : (user.roles.includes('ROLE_SUPER_ADMIN') && !currentUser.roles.includes('ROLE_SUPER_ADMIN'))
                                                                ? 'No puedes banear a un Super Administrador'
                                                                : ''
                                                            }
                                                        >
                                                            {user.banned ? 'Desbanear' : 'Banear'}
                                                        </button>
                                                        <button
                                                            className={`btn btn-sm ${user.roles.includes('ROLE_ADMIN') ? 'btn-warning' : 'btn-info'}`}
                                                            onClick={() => handleToggleAdmin(user)}                                                            disabled={
                                                                user.id === currentUser.id || 
                                                                (user.roles.includes('ROLE_SUPER_ADMIN') && !currentUser.roles.includes('ROLE_SUPER_ADMIN'))
                                                            }
                                                            title={
                                                                user.id === currentUser.id 
                                                                ? 'No puedes modificar tus propios permisos' 
                                                                : (user.roles.includes('ROLE_SUPER_ADMIN') && !currentUser.roles.includes('ROLE_SUPER_ADMIN'))
                                                                ? 'No puedes modificar los permisos de un Super Administrador'
                                                                : ''
                                                            }
                                                        >
                                                            {user.roles.includes('ROLE_ADMIN') ? 'Quitar Admin' : 'Hacer Admin'}
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {totalPages > 1 && (
                                <nav className="mt-4">
                                    <ul className="pagination justify-content-center">
                                        {[...Array(totalPages)].map((_, index) => (
                                            <li key={index} className={`page-item ${currentPage === index + 1 ? 'active' : ''}`}>
                                                <button
                                                    className="page-link"
                                                    onClick={() => handlePageChange(index + 1)}
                                                >
                                                    {index + 1}
                                                </button>
                                            </li>
                                        ))}
                                    </ul>
                                </nav>
                            )}
                        </>
                    )}
                </div>
            </div>

            <ConfirmModal
                show={modalConfig.show}
                title={modalConfig.title}
                message={modalConfig.message}
                onConfirm={modalConfig.onConfirm}
                onCancel={modalConfig.onCancel}
            />
        </div>
    );
};

export default UserManagement;