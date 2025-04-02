import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from '../../utils/axios';
import { useAuth } from '../../context/AuthContext';

const MainLogin = () => {
  const navigate = useNavigate();
  const { login } = useAuth();
  const [loginData, setLoginData] = useState({
    email: '',
    password: ''
  });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleChange = (e) => {
    const { id, value } = e.target;
    setLoginData(prevState => ({
      ...prevState,
      [id]: value
    }));
    setError(''); // Limpiar error cuando el usuario escribe
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      console.log('Enviando datos:', loginData); // Para debug
      const response = await axios.post('/api/login', loginData);
      console.log('Respuesta:', response.data); // Para debug
      
      // Verificar si la cuenta está verificada
      if (!response.data.user.verificado) {
        setError('Tu cuenta no está verificada. Por favor, verifica tu cuenta antes de iniciar sesión.');
        return;
      }
      
      // Guardar el token en localStorage
      localStorage.setItem('token', response.data.token);
      
      // Actualizar el contexto de autenticación
      login(response.data.user);
      
      // Redirigir al dashboard
      navigate('/dashboard');
    } catch (err) {
      console.error('Error completo:', err); // Para debug
      if (err.response?.status === 401) {
        setError('Credenciales inválidas. Por favor, verifica tu email y contraseña.');
      } else if (err.response?.status === 400) {
        setError(err.response.data.message || 'Por favor, completa todos los campos requeridos.');
      } else {
        setError('Error al iniciar sesión. Por favor, intenta de nuevo más tarde.');
      }
    } finally {
      setLoading(false);
    }
  };

  // Definir los estilos como objetos
  const mainStyles = {
    backgroundColor: '#000000',
    backgroundImage: 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 2000 1500\'%3E%3Cdefs%3E%3Crect fill=\'none\' stroke-width=\'100\' id=\'a\' x=\'-400\' y=\'-300\' width=\'800\' height=\'600\'/%3E%3C/defs%3E%3Cg style=\'transform-origin:center\'%3E%3Cg transform=\'\' style=\'transform-origin:center\'%3E%3Cg transform=\'rotate(-160 0 0)\' style=\'transform-origin:center\'%3E%3Cg transform=\'translate(1000 750)\'%3E%3Cuse stroke=\'%23000\' href=\'%23a\' transform=\'rotate(10 0 0) scale(1.1)\'/%3E%3Cuse stroke=\'%23000011\' href=\'%23a\' transform=\'rotate(20 0 0) scale(1.2)\'/%3E%3Cuse stroke=\'%23000022\' href=\'%23a\' transform=\'rotate(30 0 0) scale(1.3)\'/%3E%3Cuse stroke=\'%23000033\' href=\'%23a\' transform=\'rotate(40 0 0) scale(1.4)\'/%3E%3Cuse stroke=\'%23000044\' href=\'%23a\' transform=\'rotate(50 0 0) scale(1.5)\'/%3E%3Cuse stroke=\'%23000055\' href=\'%23a\' transform=\'rotate(60 0 0) scale(1.6)\'/%3E%3Cuse stroke=\'%23000066\' href=\'%23a\' transform=\'rotate(70 0 0) scale(1.7)\'/%3E%3Cuse stroke=\'%23000077\' href=\'%23a\' transform=\'rotate(80 0 0) scale(1.8)\'/%3E%3Cuse stroke=\'%23000088\' href=\'%23a\' transform=\'rotate(90 0 0) scale(1.9)\'/%3E%3Cuse stroke=\'%23000099\' href=\'%23a\' transform=\'rotate(100 0 0) scale(2)\'/%3E%3Cuse stroke=\'%230000aa\' href=\'%23a\' transform=\'rotate(110 0 0) scale(2.1)\'/%3E%3Cuse stroke=\'%230000bb\' href=\'%23a\' transform=\'rotate(120 0 0) scale(2.2)\'/%3E%3Cuse stroke=\'%230000cc\' href=\'%23a\' transform=\'rotate(130 0 0) scale(2.3)\'/%3E%3Cuse stroke=\'%230000dd\' href=\'%23a\' transform=\'rotate(140 0 0) scale(2.4)\'/%3E%3Cuse stroke=\'%230000ee\' href=\'%23a\' transform=\'rotate(150 0 0) scale(2.5)\'/%3E%3Cuse stroke=\'%2300F\' href=\'%23a\' transform=\'rotate(160 0 0) scale(2.6)\'/%3E%3C/g%3E%3C/g%3E%3C/g%3E%3C/g%3E%3C/svg%3E")',
    backgroundAttachment: 'fixed',
    backgroundSize: 'cover',
    overflow: 'hidden',
    paddingTop: '80px' // Agregar padding para el navbar
  };

  const floatingAnimation = `
    @keyframes float {
      0% { transform: translateY(0px); }
      100% { transform: translateY(-20px); }
    }
    .floating-object {
      animation: float 3s ease-in-out infinite alternate;
    }
  `;

  return (
    <main 
      className="position-relative vh-100 d-flex align-items-center justify-content-center" 
      style={mainStyles}
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

      {/* Contenedor de Login */}
      <div className="container">
        <div className="row justify-content-center">
          <div 
            className="col-md-6 bg-dark bg-gradient text-white bg-opacity-75 p-4 rounded-3 shadow" 
            style={{position: 'relative', zIndex: 10}}
          >
            <h2 className="text-center mb-4">Iniciar Sesión</h2>
            {error && (
              <div className="alert alert-danger" role="alert">
                {error}
                {error.includes('no está verificada') && (
                  <div className="mt-2">
                    <a href="/verify" className="btn btn-outline-light btn-sm">
                      Verificar Cuenta
                    </a>
                  </div>
                )}
              </div>
            )}
            <form onSubmit={handleSubmit}>
              <div className="row">
                <div className="col-md-12 mb-3">
                  <label htmlFor="email" className="form-label">Correo Electrónico:</label>
                  <input 
                    type="email" 
                    className="form-control bg-secondary bg-gradient" 
                    id="email" 
                    value={loginData.email}
                    onChange={handleChange}
                    required 
                    disabled={loading}
                  />
                </div>
                <div className="col-md-12 mb-3">
                  <label htmlFor="password" className="form-label">Contraseña:</label>
                  <input 
                    type="password" 
                    className="form-control bg-secondary bg-gradient" 
                    id="password" 
                    value={loginData.password}
                    onChange={handleChange}
                    required 
                    disabled={loading}
                  />
                </div>
                <div className="col-md-12 mb-3">
                  <div className="form-check">
                    <input 
                      type="checkbox" 
                      className="form-check-input" 
                      id="rememberMe" 
                      disabled={loading}
                    />
                    <label className="form-check-label" htmlFor="rememberMe">
                      Recordarme
                    </label>
                  </div>
                </div>
                <div className="col-md-12 mb-3">
                  <button 
                    type="submit" 
                    className="btn btn-primary w-100"
                    disabled={loading}
                  >
                    {loading ? 'Iniciando sesión...' : 'Iniciar Sesión'}
                  </button>
                </div>
                <div className="col-md-12 text-center">
                  <a href="/verify" className="text-light">¿Olvidaste tu contraseña?</a>
                </div>
                <div className="col-md-12 text-center mt-3">
                  <p>¿No tienes cuenta? <a href="/register" className="text-primary">Regístrate</a></p>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      <style>{floatingAnimation}</style>
    </main>
  );
};

export default MainLogin;