import React from 'react';
import { Link } from 'react-router-dom';
import { useTheme } from '../../context/ThemeContext';
import { useCookies } from '../../context/CookieContext';
import Icon from '../Icon';

const CookieBanner = () => {
  const { isDarkMode } = useTheme();
  const { cookieConsent, acceptCookies } = useCookies();

  if (cookieConsent !== null) {
    return null;
  }

  return (
    <div 
      className={`fixed-bottom ${isDarkMode ? 'bg-dark' : 'bg-light'}`}
      style={{
        boxShadow: '0 -2px 10px rgba(0,0,0,0.1)',
        zIndex: 1050
      }}
    >
      <div className="container py-3">
        <div className="row align-items-center">
          <div className="col-md-8">
            <p className="mb-2 mb-md-0">
              <Icon name="cookie" size={20} className="me-2" />
              Utilizamos cookies esenciales para el funcionamiento del sitio. También utilizamos cookies de terceros (YouTube) para mejorar tu experiencia.
              <Link to="/privacy-policy" className="ms-2">Más información</Link>
            </p>
          </div>
          <div className="col-md-4 text-md-end">
            <button 
              className="btn btn-outline-secondary me-2" 
              onClick={() => acceptCookies(true)}
            >
              Solo esenciales
            </button>
            <button 
              className="btn btn-primary" 
              onClick={() => acceptCookies(false)}
            >
              Aceptar todas
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default CookieBanner;