import React from 'react';
import { useTheme } from '../context/ThemeContext';

const PrivacyPolicy = () => {
  const { isDarkMode } = useTheme();

  return (
    <div className="container py-5">
      <div className={`card ${isDarkMode ? 'bg-dark text-light' : ''}`}>
        <div className="card-body">
          <h1 className="mb-4">Política de Privacidad y Cookies</h1>
          
          <section className="mb-5">
            <h2>Cookies Esenciales</h2>
            <p>Las cookies esenciales son necesarias para el funcionamiento básico del sitio:</p>
            <ul>
              <li>Autenticación y token de sesión</li>
              <li>Preferencias de tema (modo claro/oscuro)</li>
              <li>Datos temporales de quizzes</li>
            </ul>
          </section>

          <section className="mb-5">
            <h2>Cookies de Terceros</h2>
            <p>Utilizamos cookies de terceros para:</p>
            <ul>
              <li>Reproducir videos de YouTube en el editor de contenido</li>
            </ul>
            <p>Puedes elegir no aceptar estas cookies, aunque esto limitará algunas funcionalidades.</p>
          </section>

          <section className="mb-5">
            <h2>Almacenamiento Local</h2>
            <p>Utilizamos el almacenamiento local del navegador para:</p>
            <ul>
              <li>Mantener tu sesión iniciada</li>
              <li>Guardar tus preferencias de tema</li>
              <li>Almacenar temporalmente el progreso en quizzes</li>
            </ul>
          </section>

          <section className="mb-5">
            <h2>Tus Derechos</h2>
            <p>Tienes derecho a:</p>
            <ul>
              <li>Elegir qué cookies aceptas</li>
              <li>Solicitar información sobre los datos almacenados</li>
              <li>Solicitar la eliminación de tus datos</li>
            </ul>
          </section>

          <section className="mb-5">
            <h2>Contacto</h2>
            <p>Para cualquier consulta sobre privacidad o datos personales, puedes contactarnos a través de:</p>
            <ul>
              <li>Email: privacy@gemacademy.com</li>
            </ul>
          </section>

          <section>
            <h2>Cambios en la Política</h2>
            <p>Esta política puede actualizarse ocasionalmente. Los cambios importantes serán notificados en el sitio web.</p>
            <p>Última actualización: Mayo 2025</p>
          </section>
        </div>
      </div>
    </div>
  );
};

export default PrivacyPolicy;