import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { useTheme } from '../../context/ThemeContext';
import { motion } from 'framer-motion';
import axios from '../../utils/axios';
import Icon from '../Icon';
import Loader from '../common/Loader';
import ImageGallery from '../dashboard/ImageGallery';
import Editor from '../common/Editor';

export const EditCourse = () => {
  const navigate = useNavigate();
  const { id } = useParams();
  const { user } = useAuth();
  const { isDarkMode } = useTheme();
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [images, setImages] = useState([]);
  const [formData, setFormData] = useState({
    nombre: '',
    descripcion: '',
    imagen: ''
  });

  useEffect(() => {
    const fetchCourse = async () => {
      try {
        const [courseResponse, imagesResponse] = await Promise.all([
          axios.get(`/api/course/${id}`),
          axios.get('/api/createcourse/images')
        ]);

        const course = courseResponse.data;
        
        // Verificar si el usuario es el profesor del curso
        if (course.userRole !== 'profesor') {
          navigate(`/cursos/${id}`);
          return;
        }

        setFormData({
          nombre: course.nombre,
          descripcion: course.descripcion,
          imagen: course.imagen
        });
        setImages(imagesResponse.data.images);
        setLoading(false);
      } catch (err) {
        console.error('Error al cargar el curso:', err);
        setError(err.response?.data?.message || 'Error al cargar el curso');
        setLoading(false);
      }
    };

    fetchCourse();
  }, [id, navigate]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleDescriptionChange = (content) => {
    setFormData(prev => ({
      ...prev,
      descripcion: content
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
    setSaving(true);
    setError('');

    try {
      await axios.put(`/api/course/${id}/update`, formData);
      navigate(`/cursos/${id}`);
    } catch (err) {
      setError(err.response?.data?.message || 'Error al actualizar el curso');
      setSaving(false);
    }
  };

  if (!user) {
    return (
      <div className="alert alert-warning">
        Debes iniciar sesión para editar un curso
      </div>
    );
  }

  if (loading) {
    return (
      <div className="container mt-5">
        <Loader size="large" />
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
            <div className="card-header border-0 bg-gradient-warning text-white py-4 rounded-top-4">
              <h4 className="mb-0 fw-bold">
                Editar Curso <Icon name="pen" color="white" size={24} />
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
                  <Editor
                    data={formData.descripcion}
                    onChange={handleDescriptionChange}
                    placeholder="Describe el contenido y objetivos del curso..."
                  />
                </div>

                <ImageGallery 
                  images={images}
                  onSelectImage={handleSelectImage}
                  selectedImageUrl={formData.imagen}
                />

                <div className="d-flex gap-2 mt-4">
                  <button
                    type="button"
                    className="btn btn-secondary btn-lg"
                    onClick={() => navigate(`/cursos/${id}`)}
                  >
                    <Icon name="x" size={24} /> Cancelar
                  </button>
                  <button
                    type="submit"
                    className="btn btn-warning btn-lg flex-grow-1"
                    disabled={saving || !formData.imagen}
                  >
                    {saving ? (
                      <>
                        <Loader size="small" /> Guardando cambios...
                      </>
                    ) : (
                      <>
                        Guardar Cambios <Icon name="floppy-disk" color="white" size={24} />
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
        .bg-gradient-warning {
          background: linear-gradient(45deg, #ffc107, #ff9800);
        }
        
        .form-control:focus {
          border-color: #ffc107;
          box-shadow: 0 0 0 0.25rem rgba(255,193,7,.25);
        }
      `}</style>
    </motion.div>
  );
};