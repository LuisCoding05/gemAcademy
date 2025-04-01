import React from 'react';
import { Navigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';

const ProtectedRoute = ({ children, requiredRole }) => {
  const { isAuthenticated, loading, user } = useAuth();

  if (loading) {
    return <div>Cargando...</div>;
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  console.log('Usuario actual:', user); // Para debug
  console.log('Rol requerido:', requiredRole); // Para debug
  console.log('Roles del usuario:', user?.roles); // Para debug

  if (requiredRole && user && (!user.roles || !Array.isArray(user.roles) || !user.roles.includes(requiredRole))) {
    console.log('Acceso denegado - Redirigiendo a dashboard'); // Para debug
    return <Navigate to="/dashboard" replace />;
  }

  return children;
};

export default ProtectedRoute; 