import React from 'react';
import { useAuth } from '../../context/AuthContext';

const Dashboard = () => {
  const { user, logout } = useAuth();

  return (
    <div className="container mt-5">
      <div className="row">
        <div className="col-12">
          <div className="card">
            <div className="card-header d-flex justify-content-between align-items-center">
              <h2>Dashboard</h2>
              <button className="btn btn-danger" onClick={logout}>
                Cerrar Sesión
              </button>
            </div>
            <div className="card-body">
              <h3>Bienvenido, {user.nombre} {user.apellido}</h3>
              <p>Email: {user.email}</p>
              <div className="alert alert-success">
                ¡Has accedido exitosamente a una ruta protegida!
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard; 