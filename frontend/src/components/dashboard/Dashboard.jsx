import React, { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import { useTheme } from '../../context/ThemeContext';
import axios from '../../utils/axios';
import ImageGallery from './ImageGallery';

const Dashboard = () => {
  const { user, login } = useAuth();
  const { isDarkMode } = useTheme();
  const [dashboardData, setDashboardData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [editingProfile, setEditingProfile] = useState(false);
  const [newUsername, setNewUsername] = useState('');
  const [newNombre, setNewNombre] = useState('');
  const [newApellido, setNewApellido] = useState('');
  const [selectedImageUrl, setSelectedImageUrl] = useState('');
  const [updateError, setUpdateError] = useState('');
  const [updateSuccess, setUpdateSuccess] = useState('');
  const [cleanupStatus, setCleanupStatus] = useState('');

  useEffect(() => {
    const fetchDashboardData = async () => {
      try {
        const response = await axios.get('/api/dashboard');
        setDashboardData(response.data);
        setNewUsername(response.data.user.username || '');
        setNewNombre(response.data.user.nombre || '');
        setNewApellido(response.data.user.apellido || '');
        setSelectedImageUrl(response.data.user.imagen?.url || './images/pfpgemacademy/default.webp');
        setLoading(false);
      } catch (err) {
        console.error('Error completo:', err);
        setError('Error al cargar el dashboard: ' + (err.response?.data?.message || err.message));
        setLoading(false);
      }
    };

    fetchDashboardData();
  }, []);

  const handleEditProfile = () => {
    setEditingProfile(true);
  };

  const handleCancelEdit = () => {
    setEditingProfile(false);
    setNewUsername(dashboardData.user.username || '');
    setNewNombre(dashboardData.user.nombre || '');
    setNewApellido(dashboardData.user.apellido || '');
    setSelectedImageUrl(dashboardData.user.imagen?.url || './images/pfpgemacademy/default.webp');
    setUpdateError('');
    setUpdateSuccess('');
  };

  const handleUpdateProfile = async () => {
    try {
      setUpdateError('');
      setUpdateSuccess('');
      
      // Preparar los datos para enviar
      const formData = {
        username: newUsername,
        nombre: newNombre,
        apellido: newApellido,
        imagen: {
          url: selectedImageUrl
        }
      };
      
      // Enviar la actualización
      const response = await axios.put('/api/dashboard/profile', formData);
      
      // Actualizar los datos del dashboard y el contexto de autenticación
      setDashboardData(prevData => ({
        ...prevData,
        user: response.data.user
      }));
      
      // Actualizar el contexto de autenticación
      login(response.data.user, localStorage.getItem('token'));
      
      setUpdateSuccess('Perfil actualizado correctamente');
      setEditingProfile(false);
    } catch (err) {
      console.error('Error al actualizar el perfil:', err);
      setUpdateError(err.response?.data?.error || 'Error al actualizar el perfil');
    }
  };

  const handleCleanupImages = async () => {
    try {
      setCleanupStatus('Limpiando imágenes duplicadas...');
      const response = await axios.post('/api/dashboard/cleanup-images');
      setCleanupStatus(`¡Listo! ${response.data.message}`);
      
      // Recargar los datos del dashboard para actualizar la lista de imágenes
      const dashboardResponse = await axios.get('/api/dashboard');
      setDashboardData(dashboardResponse.data);
    } catch (err) {
      setCleanupStatus('Error: ' + (err.response?.data?.message || 'No se pudieron limpiar las imágenes'));
    }
  };

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
      <div className="row justify-content-center mb-4">
        <div className="col-lg-8">
          {user.roles.includes('ROLE_ADMIN') && (
            <div className={`card ${isDarkMode ? 'bg-dark text-light' : ''} shadow-sm mb-4`}>
              <div className="card-body">
                <h5 className="card-title">Herramientas de Administrador</h5>
                <button 
                  className="btn btn-warning"
                  onClick={handleCleanupImages}
                >
                  Limpiar imágenes duplicadas
                </button>
                {cleanupStatus && (
                  <div className="alert alert-info mt-3">
                    {cleanupStatus}
                  </div>
                )}
              </div>
            </div>
          )}

          <div className={`card ${isDarkMode ? 'bg-dark text-light' : ''} shadow-sm mb-4`}>
            <div className="card-body text-center">
              {editingProfile ? (
                <div className="mb-3">
                  <div className="text-center mb-4">
                    <img 
                      src={selectedImageUrl} 
                      alt="Foto de perfil" 
                      className="rounded-circle mb-3 shadow"
                      style={{ 
                        width: '180px', 
                        height: '180px', 
                        objectFit: 'cover',
                        border: '3px solid #0d6efd'
                      }}
                    />
                    <h4 className="mb-3">Editar Perfil</h4>
                  </div>
                  <div className="row justify-content-center">
                    <div className="col-md-6 mb-4">
                      <label className="form-label">Nombre</label>
                      <input 
                        type="text" 
                        className="form-control mb-3" 
                        value={newNombre} 
                        onChange={(e) => setNewNombre(e.target.value)}
                        placeholder="Nombre"
                      />
                      <label className="form-label">Apellidos</label>
                      <input 
                        type="text" 
                        className="form-control mb-3" 
                        value={newApellido} 
                        onChange={(e) => setNewApellido(e.target.value)}
                        placeholder="Apellidos"
                      />
                      <label className="form-label">Nombre de usuario</label>
                      <div className="input-group">
                        <span className="input-group-text">@</span>
                        <input 
                          type="text" 
                          className="form-control" 
                          value={newUsername} 
                          onChange={(e) => setNewUsername(e.target.value)}
                          placeholder="Nombre de usuario"
                        />
                      </div>
                    </div>
                  </div>
                  <ImageGallery 
                    images={dashboardData.imagenesDisponibles || []}
                    onSelectImage={setSelectedImageUrl} 
                    selectedImageUrl={selectedImageUrl} 
                  />
                  {updateError && (
                    <div className="alert alert-danger py-2" role="alert">
                      {updateError}
                    </div>
                  )}
                  {updateSuccess && (
                    <div className="alert alert-success py-2" role="alert">
                      {updateSuccess}
                    </div>
                  )}
                  <div className="d-flex justify-content-center gap-3 mt-4">
                    <button 
                      className="btn btn-primary px-4" 
                      onClick={handleUpdateProfile}
                    >
                      Guardar
                    </button>
                    <button 
                      className="btn btn-outline-secondary px-4" 
                      onClick={handleCancelEdit}
                    >
                      Cancelar
                    </button>
                  </div>
                </div>
              ) : (
                <>
                  <img 
                    src={userData.imagen?.url || './images/pfpgemacademy/default.webp'} 
                    alt="Foto de perfil" 
                    className="rounded-circle mb-3 shadow"
                    style={{ 
                      width: '180px', 
                      height: '180px', 
                      objectFit: 'cover',
                      border: '3px solid #0d6efd'
                    }}
                  />
                  <h3 className="card-title">{userData.nombre} {userData.apellido}</h3>
                  <p className="text-muted mb-1">@{userData.username}</p>
                  <p className="text-muted">{userData.email}</p>
                  <button 
                    className="btn btn-outline-primary btn-sm mt-3" 
                    onClick={handleEditProfile}
                  >
                    Editar perfil
                  </button>
                </>
              )}
            </div>
          </div>

          {/* Estadísticas */}
          <div className={`card ${isDarkMode ? 'bg-dark text-light' : ''} shadow-sm mb-4`}>
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

          {/* Cursos */}
          <div className="mb-4">
            <h2 className="mb-3">Mis Cursos</h2>
            {cursos.length > 0 ? (
              <div className="row">
                {cursos.map(curso => (
                  <div key={curso.id} className="col-md-6 mb-3">
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

          {/* Logros */}
          <div>
            <h2 className="mb-3">Mis Logros</h2>
            {logros.length > 0 ? (
              <div className="row">
                {logros.map(logro => (
                  <div key={logro.id} className="col-md-4 mb-3">
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
    </div>
  );
};

export default Dashboard; 