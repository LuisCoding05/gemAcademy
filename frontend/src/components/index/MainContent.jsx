import React from 'react'

export const MainContent = () => {
  return (
            <main className="col-lg-9">
                <h1 className="zoomIn text-center pb-3 bounce-in">Bienvenido a la plataforma</h1>

                <section className="container features ">
                    <article className="row">
                        <div className="col-xl-3 col-md-4 col-sm-6">
                            <div className="container feature-holder shadow p-3 mb-4">
                                <div className="row">
                                    <svg xmlns="http://www.w3.org/2000/svg" id="info-logo" width="35" height="35" fill="currentColor" className="bi bi-info-circle mb-2" viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                        <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                                      </svg>
                                </div>
                                <h3>Información general</h3>
                                <p>Somos una página web en la cual puedes crear tus propios cursos y unirte al que tú quieras</p>
                            </div>
                        </div>
                        <div className="col-xl-3 col-md-4 col-sm-6">
                            <div className="container feature-holder shadow-lg p-3 mb-4">
                                <div className="row">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" id="gamification-logo" fill="currentColor" className="bi bi-controller mb-2" viewBox="0 0 16 16">
                                        <path d="M11.5 6.027a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0m-1.5 1.5a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1m2.5-.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0m-1.5 1.5a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1m-6.5-3h1v1h1v1h-1v1h-1v-1h-1v-1h1z"/>
                                        <path d="M3.051 3.26a.5.5 0 0 1 .354-.613l1.932-.518a.5.5 0 0 1 .62.39c.655-.079 1.35-.117 2.043-.117.72 0 1.443.041 2.12.126a.5.5 0 0 1 .622-.399l1.932.518a.5.5 0 0 1 .306.729q.211.136.373.297c.408.408.78 1.05 1.095 1.772.32.733.599 1.591.805 2.466s.34 1.78.364 2.606c.024.816-.059 1.602-.328 2.21a1.42 1.42 0 0 1-1.445.83c-.636-.067-1.115-.394-1.513-.773-.245-.232-.496-.526-.739-.808-.126-.148-.25-.292-.368-.423-.728-.804-1.597-1.527-3.224-1.527s-2.496.723-3.224 1.527c-.119.131-.242.275-.368.423-.243.282-.494.575-.739.808-.398.38-.877.706-1.513.773a1.42 1.42 0 0 1-1.445-.83c-.27-.608-.352-1.395-.329-2.21.024-.826.16-1.73.365-2.606.206-.875.486-1.733.805-2.466.315-.722.687-1.364 1.094-1.772a2.3 2.3 0 0 1 .433-.335l-.028-.079zm2.036.412c-.877.185-1.469.443-1.733.708-.276.276-.587.783-.885 1.465a14 14 0 0 0-.748 2.295 12.4 12.4 0 0 0-.339 2.406c-.022.755.062 1.368.243 1.776a.42.42 0 0 0 .426.24c.327-.034.61-.199.929-.502.212-.202.4-.423.615-.674.133-.156.276-.323.44-.504C4.861 9.969 5.978 9.027 8 9.027s3.139.942 3.965 1.855c.164.181.307.348.44.504.214.251.403.472.615.674.318.303.601.468.929.503a.42.42 0 0 0 .426-.241c.18-.408.265-1.02.243-1.776a12.4 12.4 0 0 0-.339-2.406 14 14 0 0 0-.748-2.295c-.298-.682-.61-1.19-.885-1.465-.264-.265-.856-.523-1.733-.708-.85-.179-1.877-.27-2.913-.27s-2.063.091-2.913.27"/>
                                      </svg>
                                </div>
                                <h3>Gamificación</h3>
                                <p>Usamos la gamificación para garantizar un aprendizaje divertido y efectivo, aprendiendo habilidades nuevas</p>
                            </div>
                        </div>
                        <div className="col-xl-3 col-md-4 col-sm-6">
                            <div className="container feature-holder shadow-lg p-3 mb-4">
                                <div className="row">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" id="teacher-logo" className="bi bi-patch-plus-fill mb-2" viewBox="0 0 16 16">
                                        <path d="M10.067.87a2.89 2.89 0 0 0-4.134 0l-.622.638-.89-.011a2.89 2.89 0 0 0-2.924 2.924l.01.89-.636.622a2.89 2.89 0 0 0 0 4.134l.637.622-.011.89a2.89 2.89 0 0 0 2.924 2.924l.89-.01.622.636a2.89 2.89 0 0 0 4.134 0l.622-.637.89.011a2.89 2.89 0 0 0 2.924-2.924l-.01-.89.636-.622a2.89 2.89 0 0 0 0-4.134l-.637-.622.011-.89a2.89 2.89 0 0 0-2.924-2.924l-.89.01zM8.5 6v1.5H10a.5.5 0 0 1 0 1H8.5V10a.5.5 0 0 1-1 0V8.5H6a.5.5 0 0 1 0-1h1.5V6a.5.5 0 0 1 1 0"/>
                                      </svg>
                                </div>
                                <h3>Creación de cursos</h3>
                                <p>Puedes crear los cursos que tú desees dividiéndolos en secciones y creando diferentes tipos de juegos</p>
                            </div>
                        </div>
                        <div className="col-xl-3 col-md-12 mx-auto col-sm-6" >
                            <div className="container feature-holder shadow p-3 mb-4" id="lastFeature">
                                <div className="row">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" id="student-logo" fill="currentColor" className="bi bi-backpack2-fill mb-2" viewBox="0 0 16 16">
                                        <path d="M5 13h6v-3h-1v.5a.5.5 0 0 1-1 0V10H5z"/>
                                        <path d="M6 2v.341C3.67 3.165 2 5.388 2 8v1.191l-1.17.585A1.5 1.5 0 0 0 0 11.118V13.5A1.5 1.5 0 0 0 1.5 15h1c.456.607 1.182 1 2 1h7c.818 0 1.544-.393 2-1h1a1.5 1.5 0 0 0 1.5-1.5v-2.382a1.5 1.5 0 0 0-.83-1.342L14 9.191V8a6 6 0 0 0-4-5.659V2a2 2 0 1 0-4 0m2-1a1 1 0 0 1 1 1v.083a6 6 0 0 0-2 0V2a1 1 0 0 1 1-1m0 3a4 4 0 0 1 3.96 3.43.5.5 0 1 1-.99.14 3 3 0 0 0-5.94 0 .5.5 0 1 1-.99-.14A4 4 0 0 1 8 4M4.5 9h7a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5v-4a.5.5 0 0 1 .5-.5"/>
                                      </svg>

                                </div>
                                <h3>Unión a cursos</h3>
                                <p>Puedes unirte a todos los cursos que tú desees y veas interesantes y atractivos</p>
                            </div>
                        </div>
                    </article>
                    
                    <div className="row">
                        <article className="col-12 p-3">
                            <h2 className="text-center text-decoration-underline">Cursos en línea</h2>
                            <p className="fs-5 text-center">Como has visto anteriormente
                                somos una página web donde los usuarios
                                creann cursos gamificativos, a continuación
                                te pulsamos como ejemplo los 3 cursos más 
                                populares de nuestra página:
                            </p>
                        </article>
                    </div>
                </section>
                <section className="online-courses col-mt-4 mb-4">
                    <div id="carouselExampleAutoplaying" className="carousel slide" data-bs-ride="carousel">
                      
                        <div className="carousel-inner">

                          <div className="carousel-item active">
                            <img src="https://res.cloudinary.com/dlgpvjulu/image/upload/v1746896740/portada_de_curso_de_html_anutmq.jpg" className="d-block w-100" alt="..."></img>
                            <div className="carousel-caption d-none d-md-block">
                              <h5>Curso de HTML</h5>
                              <p>Este es un curso básico de HTML para principiantes.</p>
                            </div>
                          </div>

                          <div className="carousel-item">
                            <img src="https://res.cloudinary.com/dlgpvjulu/image/upload/v1746896740/portada_de_curso_de_CSS_mmoq5e.jpg" className="d-block w-100" alt="..."></img>
                            <div className="carousel-caption d-none d-md-block">
                              <h5>Curso de CSS</h5>
                              <p>Este es un curso básico de CSS para principiantes.</p>
                            </div>
                          </div>

                          <div className="carousel-item">
                            <img src="https://res.cloudinary.com/dlgpvjulu/image/upload/v1746896740/portada_de_curso_de_JavaScript_zlqm04.jpg" className="d-block w-100" alt="..."></img>
                            <div className="carousel-caption d-none d-md-block">
                              <h5>Curso de JavaScript</h5>
                              <p>Este es un curso básico de CSS para JavaScript.</p>
                            </div>
                          </div>
                        </div>
                      

                        <button className="carousel-control-prev" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="prev">
                          <span className="carousel-control-prev-icon" aria-hidden="true"></span>
                          <span className="visually-hidden">Previous</span>
                        </button>
                        <button className="carousel-control-next" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="next">
                          <span className="carousel-control-next-icon" aria-hidden="true"></span>
                          <span className="visually-hidden">Next</span>
                        </button>
                      </div>
                </section>        
            </main>
  )
}
