import React from 'react';

const Loader = ({ size = 'normal', className = '' }) => {
  const getSize = () => {
    switch (size) {
      case 'small':
        return { width: '20px', height: '20px' };
      case 'large':
        return { width: '100px', height: '100px' };
      default:
        return { width: '50px', height: '50px' };
    }
  };

  return (
    <div className={`d-flex justify-content-center align-items-center ${className}`}>
      <img 
        src="/images/charging/charging.gif" 
        alt="Cargando..." 
        style={getSize()}
      />
    </div>
  );
};

export default Loader; 