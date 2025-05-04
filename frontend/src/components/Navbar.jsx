import React from 'react'
import { useTheme } from '../context/ThemeContext';
import { Link } from 'react-router-dom'
import { useAuth } from '../context/AuthContext';
import NotificationIndicator from './notifications/NotificationIndicator';

export const Navbar = () => {
    const { isDarkMode, toggleDarkMode } = useTheme();
    const { user, logout } = useAuth();
    return (
        <nav className="navbar navbar-expand-lg navbar-dark bg-dark absolute-top">
            <div className="container">
                <Link className="navbar-brand" to="/">G.E.M Academy</Link>
                <button className="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span className="navbar-toggler-icon"></span>
                </button>

                <div className="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul className="navbar-nav me-auto mb-2 mb-lg-0">
                        
                        <li className="nav-item dropdown">
                            <a className="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Rutas
                            </a>
                            <ul className="dropdown-menu" aria-labelledby="navbarDropdown">
                                {/* Rutas para administradores */}
                                {user?.roles?.includes('ROLE_ADMIN') && (
                                    <>
                                        <li>
                                            <Link className="dropdown-item" to="/logs">Logs</Link>
                                        </li>
                                        <li><hr className="dropdown-divider"></hr></li>
                                    </>
                                )}

                                {/* Rutas globales */}
                                <li>
                                    <Link className="dropdown-item" to="/">Índice</Link>
                                </li>
                                <li><hr className="dropdown-divider"></hr></li>
                                <li><Link className="dropdown-item" to="/cursos">Cursos</Link></li>
                                <li><hr className="dropdown-divider"></hr></li>
                                {/* Rutas para usuarios autenticados */}
                                {user && (
                                    <li>
                                        <Link className="dropdown-item" to="/dashboard">Perfil</Link>
                                    </li>    
                                )}
                            </ul>
                        </li>
                        
                        <li className="nav-item dropdown">
                            <a className="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Guías
                            </a>
                            <ul className="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a className="dropdown-item" href="#">Guía para profesor</a></li>
                                <li><hr className="dropdown-divider"></hr></li>
                                <li><a className="dropdown-item" href="#">Guía para alumnos</a></li>
                            </ul>
                        </li>
                        <li className="nav-item dropdown">
                            <a className="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Autenticación
                            </a>
                            <ul className="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><Link className="dropdown-item" to="/login">Iniciar sesión</Link></li>
                                <li><hr className="dropdown-divider"></hr></li>
                                <li><Link className="dropdown-item" to="/register">Registrarse</Link></li>
                                <li><hr className="dropdown-divider"></hr></li>
                                <li><Link className="dropdown-item" to="/verify">Verificarse/recuperar contraseña</Link></li>
                            </ul>
                        </li>
                    </ul>
                    <div className="d-flex align-items-center">
                        {user && (
                            <div className="me-3">
                                <NotificationIndicator />
                            </div>
                        )}
                        <button 
                            className={`btn ${isDarkMode ? 'btn-outline-light me-3' : 'btn-outline-dark me-3'} rounded-circle`}
                            onClick={toggleDarkMode}
                            aria-pressed={isDarkMode}
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" style={{display: isDarkMode ? 'inline' : 'none'}} fill="white" className="bi bi-sun-fill" viewBox="0 0 16 16">
                                <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/>
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" style={{display: isDarkMode ? 'none' : 'inline'}} width="16" height="16" fill="white" className="bi bi-moon-stars" viewBox="0 0 16 16">
                                <path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278M4.858 1.311A7.27 7.27 0 0 0 1.025 7.71c0 4.02 3.279 7.276 7.319 7.276a7.32 7.32 0 0 0 5.205-2.162q-.506.063-1.029.063c-4.61 0-8.343-3.714-8.343-8.29 0-1.167.242-2.278.681-3.286"/>
                                <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.73 1.73 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.73 1.73 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.73 1.73 0 0 0 1.097-1.097zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.16 1.16 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.16 1.16 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732z"/>
                            </svg>
                        </button>
                        {user && (
                            <button className="btn btn-danger rounded-circle" onClick={logout}><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" className="bi bi-box-arrow-right" viewBox="0 0 16 16">
                            <path fillRule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z"/>
                            <path fillRule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/>
                          </svg></button>
                            
                        )}
                        {user && (<Link to={"/dashboard"}><img src={user.imagen.url} className="pfp-static-icon"></img></Link>)}
                    </div>
                </div>
            </div>
        </nav>
    )
}
