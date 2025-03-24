import React from 'react'

export const Aside = () => {

  return (
    <aside id="aside" className="col-lg-3 h-100 mb-4 p-3 rounded">
                <div className="card shadow-sm p-3 h-100 shadow">
                    <div className="personal-section mb-4">
                        <h3 className="text-center">Personal</h3>
                        <hr></hr>
                        <ol className="list-unstyled">
                            <li className="mb-4">
                                <p className="fw-bold text-center">Luis Miguel</p>
                                <figure className="text-center border border-2 border-black rounded-3 w-75 mx-auto" id="figure1">
                                    <img src="images/Luis Miguel.jpg" className="img-fluid rounded-circle mb-2" alt="Luis Miguel"></img>
                                    <figcaption className="descripcion bg-dark text-white p-2">Full stack developer</figcaption>
                                </figure>
                            </li>
                        </ol>
                    </div>

                    <div className="tools-section">
                        <h3 className="text-center">Herramientas</h3>
                        <hr></hr>
                        <ol className="list-unstyled">
                            <li className="mb-4">
                                <p className="fw-bold text-center">Bootstrap</p>
                                <figure className="text-center border border-2 border-black rounded-3 w-75 mx-auto " id="figure2">
                                    <img src="images/Bootstrap_logo.png" className="img-fluid rounded mb-2" alt="Bootstrap Logo"></img>
                                    <figcaption className="descripcion bg-dark text-white p-2">Framework utilizado</figcaption>
                                </figure>
                            </li>
                        </ol>
                    </div>
                </div>

                <article className=" p-2 mt-4 border rounded" id="mejoras">
                    <h3 className="mt-4 text-center" >Posibles mejoras y contacto</h3>
                    <hr></hr>
                    <div className="accordion mt-3" id="accordionExample">
                        <div className="accordion-item">
                        <h2 className="accordion-header">
                            <button className="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                            <b>Diversión y ocio</b>
                            </button>
                        </h2>
                        <div id="collapseOne" className="accordion-collapse collapse show" data-bs-parent="#accordionExample">
                            <div className="accordion-body">
                                <h4>¡Entretente con tus amigos!</h4> <p>Entra con tus amigos y ten diversión creando quizzes o cursos sobre memes o trendings</p>
                            </div>
                        </div>
                        </div>
                        <div className="accordion-item">
                        <h2 className="accordion-header">
                            <button className="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            <b>Certificaciones</b>
                            </button>
                        </h2>
                        <div id="collapseTwo" className="accordion-collapse collapse" data-bs-parent="#accordionExample">
                            <div className="accordion-body">
                                <h4>Si un curso está verificado por un profesor real y una agencia</h4> <p>Podriamos asignar una certificación para que el alumno pueda ponerla en su CV</p>
                            </div>
                        </div>
                        </div>
                        <div className="accordion-item">
                        <h2 className="accordion-header">
                            <button className="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            <b>Siéntete cómodo</b>
                            </button>
                        </h2>
                        <div id="collapseThree" className="accordion-collapse collapse" data-bs-parent="#accordionExample">
                            <div className="accordion-body">
                                <h4>Entra a aprender cuando tú quieras y aprende a tu ritmo.</h4> <p>Entra al curso que desees y progresa cuando tu lo desees, en tus ratos libres, donde quieras y cuando quieras.</p>
                            </div>
                        </div>
                        </div>
                        <div className="accordion-item">
                            <h2 className="accordion-header">
                            <button className="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                <b>Contáctanos y cuéntanos tu experiencia</b>
                            </button>
                            </h2>
                            <div id="collapseFour" className="accordion-collapse collapse" data-bs-parent="#accordionExample">
                            <div className="accordion-body">
                                <h4>Más abajo tienes los enlaces a nuestras redes sociales</h4> <p>Siéntete libre de contactarnos, comunicarnos problemas, decirnos posibles mejoras de la aplicación, ¡o simplemente contarnos tu experiencia!</p>
                            </div>
                            </div>
                        </div>
                    </div>
            </article>
        </aside>
  )
}
