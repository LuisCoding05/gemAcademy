import { Link, useParams } from 'react-router-dom';
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
    const [newMessage, setNewMessage] = useState('');
    const [replyTo, setReplyTo] = useState(null);

    useEffect(() => {
        const fetchCourse = async () => {
            try {
                setLoading(true);
                const response = await axios.get(`/api/course/${id}`);
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

    const handleEnroll = async () => {
        try {
            await axios.post(`/api/course/${id}/enroll`);
            // Recargar el curso para actualizar el estado
            const response = await axios.get(`/api/course/${id}`);
            setCourse(response.data);
        } catch (error) {
            console.error('Error al inscribirse al curso:', error);
        }
    };

    const handleSendMessage = async (foroId, parentId = null) => {
        try {
            const response = await fetch(`${import.meta.env.VITE_API_URL}/api/foro/${foroId}/mensaje`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                },
                body: JSON.stringify({
                    contenido: newMessage,
                    mensajePadreId: parentId
                })
            });

            if (response.ok) {
                setNewMessage('');
                setReplyTo(null);
                // Recargar los datos del curso
                fetchCourseData();
            }
        } catch (error) {
            console.error('Error al enviar mensaje:', error);
        }
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
                            {user ? (
                                course.isEnrolled ? (
                                    <div className="alert alert-success">
                                        <Icon name="check-circle" size={20} className="me-2" />
                                        Inscrito como {course.userRole}
                                    </div>
                                ) : (
                                    <button 
                                        className="btn btn-primary w-100"
                                        onClick={handleEnroll}
                                    >
                                        <Icon name="user-plus" size={20} className="me-2" />
                                        Inscribirse al curso
                                    </button>
                                )
                            ) : (
                                <Link to={`/login`}>
                                    <button className="btn btn-primary w-100">
                                        <Icon name="login" size={20} className="me-2" />
                                        ¡Inicia sesión para inscribirte!
                                    </button>
                                </Link>
                            )}
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

                    {/* Acordeón de Materiales */}
                    <div className="accordion mb-4" id="courseAccordion">
                        <div className="accordion-item">
                            <h2 className="accordion-header" id="headingMateriales">
                                <button 
                                    className="accordion-button collapsed" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapseMateriales" 
                                    aria-expanded="false" 
                                    aria-controls="collapseMateriales"
                                >
                                    <Icon name="book" size={24} className="me-2" />
                                    Materiales del curso
                                </button>
                            </h2>
                            <div 
                                id="collapseMateriales" 
                                className="accordion-collapse collapse" 
                                aria-labelledby="headingMateriales" 
                                data-bs-parent="#courseAccordion"
                            >
                                <div className="accordion-body">
                                    {course.materiales && course.materiales.length > 0 ? (
                                        <ul className="list-group">
                                            {course.materiales.map((material, index) => (
                                                <li key={index} className="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <Icon name="file-text" size={20} className="me-2" />
                                                        <a href={material.url} target="_blank" rel="noopener noreferrer">
                                                            {material.titulo}
                                                        </a>
                                                    </div>
                                                    <small className="text-muted">
                                                        {new Date(material.fechaPublicacion).toLocaleDateString()}
                                                    </small>
                                                </li>
                                            ))}
                                        </ul>
                                    ) : (
                                        <p className="text-muted">No hay materiales disponibles</p>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Acordeón de Tareas */}
                        <div className="accordion-item">
                            <h2 className="accordion-header" id="headingTareas">
                                <button 
                                    className="accordion-button collapsed" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapseTareas" 
                                    aria-expanded="false" 
                                    aria-controls="collapseTareas"
                                >
                                    <Icon name="clipboard-edit" size={24} className="me-2" />
                                    Tareas del curso
                                </button>
                            </h2>
                            <div 
                                id="collapseTareas" 
                                className="accordion-collapse collapse" 
                                aria-labelledby="headingTareas" 
                                data-bs-parent="#courseAccordion"
                            >
                                <div className="accordion-body">
                                    {course.tareas && course.tareas.length > 0 ? (
                                        <ul className="list-group">
                                            {course.tareas.map((tarea, index) => (
                                                <li key={index} className="list-group-item">
                                                    <div className="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <Icon name="journal-text" size={20} className="me-2" />
                                                            {tarea.titulo}
                                                        </div>
                                                        <small className="text-muted">
                                                            Fecha límite: {new Date(tarea.fechaLimite).toLocaleDateString()}
                                                        </small>
                                                    </div>
                                                </li>
                                            ))}
                                        </ul>
                                    ) : (
                                        <p className="text-muted">No hay tareas disponibles</p>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Acordeón de Quizzes */}
                        <div className="accordion-item">
                            <h2 className="accordion-header" id="headingQuizzes">
                                <button 
                                    className="accordion-button collapsed" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapseQuizzes" 
                                    aria-expanded="false" 
                                    aria-controls="collapseQuizzes"
                                >
                                    <Icon name="spaceinvaders" size={24} className="me-2" />
                                    Quizzes del curso
                                </button>
                            </h2>
                            <div 
                                id="collapseQuizzes" 
                                className="accordion-collapse collapse" 
                                aria-labelledby="headingQuizzes" 
                                data-bs-parent="#courseAccordion"
                            >
                                <div className="accordion-body">
                                    {course.quizzes && course.quizzes.length > 0 ? (
                                        <ul className="list-group">
                                            {course.quizzes.map((quiz, index) => (
                                                <li key={index} className="list-group-item">
                                                    <div className="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <Icon name="quiz" size={20} className="me-2" />
                                                            {quiz.titulo}
                                                        </div>
                                                        <small className="text-muted">
                                                            Fecha límite: {new Date(quiz.fechaLimite).toLocaleDateString()}
                                                        </small>
                                                    </div>
                                                </li>
                                            ))}
                                        </ul>
                                    ) : (
                                        <p className="text-muted">No hay quizzes disponibles</p>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Acordeón de Foros */}
                        <div className="accordion-item">
                            <h2 className="accordion-header" id="headingForos">
                                <button 
                                    className="accordion-button collapsed" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapseForos" 
                                    aria-expanded="false" 
                                    aria-controls="collapseForos"
                                >
                                    <Icon name="earth" size={24} className="me-2" />
                                    Foros del curso
                                </button>
                            </h2>
                            <div 
                                id="collapseForos" 
                                className="accordion-collapse collapse" 
                                aria-labelledby="headingForos" 
                                data-bs-parent="#courseAccordion"
                            >
                                <div className="accordion-body">
                                    {course.foros && course.foros.length > 0 ? (
                                        course.foros.map((foro, index) => (
                                            <div key={index} className="card mb-4">
                                                <div className="card-header">
                                                    <h5 className="mb-0">{foro.titulo}</h5>
                                                    <p className="text-muted small mb-0">{foro.descripcion}</p>
                                                </div>
                                                
                                                {/* Lista de Mensajes */}
                                                <div className="card-body">
                                                    {foro.mensajes?.map((mensaje) => (
                                                        <div key={mensaje.id} className="border-start border-primary border-3 ps-3 mb-3">
                                                            <div className="d-flex gap-3">
                                                                <img 
                                                                    src={getImageUrl(mensaje.usuario.imagen)} 
                                                                    alt={mensaje.usuario.nombre}
                                                                    className="rounded-circle"
                                                                    width="40"
                                                                    height="40"
                                                                />
                                                                <div className="flex-grow-1">
                                                                    <div className="d-flex justify-content-between align-items-center">
                                                                        <h6 className="mb-0">{mensaje.usuario.nombre}</h6>
                                                                        <small className="text-muted">
                                                                            {new Date(mensaje.fechaPublicacion).toLocaleString()}
                                                                        </small>
                                                                    </div>
                                                                    <p className="mb-2">{mensaje.contenido}</p>
                                                                    {mensaje.mensajePadre && (
                                                                        <div className={`p-2 rounded mb-2 small ${isDarkMode ? 'bg-dark text-light border border-secondary' : 'bg-light'}`}>
                                                                            <strong>Respondiendo a {mensaje.mensajePadre.usuario.nombre}:</strong>
                                                                            <p className="mb-0">{mensaje.mensajePadre.contenido}</p>
                                                                        </div>
                                                                    )}
                                                                    {course.isEnrolled && (
                                                                        <button
                                                                            onClick={() => setReplyTo(mensaje.id)}
                                                                            className={`btn btn-link btn-sm p-0 ${isDarkMode ? 'text-info' : 'text-primary'}`}
                                                                        >
                                                                            <Icon name="forward" size={16} className="me-1" />
                                                                            Responder
                                                                        </button>
                                                                    )}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    ))}

                                                    {/* Formulario para nuevo mensaje */}
                                                    {course.isEnrolled && (
                                                        <div className="mt-3">
                                                            {replyTo && (
                                                                <div className={`alert ${isDarkMode ? 'alert-dark' : 'alert-info'} d-flex justify-content-between align-items-center`}>
                                                                    <span>Respondiendo a un mensaje...</span>
                                                                    <button
                                                                        onClick={() => setReplyTo(null)}
                                                                        className="btn-close"
                                                                        data-bs-theme={isDarkMode ? 'dark' : 'light'}
                                                                        aria-label="Cancelar respuesta"
                                                                    />
                                                                </div>
                                                            )}
                                                            <div className="d-flex gap-2">
                                                                <textarea
                                                                    value={newMessage}
                                                                    onChange={(e) => setNewMessage(e.target.value)}
                                                                    placeholder="Escribe tu mensaje..."
                                                                    className={`form-control ${isDarkMode ? 'bg-dark text-light' : ''}`}
                                                                    rows="3"
                                                                />
                                                                <button
                                                                    onClick={() => handleSendMessage(foro.id, replyTo)}
                                                                    className="btn btn-primary align-self-start"
                                                                    disabled={!newMessage.trim()}
                                                                >
                                                                    <Icon name="email" size={20} className="me-2" />
                                                                    Enviar
                                                                </button>
                                                            </div>
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                        ))
                                    ) : (
                                        <p className="text-muted">No hay foros disponibles</p>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="mt-4 mb-4">
                        <h4>Instructor</h4>
                        <div className="d-flex align-items-center">
                            <img 
                                src={getImageUrl(course.profesor?.imagen)} 
                                className="rounded-circle me-3" 
                                alt={course.profesor?.nombre || "Instructor"}
                                width="50"
                                height="50"
                            />
                            <div>
                                <h5 className="mb-0">{course.profesor?.nombre || "Instructor"}</h5>
                                <small className="text-muted">@{course.profesor?.username || 'usuario'}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export { CourseDetail };
