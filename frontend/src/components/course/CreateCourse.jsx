import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { useTheme } from '../../context/ThemeContext';
import { motion } from 'framer-motion';
import axios from '../../utils/axios';
import Icon from '../Icon';
import Loader from '../common/Loader';
import ImageGallery from '../dashboard/ImageGallery';

export const CreateCourse = () => {
  const navigate = useNavigate();
  const { user } = useAuth();
  const { isDarkMode } = useTheme();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [images, setImages] = useState([]);
  const [formData, setFormData] = useState({
    nombre: '',
    descripcion: '',
    imagen: ''
  });

  useEffect(() => {
    const fetchImages = async () => {
      try {
        const response = await axios.get('/api/createcourse/images');
        setImages(response.data.images);
      } catch (err) {
        console.error('Error al cargar las imágenes:', err);
      }
    };
    fetchImages();
  }, []);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleSelectImage = (url) => {
    setFormData(prev => ({
      ...prev,
      imagen: url
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      const response = await axios.post('/api/createcourse/create', formData);
      if (response.data) {
        navigate(`/cursos/${response.data.curso.id}`);
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Error al crear el curso');
    } finally {
      setLoading(false);
    }
  };

  if (!user) {
    return (
      <div className="alert alert-warning">
        Debes iniciar sesión para crear un curso
      </div>
    );
  }

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      className="container py-4"
    >
      <div className="row justify-content-center">
        <div className="col-lg-8">
          <div className={`card shadow-lg rounded-4 ${isDarkMode ? 'bg-dark text-light' : 'bg-light'}`}>
            <div className="card-header border-0 bg-gradient-primary text-white py-4 rounded-top-4">
              <h4 className="mb-0 fw-bold">
                Crear Nuevo Curso <Icon name="pencil" color="white" size={24} />
              </h4>
            </div>

            <div className="card-body p-4">
              {error && (
                <div className="alert alert-danger" role="alert">
                  {error}
                </div>
              )}

              <form onSubmit={handleSubmit}>
                <div className="mb-4">
                  <label htmlFor="nombre" className="form-label">Nombre del Curso</label>
                  <input
                    type="text"
                    className={`form-control form-control-lg ${isDarkMode ? 'bg-dark text-light' : ''}`}
                    id="nombre"
                    name="nombre"
                    value={formData.nombre}
                    onChange={handleChange}
                    required
                    placeholder="Ej: Introducción a la Programación"
                  />
                </div>

                <div className="mb-4">
                  <label htmlFor="descripcion" className="form-label">Descripción</label>
                  <textarea
                    className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                    id="descripcion"
                    name="descripcion"
                    value={formData.descripcion}
                    onChange={handleChange}
                    required
                    rows="4"
                    placeholder="Describe el contenido y objetivos del curso..."
                  />
                </div>

                <ImageGallery 
                  images={images}
                  onSelectImage={handleSelectImage}
                  selectedImageUrl={formData.imagen}
                />

                <div className="d-grid gap-2 mt-4">
                  <button
                    type="submit"
                    className="btn btn-primary btn-lg"
                    disabled={loading || !formData.imagen}
                  >
                    {loading ? (
                      <>
                        <Loader size="small" /> Creando curso...
                      </>
                    ) : (
                      <>
                        Crear Curso <Icon name="plus-circle" color="white" size={24} />
                      </>
                    )}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <style>{`
        .bg-gradient-primary {
          background: linear-gradient(45deg, #007bff, #00bcd4);
        }
        
        .form-control:focus {
          border-color: #007bff;
          box-shadow: 0 0 0 0.25rem rgba(0,123,255,.25);
        }
      `}</style>
    </motion.div>
  );
};