import React, { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import axios from 'axios';
import { useTheme } from '../../context/ThemeContext';

const Dashboard = () => {
  const { user } = useAuth();
  const { isDarkMode } = useTheme();
  const [dashboardData, setDashboardData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchDashboardData = async () => {
      try {
        const response = await axios.get('/api/dashboard');
        setDashboardData(response.data);
        console.log(response.data);
        setLoading(false);
      } catch (err) {
        setError('Error al cargar el dashboard');
        setLoading(false);
      }
    };

    fetchDashboardData();
  }, []);

  if (loading) {
    return (
      <div className="d-flex justify-content-center align-items-center vh-100">
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Cargando...</span>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="alert alert-danger" role="alert">
        {error}
      </div>
    );
  }

  if (!dashboardData) {
    return (
      <div className="alert alert-warning" role="alert">
        No se pudieron cargar los datos del dashboard
      </div>
    );
  }

  const userData = dashboardData.user || {};
  const estadisticas = dashboardData.estadisticas || {};
  const cursos = dashboardData.cursos || [];
  const logros = dashboardData.logros || [];

  return (
    <div className="container py-4">
      {/* Perfil y Estadísticas */}
      <div className="row mb-4">
        <div className="col-md-4">
          <div className={`card ${isDarkMode ? 'bg-dark text-light' : ''} shadow-sm`}>
            <div className="card-body text-center">
              <img 
                src={userData.imagen?.url || './images/pfpgemacademy/default.webp'} 
                alt="Foto de perfil" 
                className="rounded-circle mb-3"
                style={{ width: '120px', height: '120px', objectFit: 'cover' }}
              />
              <h3 className="card-title">{userData.nombre} {userData.apellido}</h3>
              <p className="text-muted mb-1">@{userData.username}</p>
              <p className="text-muted">{userData.email}</p>
            </div>
          </div>
        </div>
        <div className="col-md-8">
          <div className={`card ${isDarkMode ? 'bg-dark text-light' : ''} shadow-sm`}>
            <div className="card-body">
              <div className="row text-center">
                <div className="col-md-4">
                  <div className="display-4 fw-bold">{estadisticas.nivel || 1}</div>
                  <p className="text-muted">Nivel</p>
                </div>
                <div className="col-md-4">
                  <div className="display-4 fw-bold">{estadisticas.puntosTotales || 0}</div>
                  <p className="text-muted">Puntos</p>
                </div>
                <div className="col-md-4">
                  <div className="display-4 fw-bold">{estadisticas.cursosCompletados || 0}/{estadisticas.totalCursos || 0}</div>
                  <p className="text-muted">Cursos Completados</p>
                </div>
              </div>
              <div className="progress mt-3" style={{ height: '20px' }}>
                <div 
                  className="progress-bar bg-primary" 
                  role="progressbar" 
                  style={{ width: `${estadisticas.progresoNivel || 0}%` }}
                  aria-valuenow={estadisticas.progresoNivel || 0}
                  aria-valuemin="0"
                  aria-valuemax="100"
                >
                  {estadisticas.progresoNivel || 0}%
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Cursos */}
      <div className="row mb-4">
        <div className="col-12">
          <h2 className="mb-3">Mis Cursos</h2>
          {cursos.length > 0 ? (
            <div className="row">
              {cursos.map(curso => (
                <div key={curso.id} className="col-md-4 mb-3">
                  <div className={`card ${isDarkMode ? 'bg-dark text-light' : ''} h-100 shadow-sm`}>
                    <div className="card-body">
                      <h5 className="card-title">{curso.titulo}</h5>
                      <p className="card-text text-muted">{curso.descripcion}</p>
                      <div className="progress mb-2">
                        <div 
                          className="progress-bar bg-success" 
                          role="progressbar" 
                          style={{ width: `${curso.porcentajeCompletado || 0}%` }}
                          aria-valuenow={curso.porcentajeCompletado || 0}
                          aria-valuemin="0"
                          aria-valuemax="100"
                        >
                          {curso.porcentajeCompletado || 0}%
                        </div>
                      </div>
                      <div className="d-flex justify-content-between small text-muted">
                        <span>Materiales: {curso.materialesCompletados || 0}/{curso.materialesTotales || 0}</span>
                        <span>Tareas: {curso.tareasCompletadas || 0}/{curso.tareasTotales || 0}</span>
                        <span>Quizzes: {curso.quizzesCompletados || 0}/{curso.quizzesTotales || 0}</span>
                      </div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="alert alert-info">
              No estás inscrito en ningún curso todavía.
            </div>
          )}
        </div>
      </div>

      {/* Logros */}
      <div className="row">
        <div className="col-12">
          <h2 className="mb-3">Mis Logros</h2>
          {logros.length > 0 ? (
            <div className="row">
              {logros.map(logro => (
                <div key={logro.id} className="col-md-3 mb-3">
                  <div className={`card ${isDarkMode ? 'bg-dark text-light' : ''} h-100 shadow-sm`}>
                    <div className="card-body text-center">
                      <img 
                        src={logro.imagen?.url || './images/pfpgemacademy/default.webp'} 
                        alt={logro.titulo}
                        className="img-fluid mb-2"
                        style={{ width: '80px', height: '80px', objectFit: 'cover' }}
                      />
                      <h6 className="card-title">{logro.titulo}</h6>
                      <p className="card-text small text-muted">{logro.motivo}</p>
                      <span className="badge bg-primary">{logro.puntos || 0} puntos</span>
                      <p className="small text-muted mt-2">
                        Obtenido: {new Date(logro.fechaObtencion).toLocaleDateString()}
                      </p>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="alert alert-info">
              Aún no has obtenido ningún logro.
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default Dashboard; 