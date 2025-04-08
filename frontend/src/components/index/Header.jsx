import React from 'react';

export const Header = () => {
    return (
        <header className="main-banner">
            <div className="container">
                <div className="row">
                    <div className="col-lg-8 col-md-8">
                        <div className="main-banner-content">
                            <h1>Búsqueda de cursos</h1>
                            <p>
                                G.E.M.A(Gamificative Educational MyLion Academy) Somos una aplicación web destinada a la creación y unión a cursos creados por
                                la comunidad, promovemos la gamificación como método de aprendizaje, siéntete
                                libre de unirte a esta bonita comunidad
                            </p>
                        </div>
                    </div>
                </div>
                <div className="row d-sm-block">
                    <a className=" justify-content-center btn btn-success d-flex align-items-center gap-4 col-sm-3 col-md-3" type="button">
                        Buscar cursos
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" className="bi bi-search" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                        </svg>
                    </a>
                </div>
            </div>
        </header>
    );
};
