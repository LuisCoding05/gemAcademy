import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from '../../utils/axios';
import { useAuth } from '../../context/AuthContext';
import Icon from '../Icon';

const MainLogin = () => {
  const navigate = useNavigate();
  const { login } = useAuth();
  const [formData, setFormData] = useState({
    email: '',
    password: ''
  });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  
  const togglePassword = () => {
    setShowPassword((prev) => !prev);
  };

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
    setError('');
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (loading) return;

    setLoading(true);
    setError('');

    try {
      const response = await axios.post('/api/login', {
        email: formData.email,
        password: formData.password
      });

      // Si llegamos aquí, el login fue exitoso
      const { token, user } = response.data;
      localStorage.setItem('token', token);
      login(user, token);
      navigate('/dashboard');

    } catch (err) {
      console.error('Error de login:', err);
      
      if (err.response?.status === 401) {
        if (err.response.data.message.includes('no verificado')) {
          setError('Tu cuenta no está verificada. Por favor, verifica tu cuenta antes de iniciar sesión.');
        } else if (err.response.data.message.includes('No tienes acceso')) {
          setError('Tu cuenta ha sido suspendida. Por favor, contacta con el administrador.');
        } else {
          setError('Credenciales inválidas. Por favor, verifica tu email y contraseña.');
        }
      } else if (err.response?.status === 400) {
        setError('Por favor, completa todos los campos correctamente.');
      } else {
        setError('Error al intentar iniciar sesión. Por favor, intenta nuevamente.');
      }
    } finally {
      setLoading(false);
    }
  };

  const handleNavigate = (path) => {
    window.location.href = path;
  };

  return (
    <main className="position-relative vh-100 d-flex align-items-center justify-content-center" style={{
      backgroundColor: '#000000',
      backgroundImage: 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 2000 1500\'%3E%3Cdefs%3E%3Crect fill=\'none\' stroke-width=\'100\' id=\'a\' x=\'-400\' y=\'-300\' width=\'800\' height=\'600\'/%3E%3C/defs%3E%3Cg style=\'transform-origin:center\'%3E%3Cg transform=\'\' style=\'transform-origin:center\'%3E%3Cg transform=\'rotate(-160 0 0)\' style=\'transform-origin:center\'%3E%3Cg transform=\'translate(1000 750)\'%3E%3Cuse stroke=\'%23000\' href=\'%23a\' transform=\'rotate(10 0 0) scale(1.1)\'/%3E%3Cuse stroke=\'%23000011\' href=\'%23a\' transform=\'rotate(20 0 0) scale(1.2)\'/%3E%3Cuse stroke=\'%23000022\' href=\'%23a\' transform=\'rotate(30 0 0) scale(1.3)\'/%3E%3Cuse stroke=\'%23000033\' href=\'%23a\' transform=\'rotate(40 0 0) scale(1.4)\'/%3E%3Cuse stroke=\'%23000044\' href=\'%23a\' transform=\'rotate(50 0 0) scale(1.5)\'/%3E%3Cuse stroke=\'%23000055\' href=\'%23a\' transform=\'rotate(60 0 0) scale(1.6)\'/%3E%3Cuse stroke=\'%23000066\' href=\'%23a\' transform=\'rotate(70 0 0) scale(1.7)\'/%3E%3Cuse stroke=\'%23000077\' href=\'%23a\' transform=\'rotate(80 0 0) scale(1.8)\'/%3E%3Cuse stroke=\'%23000088\' href=\'%23a\' transform=\'rotate(90 0 0) scale(1.9)\'/%3E%3Cuse stroke=\'%23000099\' href=\'%23a\' transform=\'rotate(100 0 0) scale(2)\'/%3E%3Cuse stroke=\'%230000aa\' href=\'%23a\' transform=\'rotate(110 0 0) scale(2.1)\'/%3E%3Cuse stroke=\'%230000bb\' href=\'%23a\' transform=\'rotate(120 0 0) scale(2.2)\'/%3E%3Cuse stroke=\'%230000cc\' href=\'%23a\' transform=\'rotate(130 0 0) scale(2.3)\'/%3E%3Cuse stroke=\'%230000dd\' href=\'%23a\' transform=\'rotate(140 0 0) scale(2.4)\'/%3E%3Cuse stroke=\'%230000ee\' href=\'%23a\' transform=\'rotate(150 0 0) scale(2.5)\'/%3E%3Cuse stroke=\'%2300F\' href=\'%23a\' transform=\'rotate(160 0 0) scale(2.6)\'/%3E%3C/g%3E%3C/g%3E%3C/g%3E%3C/g%3E%3C/svg%3E")',
      backgroundAttachment: 'fixed',
      backgroundSize: 'cover',
      overflow: 'hidden',
      paddingTop: '80px'
    }}>
      <div className="container">
        <div className="row justify-content-center">
          <div className="col-md-6 bg-dark bg-gradient text-white bg-opacity-75 p-4 rounded-3 shadow">
            <h2 className="text-center mb-4">Iniciar Sesión</h2>
            
            {error && (
              <div className="alert alert-danger" role="alert">
                {error}
                {error.includes('no está verificada') && (
                  <div className="mt-2">
                    <button 
                      className="btn btn-outline-light btn-sm text-dark"
                      onClick={() => handleNavigate('/verify')}
                      type="button"
                    >
                      Verificar Cuenta
                    </button>
                  </div>
                )}
              </div>
            )}

            <form onSubmit={handleSubmit}>
              <div className="mb-3">
                <label htmlFor="email" className="form-label">Correo Electrónico</label>
                <input
                  type="email"
                  className="form-control bg-secondary bg-gradient"
                  id="email"
                  name="email"
                  value={formData.email}
                  onChange={handleChange}
                  disabled={loading}
                  required
                />
              </div>

              <div className="mb-3">
                <label htmlFor="password" className="form-label">Contraseña</label>
                <div className="position-relative">
                  <input
                    type={showPassword ? 'text' : 'password'}
                    className="form-control bg-secondary bg-gradient"
                    style={{ paddingRight: '40px' }}
                    id="password"
                    name="password"
                    value={formData.password}
                    onChange={handleChange}
                    disabled={loading}
                    required
                  />
                  <button
                    type='button'
                    className='btn btn-outline-light border-0 position-absolute end-0 top-50 translate-middle-y'
                    style={{ padding: '0.375rem' }}
                    onClick={togglePassword}
                    aria-label={showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'}
                  >
                    <Icon name={showPassword ? 'eye-hidden' : 'eye'} />
                  </button>
                </div>
              </div>

              <button 
                type="submit" 
                className="btn btn-primary w-100 mb-3"
                disabled={loading}
              >
                {loading ? 'Iniciando sesión...' : 'Iniciar Sesión'}
              </button>

              <div className="text-center">
                <button 
                  type="button"
                  className="btn btn-link text-light text-decoration-none"
                  onClick={() => handleNavigate('/verify')}
                >
                  ¿Olvidaste tu contraseña?
                </button>
                <p className="mb-0">
                  ¿No tienes cuenta? 
                  <button 
                    type="button"
                    className="btn btn-link text-primary text-decoration-none"
                    onClick={() => handleNavigate('/register')}
                  >
                    Regístrate
                  </button>
                </p>
              </div>
            </form>
          </div>
        </div>
      </div>
    </main>
  );
};

export default MainLogin;