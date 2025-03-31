import { useEffect, useState } from 'react';
import { useTheme } from './ThemeContext';

export const Home = () => {
    const { isDarkMode } = useTheme();
    const [logs, setLogs] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    async function fetchData() {
        try {
            setIsLoading(true);
            const response = await fetch("http://localhost:8000/test");
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();
            setLogs(data.logs);
        } catch (error) {
            setError(error.message);
        } finally {
            setIsLoading(false);
        }
    }

    useEffect(() => {
        fetchData();
    }, []);

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