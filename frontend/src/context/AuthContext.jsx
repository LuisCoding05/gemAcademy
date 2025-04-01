import React, { createContext, useState, useContext, useEffect } from 'react';

const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Verificar si hay un usuario guardado al cargar la aplicación
    const storedUser = localStorage.getItem('user');
    if (storedUser) {
      const parsedUser = JSON.parse(storedUser);
      // Asegurarnos de que roles sea un array
      if (parsedUser && !Array.isArray(parsedUser.roles)) {
        parsedUser.roles = [parsedUser.roles];
      }
      setUser(parsedUser);
    }
    setLoading(false);
  }, []);

  const login = (userData) => {
    // Asegurarnos de que roles sea un array
    if (userData && !Array.isArray(userData.roles)) {
      userData.roles = [userData.roles];
    }
    console.log('Usuario con roles:', userData); // Para debug
    setUser(userData);
    localStorage.setItem('user', JSON.stringify(userData));
  };

  const logout = () => {
    setUser(null);
    localStorage.removeItem('user');
    localStorage.removeItem('token');
    window.location.href = '/login';
  };

  const value = {
    user,
    login,
    logout,
    loading,
    isAuthenticated: !!user
  };

  return (
    <AuthContext.Provider value={value}>
      {!loading && children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth debe ser usado dentro de un AuthProvider');
  }
  return context;
}; 