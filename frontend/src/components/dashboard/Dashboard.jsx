import React, { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import { useTheme } from '../../context/ThemeContext';
import axios from '../../utils/axios';
import ImageGallery from './ImageGallery';
import Icon from '../Icon';
import Loader from '../common/Loader';
import Editor from '../common/Editor';
import { Link } from 'react-router-dom';

const Dashboard = () => {
  const { user, login } = useAuth();
  const { isDarkMode } = useTheme();
  const [dashboardData, setDashboardData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [editingProfile, setEditingProfile] = useState(false);
  const [newUsername, setNewUsername] = useState('');
  const [newNombre, setNewNombre] = useState('');
  const [newApellido1, setNewApellido1] = useState('');
  const [newApellido2, setNewApellido2] = useState('');
  const [newDescripcion, setNewDescripcion] = useState('');
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
        setNewApellido1(response.data.user.apellido || '');
        setNewApellido2(response.data.user.apellido2 || '');
        setNewDescripcion(response.data.user.descripcion || '');
        setSelectedImageUrl(response.data.user.imagen?.url || 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483544/default_bumnyb.webp');
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
    setNewApellido1(dashboardData.user.apellido || '');
    setNewApellido2(dashboardData.user.apellido2 || '');
    setNewDescripcion(dashboardData.user.descripcion || '');
    setSelectedImageUrl(dashboardData.user.imagen?.url || 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483544/default_bumnyb.webp');
    setUpdateError('');
    setUpdateSuccess('');
  };

  const handleUpdateProfile = async () => {
    try {
      setUpdateError('');
      setUpdateSuccess('');
      
      // Validar campos requeridos
      if (!newNombre || !newApellido1 || !newUsername) {
        setUpdateError('El nombre, primer apellido y nombre de usuario son obligatorios');
        return;
      }

      // Preparar los datos para enviar
      const formData = {
        username: newUsername,
        nombre: newNombre,
        apellido: newApellido1,
        apellido2: newApellido2,
        descripcion: newDescripcion,
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
      <div className="container mt-5">
        <Loader size="large" />
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
                    <h4 className="mb-3"> Editar Perfil <Icon name="pencil" color="#1337ea" size={20} /></h4>
                  </div>
                  <div className="row justify-content-center">
                    <div className="col-md-8 mb-4">
                      <div className="mb-3">
                        <label className="form-label">Nombre</label>
                        <input 
                          type="text" 
                          className="form-control" 
                          value={newNombre} 
                          onChange={(e) => setNewNombre(e.target.value)}
                          placeholder="Nombre"
                          required
                        />
                      </div>

                      <div className="mb-3">
                        <label className="form-label">Primer Apellido</label>
                        <input 
                          type="text" 
                          className="form-control" 
                          value={newApellido1} 
                          onChange={(e) => setNewApellido1(e.target.value)}
                          placeholder="Primer Apellido"
                          required
                        />
                      </div>

                      <div className="mb-3">
                        <label className="form-label">Segundo Apellido (opcional)</label>
                        <input 
                          type="text" 
                          className="form-control" 
                          value={newApellido2} 
                          onChange={(e) => setNewApellido2(e.target.value)}
                          placeholder="Segundo Apellido"
                        />
                      </div>

                      <div className="mb-3">
                        <label className="form-label">Nombre de usuario</label>
                        <div className="input-group">
                          <span className="input-group-text">@</span>
                          <input 
                            type="text" 
                            className="form-control" 
                            value={newUsername} 
                            onChange={(e) => setNewUsername(e.target.value)}
                            placeholder="Nombre de usuario"
                            required
                          />
                        </div>
                      </div>

                      <div className="mb-3">
                        <label className="form-label">Descripción</label>
                        <Editor
                          data={newDescripcion}
                          onChange={setNewDescripcion}
                          placeholder="Cuéntanos sobre ti..."
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
                    src={userData.imagen?.url || 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483544/default_bumnyb.webp'} 
                    alt="Foto de perfil" 
                    className="rounded-circle mb-3 shadow"
                    style={{ 
                      width: '180px', 
                      height: '180px', 
                      objectFit: 'cover',
                      border: '3px solid #0d6efd'
                    }}
                  />
                  <h3 className="card-title">
                    {userData.nombre} {userData.apellido} {userData.apellido2 && `${userData.apellido2}`}
                  </h3>
                  <p className="text-muted mb-1">@{userData.username}</p>
                  <p className="text-muted">{userData.email}</p>
                  {userData.descripcion && (
                    <div className="mt-3 text-start" 
                         dangerouslySetInnerHTML={{ __html: userData.descripcion }} />
                  )}
                  <button 
                    className="btn btn-outline-primary btn-sm mt-3" 
                    onClick={handleEditProfile}
                  >
                    Editar perfil <Icon name="pencil" color="#1337ea" size={16} />
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
                  <div className="display-4 fw-bold">{estadisticas.nivel?.numero || 1}</div>
                  <h5>{estadisticas.nivel?.nombre || 'Novato'}</h5>
                  <p className="text-muted small">{estadisticas.nivel?.descripcion || 'Acabas de empezar'}</p>
                </div>
                <div className="col-md-4">
                  <div className="display-4 fw-bold">{estadisticas.puntosTotales || 0}</div>
                  <p className="text-muted">Puntos Totales</p>
                  <p className="small text-info">
                    {estadisticas.puntosSiguienteNivel ? 
                      `${estadisticas.puntosSiguienteNivel - estadisticas.puntosTotales} puntos para el siguiente nivel` :
                      'Nivel máximo alcanzado'}
                  </p>
                </div>
                <div className="col-md-4">
                  <div className="display-4 fw-bold">
                    {estadisticas.cursosCompletados || 0}/{estadisticas.totalCursos || 0}
                  </div>
                  <p className="text-muted">Cursos Completados</p>
                  <p className="small text-success">
                    {estadisticas.porcentajeCompletado?.toFixed(1) || 0}% completado
                  </p>
                </div>
              </div>
              <div className="progress mt-3" style={{ height: '20px' }}>
                <div 
                  className="progress-bar bg-primary progress-bar-striped progress-bar-animated" 
                  role="progressbar" 
                  style={{ width: `${estadisticas.progresoNivel || 0}%` }}
                  aria-valuenow={estadisticas.progresoNivel || 0}
                  aria-valuemin="0"
                  aria-valuemax="100"
                >
                  {`${Math.round(estadisticas.progresoNivel || 0)}%`}
                </div>
              </div>
              {estadisticas.puntosSiguienteNivel && (
                <p className="text-center small text-muted mt-2">
                  Progreso hacia el nivel {(estadisticas.nivel?.numero || 1) + 1}
                </p>
              )}
            </div>
          </div>

          {/* Cursos */}
          <div className="mb-4">
            <h2 className="mb-3">Mis Cursos <Icon name="books" color="green" size={34} /></h2>
            
            {/* Cursos como Profesor */}
            {cursos.filter(curso => curso.userRole === 'profesor').length > 0 && (
              <div className="mb-4">
                <h4 className="mb-3">Cursos como Profesor <Icon name="teacher" color="blue" size={24} /></h4>
                <div id="cursosProfesorCarousel" className="carousel slide bg-light bg-opacity-25 rounded p-3" data-bs-ride="carousel">
                  <div className="carousel-inner">
                    {cursos.filter(curso => curso.userRole === 'profesor').map((curso, index) => (
                      <div key={curso.id} className={`carousel-item ${index === 0 ? 'active' : ''}`}>
                        <div className="row justify-content-center">
                          <div className="col-md-8">
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
                                <div className="d-flex justify-content-between small text-muted mb-3">
                                  <span>Materiales: {curso.materialesCompletados || 0}/{curso.materialesTotales || 0}</span>
                                  <span>Tareas: {curso.tareasCompletadas || 0}/{curso.tareasTotales || 0}</span>
                                  <span>Quizzes: {curso.quizzesCompletados || 0}/{curso.quizzesTotales || 0}</span>
                                </div>
                                <Link to={`/cursos/${curso.id}`} className="btn btn-primary w-100">
                                  <Icon name="eye" size={20} className="me-2" />
                                  Ver Curso
                                </Link>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                  <button className="carousel-control-prev" type="button" data-bs-target="#cursosProfesorCarousel" data-bs-slide="prev">
                    <span className="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span className="visually-hidden">Anterior</span>
                  </button>
                  <button className="carousel-control-next" type="button" data-bs-target="#cursosProfesorCarousel" data-bs-slide="next">
                    <span className="carousel-control-next-icon" aria-hidden="true"></span>
                    <span className="visually-hidden">Siguiente</span>
                  </button>
                  <div className="carousel-indicators position-relative mt-3">
                    {cursos.filter(curso => curso.userRole === 'profesor').map((_, index) => (
                      <button
                        key={index}
                        type="button"
                        data-bs-target="#cursosProfesorCarousel"
                        data-bs-slide-to={index}
                        className={index === 0 ? 'active' : ''}
                        aria-current={index === 0 ? 'true' : 'false'}
                        aria-label={`Slide ${index + 1}`}
                      ></button>
                    ))}
                  </div>
                </div>
              </div>
            )}
            
            {/* Cursos como Alumno */}
            {cursos.filter(curso => curso.userRole === 'estudiante').length > 0 && (
              <div className="mb-4">
                <h4 className="mb-3">Cursos como Alumno <Icon name="student" color="purple" size={24} /></h4>
                <div id="cursosAlumnoCarousel" className="carousel slide bg-light bg-opacity-25 rounded p-3" data-bs-ride="carousel">
                  <div className="carousel-inner">
                    {cursos.filter(curso => curso.userRole === 'estudiante').map((curso, index) => (
                      <div key={curso.id} className={`carousel-item ${index === 0 ? 'active' : ''}`}>
                        <div className="row justify-content-center">
                          <div className="col-md-8">
                            <div className={`card ${isDarkMode ? 'bg-dark text-light' : ''} h-100 shadow-sm`}>
                              <div className="card-body">
                                <h5 className="card-title">{curso.titulo}</h5>
                                <p className="card-text text-muted">{curso.descripcion}</p>
                                
                                {/* Promedio del curso */}
                                <div className="mb-3">
                                  <p className="mb-1 fw-bold">Promedio del curso</p>
                                  <div className="progress" style={{ height: '25px' }}>
                                    <div 
                                      className={`progress-bar progress-bar-striped progress-bar-animated ${
                                        parseFloat(curso.promedio || 0) >= 7 ? 'bg-success' :
                                        parseFloat(curso.promedio || 0) >= 5 ? 'bg-warning' : 'bg-danger'
                                      }`}
                                      role="progressbar" 
                                      style={{ width: `${(parseFloat(curso.promedio || 0) * 10)}%` }}
                                      aria-valuenow={parseFloat(curso.promedio || 0)}
                                      aria-valuemin="0"
                                      aria-valuemax="10"
                                    >
                                      {curso.promedio ? `${curso.promedio}/10` : 'Sin calificaciones'}
                                    </div>
                                  </div>
                                </div>

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
                                <div className="d-flex justify-content-between small text-muted mb-3">
                                  <span>Materiales: {curso.materialesCompletados || 0}/{curso.materialesTotales || 0}</span>
                                  <span>Tareas: {curso.tareasCompletadas || 0}/{curso.tareasTotales || 0}</span>
                                  <span>Quizzes: {curso.quizzesCompletados || 0}/{curso.quizzesTotales || 0}</span>
                                </div>
                                <Link to={`/cursos/${curso.id}`} className="btn btn-primary w-100">
                                  <Icon name="eye" size={20} className="me-2" />
                                  Ver Curso
                                </Link>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                  <button className="carousel-control-prev" type="button" data-bs-target="#cursosAlumnoCarousel" data-bs-slide="prev">
                    <span className="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span className="visually-hidden">Anterior</span>
                  </button>
                  <button className="carousel-control-next" type="button" data-bs-target="#cursosAlumnoCarousel" data-bs-slide="next">
                    <span className="carousel-control-next-icon" aria-hidden="true"></span>
                    <span className="visually-hidden">Siguiente</span>
                  </button>
                  <div className="carousel-indicators position-relative mt-3">
                    {cursos.filter(curso => curso.userRole === 'estudiante').map((_, index) => (
                      <button
                        key={index}
                        type="button"
                        data-bs-target="#cursosAlumnoCarousel"
                        data-bs-slide-to={index}
                        className={index === 0 ? 'active' : ''}
                        aria-current={index === 0 ? 'true' : 'false'}
                        aria-label={`Slide ${index + 1}`}
                      ></button>
                    ))}
                  </div>
                </div>
              </div>
            )}
            
            {cursos.length === 0 && (
              <div className="alert alert-info">
                No estás inscrito en ningún curso todavía.
              </div>
            )}
          </div>

          {/* Logros */}
          <div>
            <h2 className="mb-3">Mis Logros <Icon name="award" color="yellow" size={34} /></h2>
            {logros.length > 0 ? (
              <div id="logrosCarousel" className="carousel slide bg-light bg-opacity-25 rounded p-3" data-bs-ride="carousel">
                <div className="carousel-inner">
                  {logros.map((logro, index) => (
                    <div key={logro.id} className={`carousel-item ${index === 0 ? 'active' : ''}`}>
                      <div className="row justify-content-center">
                        <div className="col-md-6">
                          <div className={`card ${isDarkMode ? 'bg-dark text-light' : ''} h-100 shadow-sm`}>
                            <div className="card-body text-center">
                              <img 
                                src={logro.imagen?.url || 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483544/default_bumnyb.webp'} 
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
                      </div>
                    </div>
                  ))}
                </div>
                <button className="carousel-control-prev" type="button" data-bs-target="#logrosCarousel" data-bs-slide="prev">
                  <span className="carousel-control-prev-icon" aria-hidden="true"></span>
                  <span className="visually-hidden">Anterior</span>
                </button>
                <button className="carousel-control-next" type="button" data-bs-target="#logrosCarousel" data-bs-slide="next">
                  <span className="carousel-control-next-icon" aria-hidden="true"></span>
                  <span className="visually-hidden">Siguiente</span>
                </button>
                <div className="carousel-indicators position-relative mt-3">
                  {logros.map((_, index) => (
                    <button
                      key={index}
                      type="button"
                      data-bs-target="#logrosCarousel"
                      data-bs-slide-to={index}
                      className={index === 0 ? 'active' : ''}
                      aria-current={index === 0 ? 'true' : 'false'}
                      aria-label={`Slide ${index + 1}`}
                    ></button>
                  ))}
                </div>
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