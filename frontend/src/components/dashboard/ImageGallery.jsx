import React from 'react';
import { useTheme } from '../../context/ThemeContext';

const ImageGallery = ({ images, onSelectImage, selectedImageUrl }) => {
  const { isDarkMode } = useTheme();

  return (
    <div className={`card ${isDarkMode ? 'bg-dark text-light' : ''} shadow-sm mb-3`}>
      <div className="card-header text-center">
        <h5 className="mb-0">Selecciona una imagen de perfil</h5>
      </div>
      <div className="card-body">
        <div className="row justify-content-center g-3">
          {images.map((image) => (
            <div key={image.id} className="col-4 col-md-3 col-lg-2">
              <div 
                className={`image-option ${selectedImageUrl === image.url ? 'selected' : ''}`}
                onClick={() => onSelectImage(image.url)}
                style={{
                  transition: 'all 0.3s ease',
                  transform: selectedImageUrl === image.url ? 'scale(1.05)' : 'scale(1)',
                  boxShadow: selectedImageUrl === image.url 
                    ? '0 0 15px rgba(13, 110, 253, 0.5)' 
                    : '0 0 5px rgba(0, 0, 0, 0.1)',
                  borderRadius: '10px',
                  overflow: 'hidden',
                  cursor: 'pointer',
                  padding: '5px',
                  backgroundColor: isDarkMode ? '#343a40' : '#f8f9fa'
                }}
              >
                <img 
                  src={image.url} 
                  alt="Imagen de perfil" 
                  className="img-fluid rounded"
                  style={{ 
                    width: '100%', 
                    height: '100px', 
                    objectFit: 'cover',
                    border: selectedImageUrl === image.url ? '2px solid #0d6efd' : '1px solid #dee2e6',
                  }}
                />
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default ImageGallery; 