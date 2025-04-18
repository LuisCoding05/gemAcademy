import { Link, useParams, useNavigate } from 'react-router-dom';
import { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import { useTheme } from '../../context/ThemeContext';
import axios from '../../utils/axios';
import Icon from '../Icon';
import Loader from '../common/Loader';
import CreateMaterial from '../item/CreateMaterial';

const CourseDetail = () => {
    const { user } = useAuth();
    const { isDarkMode } = useTheme();
    const { id } = useParams();
    const [course, setCourse] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [newMessage, setNewMessage] = useState('');
    const [replyTo, setReplyTo] = useState(null);
    const [enrollError, setEnrollError] = useState(null);
    const [enrolling, setEnrolling] = useState(false);
    const [isSearching, setIsSearching] = useState(false);
    const [showCreateMaterial, setShowCreateMaterial] = useState(false);

    const handleDeleteMaterial = async (materialId) => {
        if (window.confirm('¿Estás seguro de que quieres eliminar este material?')) {
            try {
                await axios.delete(`/api/item/${id}/material/${materialId}/delete`);
                setCourse(prev => ({
                    ...prev,
                    materiales: prev.materiales.filter(material => material.id !== materialId)
                }));
            } catch (error) {
                console.error('Error al eliminar el material:', error);
                alert(error.response?.data?.message || 'Error al eliminar el material');
            }
        }
    };

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

    const handleEnroll = async () => {
        try {
            setEnrolling(true);
            setEnrollError(null);
            await axios.post(`/api/course/${id}/enroll`);
            // Recargar el curso para actualizar el estado
            const response = await axios.get(`/api/course/${id}`);
            setCourse(response.data);
        } catch (error) {
            console.error('Error al inscribirse al curso:', error);
            setEnrollError(error.response?.data?.message || 'Error al inscribirse al curso');
        } finally {
            setEnrolling(false);
        }
    };

    const handleSendMessage = async (parentId = null) => {
        try {
            setIsSearching(true);
            // Usamos el ID del primer foro del curso
            const foroId = course.foros[0].id;
            await axios.post(`/api/foro/${foroId}/mensaje`, {
                contenido: newMessage,
                mensajePadreId: parentId
            });

            // Recargar los datos del curso
            const response = await axios.get(`/api/course/${id}`);
            setCourse(response.data);
            setNewMessage('');
            setReplyTo(null);
        } catch (error) {
            console.error('Error al enviar mensaje:', error);
        } finally {
            setIsSearching(false);
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
                    <div className="card mb-4">
                        <img 
                            src={course.imagen || 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483544/default_bumnyb.webp'} 
                            className="card-img-top" 
                            alt={course.nombre}
                        />
                        <div className="card-body">
                            <h5 className="card-title">{course.nombre}</h5>
                            {user ? (
                                course.isEnrolled ? (
                                    <div className="alert alert-success">
                                        <Icon name="checkmark" size={20} className="me-2" />
                                        Inscrito como {course.userRole}
                                    </div>
                                ) : (
                                    <>
                                        <button 
                                            className="btn btn-primary w-100"
                                            onClick={handleEnroll}
                                            disabled={enrolling}
                                        >
                                            {enrolling ? (
                                                <>
                                                    <Loader size="small" className="me-2" />
                                                    Inscribiendo...
                                                </>
                                            ) : (
                                                <>
                                                    <Icon name="heart" color="red" size={20} className="me-2" />
                                                    Inscribirse al curso
                                                </>
                                            )}
                                        </button>
                                        {enrollError && (
                                            <div className="alert alert-danger mt-2">
                                                {enrollError}
                                            </div>
                                        )}
                                    </>
                                )
                            ) : (
                                <Link to={`/login`}>
                                    <button className="btn btn-primary w-100">
                                        <Icon name="user1" size={20} className="me-2" />
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
                                    <Icon name="book" color="green" size={24} className="me-2" />
                                    Materiales de estudio y repaso
                                </button>
                            </h2>
                            <div 
                                id="collapseMateriales" 
                                className="accordion-collapse collapse" 
                                aria-labelledby="headingMateriales" 
                                data-bs-parent="#courseAccordion"
                            >
                                <div className="accordion-body">
                                    {course.userRole === 'profesor' && (
                                        <button 
                                            className="btn btn-success mb-3"
                                            onClick={() => setShowCreateMaterial(true)}
                                        >
                                            <Icon name="plus" size={20} className="me-2" />
                                            Crear nuevo material
                                        </button>
                                    )}

                                    {showCreateMaterial && (
                                        <CreateMaterial 
                                            courseId={id}
                                            onCreated={(newMaterial) => {
                                                setCourse(prev => ({
                                                    ...prev,
                                                    materiales: [...prev.materiales, newMaterial]
                                                }));
                                                setShowCreateMaterial(false);
                                            }}
                                            onCancel={() => setShowCreateMaterial(false)}
                                        />
                                    )}

                                    {course.materiales && course.materiales.length > 0 ? (
                                        <ul className="list-group">
                                            {course.materiales.map((material, index) => (
                                                <li key={index} className="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <Icon name="folder" size={20} className="me-2" />
                                                        {material.titulo}
                                                        {material.fichero && (
                                                            <span className="ms-2 text-muted small">
                                                                <Icon name="file" size={16} className="me-1" />
                                                                {material.fichero.nombreOriginal}
                                                            </span>
                                                        )}
                                                    </div>
                                                    <div className="d-flex align-items-center gap-2">
                                                        <small className="text-muted me-3">
                                                            {new Date(material.fechaPublicacion).toLocaleDateString()}
                                                        </small>
                                                        {course.isEnrolled && (
                                                            <Link 
                                                                to={`/cursos/${id}/material/${material.id}`}
                                                                className="btn btn-link btn-sm p-0" 
                                                                title="Ver material"
                                                            >
                                                                <Icon name="eye" size={20} color="#0d6efd" />
                                                            </Link>
                                                        )}
                                                        {course.userRole === 'profesor' && (
                                                            <div className="ms-2">
                                                                <Link 
                                                                    to={`/cursos/${id}/material/${material.id}`}
                                                                    className="btn btn-link btn-sm text-warning p-0 me-2" 
                                                                    title="Editar material"
                                                                >
                                                                    <Icon name="pen" size={20} />
                                                                </Link>
                                                                <button 
                                                                    className="btn btn-link btn-sm text-danger p-0" 
                                                                    title="Eliminar material"
                                                                    onClick={() => handleDeleteMaterial(material.id)}
                                                                >
                                                                    <Icon name="trash-can" size={20} />
                                                                </button>
                                                            </div>
                                                        )}
                                                    </div>
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
                                    <Icon name="clipboard-edit" color="#FFC000" size={24} className="me-2" />
                                    Tareas y ejercicios
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
                                                            <Icon name="files" size={20} className="me-2" />
                                                            {tarea.titulo}
                                                        </div>
                                                        <div>
                                                            <small className="text-muted me-3">
                                                                Fecha límite: {new Date(tarea.fechaLimite).toLocaleDateString()}
                                                            </small>
                                                            {course.isEnrolled && (
                                                                <Link 
                                                                    to={`/cursos/${id}/tarea/${tarea.id}`}
                                                                    className="btn btn-link btn-sm p-0" 
                                                                    title="Ver tarea"
                                                                >
                                                                    <Icon name="eye" size={20} color="#0d6efd" />
                                                                </Link>
                                                            )}
                                                        </div>
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
                                    <Icon name="spaceinvaders" color="purple" size={24} className="me-2" />
                                    Quizzes y tipo test
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
                                                            <Icon name="gamepad" size={20} className="me-2" />
                                                            {quiz.titulo}
                                                        </div>
                                                        <div>
                                                            <small className="text-muted me-3">
                                                                Fecha límite: {new Date(quiz.fechaLimite).toLocaleDateString()}
                                                            </small>
                                                            {course.isEnrolled && (
                                                                <Link 
                                                                    to={`/cursos/${id}/quiz/${quiz.id}`}
                                                                    className="btn btn-link btn-sm p-0" 
                                                                    title="Ver quiz"
                                                                >
                                                                    <Icon name="eye" size={20} color="#0d6efd" />
                                                                </Link>
                                                            )}
                                                        </div>
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
                                    <Icon name="earth" color="#3498db" size={24} className="me-2" />
                                    Foro y dudas
                                </button>
                            </h2>
                            <div 
                                id="collapseForos" 
                                className="accordion-collapse collapse" 
                                aria-labelledby="headingForos" 
                                data-bs-parent="#courseAccordion"
                            >
                                <div className="accordion-body">
                                    <div className="foro-container" style={{ maxHeight: '400px', overflowY: 'auto' }}>
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
                                                                        src={mensaje.usuario.imagen || 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483544/default_bumnyb.webp'} 
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
                                                    </div>
                                                </div>
                                            ))
                                        ) : (
                                            <p className="text-muted">No hay foros disponibles</p>
                                        )}
                                    </div>

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
                                                    onClick={() => handleSendMessage(replyTo)}
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
                        </div>
                    </div>

                    <div className="mt-4 mb-4">
                        <h4>Instructor</h4>
                        <div className="d-flex align-items-center">
                            <img 
                                src={course.profesor?.imagen || 'https://res.cloudinary.com/dlgpvjulu/image/upload/v1744483544/default_bumnyb.webp'} 
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
