import React, { useEffect, useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import { useTheme } from '../../context/ThemeContext';
import { motion } from 'framer-motion';
import axios from '../../utils/axios';
import Icon from '../Icon';
import { Link } from 'react-router-dom';

export const SearchCourse = () => {
  const { user } = useAuth();
  const { isDarkMode } = useTheme();
  const [searchTerm, setSearchTerm] = useState('');
  const [professorUsername, setProfessorUsername] = useState('');
  const [courses, setCourses] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [isSearching, setIsSearching] = useState(false);
  const [pagination, setPagination] = useState({
    total: 0,
    page: 1,
    limit: 4,
    pages: 0
  });

  async function fetchData() {
    try {
      setLoading(true);
      setIsSearching(true);
      
      // Construir URL con parámetros
      const params = new URLSearchParams({
        page: pagination.page,
        limit: pagination.limit,
        ...(searchTerm && { nombre: searchTerm }),
        ...(professorUsername && { username: professorUsername })
      });
      
      const response = await axios.get(`/api/course?${params.toString()}`);
      setCourses(response.data.cursos);
      setPagination(response.data.pagination);
      setError(null);
    } catch (error) {
      setError(error.message || 'Error al cargar los cursos');
    } finally {
      setLoading(false);
      setIsSearching(false);
    }
  }

  useEffect(() => {
    fetchData();
  }, [pagination.page, pagination.limit]);

  // Función para manejar cambios en los filtros
  const handleFilterChange = (e) => {
    const { name, value } = e.target;
    if (name === 'searchTerm') {
      setSearchTerm(value);
    } else if (name === 'professorUsername') {
      setProfessorUsername(value);
    }
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

  const getImageUrl = (url) => {
    if (!url) return 'https://via.placeholder.com/300x200';
    if (url.startsWith('http')) return url;
    return url.replace('./images/', '/images/');
  };

  if (loading && courses.length === 0) {
    return (
      <div className="text-center mt-5">
        <img src='./images/charging/charging.gif' className='align-center' alt="Cargando..."></img>
      </div>
    );
  }

  if (error && courses.length === 0) {
    return (
      <div className="alert alert-danger mt-5" role="alert">
        {error}
      </div>
    );
  }

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

  return (
    <motion.main 
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      className='container py-4'
    >
      <div className='row justify-content-center'>
        <div className='col-lg-11'>
          <div className={`card border-0 shadow-lg rounded-4 ${isDarkMode ? 'bg-dark text-light' : 'bg-light'}`}>
            {/* Header con diseño moderno */}
            <div className='card-header border-0 bg-gradient-primary text-white py-4 rounded-top-4'>
              <div className='d-flex justify-content-between align-items-center'>
                <h4 className='mb-0 fw-bold'>
                  Explora tu próxima aventura <Icon name="pacman1" color="gold" size={34} />
                </h4>
                {user && (
                  <motion.button 
                    whileHover={{ scale: 1.05 }}
                    whileTap={{ scale: 0.95 }}
                    className='btn btn-success btn-lg rounded-pill'
                  >
                    Crear curso <Icon name="cloud-add" color="white" size={34} />
                  </motion.button>
                )}
              </div>
            </div>

            <div className='card-body p-4'>
              {/* Buscador con diseño neomórfico */}
              <div className='search-container mb-4'>
                <form onSubmit={handleSearch} className='row g-3'>
                  <div className='col-md-8'>
                    <div className={`search-box ${isDarkMode ? 'dark' : ''}`}>
                      <input
                        type='text'
                        className='form-control form-control-lg border-0 shadow-none'
                        placeholder='Buscar por nombre del curso'
                        name="searchTerm"
                        value={searchTerm}
                        onChange={handleFilterChange}
                      />
                    </div>
                  </div>
                  <div className='col-md-4'>
                    <div className={`search-box ${isDarkMode ? 'dark' : ''}`}>
                      <input
                        type='text'
                        className='form-control form-control-lg border-0 shadow-none'
                        placeholder='Usuario del profesor'
                        name="professorUsername"
                        value={professorUsername}
                        onChange={handleFilterChange}
                      />
                    </div>
                  </div>
                  <div className='col-12 text-end'>
                    <button 
                      type="submit" 
                      className="btn btn-primary"
                      disabled={isSearching}
                    >
                      {isSearching ? (
                        <>
                          <span className="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                          Buscando...
                        </>
                      ) : (
                        'Buscar'
                      )}
                    </button>
                  </div>
                </form>
              </div>

              {/* Grid de cursos con animaciones */}
              {courses.length > 0 ? (
                <>
                  <div className="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                    {courses.map((course, index) => (
                      <div className="col" key={course.id}>
                        <motion.div 
                          className={`card course-card h-100 ${isDarkMode ? 'bg-dark text-light' : ''}`}
                          whileHover={{ y: -5 }}
                        >
                          <div className="position-relative">
                            <img 
                              src={getImageUrl(course.imagen)} 
                              className="card-img-top" 
                              alt={course.nombre}
                              style={{ height: '140px', objectFit: 'cover' }}
                            />
                            <div className="position-absolute top-0 end-0 p-2">
                              <button className="btn btn-sm btn-light rounded-circle">
                                <Icon color="green" size={24} name="lab"></Icon>
                              </button>
                            </div>
                          </div>
                          <div className="card-body p-3">
                            <h5 className="card-title fw-bold fs-6 mb-2 text-truncate">{course.nombre}</h5>
                            <p className="card-text small text-muted mb-2" style={{ 
                              display: '-webkit-box', 
                              WebkitLineClamp: 2, 
                              WebkitBoxOrient: 'vertical', 
                              overflow: 'hidden',
                              textOverflow: 'ellipsis',
                              minHeight: '2.4em'
                            }}>
                              {course.descripcion}
                            </p>

                            <div className="d-flex align-items-center mb-2">
                              <img 
                                src={getImageUrl(course.profesor?.imagen)} 
                                className="rounded-circle me-2" 
                                alt="Teacher" 
                                width="30" 
                                height="30"
                              />
                              <div>
                                <h6 className="mb-0 small fw-bold">{course.profesor?.nombre || 'Nombre del Profesor'}</h6>
                                <small className="text-muted">@{course.profesor?.username || 'usuario'}</small>
                              </div>
                            </div>

                            <div className="d-flex justify-content-end mt-2">
                              <button className="btn btn-sm btn-primary rounded-pill p-2">
                                <Link to={`/cursos/${course.id}`}><span className='text-white'>Ver Curso</span> <Icon color="white" size={24} name="eye"></Icon></Link>
                              </button>
                            </div>
                          </div>
                        </motion.div>
                      </div>
                    ))}
                  </div>
                  
                  {/* Paginación */}
                  <div className="mt-4">
                    {renderPagination()}
                  </div>
                </>
              ) : (
                <div className="alert alert-info">
                  No hay cursos disponibles con los filtros seleccionados.
                </div>
              )}
            </div>
          </div>
        </div>
      </div>

      <style>{`
        .search-box {
          background: ${isDarkMode ? '#2d3748' : '#f8f9fa'};
          border-radius: 1rem;
          padding: 0.5rem 1.5rem;
          display: flex;
          align-items: center;
          box-shadow: ${isDarkMode ? 'none' : '0 4px 6px -1px rgba(0,0,0,0.1)'};
        }
        
        .search-icon {
          color: #6c757d;
          font-size: 1.2rem;
          margin-right: 1rem;
        }

        .course-card {
          border: none;
          box-shadow: 0 5px 15px rgba(0,0,0,0.08);
          transition: all 0.3s ease;
          border-radius: 0.75rem;
          overflow: hidden;
        }

        .bg-gradient-primary {
          background: linear-gradient(45deg, #007bff, #00bcd4);
        }

        .card-img-top {
          border-top-left-radius: 0.75rem;
          border-top-right-radius: 0.75rem;
        }

        .course-card:hover {
          transform: translateY(-5px);
          box-shadow: 0 10px 20px rgba(0,0,0,0.12);
        }
      `}</style>
    </motion.main>
  );
};

const getFilterIcon = (filter) => {
  const icons = {
    popular: 'trophy',
    rated: 'star',
    recent: 'clock-history',
    spanish: 'translate',
    certified: 'patch-check'
  };
  return icons[filter];
};

