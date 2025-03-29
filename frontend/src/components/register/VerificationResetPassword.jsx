import React, { useState } from 'react';

const VerificationResetPassword = () => {
  const [formData, setFormData] = useState({
    email: '',
    verificationCode: '',
    newPassword: '',
    showPasswordField: false
  });

  const handleChange = (e) => {
    const { id, value } = e.target;
    setFormData(prevState => ({
      ...prevState,
      [id]: value
    }));
  };

  const togglePasswordField = () => {
    setFormData(prevState => ({
      ...prevState,
      showPasswordField: !prevState.showPasswordField
    }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    // Lógica de verificación o restablecimiento
    if (formData.showPasswordField) {
      console.log('Restablecer contraseña:', formData);
      alert('Contraseña restablecida con éxito');
    } else {
      console.log('Verificar cuenta:', formData);
      alert('Cuenta verificada con éxito');
    }
  };

  const handleResendCode = () => {
    console.log('Reenviar código a:', formData.email);
    alert('Código reenviado');
  };

  return (
    <main 
      className="position-relative vh-100 d-flex align-items-center justify-content-center" 
      style={{
        backgroundColor: '#000000',
        backgroundImage: 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 2000 1500\'%3E%3Cdefs%3E%3Crect fill=\'none\' stroke-width=\'100\' id=\'a\' x=\'-400\' y=\'-300\' width=\'800\' height=\'600\'/%3E%3C/defs%3E%3Cg style=\'transform-origin:center\'%3E%3Cg transform=\'\' style=\'transform-origin:center\'%3E%3Cg transform=\'rotate(-160 0 0)\' style=\'transform-origin:center\'%3E%3Cg transform=\'translate(1000 750)\'%3E%3Cuse stroke=\'%23000\' href=\'%23a\' transform=\'rotate(10 0 0) scale(1.1)\'/%3E%3Cuse stroke=\'%23000011\' href=\'%23a\' transform=\'rotate(20 0 0) scale(1.2)\'/%3E%3Cuse stroke=\'%23000022\' href=\'%23a\' transform=\'rotate(30 0 0) scale(1.3)\'/%3E%3Cuse stroke=\'%23000033\' href=\'%23a\' transform=\'rotate(40 0 0) scale(1.4)\'/%3E%3Cuse stroke=\'%23000044\' href=\'%23a\' transform=\'rotate(50 0 0) scale(1.5)\'/%3E%3Cuse stroke=\'%23000055\' href=\'%23a\' transform=\'rotate(60 0 0) scale(1.6)\'/%3E%3Cuse stroke=\'%23000066\' href=\'%23a\' transform=\'rotate(70 0 0) scale(1.7)\'/%3E%3Cuse stroke=\'%23000077\' href=\'%23a\' transform=\'rotate(80 0 0) scale(1.8)\'/%3E%3Cuse stroke=\'%23000088\' href=\'%23a\' transform=\'rotate(90 0 0) scale(1.9)\'/%3E%3Cuse stroke=\'%23000099\' href=\'%23a\' transform=\'rotate(100 0 0) scale(2)\'/%3E%3Cuse stroke=\'%230000aa\' href=\'%23a\' transform=\'rotate(110 0 0) scale(2.1)\'/%3E%3Cuse stroke=\'%230000bb\' href=\'%23a\' transform=\'rotate(120 0 0) scale(2.2)\'/%3E%3Cuse stroke=\'%230000cc\' href=\'%23a\' transform=\'rotate(130 0 0) scale(2.3)\'/%3E%3Cuse stroke=\'%230000dd\' href=\'%23a\' transform=\'rotate(140 0 0) scale(2.4)\'/%3E%3Cuse stroke=\'%230000ee\' href=\'%23a\' transform=\'rotate(150 0 0) scale(2.5)\'/%3E%3Cuse stroke=\'%2300F\' href=\'%23a\' transform=\'rotate(160 0 0) scale(2.6)\'/%3E%3C/g%3E%3C/g%3E%3C/g%3E%3C/g%3E%3C/svg%3E")',
        backgroundAttachment: 'fixed',
        backgroundSize: 'cover',
        overflow: 'hidden'
      }}
    >
      {/* Objetos flotantes */}
      <div className="position-absolute" style={{top: '10%', left: '5%', zIndex: 1}}>
        <img 
          src="images/floatingObjects/controller.png" 
          alt="Game Controller" 
          className="floating-object" 
          style={{width: '100px', opacity: 0.7}}
        />
      </div>
      <div className="position-absolute" style={{top: '50%', right: '10%', zIndex: 1}}>
        <img 
          src="images/floatingObjects/bookFloating.png" 
          alt="Book" 
          className="floating-object" 
          style={{width: '180px', opacity: 0.6}}
        />
      </div>
      <div className="position-absolute" style={{bottom: '20%', left: '15%', zIndex: 1}}>
        <img 
          src="images/floatingObjects/dispositivosFlotantes.png" 
          alt="Device" 
          className="floating-object" 
          style={{width: '160px', opacity: 0.5}}
        />
      </div>

      {/* Contenedor de Verificación/Restablecimiento */}
      <div className="container">
        <div className="row justify-content-center">
          <div 
            className="col-md-6 bg-dark bg-gradient text-white bg-opacity-75 p-4 rounded-3 shadow" 
            style={{position: 'relative', zIndex: 10}}
          >
            <h2 className="text-center mb-4">
              {formData.showPasswordField ? 'Restablecer Contraseña' : 'Verificar Cuenta'}
            </h2>
            <form onSubmit={handleSubmit}>
              <div className="row">
                <div className="col-md-12 mb-3">
                  <label htmlFor="email" className="form-label">Correo Electrónico:</label>
                  <input 
                    type="email" 
                    className="form-control bg-secondary bg-gradient" 
                    id="email" 
                    value={formData.email}
                    onChange={handleChange}
                    required 
                  />
                </div>
                <div className="col-md-12 mb-3">
                  <label htmlFor="verificationCode" className="form-label">Código de Verificación:</label>
                  <input 
                    type="text" 
                    className="form-control bg-secondary bg-gradient" 
                    id="verificationCode" 
                    value={formData.verificationCode}
                    onChange={handleChange}
                    required 
                  />
                </div>
                
                {/* Opción para mostrar/ocultar campo de contraseña */}
                <div className="col-md-12 mb-3">
                  <div className="form-check form-switch">
                    <input 
                      className="form-check-input" 
                      type="checkbox" 
                      id="togglePassword" 
                      checked={formData.showPasswordField}
                      onChange={togglePasswordField}
                    />
                    <label className="form-check-label" htmlFor="togglePassword">
                      Restablecer contraseña
                    </label>
                  </div>
                </div>
                
                {/* Campo de nueva contraseña (condicional) */}
                {formData.showPasswordField && (
                  <div className="col-md-12 mb-3">
                    <label htmlFor="newPassword" className="form-label">Nueva Contraseña:</label>
                    <input 
                      type="password" 
                      className="form-control bg-secondary bg-gradient" 
                      id="newPassword" 
                      value={formData.newPassword}
                      onChange={handleChange}
                      required={formData.showPasswordField}
                    />
                  </div>
                )}
                
                {/* Botones */}
                <div className="col-md-12 mb-3 d-grid">
                  <button type="submit" className="btn btn-primary">
                    {formData.showPasswordField ? 'Restablecer Contraseña' : 'Verificar Cuenta'}
                  </button>
                </div>
                
                <div className="col-md-12 mb-3 d-grid">
                  <button 
                    type="button" 
                    className="btn btn-outline-light" 
                    onClick={handleResendCode}
                  >
                    Reenviar Código
                  </button>
                </div>
                
                <div className="col-md-12 text-center mt-3">
                  <a href="#" className="text-light">Volver al inicio de sesión</a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      {/* Estilos adicionales */}
      <style jsx>{`
        @keyframes float {
          0% { transform: translateY(0px); }
          100% { transform: translateY(-20px); }
        }
        .floating-object {
          animation: float 3s ease-in-out infinite alternate;
        }
      `}</style>
    </main>
  );
};

export default VerificationResetPassword;