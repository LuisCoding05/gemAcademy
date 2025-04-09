import PropTypes from 'prop-types';
import '../styles/Icon.css';

const Icon = ({ name, size = 24, color = 'currentColor', className = '' }) => {
  return (
    <svg 
      width={size} 
      height={size} 
      style={{ color }}
      className={`icon ${className}`}
    >
      <use xlinkHref={`/icons/symbol-defs.svg#icon-${name}`} />
    </svg>
  );
};

Icon.propTypes = {
  name: PropTypes.string.isRequired,
  size: PropTypes.number,
  color: PropTypes.string,
  className: PropTypes.string
};

export default Icon; 