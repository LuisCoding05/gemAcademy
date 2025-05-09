import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import { useTheme } from '../../context/ThemeContext';
import axios from '../../utils/axios';
import Icon from '../Icon';
import Loader from '../common/Loader';
import { Link } from 'react-router-dom';

const UserProfile = () => {
  const { id } = useParams();
  const { isDarkMode } = useTheme();
  const [profileData, setProfileData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchProfileData = async () => {
      try {
        const response = await axios.get(`/api/profile/${id}`);
        setProfileData(response.data);
        setLoading(false);
      } catch (err) {
        console.error('Error:', err);
        setError('Error al cargar el perfil: ' + (err.response?.data?.message || err.message));
        setLoading(false);
      }
    };

    fetchProfileData();
  }, [id]);

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

  if (!profileData) {
    return (
      <div className="alert alert-warning" role="alert">
        No se pudo cargar el perfil del usuario
      </div>
    );
  }

  const userData = profileData.user || {};
  const estadisticas = profileData.estadisticas || {};
  const cursos = profileData.cursos || [];
  const logros = profileData.logros || [];

  return (
    <div className="container py-4">
      <div className="row justify-content-center mb-4">
        <div className="col-lg-8">
          {/* Perfil */}
          <div className={`card ${isDarkMode ? 'bg-dark text-light' : ''} shadow-sm mb-4`}>
            <div className="card-body text-center">
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
              {userData.descripcion && (
                <div className="mt-3 text-start" 
                     dangerouslySetInnerHTML={{ __html: userData.descripcion }} />
              )}
            </div>
          </div>

          {/* Nivel */}
          {estadisticas.nivel && (
            <div className={`card ${isDarkMode ? 'bg-dark text-light' : ''} shadow-sm mb-4`}>
              <div className="card-body text-center">
                <div className="row">
                  <div className="col">
                    <div className="display-4 fw-bold">Nivel {estadisticas.nivel.numero || 1}</div>
                    <h5>{estadisticas.nivel.nombre || 'Novato'}</h5>
                    <p className="text-muted small">{estadisticas.nivel.descripcion || 'Comenzando su viaje'}</p>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* Cursos */}
          <div className="mb-4">
            <h2 className="mb-3">Cursos <Icon name="books" color="green" size={34} /></h2>
            
            {/* Cursos como Profesor */}
            {cursos.length > 0 ? (
              <div id="cursosCarousel" className="carousel slide bg-light bg-opacity-25 rounded p-3" data-bs-ride="carousel">
                <div className="carousel-inner">
                  {cursos.map((curso, index) => (
                    <div key={curso.id} className={`carousel-item ${index === 0 ? 'active' : ''}`}>
                      <div className="row justify-content-center">
                        <div className="col-md-8">
                          <div className={`card ${isDarkMode ? 'bg-dark text-light' : ''} h-100 shadow-sm`}>
                            <div className="card-body">
                              <h5 className="card-title">{curso.titulo}</h5>
                              <p className="card-text text-muted" dangerouslySetInnerHTML={{ __html: curso.descripcion }} ></p>
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
                <button className="carousel-control-prev" type="button" data-bs-target="#cursosCarousel" data-bs-slide="prev">
                  <span className="carousel-control-prev-icon" aria-hidden="true"></span>
                  <span className="visually-hidden">Anterior</span>
                </button>
                <button className="carousel-control-next" type="button" data-bs-target="#cursosCarousel" data-bs-slide="next">
                  <span className="carousel-control-next-icon" aria-hidden="true"></span>
                  <span className="visually-hidden">Siguiente</span>
                </button>
                <div className="carousel-indicators position-relative mt-3">
                  {cursos.map((_, index) => (
                    <button
                      key={index}
                      type="button"
                      data-bs-target="#cursosCarousel"
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
                Este usuario no tiene cursos publicados.
              </div>
            )}
          </div>

          {/* Logros */}
          <div>
            <h2 className="mb-3">Logros <Icon name="award" color="yellow" size={34} /></h2>
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
                Este usuario aún no ha obtenido ningún logro.
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default UserProfile;