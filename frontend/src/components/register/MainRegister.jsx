import React, { useState } from 'react';
import axios from '../../utils/axios';
import { useNavigate } from 'react-router-dom';

const MainRegister = () => {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    nombre: '',
    apellido: '',
    apellido2: '',
    username: '',
    email: '',
    password: ''
  });
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [loading, setLoading] = useState(false);

  const handleChange = (e) => {
    const { id, value } = e.target;
    setFormData(prevState => ({
      ...prevState,
      [id]: value
    }));
    setError('');
    setSuccess('');
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    setSuccess('');

    try {
      // Validaciones del lado del cliente
      const validationErrors = [];

      if (!formData.nombre.trim()) validationErrors.push('El nombre es requerido');
      if (!formData.apellido.trim()) validationErrors.push('El primer apellido es requerido');
      if (!formData.email.trim()) validationErrors.push('El correo electrónico es requerido');
      if (!formData.password) validationErrors.push('La contraseña es requerida');

      // Validar formato de email
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (formData.email.trim() && !emailRegex.test(formData.email)) {
        validationErrors.push('El formato del correo electrónico no es válido');
      }

      // Validar longitud y complejidad de la contraseña
      if (formData.password) {
        if (formData.password.length < 6) {
          validationErrors.push('La contraseña debe tener al menos 6 caracteres');
        }
        if (!/\d/.test(formData.password)) {
          validationErrors.push('La contraseña debe contener al menos un número');
        }
        if (!/[A-Z]/.test(formData.password)) {
          validationErrors.push('La contraseña debe contener al menos una letra mayúscula');
        }
      }

      if (validationErrors.length > 0) {
        throw new Error(validationErrors.join('\\n'));
      }

      const dataToSend = {
        nombre: formData.nombre.trim(),
        apellido: formData.apellido.trim(),
        apellido2: formData.apellido2.trim() || null,
        username: formData.username.trim() || formData.email.trim(),
        email: formData.email.trim(),
        password: formData.password
      };

      const response = await axios.post('/api/register', dataToSend);
      
      setSuccess(response.data.message);
      setTimeout(() => navigate('/verify'), 2000);

    } catch (err) {
      console.error('Error en el registro:', err);
      
      if (err.message.includes('\\n')) {
        // Errores de validación del cliente
        setError(err.message.split('\\n').map((error, index) => (
          <div key={index}>{error}</div>
        )));
      } else if (err.response?.status === 400) {
        // Errores específicos del servidor
        if (err.response.data.message.includes('email ya está registrado')) {
          setError('Este correo electrónico ya está registrado. Por favor, utiliza otro o inicia sesión.');
        } else if (err.response.data.message.includes('nombre de usuario ya está en uso')) {
          setError('Este nombre de usuario ya está en uso. Por favor, elige otro.');
        } else {
          setError(err.response.data.message);
        }
      } else if (!navigator.onLine) {
        setError('No hay conexión a internet. Por favor, verifica tu conexión e intenta nuevamente.');
      } else {
        setError('Ha ocurrido un error al intentar registrarte. Por favor, intenta nuevamente más tarde.\n asegurate de que la contraseña tenga mayuscula, números y +6 carácteres');
      }
    } finally {
      setLoading(false);
    }
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

      {/* Contenedor de Registro */}
      <div className="container">
        <div className="row justify-content-center">
          <div 
            className="col-md-6 bg-dark bg-gradient text-white bg-opacity-75 p-4 rounded-3 shadow" 
            style={{position: 'relative', zIndex: 10}}
          >
            <h2 className="text-center mb-4">Registro</h2>
            {error && (
              <div className="alert alert-danger" role="alert">
                {error}
              </div>
            )}
            {success && (
              <div className="alert alert-success" role="alert">
                {success}
              </div>
            )}
            <form onSubmit={handleSubmit}>
              <div className="row">
                <div className="col-md-6 mb-3">
                  <label htmlFor="nombre" className="form-label">Nombre:</label>
                  <input  
                    type="text" 
                    className="form-control bg-secondary bg-gradient text-white" 
                    id="nombre" 
                    value={formData.nombre}
                    onChange={handleChange}
                    required 
                    disabled={loading}
                    placeholder="Tu nombre"
                  />
                </div>
                <div className="col-md-6 mb-3">
                  <label htmlFor="apellido" className="form-label">Primer Apellido:</label>
                  <input 
                    type="text" 
                    className="form-control bg-secondary bg-gradient text-white" 
                    id="apellido" 
                    value={formData.apellido}
                    onChange={handleChange}
                    required 
                    disabled={loading}
                    placeholder="Tu primer apellido"
                  />
                </div>
                <div className="col-md-12 mb-3">
                  <label htmlFor="apellido2" className="form-label">Segundo Apellido:</label>
                  <input 
                    type="text" 
                    className="form-control bg-secondary bg-gradient text-white" 
                    id="apellido2" 
                    value={formData.apellido2}
                    onChange={handleChange}
                    disabled={loading}
                    placeholder="Tu segundo apellido (opcional)"
                  />
                </div>
                <div className="col-md-12 mb-3">
                  <label htmlFor="username" className="form-label">Nombre de Usuario:</label>
                  <input 
                    type="text" 
                    className="form-control bg-secondary bg-gradient text-white" 
                    id="username" 
                    value={formData.username}
                    onChange={handleChange}
                    required 
                    disabled={loading}
                    placeholder="Elige un nombre de usuario"
                  />
                </div>
                <div className="col-md-12 mb-3">
                  <label htmlFor="email" className="form-label">Correo Electrónico:</label>
                  <input 
                    type="email" 
                    className="form-control bg-secondary bg-gradient text-white" 
                    id="email" 
                    value={formData.email}
                    onChange={handleChange}
                    required 
                    disabled={loading}
                    placeholder="tu.correo@ejemplo.com"
                  />
                </div>
                <div className="col-md-12 mb-3">
                  <label htmlFor="password" className="form-label">Contraseña:</label>
                  <input 
                    type="password" 
                    className="form-control bg-secondary bg-gradient text-white" 
                    id="password" 
                    value={formData.password}
                    onChange={handleChange}
                    required 
                    disabled={loading}
                    placeholder="Tu contraseña"
                  />
                </div>
                <div className="col-md-12">
                  <button 
                    type="submit" 
                    className="btn btn-primary w-100 py-2"
                    disabled={loading}
                  >
                    {loading ? 'Registrando...' : 'Registrarse'}
                  </button>
                </div>
                <div className="col-md-12 text-center mt-3">
                  <p className="mb-0">¿Tienes cuenta? <a href="/login" className="text-primary text-decoration-none">Inicia sesión</a></p>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      {/* Estilos adicionales */}
      <style>{`
        @keyframes float {
          0% { transform: translateY(0px); }
          100% { transform: translateY(-20px); }
        }
        .floating-object {
          animation: float 3s ease-in-out infinite alternate;
        }
        .form-control::placeholder {
          color: rgba(255, 255, 255, 0.5);
        }
        .form-control:focus {
          background-color: rgba(255, 255, 255, 0.1) !important;
          color: white;
          box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25);
        }
        @media (max-width: 768px) {
          .floating-object {
            display: none;
          }
          .container {
            padding: 10px;
          }
          .col-md-6 {
            width: 100%;
            max-width: 100%;
          }
          .bg-dark {
            border-radius: 10px;
            margin: 10px;
          }
          main {
            min-height: 100vh;
            padding: 20px 0;
          }
          .form-control {
            height: 30px;
          }
          .btn {
            height: 35px;
            font-size: 16px;
          }
        }
      `}</style>
    </main>
  );
};

export default MainRegister;