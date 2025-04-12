import React from 'react';

const Loader = ({ size = 'normal', className = '' }) => {
  const getSize = () => {
    switch (size) {
      case 'small':
        return { width: '20px', height: '20px', clipPath: 'circle(50% at 50% 50%)' };
      case 'large':
        return { width: '250px', height: '250px', clipPath: 'circle(50% at 50% 50%)' };
      default:
        return { width: '50px', height: '50px', clipPath: 'circle(50% at 50% 50%)' };
    }
  };

  return (
    <div className={`d-flex justify-content-center align-items-center ${className}`}>
      <img 
        src="https://res.cloudinary.com/dlgpvjulu/image/upload/v1744486297/charging_ndyjsq.gif" 
        alt="Cargando..." 
        style={getSize()}
      />
    </div>
  );
};

export default Loader; 