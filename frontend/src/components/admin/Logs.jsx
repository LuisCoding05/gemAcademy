import { useEffect, useState } from 'react';
import { useTheme } from '../../context/ThemeContext';
import { useAuth } from '../../context/AuthContext';

export const Logs = () => {
    const { isDarkMode } = useTheme();
    const { token } = useAuth();
    const [logs, setLogs] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    async function fetchData() {
        try {
            setIsLoading(true);
            console.log('Token enviado:', token);
            const response = await fetch("http://localhost:8000/api/logs", {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });
            
            if (response.status === 401) {
                throw new Error('No autorizado: Token inválido o expirado');
            }
            if (response.status === 403) {
                throw new Error('Acceso denegado: No tienes permisos de administrador');
            }
            if (!response.ok) {
                throw new Error('Error en la petición: ' + response.statusText);
            }
            
            const data = await response.json();
            setLogs((data.logs).reverse());
        } catch (error) {
            setError(error.message);
        } finally {
            setIsLoading(false);
        }
    }

    useEffect(() => {
        if (token) {
            fetchData();
        } else {
            setError('No hay token de autenticación');
            setIsLoading(false);
        }
    }, [token]);

    if (isLoading) return <div className="text-center mt-5"><img src="images/charging/charging.gif" alt="" /></div>;
    if (error) return <div className="text-center mt-5 text-danger">Error: {error}</div>;

    return (
        <div className={`${isDarkMode ? 'dark-mode' : ''}`}>
            <div className="row">
                <div className="col-12">
                    <h2 className="mb-4">Registro de Actividades</h2>
                    {logs.length > 0 ? (
                        <div className="table-responsive">
                            <table className="table table-striped table-hover">
                                <thead className="table-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {logs.map((log, index) => (
                                        <tr key={index}>
                                            <td>{log.fecha}</td>
                                            <td>{log.usuario.nombre}</td>
                                            <td>{log.usuario.email}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <p>No hay registros disponibles.</p>
                    )}
                </div>
            </div>
        </div>
    );
};