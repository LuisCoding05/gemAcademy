import { useParams } from 'react-router-dom';
import { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import { useTheme } from '../../context/ThemeContext';
import axios from '../../utils/axios';
import Icon from '../Icon';

const CourseDetail = () => {
    const { user } = useAuth();
    const { isDarkMode } = useTheme();
    const { id } = useParams();
    const [course, setCourse] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchCourse = async () => {
            try {
                setLoading(true);
                const response = await axios.get(`/api/course/${id}`);
                console.log('Datos del curso:', response.data);
                setCourse(response.data);
                setLoading(false);
            } catch (error) {
                console.error('Error al cargar el curso:', error);
                setError(error.message);
                setLoading(false);
            }
        };

        fetchCourse();
    }, [id]);

    const getImageUrl = (url) => {
        if (!url) return 'https://via.placeholder.com/400x250';
        if (url.startsWith('http')) return url;
        return url.replace('./images/', '/images/');
    };

    if (loading) {
        return (
            <div className="container mt-5">
                <div className="text-center">
                    <div className="spinner-border" role="status">
                        <span className="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="container mt-5">
                <div className="alert alert-danger" role="alert">
                    Error al cargar el curso: {error}
                </div>
            </div>
        );
    }

    if (!course) {
        return (
            <div className="container mt-5">
                <div className="alert alert-danger" role="alert">
                    No se pudo cargar la información del curso
                </div>
            </div>
        );
    }

    return (
        <div className="container mt-4">
            <div className="row">
                {/* Imagen del curso */}
                <div className="col-md-4">
                    <div className="card">
                        <img 
                            src={getImageUrl(course.imagen)} 
                            className="card-img-top" 
                            alt={course.nombre}
                        />
                        <div className="card-body">
                            <h5 className="card-title">{course.nombre}</h5>
                            <button className="btn btn-primary w-100">Inscribirse</button>
                        </div>
                    </div>
                </div>

                {/* Información del curso */}
                <div className="col-md-8">
                    <h1 className="mb-3">{course.nombre}</h1>
                    <div className="d-flex align-items-center mb-3">
                        <span className="me-3">
                            <i className="bi bi-people"></i> {course.estudiantes || "0"} estudiantes
                        </span>
                        <span className="text-muted">
                            <i className="bi bi-calendar"></i> Creado: {new Date(course.fechaCreacion).toLocaleDateString()}
                        </span>
                    </div>

                    <div className="mb-4">
                        <h4>Descripción</h4>
                        <p>{course.descripcion}</p>
                    </div>

                    {/* Materiales del curso */}
                    {course.materiales && course.materiales.length > 0 && (
                        <div className="mb-4">
                            <h4>Materiales del curso</h4>
                            <ul className="list-group">
                                {course.materiales.map((material, index) => (
                                    <li key={index} className="list-group-item">
                                        <i className="bi bi-file-earmark-text me-2"></i>
                                        <a href={material.url} target="_blank" rel="noopener noreferrer">
                                            {material.titulo}
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}

                    {/* Tareas del curso */}
                    {course.tareas && course.tareas.length > 0 && (
                        <div className="mb-4">
                            <h4>Tareas del curso</h4>
                            <ul className="list-group">
                                {course.tareas.map((tarea, index) => (
                                    <li key={index} className="list-group-item">
                                        <i className="bi bi-journal-text me-2"></i>
                                        {tarea.titulo}
                                        <small className="text-muted ms-2">
                                            (Fecha límite: {new Date(tarea.fechaLimite).toLocaleDateString()})
                                        </small>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}

                    {/* Quizzes del curso */}
                    {course.quizzes && course.quizzes.length > 0 && (
                        <div className="mb-4">
                            <h4>Quizzes del curso</h4>
                            <ul className="list-group">
                                {course.quizzes.map((quiz, index) => (
                                    <li key={index} className="list-group-item">
                                        <i className="bi bi-question-circle me-2"></i>
                                        {quiz.titulo}
                                        <small className="text-muted ms-2">
                                            (Fecha límite: {new Date(quiz.fechaLimite).toLocaleDateString()})
                                        </small>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}

                    <div className="mt-4 mb-4">
                        <h4>Instructor</h4>
                        <div className="d-flex align-items-center">
                            <img 
                                src={getImageUrl(course.profesor?.imagen)} 
                                className="rounded-circle me-3 icon-show" 
                                alt={course.profesor?.nombre || "Instructor"}
                            />
                            <div>
                                <h5 className="mb-0">{course.profesor?.nombre || "Instructor"}</h5>
                                <small className="text-muted">Creación: {new Date(course.fechaCreacion).toLocaleDateString()}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export { CourseDetail };
