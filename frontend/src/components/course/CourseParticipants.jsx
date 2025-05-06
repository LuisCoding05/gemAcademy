import React, { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import { useTheme } from '../../context/ThemeContext';
import axios from '../../utils/axios';
import Loader from '../common/Loader';
import { Link } from 'react-router-dom';

const CourseParticipants = ({ courseId }) => {
  const { isDarkMode } = useTheme();
  const [participants, setParticipants] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchParticipants = async () => {
      try {
        const response = await axios.get(`/api/courses/${courseId}/participants`);
        setParticipants(response.data);
        setLoading(false);
      } catch (err) {
        console.error('Error:', err);
        setError('Error al cargar los participantes: ' + (err.response?.data?.message || err.message));
        setLoading(false);
      }
    };

    fetchParticipants();
  }, [courseId]);

  if (loading) {
    return <Loader size="medium" />;
  }

  if (error) {
    return (
      <div className="alert alert-danger" role="alert">
        {error}
      </div>
    );
  }

  if (!participants) {
    return (
      <div className="alert alert-warning" role="alert">
        No se pudieron cargar los participantes del curso
      </div>
    );
  }

  return (
    <div className={`card ${isDarkMode ? 'bg-dark text-light' : ''} shadow-sm mb-4`}>
      <div className="card-body">
        <h3 className="card-title mb-4">Participantes del Curso</h3>

        {/* Profesor */}
        <div className="mb-4">
          <h5 className="border-bottom pb-2 mb-3">Profesor</h5>
          <Link 
            to={`/profile/${participants.profesor.id}`}
            className="text-decoration-none"
          >
            <div className="d-flex align-items-center">
              <img
                src={participants.profesor.imagen?.url || 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483544/default_bumnyb.webp'}
                alt={`${participants.profesor.nombre} ${participants.profesor.apellido}`}
                className="rounded-circle me-3"
                style={{ width: '50px', height: '50px', objectFit: 'cover' }}
              />
              <div>
                <h6 className="mb-0">
                  {participants.profesor.nombre} {participants.profesor.apellido} 
                  {participants.profesor.apellido2 && ` ${participants.profesor.apellido2}`}
                </h6>
                <small className="text-muted">@{participants.profesor.username}</small>
              </div>
            </div>
          </Link>
        </div>

        {/* Estudiantes */}
        <div>
          <h5 className="border-bottom pb-2 mb-3">Estudiantes</h5>
          {participants.estudiantes.length > 0 ? (
            <div className="row g-3">
              {participants.estudiantes.map(estudiante => (
                <div key={estudiante.id} className="col-12">
                  <Link 
                    to={`/profile/${estudiante.id}`}
                    className="text-decoration-none"
                  >
                    <div className="d-flex align-items-center">
                      <img
                        src={estudiante.imagen?.url || 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483544/default_bumnyb.webp'}
                        alt={`${estudiante.nombre} ${estudiante.apellido}`}
                        className="rounded-circle me-3"
                        style={{ width: '50px', height: '50px', objectFit: 'cover' }}
                      />
                      <div>
                        <h6 className="mb-0">
                          {estudiante.nombre} {estudiante.apellido}
                          {estudiante.apellido2 && ` ${estudiante.apellido2}`}
                        </h6>
                        <small className="text-muted">@{estudiante.username}</small>
                      </div>
                    </div>
                  </Link>
                </div>
              ))}
            </div>
          ) : (
            <div className="alert alert-info">
              Este curso aún no tiene estudiantes inscritos.
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default CourseParticipants;