import axios from 'axios';

// 'http://localhost:8000'
const instance = axios.create({
  baseURL: 'http://192.168.1.93:8000',
  headers: {
    'Content-Type': 'application/json',
  },
});

// Interceptor para agregar el token a todas las peticiones
instance.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Interceptor para manejar errores de autenticación
instance.interceptors.response.use(
  (response) => response,
  (error) => {
    // Solo redirigir si hay un token y es un error 401
    // Esto significa que el token es inválido o expiró
    if (error.response?.status === 401 && localStorage.getItem('token')) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default instance; 