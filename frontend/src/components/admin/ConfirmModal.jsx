import React, { useEffect } from 'react';
import { useTheme } from '../../context/ThemeContext';
import Icon from '../Icon';

const ConfirmModal = ({ show, title, message, onConfirm, onCancel }) => {
  const { isDarkMode } = useTheme();

  useEffect(() => {
    if (show) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = 'unset';
    }
    return () => {
      document.body.style.overflow = 'unset';
    };
  }, [show]);

  const handleBackdropClick = (e) => {
    if (e.target === e.currentTarget) {
      onCancel();
    }
  };

  if (!show) return null;

  return (
    <div 
      className="modal-wrapper"
      onClick={handleBackdropClick}
      style={{
        position: 'fixed',
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        backgroundColor: 'rgba(0, 0, 0, 0.7)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        zIndex: 1050,
        padding: '1rem'
      }}
    >
      <div 
        className={`modal-content ${isDarkMode ? 'bg-dark' : 'bg-light'}`}
        style={{
          width: '100%',
          maxWidth: '450px',
          margin: 'auto',
          borderRadius: '12px',
          boxShadow: isDarkMode ? 
            '0 8px 32px rgba(0, 0, 0, 0.4)' : 
            '0 8px 32px rgba(0, 0, 0, 0.1)',
          border: isDarkMode ? '1px solid rgba(255, 255, 255, 0.1)' : 'none',
          overflow: 'hidden'
        }}
      >
        <div 
          className={`modal-header ${isDarkMode ? 'border-secondary' : ''}`}
          style={{
            padding: '1.25rem',
            borderBottom: '1px solid',
            borderColor: isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between'
          }}
        >
          <h5 
            className={`modal-title ${isDarkMode ? 'text-light' : ''}`}
            style={{ 
              margin: 0,
              fontSize: '1.25rem',
              fontWeight: '600'
            }}
          >
            {title}
          </h5>
          <button 
            type="button" 
            className={`btn btn-icon ${isDarkMode ? 'text-light' : 'text-dark'}`}
            onClick={onCancel}
            style={{
              padding: '0.5rem',
              border: 'none',
              background: 'none',
              borderRadius: '50%',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              opacity: '0.7',
              transition: 'opacity 0.2s',
              cursor: 'pointer'
            }}
          >
            <Icon name="cross" size={20} />
          </button>
        </div>
        <div 
          className={`modal-body ${isDarkMode ? 'text-light' : ''}`}
          style={{
            padding: '1.5rem',
            fontSize: '1rem',
            lineHeight: '1.5'
          }}
        >
          <p className="mb-0">{message}</p>
        </div>
        <div 
          className={`modal-footer ${isDarkMode ? 'border-secondary' : ''}`}
          style={{
            padding: '1rem 1.25rem',
            borderTop: '1px solid',
            borderColor: isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)',
            display: 'flex',
            justifyContent: 'flex-end',
            gap: '0.5rem'
          }}
        >
          <button 
            type="button" 
            className={`btn btn-outline-secondary ${isDarkMode ? 'text-light border-secondary' : ''}`}
            onClick={onCancel}
            style={{
              minWidth: '100px'
            }}
          >
            Cancelar
          </button>
          <button 
            type="button" 
            className="btn btn-primary"
            onClick={onConfirm}
            style={{
              minWidth: '100px'
            }}
          >
            Confirmar
          </button>
        </div>
      </div>
    </div>
  );
};

export default ConfirmModal;